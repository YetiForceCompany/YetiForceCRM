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
		$('.saveTree').click(function(e) {
			jstreeInstance.jstree('deselect_all',true)
			var json = jstreeInstance.jstree("get_json");
			$('#treeValues').val( JSON.stringify(json) );
			editContainer.submit();
		});
		$('.addNewElement').keydown(function(event){
			if(event.keyCode == 13) {
				$('.addNewElementBtn').trigger( "click" );
				event.preventDefault();
				return false;
			}
		});
	},
	createTree : function() {
		var thisInstance = this;
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
						"remove" : {"label"	: app.vtranslate('JS_JSTREE_REMOVE'),
									"action" : function (node) {
									thisInstance.deleteItemEvent(node).then(
										function(e){
											if(e)
												node.remove();
										}
									)}},
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
	},
	deleteItemEvent : function(node) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		jstreeInstanceClone = jQuery('#treeContents ul:first').clone(true, true);

		//check last element
		if(jstreeInstanceClone.find('li').length == 1){
			Settings_Vtiger_Index_Js.showMessage({text:app.vtranslate('JS_YOU_CANNOT_DELETE_ALL_THE_ITEMS'),type : 'error'})
			aDeferred.resolve();
			return aDeferred.promise();
		}
		//check childNodes exists
		var childNodes = thisInstance.jstreeInstance.jstree('get_json', node);
		if(typeof childNodes['0']['children'] != 'undefined'){
			Settings_Vtiger_Index_Js.showMessage({text:app.vtranslate('JS_YOU_CANNOT_DELETE_PERENT_ITEM'),type : 'error'})
			aDeferred.resolve();
			return aDeferred.promise();
		}
		
		
		app.showModalWindow(null, "index.php?module=TreesManager&parent=Settings&view=ReplaceTreeItem", function(wizardContainer){
			var form = jQuery('form', wizardContainer);
			nodeId = jQuery(node).attr('id');
			var jsonReplaceIds = jQuery(node).data('replaceid');
			if(typeof jsonReplaceIds != 'undefined'){
				jsonReplaceIds.push(parseInt(nodeId));
			}else{
				jsonReplaceIds = [parseInt(nodeId)];
			}
			jstreeInstanceClone.find('li[id="'+nodeId+'"]').remove();
			
			jstreeInstanceReplace = wizardContainer.find('#treePopupContents');
			jstreeInstanceReplace.jstree({
				"html_data" : {
					"data" : jstreeInstanceClone
				},
				"themes" : {
					"theme" : "default",
					"dots" : true,
					"icons" : true
				},
				"plugins" : [ "themes", "html_data", "ui","hotkeys" ]
			}).bind("loaded.jstree", function (event, data) {
				$(this).jstree("open_all");	
			});
			form.submit(function(e){
				e.preventDefault();
				var data = jstreeInstanceReplace.jstree('get_selected');
				if(typeof data.attr('id') == 'undefined'){
					var params = {};
						params['type'] = 'error';
						params['text'] = app.vtranslate('JS_NO_ITEM_SELECTED');
						Settings_Vtiger_Index_Js.showMessage(params);
					return false;
				}
				if(typeof data.data('replaceid') != 'undefined'){
					jsonReplaceIds = jsonReplaceIds.concat(data.data('replaceid'));
				}
				jQuery('#treeContents ul:first li[id="'+data.attr('id')+'"]').attr('data-replaceId', JSON.stringify(jsonReplaceIds));
				app.hideModalWindow();
				aDeferred.resolve(data.attr('id'));
			});
		});
		return aDeferred.promise();
	},
});