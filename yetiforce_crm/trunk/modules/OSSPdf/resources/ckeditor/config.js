/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
    //vtiger editor toolbar configuration 
 		    config.removePlugins = 'save,maximize'; 
 		        config.fullPage = true; 
 		    config.allowedContent = true; 
 		    config.scayt_autoStartup = true; 
 		        config.enterMode = CKEDITOR.ENTER_BR;  
 		        config.shiftEnterMode = CKEDITOR.ENTER_P; 
 		        config.filebrowserBrowseUrl = 'libraries/kcfinder/browse.php?type=images'; 
 		        config.filebrowserUploadUrl = 'libraries/kcfinder/upload.php?type=images'; 
 	        config.plugins = 'dialogui,dialog,docprops,about,a11yhelp,dialogadvtab,basicstyles,bidi,blockquote,clipboard,button,panelbutton,panel,floatpanel,colorbutton,colordialog,menu,contextmenu,div,resize,toolbar,elementspath,enterkey,entities,popup,filebrowser,find,fakeobjects,floatingspace,listblock,richcombo,font,format,horizontalrule,htmlwriter,wysiwygarea,image,indent,indentblock,indentlist,justify,link,list,liststyle,magicline,pagebreak,preview,removeformat,selectall,showborders,sourcearea,specialchar,menubutton,scayt,stylescombo,tab,table,tabletools,undo,wsc'; 
 		    config.toolbarGroups = [ 
 		        { name: 'clipboard', groups: [ 'clipboard', 'undo' ] }, 
 		        { name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ] }, 
 	        { name: 'insert' }, 
 		        { name: 'links' }, 
 		        { name: 'document', groups: [ 'mode', 'document', 'doctools' ] }, 
 	        '/', 
 		        { name: 'styles' }, 
 		        { name: 'colors' }, 
 		        { name: 'tools' }, 
 		        { name: 'others' }, 
 		        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },{name: 'align'}, 
 	        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks' ] }, 
            ];
};
