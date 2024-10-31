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

/** function that are used in the download scripts **/
    

/** read session data
 * @return read array data
 **/
function initSession()
{
    @session_start();
    @set_time_limit(3600);
    
    if (!isset($_GET["h"]))
        die("no parameters are set");
    $lcHash 	= $_GET["h"];
    
    if (!isset($_SESSION[$lcHash]))
        die("session data not found");
    
    $data         = $_SESSION[$lcHash];
    $data["hash"] = $lcHash;
    if (!is_array($data))
        die("session data are not right data type");
    if (!array_key_exists("url", $data))
        die("reporisitory url not exists");
    if (!array_key_exists("downloadtext", $data))
        die("name of the downloaded file not exists");
    if (!array_key_exists("version", $data))
        die("version not set");
    if (!array_key_exists("type", $data))
        die("type not set");
    if (!array_key_exists("name", $data))
        die("name is not set");
    
    return $data;
}

    
/** function that add files into a zip 
 * @param $dir directory that should be added to the zip
 * @param $root root directory for the zip
 * @param $zip zip object
 **/
function addFiles2Zip($dir, $root, $zip)
{
    if ($handle = @opendir($dir)) {
        while (false !== ($file = readdir($handle))) { 
            if (($file == ".") ||( $file == ".."))
                continue;
            
            if (is_file($dir."/".$file)) {
                $lcFile = $dir."/".$file;
                $zip->addFile($lcFile, str_replace($root, null, $lcFile));
            }
            
            if ((is_dir($dir."/".$file)) && ($file[0] !== "."))
                addFiles2Zip($dir."/".$file, $root, $zip); 
        } 
        @closedir($handle); 
    } 
}
    
/** runs a command
 * @param $cmd command that should run
 * @param $args array with parameters
 * @param $stdin std-in parameter
 * @param $stdout std-out parameter
 * @param $stderr std-err parameter
 * @param $env environment variables
 * @return bool true or false for correct running
 **/
function runcmd($cmd, $args=null, $stdin = null, &$stdout = null, &$stderr = null, $env=null)
{
    if (empty($args))
        $args = array();
    if (empty($env))
        $env = array();
    
    foreach($args as &$val)
        $val = escapeshellarg($val);
    $lc = escapeshellarg($cmd)." ".implode(" ", $args);

    // in/out/err pipes
    $descriptorspec = array(
        0 => array("pipe", "r"),  // STDIN as pipe
        1 => array("pipe", "w"),  // STDOUT as pipe
        2 => array("pipe", "w")   // STDERR as pipe
    );

    // open command
    $process = proc_open($lc, $descriptorspec, $pipes, null, $env);
    if (is_resource($process)) {
        
        if (!empty($stdin))
            fwrite($pipes[0], $stdin);
            
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        // close pipes and set log data
        foreach($pipes as &$p)
            fclose($p);

        // remove "nothing to do" on git
        $ret = proc_close($process) == 0;
        if  (strripos($stderr, "not something we can merge") !== false)
        {
            $stderr = null;
            $ret    = 1;
        }
            
        // on errors we write them into the php log
        if ( (!empty($stderr)) && (!$ret) )
            error_log(__FILE__." - ".$lc." : ".implode(" ", explode("\n",$stderr)), 0);
        return $ret; 
    }

    return false;
}
    
    
/** sends the file via browser
 * @param $pcFile filename with path
 * @param $pcDownloadName download that is send to the browser
 **/
function sendBinaryFile($pcFile, $pcDownloadName)
{
    if (!file_exists($pcFile))
        die("file error");
	
    # run download
    header("Content-Description: File Transfer");
    header("Content-Transfer-Encoding: binary");
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"".$pcDownloadName."\"");
    header("Content-Type: application/force-download");
    header("Content-Type: application/download");
    
    //IE specialize
    header("Cache-Control: public, must-revalidate");
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: private");
    
    header("Content-Length: ".filesize($pcFile));
    
    $fd = @fopen($pcFile, "rb");
    while(!feof($fd))
        echo @fread($fd, 32768);
    @fclose ($fd);    
}

?>
