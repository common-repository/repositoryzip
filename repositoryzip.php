<?php
/*
Plugin Name: Repository Zip
Plugin URI: http://wordpress.org/extend/plugins/repositoryzip/
Author URI: http://flashpixx.de
Description: The plugin creates a zip file for downloading subversion or git repositorities
Author: flashpixx
Version: 0.14
 

#########################################################################
# GPL License                                                           #
#                                                                       #
# This file is part of the Wordpress Repository-Zip plugin.             #
# Copyright (c) 2012, Philipp Kraus, <philipp.kraus@flashpixx.de>       #
# This program is free software: you can redistribute it and/or modify  #
# it under the terms of the GNU General Public License as published by  #
# the Free Software Foundation, either version 3 of the License, or     #
# (at your option) any later version.                                   #
#                                                                       #
# This program is distributed in the hope that it will be useful,       #
# but WITHOUT ANY WARRANTY; without even the implied warranty of        #
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         #
# GNU General Public License for more details.                          #
#                                                                       #
# You should have received a copy of the GNU General Public License     #
# along with this program.  If not, see <http://www.gnu.org/licenses/>. #
#########################################################################
*/

namespace de\flashpixx\repositoryzip;
    
// ==== constant for developing with the correct path of the plugin ================================================================================
//define(__NAMESPACE__."\LOCALPLUGINFILE", __FILE__);
define(__NAMESPACE__."\LOCALPLUGINFILE", WP_PLUGIN_DIR."/repositoryzip/".basename(__FILE__));
// =================================================================================================================================================

    
    
// ==== plugin initialization ======================================================================================================================
@require_once("link.class.php");
@require_once("menu.class.php");
    
// stop direct call
if (preg_match("#" . basename(__FILE__) . "#", $_SERVER["PHP_SELF"])) { die("You are not allowed to call this page directly."); }

// translation
if (function_exists("load_plugin_textdomain"))
	load_plugin_textdomain("repositoryzip", false, dirname(plugin_basename(LOCALPLUGINFILE))."/lang");
// =================================================================================================================================================  
    
    

// ==== create Wordpress Hooks =====================================================================================================================
add_filter("the_content", "de\\flashpixx\\repositoryzip\\link::filter_svn");
add_filter("the_content", "de\\flashpixx\\repositoryzip\\link::filter_git");
add_action("init", "de\\flashpixx\\repositoryzip\\init");
register_activation_hook(LOCALPLUGINFILE, "de\\flashpixx\\repositoryzip\\install");
register_uninstall_hook(LOCALPLUGINFILE, "de\\flashpixx\\repositoryzip\\uninstall");
add_action("admin_menu", "de\\flashpixx\\repositoryzip\\menu::adminmenu");
add_action("admin_init", "de\\flashpixx\\repositoryzip\\menu::optionfields");
// =================================================================================================================================================

    

// ==== filter and other functions =================================================================================================================

/** installation function **/
function install()
{
    // extensions check
    if (!extension_loaded("zip"))
        trigger_error(__("required php zip extension not loaded", "repositoryzip"), E_USER_ERROR);
    
    
    // php.ini values check
    $lc = strtolower(ini_get("safe_mode"));
    if ( (!empty($lc)) && ($lc != "off") )
        trigger_error(__("safe mode must be disabled", "repositoryzip"), E_USER_ERROR);
    
    $ini = ini_get("disable_functions");
    if (!empty($ini))
    {
        $la = explode(" ", $ini);
        if (is_array($la))
            foreach( array("escapeshellarg", "proc_open", "proc_close", "stream_get_contents", "fclose", "fwrite", "svn_checkout", "svn_update", "tempnam", "sys_get_temp_dir") as $i)
                if (in_array($i, $la))
                    trigger_error( printf(__("required [%s] function, but is disallowed in your php settings (php.ini)", "repositoryzip"), $i), E_USER_ERROR);
    }
    
    $ini = ini_get("disable_classes");
    if (!empty($ini))
    {
        $la = explode(" ", $ini);
        if (is_array($la))
            foreach( array("ZipArchive") as $i)
                if (in_array($i, $la))
                    trigger_error( printf(__("required [%s] class, but is disallowed in your php settings (php.ini)", "repositoryzip"), $i), E_USER_ERROR);
    }
    
    
    // set options
    $lxConfig = get_option("fpx_repositoryzip_option");
    if (empty($lxConfig))
        update_option("fpx_repositoryzip_option",
            array(
                  "gitcmd"          =>  null,
                  "linktext"        =>  __("Repository Download", "repositoryzip"),
                  "cssclass"        =>  null,
                  "dir"             =>  array("trunk" => "trunk", "tag" => "tag", "branch" => "branch")
            )
        );
}
  
    
/** installation function **/
function uninstall()
{
	unregister_setting("fpx_repositoryzip_option", "fpx_repositoryzip_option");
	delete_option("fpx_repositoryzip_option");
}
    
    
/** we using sessions to communicate **/
function init()
{
    @session_start();
}
    

/** extract tag parameter **/    
function filterparameter($pa)
{
    // extract tag parameter
    $tagparam   = preg_split('/\G(?:"[^"]*"|\'[^\']*\'|[^"\'\s]+)*\K\s+/', $pa, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($tagparam))
        return "<strong>Repository Zip ".__("tag parameter can not be detected", "repositoryzip")."</strong>";
    
    // parse each parameter block
    $param		= array();
	foreach($tagparam as $val)
	{
		// remove double / single quotes
		$lcTag = str_replace("\"", null, $val);
		$lcTag = str_replace("'", null, $lcTag);
		
		// find first occurence of = and split the string
		$laTag = preg_split('/=/', $lcTag, 2);
		if (count($laTag) == 2)
			$param[trim($laTag[0])] = trim($laTag[1]);
	}
    
    // if url-key not exists, abort
	if (!array_key_exists("url", $param))
		return "<strong>Repository Zip ".__("URL parameter not exists", "repositoryzip")."</strong>";
    
    // set default parameter
    $lxConfig = get_option("fpx_repositoryzip_option");
    
    if (!array_key_exists("cssclass", $param))
        $param["cssclass"] = $lxConfig["cssclass"];
    if (!array_key_exists("linktext", $param))
        $param["linktext"] = $lxConfig["linktext"];
    if (!array_key_exists("downloadtext", $param))
        $param["downloadtext"] = $param["linktext"];
    
    // set default reposity data
    if (!array_key_exists("name", $param))
        $param["name"] = null;
    if (!array_key_exists("version", $param))
        $param["version"] = null;
    if (!array_key_exists("type", $param))
        $param["type"] = "trunk";
    else    
        if ( (strtolower($param["type"]) == "branch") || (strtolower($param["type"]) == "tag") )
            $param["type"] = strtolower($param["type"]);
        else
            $param["type"] = "trunk";

    
    // names of the directory for tag / trunk / branch
    $param["dir"] = $lxConfig["dir"];
    if (array_key_exists("trunkdir", $param))
        $param["dir"]["trunk"] = $param["trunkdir"];

    if (array_key_exists("tagdir", $param))
        $param["dir"]["tag"] = $param["tagdir"];

    if (array_key_exists("branchdir", $param))
        $param["dir"]["branch"] = $param["branchdir"];

    unset($param["trunkdir"]);
    unset($param["tagdir"]);
    unset($param["branchdir"]);
    
    // create hash to the session
    $param["gitcmd"]          = $lxConfig["gitcmd"];
    $param["hash"]            = md5($param["url"].$param["type"].$param["name"].$param["version"]);
    $_SESSION[$param["hash"]] = $param;
    
    return $param;
}

// =================================================================================================================================================

?>
