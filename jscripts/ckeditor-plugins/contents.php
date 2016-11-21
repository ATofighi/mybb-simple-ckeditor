<?php
define("IN_MYBB", 1);
define("NO_ONLINE", 1);
require_once "../../global.php";

header("Expires: ".gmdate("D, d M Y H:i:s", time()+3600)." GMT");
header("Cache-Control: cache");
header("Pragma: cache");
header("Content-type: text/css");

?>
/*
Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.md or http://ckeditor.com/license
*/

body
{
	/* Font */
	font-family: Tahoma, sans-serif, Arial, Verdana, "Trebuchet MS";
	font-size: 12px;

	/* Text color */
	color: #333;

	/* Remove the background color to make it transparent */
	background-color: #fff;

	margin: 20px;
}

.cke_editable
{
	font-size: 13px;
	line-height: 1.6;
}

blockquote
{
	padding: 36px 5px 5px;
	border: 1px solid #ccc;
	border-radius: 4px;
	position: relative;
}

blockquote:before, blockquote cite {
	position: absolute;
	top: 5px;
	left: 5px;
	right: 5px;
	padding: 0 0 5px;
	border-bottom: 1px solid #ccc;
	display: block;
	content: '<?php echo addslashes($lang->quote); ?>';
	font-weight: bold;
	background: #fff;
}

blockquote cite:after {
	content: '<?php echo addslashes($lang->wrote); ?>';
}

a
{
	color: #0782C1;
}

ol,ul,dl
{
	/* IE7: reset rtl list margin. (#7334) */
	*margin-right: 0px;
	/* preserved spaces for list items with text direction other than the list. (#6249,#8049)*/
	padding: 0 40px;
}

h1,h2,h3,h4,h5,h6
{
	font-weight: normal;
	line-height: 1.2;
}

hr
{
	border: 0px;
	border-top: 1px solid #ccc;
}

img.right
{
	border: 1px solid #ccc;
	float: right;
	margin-left: 15px;
	padding: 5px;
}

img.left
{
	border: 1px solid #ccc;
	float: left;
	margin-right: 15px;
	padding: 5px;
}

pre
{
	white-space: pre-wrap; /* CSS 2.1 */
	word-wrap: break-word; /* IE7 */
	-moz-tab-size: 4;
	-o-tab-size: 4;
	-webkit-tab-size: 4;
	tab-size: 4;
}

.marker
{
	background-color: Yellow;
}

span[lang]
{
	font-style: italic;
}

figure
{
	text-align: center;
	border: solid 1px #ccc;
	border-radius: 2px;
	background: rgba(0,0,0,0.05);
	padding: 10px;
	margin: 10px 20px;
	display: inline-block;
}

figure > figcaption
{
	text-align: center;
	display: block; /* For IE8 */
}

a > img {
	padding: 1px;
	margin: 1px;
	border: none;
	outline: 1px solid #0782C1;
}
