/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery.Class('Settings_TreesManager_Edit_Js', {}, {
	jstreeInstance : false,
	jstreeLastID : 0,
	
	registerEvents : function() {
		var thisInstance = this;
		var editContainer = $("#EditView");
		editContainer.validationEngine();
		var jstreeInstance = thisInstance.createTree();
		$('.addNewElementBtn').click(function(e) {
			var newElement = $('input.addNewElement');
			if(newElement.val() == ''){
				var message = app.vtranslate('JS_FIELD_CAN_NOT_BE_EMPTY');
				newElement.validationEngine('showPrompt', message , 'error','bottomLeft',true);
				return false;		
			}
			thisInstance.jstreeLastID = thisInstance.jstreeLastID+1;
			jstreeInstance.jstree("create",-1,false,{  
				"data" : { 
					"title" : newElement.val()
				},
				"attr" : { id : thisInstance.jstreeLastID},
			},false,true);
			$('input.addNewElement').val('');
		});
		editContainer.submit(function( event ) {
			var data = [];
			var json = jstreeInstance.jstree("get_json");
			$('#treeValues').val( JSON.stringify(json) ) 
		});
	},
	createTree : function() {
		if(this.jstreeInstance == false) {
			this.jstreeLastID = parseInt($('#treeLastID').val());
			var treeValues = $('#treeValues').val();
			var data = JSON.parse(treeValues);
			this.jstreeInstance = $("#treeContents");
			this.jstreeInstance.jstree({
				"json_data" : {
					"data" : data
				},
				"themes" : {
					"theme" : "default",
					"dots" : true,
					"icons" : true
				},
				"contextmenu" : {
					"items" :{
						"create" : {"label"	: app.vtranslate('JS_JSTREE_CREATE')},
						"rename" : {"label"	: app.vtranslate('JS_JSTREE_RENAME')},
						"remove" : {"label"	: app.vtranslate('JS_JSTREE_REMOVE')},
						"ccp" : {
							"label"	: app.vtranslate('JS_JSTREE_CCP'),
							"submenu" : { 
								"cut" : {"label": app.vtranslate('JS_JSTREE_CUT')},
								"copy" : {"label": app.vtranslate('JS_JSTREE_COPY')},
								"paste" : {"label": app.vtranslate('JS_JSTREE_PASTE')},
							}
						},
					}
				},
				"plugins" : [ "themes", "json_data" , "dnd", "ui", "hotkeys", "crrm","contextmenu" ]
			}).bind("loaded.jstree", function (event, data) {
				$(this).jstree("open_all");
			});
		}
		return this.jstreeInstance;
	}
});