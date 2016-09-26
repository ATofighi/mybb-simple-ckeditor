/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {

	config.extraPlugins = 'mybbmycode,mybbinsertcode,mybbinsertphp';

	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
	config.removeButtons = 'Subscript,Superscript';


	config.fontSize_sizes = 'X Small/x-small;Small/small;Meduim/medium;Large/large;X Large/x-large;XX Large/xx-large';

	// Simplify the dialog windows.
	config.removeDialogTabs = 'image:advanced;link:advanced';

	config.image_previewText = ' ';

	config.skin = 'moono';

	config.height = '300px';

	config.pasteFilter = 'strong u i strike; span{!color}; span{!size};span{!font-family};div{!text-align};	blockquote;a[!href];hr;ul li;ol li; img[!src];blockquote cite;div(!codeblock-code);div(!codeblock-php);';
	config.allowedContent = 'strong u i strike; span{!color}; span{!size};span{!font-family};div{!text-align};	blockquote;a[!href];hr;ul li;ol li; img[!src]{!width,!height};blockquote cite;div(!codeblock-code);div(!codeblock-php);';


	if($('html').attr('lang')) {
		config.language = $('html').attr('lang');

		if($('html').attr('lang') == 'persian') { // allow to use editor for mybbiran users.
			config.language = 'fa';
		}
	}

	config.contentsCss = './jscripts/ckeditor-plugins/contents.php';
};
