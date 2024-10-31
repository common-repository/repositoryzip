<?php
/** 
 * #########################################################################
 * # GPL License                                                           #
 * #                                                                       #
 * # This file is part of the Wordpress Repository-Zip plugin.             #
 * # Copyright (c) 2010-2012, Philipp Kraus, <philipp.kraus@flashpixx.de>  #
 * # This program is free software: you can redistribute it and/or modify  #
 * # it under the terms of the GNU General Public License as published by  #
 * # the Free Software Foundation, either version 3 of the License, or     #
 * # (at your option) any later version.                                   #
 * #                                                                       #
 * # This program is distributed in the hope that it will be useful,       #
 * # but WITHOUT ANY WARRANTY; without even the implied warranty of        #
 * # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         #
 * # GNU General Public License for more details.                          #
 * #                                                                       #
 * # You should have received a copy of the GNU General Public License     #
 * # along with this program.  If not, see <http://www.gnu.org/licenses/>. #
 * #########################################################################
 **/

namespace de\flashpixx\repositoryzip;


/** class for creating all visual options **/
class menu {
    
    /** creates admin menu **/
    static function adminmenu() {
        add_options_page("Repository Zip", "Repository Zip", "administrator", "fpx_repositoryzip_option", get_class()."::renderMain");
    }
    
    
    /** shows the admin panel with actions **/
    static function optionfields() {
        register_setting("repositoryzip_option", "fpx_repositoryzip_option", get_class()."::validate");
        
        add_settings_section("repositoryzip_option",  __("Main Options", "repositoryzip"),              get_class()."::render_mainsection",           "repositoryzip_optionglobal");
        add_settings_field("text_linktext",           __("Link Text", "repositoryzip")." (linktext)",   get_class()."::render_linktext",              "repositoryzip_optionglobal",      "repositoryzip_option");
        add_settings_field("text_cssclass",           __("CSS class", "repositoryzip")." (cssclass)",   get_class()."::render_cssclass",              "repositoryzip_optionglobal",      "repositoryzip_option");
        add_settings_field("text_clean",              __("remove all temporary data", "repositoryzip"), get_class()."::render_clean",                 "repositoryzip_optionglobal",      "repositoryzip_option");
        
        add_settings_section("repositoryzip_option",  __("SVN Options", "repositoryzip"),                          get_class()."::render_svnsection",    "repositoryzip_optionsvn");
        add_settings_field("text_svntrunk",           __("Trunk Directory Name", "repositoryzip")." (trunkdir)",   get_class()."::render_svntrunk",      "repositoryzip_optionsvn",      "repositoryzip_option");
        add_settings_field("text_svntag",             __("Tag Directory Name", "repositoryzip")." (tagdir)",       get_class()."::render_svntag",        "repositoryzip_optionsvn",      "repositoryzip_option");
        add_settings_field("text_svnbranch",          __("Branch Directory Name", "repositoryzip")." (branchdir)", get_class()."::render_svnbranch",     "repositoryzip_optionsvn",      "repositoryzip_option");
        
        add_settings_section("repositoryzip_option",  __("Git Options", "repositoryzip"),   get_class()."::render_gitsection",    "repositoryzip_optiongit");
        add_settings_field("text_gitcommand",         __("Git Command", "repositoryzip"),   get_class()."::render_gitcmd",        "repositoryzip_optiongit",      "repositoryzip_option");
    }
    
    
    /** validate the form input 
     * @param $pa form data
     * @return validated data
     **/
	static function validate($pa) {
		$options = get_option("fpx_repositoryzip_option");
        
        $options["linktext"]      = $pa["linktext"];
        $options["cssclass"]      = $pa["cssclass"];
        
        $options["dir"]["trunk"]  = $pa["trunkdir"];
        $options["dir"]["tag"]    = $pa["tagdir"];
        $options["dir"]["branch"] = $pa["branchdir"];
        
        $options["gitcmd"]        = $pa["gitcmd"];
        
        // remove cached data
        if ( (isset($pa["clean"])) && (!empty($pa["clean"])) )            
        {
            if ($handle = opendir(sys_get_temp_dir()))
                while (false !== ($item = readdir($handle))) {
                    $path = sys_get_temp_dir()."/".$item;
                    if ( (strpos($path, "svnzip_") !== false) || (strpos($path, "gitzip_") !== false) )
                    {
                        self::rmdirrecusive($path);
                        if (is_file($path))
                            @unlink($path);
                    }
                }
            closedir($handle);
        }
        
        return $options;
    }
    
    
    /** recursive directory delete function
     * @param $path path of the directory, that should be deleted
     **/
    private static function rmdirrecusive($path)
    {
        if (!is_dir($path))
            return;
        
        if ($handle = @opendir($path))
        {
            while (false !== ($item = @readdir($handle))) {
                if ( ($item == ".") || ($item == "..") )
                    continue;
                
                $name = $path."/".$item;
                if (is_file($name))
                    @unlink($name);
                if (is_dir($name))
                    self::rmdirrecusive($name);
            }
            @closedir($handle);
            @rmdir($path);
        }
    }
        
    
    /** render the option page **/
	static function renderMain() {
		echo "<div class=\"wrap\"><h2>Repository Zip ".__("Configuration", "repositoryzip")."</h2>\n";
        echo "<p>".__("The following options may also be passed as parameters to the tag to overwrite default settings. In parenthesis the parameter name is specified.", "repositoryzip")."</p>";
		echo "<form method=\"post\" action=\"options.php\">";
		settings_fields("repositoryzip_option");
		do_settings_sections("repositoryzip_optionglobal");
		do_settings_sections("repositoryzip_optionsvn");
		do_settings_sections("repositoryzip_optiongit");
        
		echo "<p class=\"submit\"><input type=\"submit\" name=\"submit\" class=\"button-primary\" value=\"".__("Save Changes")."\"/></p>\n";
		echo "</form></div>\n";
	}
    
    
    
    static function render_mainsection() {
        echo "<p>".__("The main section is used for linktext and CSS class name.", "repositoryzip")."</p>";
    }
    
    static function render_linktext() {
        $options = get_option("fpx_repositoryzip_option");
        echo "<input name=\"fpx_repositoryzip_option[linktext]\" size=\"30\" type=\"text\" value=\"".$options["linktext"]."\" />";
    }
    
    static function render_cssclass() {
        $options = get_option("fpx_repositoryzip_option");
        echo "<input name=\"fpx_repositoryzip_option[cssclass]\" size=\"30\" type=\"text\" value=\"".$options["cssclass"]."\" />";
    }
    
    static function render_clean() {
        echo "<input name=\"fpx_repositoryzip_option[clean]\" type=\"checkbox\" value=\"1\" />";
    }
    
    
    
    static function render_svnsection() {
        echo "<p>".__("In this section the directory names of for the Subversion pathes are set. Use the tag [svnzip] for accessing the SVN from your blog data.", "repositoryzip");
        if (!extension_loaded("svn"))
            echo " <strong>".__("The SVN PHP extension is not loaded. This extension is needed for the SVN access, please install the extension", "repositoryzip")." (<a href=\"http://de3.php.net/manual/en/book.svn.php\">PHP Subversion</a>)</strong>"; 
        echo "</p>";
    }
    
    static function render_svntrunk() {
        $options = get_option("fpx_repositoryzip_option");
        echo "<input name=\"fpx_repositoryzip_option[trunkdir]\" size=\"30\" type=\"text\" value=\"".$options["dir"]["trunk"]."\" />";
    }
    
    static function render_svnbranch() {
        $options = get_option("fpx_repositoryzip_option");
        echo "<input name=\"fpx_repositoryzip_option[branchdir]\" size=\"30\" type=\"text\" value=\"".$options["dir"]["branch"]."\" />";
    }
    
    static function render_svntag() {
        $options = get_option("fpx_repositoryzip_option");
        echo "<input name=\"fpx_repositoryzip_option[tagdir]\" size=\"30\" type=\"text\" value=\"".$options["dir"]["tag"]."\" />";
    }
    
    
    
    static function render_gitsection() {
        $options = get_option("fpx_repositoryzip_option");
        
        echo "<p>".__("In this section the command line executable for Git access can be set. Use the tag [gitzip] for accessing the Git repository from your blog data.", "repositoryzip");
        if ( (empty($options["gitcmd"])) || (!is_file($options["gitcmd"])) || (!is_executable($options["gitcmd"])) )
            echo " <strong>".__("The Git executable is not found or executable.", "repositoryzip")."</strong>"; 
        echo "</p>";
    }
    
    static function render_gitcmd() {
        $options = get_option("fpx_repositoryzip_option");
        echo "<input name=\"fpx_repositoryzip_option[gitcmd]\" size=\"30\" type=\"text\" value=\"".$options["gitcmd"]."\" />";
    }
}
        
?>