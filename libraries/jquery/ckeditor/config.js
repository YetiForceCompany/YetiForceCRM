/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	//vtiger editor toolbar configuration 
	config.removePlugins = 'save,maximize';
	config.fullPage = false;
	config.allowedContent = true;
	config.skin = 'bootstrapck';
	config.scayt_autoStartup = false;
	config.enterMode = CKEDITOR.ENTER_BR;
	config.shiftEnterMode = CKEDITOR.ENTER_P;
	config.plugins = 'dialogui,dialog,about,a11yhelp,dialogadvtab,basicstyles,bidi,blockquote,clipboard,button,panelbutton,panel,floatpanel,colorbutton,colordialog,menu,contextmenu,div,resize,toolbar,elementspath,enterkey,entities,popup,find,fakeobjects,floatingspace,listblock,richcombo,font,format,horizontalrule,htmlwriter,wysiwygarea,image,indent,indentblock,indentlist,justify,link,list,liststyle,magicline,pagebreak,preview,removeformat,selectall,showborders,sourcearea,specialchar,menubutton,scayt,stylescombo,tab,table,tabletools,undo,wsc';
	config.toolbarGroups = [
		{name: 'clipboard', groups: ['clipboard', 'undo']},
		{name: 'editing', groups: ['find', 'selection', 'spellchecker']},
		{name: 'insert'},
		{name: 'links'},
		{name: 'document', groups: ['mode', 'document', 'doctools']},
		'/',
		{name: 'styles'},
		{name: 'colors'},
		{name: 'tools'},
		{name: 'others'},
		{name: 'basicstyles', groups: ['basicstyles', 'cleanup']}, {name: 'align'},
		{name: 'paragraph', groups: ['list', 'indent', 'blocks']},
	];
	config.toolbar_Basic = [
		{name: 'styles', items: ['FontSize']},
		{name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']},
		{name: 'tools', items: ['Maximize', 'ShowBlocks', '-']},
		{name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']},
	];
};
