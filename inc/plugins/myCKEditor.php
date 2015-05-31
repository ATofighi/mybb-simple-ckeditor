<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 *
 */

// Make sure we can't access this file directly from the browser.
if(!defined('IN_MYBB'))
{
	die('This file cannot be accessed directly.');
}
	

function myCKEditor_info()
{

	return array(
		'name'			=> 'My CKEditor',
		'description'	=> '',
		'website'		=> 'http://my-bb.ir',
		'author'		=> 'ATofighi',
		'authorsite'	=> 'http://my-bb.ir',
		'version'		=> '1.0.0',
		'compatibility'	=> '18*',
		'codename'		=> 'myckeditor'
	);
}


function myCKEditor_activate()
{
    require_once MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("showthread_quickreply", "#" . preg_quote('{$codebuttons}') . "#i", '', 0);
    find_replace_templatesets("showthread_quickreply", "#" . preg_quote('{$smilieinserter}') . "#i", '', 0);
    find_replace_templatesets("showthread_quickreply", "#" . preg_quote('</textarea>') . "#i", '</textarea>{$codebuttons}');
    find_replace_templatesets("showthread_quickreply", "#" . preg_quote('{$option_signature}') . "#i", '{$smilieinserter}{$option_signature}');
}

function myCKEditor_deactivate()
{
    global $mybb, $db, $lang, $PL;
    $lang->load('ckeditor');
    $PL or require_once PLUGINLIBRARY;
    require_once MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("showthread_quickreply", "#" . preg_quote('{$codebuttons}') . "#i", '', 0);
    find_replace_templatesets("showthread_quickreply", "#" . preg_quote('{$smilieinserter}') . "#i", '', 0);
}



function myckeditor($bind="message", $smilies = true) {
	global $cache, $mybb;
	$smilie_cache = $cache->read("smilies");
	$smilieArr = $smilieCodes = [];
	usort($smilie_cache, function ($a, $b)
	{
		if ($a['find'] == $b['find']) {
			return 0;
		}
		return ($a['find'] < $b['find']) ? 1 : -1;
	});

	foreach($smilie_cache as $smilie) {
		$smilieArr[$smilie['find']] = $smilie['image'];
		$smilieCodes[] = $smilie['find'];
	}
	$jsonSimiles =  json_encode($smilieArr);
	$jsonSimileCodes =  json_encode($smilieCodes);
	$codebuttons = <<<CODE
		<script src="{$mybb->settings['bburl']}/myCKEditor/ckeditor/ckeditor.js" type="text/javascript"></script>
		<style>
		.cke_source  {
			padding: 20px!important;
			box-sizing: border-box!important;
			font-family: Tahoma, sans-serif, Arial, Verdana, "Trebuchet MS"!important;
			font-size: 13px!important;
			line-height: 1.6!important;
			text-align: right!important;
			direction: rtl!important;
		}
		
		.cke_reset_all, .cke_reset_all * {
			font: normal normal normal 12px Tahoma,Arial,Helvetica,Verdana,Sans-Serif!important;
		}
		</style>
		<script type="text/javascript">
			var SmilieCodes = {$jsonSimileCodes};
			var Smilies = {$jsonSimiles};

			var myCKEditor = CKEDITOR.replace( '{$bind}', {
				customConfig: '../config.js'
			} );
			MyBBEditor = {
				insertText: function(msg) {
					var value = myCKEditor.getData();
					value += msg;
					myCKEditor.setData(value);
				},
				getData: function() {
					return myCKEditor.getData();
				},
				focus: function () {
					return myCKEditor.focus();
				}
			};
			
			if(typeof Thread == 'object') {
				Thread.multiQuotedLoaded = function(request)
				{
					var json = $.parseJSON(request.responseText);
					if(typeof json == 'object')
					{
						if(json.hasOwnProperty("errors"))
						{
							$.each(json.errors, function(i, message)
							{
								$.jGrowl(lang.post_fetch_error + ' ' + message);
							});
							return false;
						}
					}

					if(MyBBEditor)
					{
						MyBBEditor.insertText(json.message);
						MyBBEditor.focus();
					}
					else if(typeof $('textarea').sceditor != 'undefined')
					{
						$('textarea').sceditor('instance').insert(json.message);
					}
					else
					{
						var id = $('#message');
						if(id.value)
						{
							id.value += "\\n";
						}
						id.val(id.val() + json.message);
					}

					Thread.clearMultiQuoted();
					$('#quickreply_multiquote').hide();
					$('#quoted_ids').val('all');

					$('#message').focus();
				};
				
				Thread.quickReply = function(e)
				{
					e.stopPropagation();

					if(this.quick_replying)
					{
						return false;
					}

					this.quick_replying = 1;
					myCKEditor.updateElement();
					var post_body = $('#quick_reply_form').serialize();
					
					// Spinner!
					var qreply_spinner = $('#quickreply_spinner');
					qreply_spinner.show();
					$.ajax(
					{
						url: 'newreply.php?ajax=1',
						type: 'post',
						data: post_body,
						dataType: 'html',
						complete: function (request, status)
						{
							Thread.quickReplyDone(request, status);
							
							// Get rid of spinner
							qreply_spinner.hide();
						}
					});

					return false;
				}
				
				Thread.quickReplyDone = function(request, status)
				{
					this.quick_replying = 0;

					var json = $.parseJSON(request.responseText);
					if(typeof json == 'object')
					{
						if(json.hasOwnProperty("errors"))
						{
							$(".jGrowl").jGrowl("close");

							$.each(json.errors, function(i, message)
							{
								$.jGrowl(lang.quick_reply_post_error + ' ' + message);
							});
							$('#quickreply_spinner').hide();
						}
					}

					if($('#captcha_trow').length)
					{
						cap = json.data.match(/^<captcha>([0-9a-zA-Z]+)(\|([0-9a-zA-Z]+)|)<\/captcha>/);
						if(cap)
						{
							json.data = json.data.replace(/^<captcha>(.*)<\/captcha>/, '');

							if(cap[1] == "reload")
							{
								Recaptcha.reload();
							}
							else if($("#captcha_img").length)
							{
								if(cap[1])
								{
									imghash = cap[1];
									$('#imagehash').val(imghash);
									if(cap[3])
									{
										$('#imagestring').attr('type', 'hidden').val(cap[3]);
										// hide the captcha
										$('#captcha_trow').hide();
									}
									else
									{
										$('#captcha_img').attr('src', "captcha.php?action=regimage&imagehash="+imghash);
										$('#imagestring').attr('type', 'text').val('');
										$('#captcha_trow').show();
									}
								}
							}
						}
					}
					
					if(json.hasOwnProperty("errors"))
						return false;

					if(json.data.match(/id="post_([0-9]+)"/))
					{
						var pid = json.data.match(/id="post_([0-9]+)"/)[1];
						var post = document.createElement("div");

						$('#posts').append(json.data);
						
						if (typeof inlineModeration != "undefined") // Guests don't have this object defined
							$("#inlinemod_" + pid).on('change', inlineModeration.checkItem);
							
						Thread.quickEdit("#pid_" + pid);

						// Eval javascript
						$(json.data).filter("script").each(function(e) {
							eval($(this).text());
						});

						$('#quick_reply_form')[0].reset();
						myCKEditor.setData('');

						var lastpid = $('#lastpid');
						if(lastpid.length)
						{
							lastpid.val(pid);
						}
					}
					else
					{
						// Eval javascript
						$(json.data).filter("script").each(function(e) {
							eval($(this).text());
						});
					}

					$(".jGrowl").jGrowl("close");
				}
			}
		</script>
CODE;
	//print_r($smilie_cache);
	return $codebuttons;
}

$plugins->add_hook('global_end', 'setCKeditor');
function setCKeditor() {
	global $templates;
	$templates->cache['codebuttons'] = myckeditor();
}


$plugins->add_hook('private_read_end','ckeditorPMQuickreply');
function ckeditorPMQuickreply(){
	global $mybb, $quickreply;
	if($quickreply) {
		$quickreply .= myckeditor("message");
	}
}

$plugins->add_hook('showthread_start','ckeditorQuickreply');
function ckeditorQuickreply(){
	global $mybb, $forumpermissions, $thread, $fid, $forum;
	global $codebuttons, $smilieinserter;
	if(($forumpermissions['canpostreplys'] != 0 && $mybb->user['suspendposting'] != 1 && ($thread['closed'] != 1 || is_moderator($fid)) && $mybb->settings['quickreply'] != 0 && $mybb->user['showquickreply'] != '0' && $forum['open'] != 0) && ($mybb->settings['bbcodeinserter'] != 0 && $forum['allowmycode'] != 0 && (!$mybb->user['uid'] || $mybb->user['showcodebuttons'] != 0))) {
		$codebuttons = myckeditor("message");
		if($forum['allowsmilies'] != 0)
		{
			$smilieinserter = build_clickable_smilies();
		}
	}
}


$plugins->add_hook('parse_message','ckeditorParser');
function ckeditorParser($m){
	$standard_mycode = $mycodes = array();

	$standard_mycode['sub']['regex'] = "#\[sub\](.*?)\[/sub\]#si";
	$standard_mycode['sub']['replacement'] = "<sub>$1</sub>";

	$standard_mycode['sup']['regex'] = "#\[sup\](.*?)\[/sup\]#si";
	$standard_mycode['sup']['replacement'] = "<sup>$1</sup>";
	$standard_mycode['bidiltr']['regex'] = "#\[dir=ltr\](.*?)\[/dir\]#si";
	$standard_mycode['bidiltr']['replacement'] = "<div dir=\"ltr\" style=\"direction: ltr;text-align:left;\">$1</div>";
	$standard_mycode['bidirtl']['regex'] = "#\[dir=rtl\](.*?)\[/dir\]#si";
	$standard_mycode['bidirtl']['replacement'] = "<div dir=\"rtl\" style=\"direction: rtl;text-align:right;\">$1</div>";
	foreach($standard_mycode as $code)
	{
		$mycodes['find'][] = $code['regex'];
		$mycodes['replacement'][] = $code['replacement'];
	}
	$m = preg_replace($mycodes['find'], $mycodes['replacement'], $m);

	// Table:
	while(preg_match("#\[table\](.*?)\[/table\]#si", $m, $m1))
	{
		while(preg_match("#\[tr\](.*?)\[/tr\]#si", $m1[1], $m2))
		{
			$m2[1] = preg_replace("#\[td\](.*?)\[/td\]#si", '<td style="border: 1px dashed #999;padding: 3px 5px;vertical-align: top;min-height:20px;">$1</td>', $m2[1]);
			$m1[1] = str_replace($m2[0], '<tr>'.$m2[1].'</tr>', $m1[1]);
		}
		$m = str_replace($m1[0], '<table class="ckeditor_table" style="width: 100%;border-collapse:collapse;border-spacing:0;table-layout:fixed;border: 2px solid #333;background:#fff;">'.$m1[1].'</table>', $m);
	}

	return $m;
}