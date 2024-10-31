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
$svndata = initSession();
// ==========================================================================


// create path and filenames
$lcZipName  	= tempnam(sys_get_temp_dir(), "svnzip_".$svndata["hash"]);
$lcSVNName  	= sys_get_temp_dir()."/svnzip_".$svndata["hash"];
    
    
// create download URL (with branch, tag & trunk data)
$lcURL = $svndata["url"]."/".urlencode( $svndata["dir"][$svndata["type"]] );
if ( ($svndata["type"] == "branch") || ($svndata["type"] == "tag") )
    $lcURL .= "/".urlencode($svndata["name"]);

    
// test if the directory exist, we do a svn update otherwise we do a checkout
$getdata = false;
if (is_dir($lcSVNName))
    $getdata = svn_update($lcSVNName, empty($svndata["version"]) ? SVN_REVISION_HEAD : intval($svndata["version"]), true );
else {
    @mkdir($lcSVNName);
    $getdata = svn_checkout($lcURL, $lcSVNName, empty($svndata["version"]) ? SVN_REVISION_HEAD : intval($svndata["version"]) );
}

if (!$getdata)
    die("error on checkout / update svn repository");
    
    
    
// create zip file, add the files and send the file, after sender delete zip file
$zip = new ZipArchive();
if ($zip->open($lcZipName, ZIPARCHIVE::CREATE) !== true)
	die("zip error");
addFiles2Zip($lcSVNName, $lcSVNName, $zip);
$zip->close();
sendBinaryFile($lcZipName, $svndata["downloadtext"].".zip");
@unlink($lcZipName);
?>
