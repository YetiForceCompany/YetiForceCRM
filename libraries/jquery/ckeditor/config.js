/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.fullPage = false;
	config.allowedContent = true;
	config.scayt_autoStartup = false;
	config.enterMode = CKEDITOR.ENTER_BR;
	config.shiftEnterMode = CKEDITOR.ENTER_P;
	config.toolbar = 'Full';
	config.toolbar_Full = [
		{name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']},
		{name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt']},
		{name: 'links', items: ['Link', 'Unlink']},
		{name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak']},
		{name: 'tools', items: ['Maximize', 'ShowBlocks']},
		{name: 'paragraph', items: ['Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv']},
		{name: 'document', items: ['Source', 'Print']},
		'/',
		{name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize']},
		{name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript']},
		{name: 'colors', items: ['TextColor', 'BGColor']},
		{name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl']},
		{name: 'basicstyles', items: ['CopyFormatting', 'RemoveFormat']},
	];
	config.toolbar_Min = [
		{name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript']},
		{name: 'colors', items: ['TextColor', 'BGColor']},
		{name: 'tools', items: ['Maximize']},
		{name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl']},
		{name: 'basicstyles', items: ['CopyFormatting', 'RemoveFormat']},
	];
};
