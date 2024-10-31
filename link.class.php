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

/** class for filter and link structure **/
class link
{
    
    /** content filter function for set the git tags
     * @param $pcContent Content
     **/
    static function filter_git($pcContent)
    {
        $lxConfig = get_option("fpx_repositoryzip_option");
        if ( (!empty($lxConfig["gitcmd"])) && (is_file($lxConfig["gitcmd"])) && (is_executable($lxConfig["gitcmd"])) )
            return preg_replace_callback("!\[gitzip(.*)\]!isU", "de\\flashpixx\\repositoryzip\\link::action_git", $pcContent);
        
        return $pcContent;
    }
    
    
    /** create action and the href tag
     * @param $pa Array with founded regular expressions
     * @return replace href tag or null on error
     **/
    function action_git($pa)
    {
        if ( (empty($pa)) || (count($pa) != 2) )
            return null;
        $param = filterparameter($pa[1]);
        
        // create href tag
        $lcReturn  = "<a href=\"".plugins_url("gitdownload.php?h=".$param["hash"], LOCALPLUGINFILE)."\"";
        if (!empty($param["cssclass"]))
            $lcReturn .= " class=\"".$param["cssclass"]."\"";
        $lcReturn .= ">".$param["linktext"]."</a>";
        
        return $lcReturn;    
    }
    
    
    
    /** content filter function for set the svn tags
     * @param $pcContent Content
     **/
    static function filter_svn($pcContent)
    {
        if (extension_loaded("svn"))
            return preg_replace_callback("!\[svnzip(.*)\]!isU", "de\\flashpixx\\repositoryzip\\link::action_svn", $pcContent);
        
        return $pcContent;
    }
    
    
    /** create action and the href tag
     * @param $pa Array with founded regular expressions
     * @return replace href tag or null on error
     **/
    static function action_svn($pa)
    {
        if ( (empty($pa)) || (count($pa) != 2) )
            return null;
        $param = filterparameter($pa[1]);
        
        
        // create href tag
        $lcReturn  = "<a href=\"".plugins_url("svndownload.php?h=".$param["hash"], LOCALPLUGINFILE)."\"";
        if (!empty($param["cssclass"]))
            $lcReturn .= " class=\"".$param["cssclass"]."\"";
        $lcReturn .= ">".$param["linktext"]."</a>";
        
        return $lcReturn;
    }
    
}

?>
