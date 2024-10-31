=== Plugin Name ===
Contributors: flashpixx
Tags: git, svn, subversion, download, zip, revision, repository
Requires at least: 2.7
Tested up to: 3.4.2
Stable tag: 0.14
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=WCRMFYTNCJRAU
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.en.html


With this plugin a zip download link of a subversion or git repository can be created within blog articles and pages 


== Description ==

The plugin creates zip download links within articles and pages of a subversion or git repository. On each call the subversion revision number / git tag, link text, css name and download
name can be set, so that each link points to different repositority parts.

= Features =

* remote access to Git or Subversion repository
* local caching for the repository data
* automatic update to the head revision
* extension access only need on access
* free configuration of tags, branches, trunk and revision / commit access



== Installation ==

1. Upload the folder to the "/wp-content/plugins/" directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Take a look to the plugin setting page and set your initialization data



== Requirements ==

* Wordpress 3.2 or newer
* PHP 5.3.0 or newer 
* <a href="http://de3.php.net/manual/en/book.zip.php">PHP Zip extension</a>

for SVN access
* <a href="http://de3.php.net/manual/en/book.svn.php">PHP SVN extension</a>

for Git access
* Git client
* PHP must execute shell commands (no safe mode)



== Shortcode ==

Shotcut for SVN access
<pre>[svnzip url="url-to-your-svn"]</pre>
The SVN tag uses three optional flags (the default values are set within the plugin settings):
<ul>
<li>"trunkdir" the name of the trunk directory</li>
<li>"branchdir" the name of the branch directory</li>
<li>"tagdir" the name of the tag directory</li>
</ul>

Shotcut for Git access
<pre>[gitzip url="url-to-your-svn"]</pre>

Parameters for both commands are (all parameters are optional):
<ul>
<li>"version" defines a special revision (SVN) / commit (Git), which is used. If the parameter is not set, the latest revision (head) is used</li>
<li>"downloadtext" filename for the downloaded file (if not is set, the linktext is used)</li>
<li>"linktext" text of the link (default is set within the plugin settings)</li>
<li>"cssclass" sets the CSS class of the link (default is set within the plugin settings)</li>
<li>"type" enum value trunk | branch | tag for setting the repository part (default is trunk, for Git trunk is also used and it is applied to the master)</li>
<li>"name" the value for tag- or branchname (on trunk it is ignored / empty)</li>
</ul>



== Changelog == 

= 0.14 =
* extend clean-up call
* optimize Git download code
* Git pull call changed with git-dir & work-tree flags

= 0.13 =
* clean-up expand for download files
* git pull & checkout with force flag 

= 0.12 =
* clean-up call for temporary data

= 0.11 =
* fixing Git pull path problem

= 0.1 =

* first version with the base functions for using Git and SVN support