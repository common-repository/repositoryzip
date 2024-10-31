<?php
/** 
 * #########################################################################
 * # GPL License                                                           #
 * #                                                                       #
 * # This file is part of the Wordpress Repository-Zip plugin.             #
 * # Copyright (c) 2012, Philipp Kraus, <philipp.kraus@flashpixx.de>       #
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
    
@require_once("transfer.php");


// ==== read get parameter and decode =======================================
$gitdata = initSession();
if (!array_key_exists("gitcmd", $gitdata))
    die("git executable path not found");
if ( (empty($gitdata["gitcmd"])) || (!is_file($gitdata["gitcmd"])) || (!is_executable($gitdata["gitcmd"])) )
    die("git executable not accessible");
// ==========================================================================

    
// create path and filenames
$lcZipName  	= tempnam(sys_get_temp_dir(), "gitzip_".$gitdata["hash"]);
$lcGitWorkDir  	= sys_get_temp_dir()."/gitzip_".$gitdata["hash"];
$lcGitDir       = $lcGitWorkDir."/.git";

    
// test if the directory exist, we do a git pull otherwise we do a clone
$param = array();
if (is_dir($lcGitWorkDir))
{
    
    array_push($param, "--git-dir=".$lcGitDir);
    array_push($param, "--work-tree=".$lcGitWorkDir);
    array_push($param, "pull");
    
    if (!runcmd($gitdata["gitcmd"], $param))
        die("error on pulling git repository");  
    
} else {
    
    array_push($param, "clone");
    array_push($param, $gitdata["url"]);
    array_push($param, $lcGitWorkDir);
     
    // set branch / tag
    if ( ($gitdata["type"] == "tag") || ($gitdata["type"] == "branch") )
        array_push($param, "-b".$gitdata["name"]);
    
    if (!runcmd($gitdata["gitcmd"], $param))
        die("error on cloning git repository");
    
    if (!empty($gitdata["version"])) 
    {
        $param = array();
        array_push($param, "--git-dir=".$lcGitDir);
        array_push($param, "--work-tree=".$lcGitWorkDir);
        array_push($param, "checkout");
        array_push($param, $gitdata["version"]);
        
        if (!runcmd($gitdata["gitcmd"], $param))
            die("commit can not be checked out");
    }
    
}
    
    

// create zip file, add the files and send the file, after sender delete zip file
$zip = new ZipArchive();
if ($zip->open($lcZipName, ZIPARCHIVE::CREATE) !== true)
    die("zip file can not be created");
addFiles2Zip($lcGitWorkDir, $lcGitWorkDir, $zip);
$zip->close();
sendBinaryFile($lcZipName, $gitdata["downloadtext"].".zip");
@unlink($lcZipName);
    
?>
