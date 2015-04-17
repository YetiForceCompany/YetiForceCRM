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
			var ref = jstreeInstance.jstree(true),
				sel = ref.get_selected();
			ref.create_node('#', {
				id: thisInstance.jstreeLastID,
				text: newElement.val(),
			},'last');
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
			thisInstance.jstreeLastID = parseInt($('#treeLastID').val());
			var treeValues = $('#treeValues').val();
			var data = JSON.parse(treeValues);
			thisInstance.jstreeInstance = $("#treeContents");
			thisInstance.jstreeInstance.jstree({ 
				core: {
					data: data,
					check_callback: true,
				},
				contextmenu: {
					items: {
						create: {"label"	: app.vtranslate('JS_JSTREE_CREATE')},
						rename: {"label"	: app.vtranslate('JS_JSTREE_RENAME')},
						remove: {"label"	: app.vtranslate('JS_JSTREE_REMOVE'),
							action: function (data) {
								var inst = $.jstree.reference(data.reference);
								var id = inst.get_selected();
								var status = true;
								$.each(id, function( index, value ) {
									var menu = inst.get_node(value);
									if(menu.children.length > 0){
										Settings_Vtiger_Index_Js.showMessage({text:app.vtranslate('JS_YOU_CANNOT_DELETE_PERENT_ITEM'),type : 'error'})
										status = false;
									}
								});
								if(status){
									thisInstance.deleteItemEvent(id, inst).then(function (e) {
										if(e.length > 0){
											$.each(id, function( index, value ) {
												inst.delete_node(value);
											});
										}
									})	
								}
							}},
						ccp: {
							label: app.vtranslate('JS_JSTREE_CCP'),
							submenu: { 
								cut: {label: app.vtranslate('JS_JSTREE_CUT')},
								copy: {label: app.vtranslate('JS_JSTREE_COPY')},
								paste: {label: app.vtranslate('JS_JSTREE_PASTE')},
							}
						}
					}
				},
				plugins: [ "contextmenu", "dnd"]
			});
		}
		return this.jstreeInstance;
	},
	deleteItemEvent : function(id,inst) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var treeValues = $('#treeValues').val();
		var data = JSON.parse(treeValues);
		$.each(id, function( index, id ) {
			data = jQuery.grep(data, function(value) {
				return value.id != id;
			});
		});
		if(data.length == 0){
			Settings_Vtiger_Index_Js.showMessage({text:app.vtranslate('JS_YOU_CANNOT_DELETE_ALL_THE_ITEMS'),type : 'error'})
			aDeferred.resolve();
			return aDeferred.promise();
		}
		app.showModalWindow(null, "index.php?module=TreesManager&parent=Settings&view=ReplaceTreeItem", function(wizardContainer){
			var form = jQuery('form', wizardContainer);
			jstreeInstanceReplace = wizardContainer.find('#treePopupContents');
			jstreeInstanceReplace.jstree({
				core: {
					data: data
				},
			}).bind("loaded.jstree", function (event, data) {
				$(this).jstree("open_all");	
			});
			form.submit(function(e){
				e.preventDefault();
				var selected = jstreeInstanceReplace.jstree("get_selected");
				var replaceIds = $('#replaceIds').val();
				if(replaceIds == ''){
					var data = [];
				}else{
					var data = JSON.parse(replaceIds);
				}
				data = $.merge(data, [{old:id, new:selected}]);
				$('#replaceIds').val(JSON.stringify(data));
				app.hideModalWindow();
				aDeferred.resolve(selected);
			});
		});
		return aDeferred.promise();
	},
});
