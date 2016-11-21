<?php
/**
 * Simple CKEditor
 * Copyright 2016 AliReza_Tofighi, All Rights Reserved
 *
 * Website: http://my-bb.ir
 *
 */


// Make sure we can't access this file directly from the browser.
if(!defined('IN_MYBB'))
{
	die('This file cannot be accessed directly.');
}

//define('LOCAL_CKEDITOR', true);

function simpleckeditor_info()
{

	return array(
		'name'			=> 'Simple CKEditor',
		'description'	=> '',
		'website'		=> 'http://my-bb.ir',
		'author'		=> 'ATofighi',
		'authorsite'	=> 'http://my-bb.ir',
		'version'		=> '1.0.0',
		'compatibility'	=> '18*',
		'codename'		=> 'simpleckeditor'
	);
}

function simpleckeditor_install()
{
    global $mybb, $db, $lang;

}

function simpleckeditor_is_installed()
{
	global $db;
	$query = $db->simple_select('settinggroups', 'gid', "name='simpleckeditor'");
	return $db->num_rows($query) == 1;

}

function simpleckeditor_getthemeeditors() {
	global $setting;
	$select = '<select name="upsetting['.$setting['name'].']">';
	$options = array();
	$editor_theme_root = MYBB_ROOT."jscripts/ckeditor-plugins/skins/";
	if($dh = @opendir($editor_theme_root))
	{
		while($dir = readdir($dh))
		{
			if($dir == ".svn" || $dir == "." || $dir == ".." || !is_dir($editor_theme_root.$dir))
			{
				continue;
			}
			$options[$dir] = ucfirst(str_replace('_', ' ', $dir));
			if ($setting['value'] == $dir)
			{
				$select .= '<option value="'.$dir.'" selected="selected">'.$options[$dir].'</option>';
			}
			else
			{
				$select .= '<option value="'.$dir.'">'.$options[$dir].'</option>';
			}
		}
	}
	$select .= '</select>';
	return $select;

}



function simpleckeditor_activate()
{
	global $db, $mybb, $lang;
	$lang->load('simpleckeditor');
    require_once MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("showthread_quickreply", "#" . preg_quote('{$codebuttons}') . "#i", '', 0);
    find_replace_templatesets("showthread_quickreply", "#" . preg_quote('{$smilieinserter}') . "#i", '', 0);
    find_replace_templatesets("showthread_quickreply", "#" . preg_quote('</textarea>') . "#i", '</textarea>{$codebuttons}');
    find_replace_templatesets("showthread_quickreply", "#" . preg_quote('{$option_signature}') . "#i", '{$smilieinserter}{$option_signature}');

	// Settings group array details
	$group = array(
		'name' => 'simpleckeditor',
		'title' => $db->escape_string($lang->setting_group_simpleckeditor),
		'description' => $db->escape_string($lang->setting_group_simpleckeditor_desc),
		'isdefault' => 0
	);

	// Check if the group already exists.
	$query = $db->simple_select('settinggroups', 'gid', "name='simpleckeditor'");

	if($gid = (int)$db->fetch_field($query, 'gid'))
	{
		// We already have a group. Update title and description.
		$db->update_query('settinggroups', $group, "gid='{$gid}'");
	}
	else
	{
		// We don't have a group. Create one with proper disporder.
		$query = $db->simple_select('settinggroups', 'MAX(disporder) AS disporder');
		$disporder = (int)$db->fetch_field($query, 'disporder');

		$group['disporder'] = ++$disporder;

		$gid = (int)$db->insert_query('settinggroups', $group);
	}

	// Deprecate all the old entries.
	$db->update_query('settings', array('description' => 'SIMPLECKEDITORDELETEMARKER'), "gid='{$gid}'");

	// add settings
	$settings = array(
		'skin'	=> array(
			'optionscode'	=> 'php
	".simpleckeditor_getthemeeditors()."',
			'value'			=> 'moono-lisa'
		),
		'toolbar'	=> array(
			'optionscode'	=> 'textarea',
			'value'			=> "[
	['Source', '-', 'NewPage'],
	['Cut', 'Copy', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'],
	['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt'],
	['Bold', 'Italic', 'Underline', 'Strike', '-', 'CopyFormatting', 'RemoveFormat'],
	['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'],
	['Blockquote', '-', 'mybbinsertcode', 'mybbinsertphp'],
	['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', 'Font', 'FontSize', 'TextColor'],
	['HorizontalRule', 'Image', '-', 'Link', 'Unlink', '-', 'SpecialChar'],
	['Maximize', 'About']
]"
		),
		'disallowed_pages'	=> array(
			'optionscode'	=> 'textarea',
			'value'			=> ""
		)
	);

	$disporder = 0;

	// Create and/or update settings.
	foreach($settings as $key => $setting)
	{
		// Prefix all keys with group name.
		$key = "simpleckeditor_{$key}";

		$lang_var_title = "setting_{$key}";
		$lang_var_description = "setting_{$key}_desc";

		$setting['title'] = $lang->{$lang_var_title};
		if(!$setting['description']) {
			$setting['description'] = $lang->{$lang_var_description};
		}

		// Filter valid entries.
		$setting = array_intersect_key($setting,
			array(
				'title' => 0,
				'description' => 0,
				'optionscode' => 0,
				'value' => 0,
		));

		// Escape input values.
		$setting = array_map(array($db, 'escape_string'), $setting);

		// Add missing default values.
		++$disporder;

		$setting = array_merge(
			array('description' => '',
				'optionscode' => 'yesno',
				'value' => 0,
				'disporder' => $disporder),
		$setting);

		$setting['name'] = $db->escape_string($key);
		$setting['gid'] = $gid;

		// Check if the setting already exists.
		$query = $db->simple_select('settings', 'sid', "gid='{$gid}' AND name='{$setting['name']}'");

		if($sid = $db->fetch_field($query, 'sid'))
		{
			// It exists, update it, but keep value intact.
			unset($setting['value']);
			$db->update_query('settings', $setting, "sid='{$sid}'");
		}
		else
		{
			$db->insert_query('settings', $setting);
		}
	}

	$db->delete_query('settings', "gid='{$gid}' AND description='SIMPLECKEDITORDELETEMARKER'");

	rebuild_settings();

}

function simpleckeditor_deactivate()
{
    global $mybb, $db, $lang;

    require_once MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("showthread_quickreply", "#" . preg_quote('{$codebuttons}') . "#i", '', 0);
    find_replace_templatesets("showthread_quickreply", "#" . preg_quote('{$smilieinserter}') . "#i", '', 0);
}

function simpleckeditor_uninstall()
{
    global $mybb, $db, $lang;

	$db->delete_query('settinggroups', "name='simpleckeditor'");

	$db->delete_query('settings', "name LIKE 'simpleckeditor\_%'");

	rebuild_settings();
}



function simpleckeditor($bind="message", $smilies = true) {
	global $cache, $mybb, $lang;

	if(!simpleckeditor_is_allowed()) {
		return false;
	}

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
		$finds = explode("\n", $smilie['find']);
		foreach($finds as $find) {
			$smilieArr[$find] = $smilie['image'];
			$smilieCodes[] = $find;
		}
	}
	$jsonSimiles =  json_encode($smilieArr);
	$jsonSimileCodes =  json_encode($smilieCodes);

	$dir = 'ltr';
	$startDir = 'left';
	$endDir = 'right';
	if($lang->settings['rtl']) {
		$dir = 'rtl';
		$startDir = 'right';
		$endDir = 'left';
	}
	if(defined('LOCAL_CKEDITOR')) {
		$ckePath = $mybb->settings['bburl'].'/ckeditor/ckeditor.js';
	}
	else {
		$ckePath = '//cdn.ckeditor.com/4.6.0/full/ckeditor.js';
	}
	$codebuttons = <<<CODE
		<script src="{$ckePath}"></script>
		<style>
		.cke_source  {
			padding: 20px!important;
			box-sizing: border-box!important;
			font-family: Tahoma, sans-serif, Arial, Verdana, "Trebuchet MS"!important;
			font-size: 13px!important;
			line-height: 1.6!important;
			text-align: {$startDir}!important;
			direction: {$dir}!important;
		}

		.cke_reset_all, .cke_reset_all * {
			font-family: Tahoma, sans-serif, Arial, Verdana, "Trebuchet MS"!important;
		}
		</style>
		<script type="text/javascript">
			{
				var plugins = ['mybbmycode','mybbinsertcode','mybbinsertphp', 'mybbfixquote'];
				for(var pluginIndex in plugins)
				{
					var plugin = plugins[pluginIndex];
					CKEDITOR.plugins.addExternal( plugin, '{$mybb->settings['bburl']}/jscripts/ckeditor-plugins/plugins/'+plugin+'/', 'plugin.js' );
				}
			}
			var SmilieCodes = {$jsonSimileCodes};
			var Smilies = {$jsonSimiles};

			var simpleckeditor = CKEDITOR.replace( '{$bind}', {
				customConfig: '{$mybb->settings['bburl']}/jscripts/ckeditor-plugins/config.js',
				toolbar: {$mybb->settings['simpleckeditor_toolbar']},
				skin: '{$mybb->settings['simpleckeditor_skin']},{$mybb->settings['bburl']}/jscripts/ckeditor-plugins/skins/{$mybb->settings['simpleckeditor_skin']}/',
			} );
			MyBBEditor = {
				insertText: function(msg) {

					if(simpleckeditor.mode == 'wysiwyg') {
						simpleckeditor.insertHtml(simpleckeditor.BBCodeToHtml(msg+"‌"));
					}
					else {
						var input = simpleckeditor.ui.space('contents')
												  .getElementsByTag('textarea').$[0];
			            input.focus();

			            if(typeof input.selectionStart != 'undefined')
			            {
			               var start = input.selectionStart;
			               var end = input.selectionEnd;

			               input.value = input.value.substr(0, start) + msg + input.value.substr(end);

						   var pos = start+msg.length;

						   input.selectionStart = pos;
			               input.selectionEnd = pos;
			            }
					}
				},
				getData: function() {
					return simpleckeditor.getData();
				},
				focus: function () {
					return simpleckeditor.focus();
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
					simpleckeditor.updateElement();
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
						simpleckeditor.setData('');

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

	return $codebuttons;
}

$plugins->add_hook('global_end', 'setCKeditor');
function setCKeditor() {
	global $templates;

	if(!simpleckeditor_is_allowed()) {
		return false;
	}

	$templates->cache['codebuttons'] = simpleckeditor('{$bind}');
}


$plugins->add_hook('private_read_end','ckeditorPMQuickreply');
function ckeditorPMQuickreply(){
	global $mybb, $quickreply;

	if(!simpleckeditor_is_allowed()) {
		return false;
	}

	if($quickreply) {
		$quickreply .= simpleckeditor("message");
	}
}

$plugins->add_hook('showthread_start','ckeditorQuickreply');
function ckeditorQuickreply(){
	global $mybb, $forumpermissions, $thread, $fid, $forum;
	global $codebuttons, $smilieinserter;

	if(!simpleckeditor_is_allowed()) {
		return false;
	}

	if(($forumpermissions['canpostreplys'] != 0 && $mybb->user['suspendposting'] != 1 && ($thread['closed'] != 1 || is_moderator($fid)) && $mybb->settings['quickreply'] != 0 && $mybb->user['showquickreply'] != '0' && $forum['open'] != 0) && ($mybb->settings['bbcodeinserter'] != 0 && $forum['allowmycode'] != 0 && (!$mybb->user['uid'] || $mybb->user['showcodebuttons'] != 0))) {
		$codebuttons = simpleckeditor("message");
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

$plugins->add_hook('admin_config_settings_manage', 'simpleckeditor_settings');
$plugins->add_hook('admin_config_settings_change', 'simpleckeditor_settings');
$plugins->add_hook('admin_config_settings_start', 'simpleckeditor_settings');

function simpleckeditor_settings()
{
	global $lang;
	$lang->load('simpleckeditor');
}

function simpleckeditor_is_allowed(){
	global $mybb;
	$disallowedPages = explode("\n", $mybb->settings['simpleckeditor_disallowed_pages']);
	foreach($disallowedPages as $page) {

		list($page, $queryStr) = explode("?", $page, 2);

		$query = array();
		parse_str($queryStr, $query);

		$matched = true;

		if(THIS_SCRIPT != $page) {
			$matched = false;
		}

		foreach($query as $key => $val) {
			if($mybb->get_input($key) != $val) {
				$matched = false;
			}
		}

		if($matched)
			return false;
	}
	return true;
}
