/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery.Class('Settings_Menu_Index_Js', {}, {
	treeInstance: false,
	loadMenuTree: function () {
		var thisInstance = this;
		if (thisInstance.treeInstance == false) {
			var data = JSON.parse($('#treeValues').val());
			if(data.length == 0){
				Settings_Vtiger_Index_Js.showMessage({text: app.vtranslate('JS_NO_DATA')});
			}
			thisInstance.treeInstance = $("#treeContent");
			thisInstance.treeInstance.jstree({ 
				core: {
					data: data,
					check_callback: true,
				},
				contextmenu: {
					items: {
						rename: {
							label: app.vtranslate('JS_EDIT'),
							action: function (data) {
								var inst = $.jstree.reference(data.reference);
								var id = inst.get_selected();
								var menu = inst.get_node(id);
								var progress = jQuery.progressIndicator();
								app.showModalWindow(null, "index.php?module=Menu&parent=Settings&view=EditMenu&id="+id, function (container) {
									thisInstance.registerEditMenu(container);
									progress.progressIndicator({'mode': 'hide'});
								});
							}
						},
						remove: {
							label: app.vtranslate('JS_REMOVE'),
							action: function (data) {
								var inst = $.jstree.reference(data.reference);
								var id = inst.get_selected();
								var menu = inst.get_node(id);
								if(menu.children.length > 0){
									var modal = $('.modal.deleteAlert').clone(true, true);
									var callBackFunction = function(data) {
										data.find('.deleteAlert').removeClass('hide');
										data.find('.btn-danger').click(function (e) {
											thisInstance.removeMenu(id,inst);
										});
									};
									app.showModalWindow(modal,function(data) {
										if(typeof callBackFunction == 'function') {
											callBackFunction(data);
										}
									});
								}else{
									thisInstance.removeMenu(id,inst);
								}
							}
						},
					}
				},
				plugins: [ "contextmenu", "dnd", "search","state", "types"]
			});
		}
		thisInstance.registerMenuChanges();
		return this.treeInstance;
	},
	registerMenuChanges: function () {
		var thisInstance = this;
		thisInstance.treeInstance.on('move_node.jstree', function (obj) {
			var progress = jQuery.progressIndicator();
			var json = thisInstance.treeInstance.jstree("get_json");
			var menus = thisInstance.getChildrenMenu(json,0);
			thisInstance.save('updateSequence', JSON.stringify(menus)).then(function (data) {
				Settings_Vtiger_Index_Js.showMessage({type: 'success', text: data.result.message});
				thisInstance.loadContent();
				progress.progressIndicator({'mode': 'hide'});
			});
		})
	},
	getChildrenMenu: function (childrens, parent) {
		var menus = [];
		var thisInstance = this;
		var roleMenu = $('[name="roleMenu"]').val()
		$.each( childrens, function( key, value ) {
			var menu = { i: value.id, s: key, p: parent, r: roleMenu };
			if(value.children.length > 0){
				menu.c = thisInstance.getChildrenMenu(value.children, value.id);
			}
			menus.push(menu);
		});
		return menus;
	},
	registerChangeRoleMenu: function () {
		var thisInstance = this;
		$('.menuContainer').on('change', '[name="roleMenu"]', function (e) {
			thisInstance.loadContent();
		});
	},
	getMenuData: function (selectedRole) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['view'] = app.getViewName();
		params['roleid'] = selectedRole;
		AppConnector.requestPjax(params).then(
			function (data) {
				aDeferred.resolve(data);
			},
			function (error) {
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	registerAddMenu: function () {
		var thisInstance = this;
		$('.addMenu').click(function (e) {
			var progress = jQuery.progressIndicator();
			app.showModalWindow(null, "index.php?module=Menu&parent=Settings&view=CreateMenu&mode=step1", function (container) {
				thisInstance.registerStep1(container);
				progress.progressIndicator({'mode': 'hide'});
			});
		});
	},
	loadContent: function () {
		var progress = jQuery.progressIndicator({
			'position' : '#treeContent',
			'blockInfo' : {
				'enabled' : true
			}
		});
		var thisInstance = this;
		var contentsDiv = $('.contentsDiv');
		thisInstance.getMenuData($('[name="roleMenu"]').val()).then(
			function (data) {
				contentsDiv.html(data);
				app.showSelect2ElementView(contentsDiv.find('select'));
				thisInstance.registerEvents();
				progress.progressIndicator({'mode': 'hide'});
			}
		);
	},
	registerEditMenu: function (container) {
		var thisInstance = this;
		container.find('form').validationEngine(app.validationEngineOptions);
		thisInstance.registerHotkeys(container);
		thisInstance.registerHiddenInput(container);
		thisInstance.registerFilters(container);
		container.find('.saveButton').click(function (e) {
			var form = container.find('form').serializeFormData();
			var errorExists = container.find('form').validationEngine('validate');
			if(errorExists != false){
				var progress = jQuery.progressIndicator();
				thisInstance.save('updateMenu', form).then(function (data) {
					Settings_Vtiger_Index_Js.showMessage({type: 'success',text: data.result.message});
					app.hideModalWindow();
					thisInstance.loadContent();
					progress.progressIndicator({'mode': 'hide'});
				});
			}
		});
		container.find('form').submit(function( event ) {
			event.preventDefault();
		});
	},
	registerStep1: function (container) {
		var thisInstance = this;
		container.find('.nextButton').click(function (e) {
			var progress = jQuery.progressIndicator();
			app.showModalWindow(null, "index.php?module=Menu&parent=Settings&view=CreateMenu&mode=step2&mtype=" + container.find('select.type').val(), function (container) {
				thisInstance.registerStep2(container);
				progress.progressIndicator({'mode': 'hide'});
			});
		});
	},
	registerStep2: function (container) {
		var thisInstance = this;
		container.find('form').validationEngine(app.validationEngineOptions);
		thisInstance.registerHotkeys(container);
		thisInstance.registerHiddenInput(container);
		thisInstance.registerFilters(container);
		app.showPopoverElementView(jQuery(container).find('.popoverTooltip'));
		container.find('.saveButton').click(function (e) {
			var form = container.find('form').serializeFormData();
			form.role = $('[name="roleMenu"]').val();
			var errorExists = container.find('form').validationEngine('validate');
			if(errorExists != false){
				var progress = jQuery.progressIndicator();
				thisInstance.save('createMenu', form).then(function (data) {
					Settings_Vtiger_Index_Js.showMessage({type: 'success', text: data.result.message});
					app.hideModalWindow();
					thisInstance.loadContent();
					progress.progressIndicator({'mode': 'hide'});
				});
			}
		});
		container.find('form').submit(function( event ) {
			event.preventDefault();
		});
	},
	save: function (mode, data) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'SaveAjax';
		params['mode'] = mode;
		params['mdata'] = data;
		AppConnector.request(params).then(
			function (data) {
				aDeferred.resolve(data);
			},
			function (error) {
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	removeMenu: function (id, inst) {
		var thisInstance = this;
		thisInstance.save('removeMenu', id).then(function (data) {
			Settings_Vtiger_Index_Js.showMessage({type: 'success', text: data.result.message});
			inst.delete_node(id);
		});
	},
	registerHotkeys: function (container) {
		var thisInstance = this;
		container.find('.testBtn').click(function (e) {
			var testBtn = $(this);
			var key = container.find('[name="hotkey"]').val();
			Mousetrap.bind(key, function() { 
				Settings_Vtiger_Index_Js.showMessage({type: 'success', text: app.vtranslate('JS_TEST_HOTKEY_OK')});
				testBtn.addClass('btn-success');
				Mousetrap.unbind(key);
			});
		}); 
	},
	registerHiddenInput: function (container) {
		if(container.find('#menuType').val() == 'CustomFilter'){
			var tabid = container.find('select[name="dataurl"] option:selected').data('tabid');
			container.find('[name="module"]').val(tabid);
			container.on('change', 'select[name="dataurl"]', function (e) {
				var tabid = container.find('select[name="dataurl"] option:selected').data('tabid');
				container.find('[name="module"]').val(tabid);
			});
			
		}
	},
	cacheFilters: false,
	registerFilters: function (container) {
		var thisInstance = this;
		if(container.find('#menuType').val() == 'Module'){
			thisInstance.cacheFilters = container.find('[name="filters"]').clone(true, true);
			container.find('[name="module"]').on('change', function (e) {
				thisInstance.loadFilters(container, $(this));
			});
			thisInstance.loadFilters(container, container.find('[name="module"]'));
		}
	},
	loadFilters: function (container, module) {
		var thisInstance = this;
		container.find('[name="filters"]').select2('destroy');
		container.find('[name="filters"]').html(thisInstance.cacheFilters.html());
		container.find('[name="filters"] option').each(function (index) {
			if ($(this).data('tabid') != module.val()) {
				$(this).remove();
			}
		});
		app.showSelect2ElementView(container.find('[name="filters"]'), {width: '100%'});
	},
	registerEvents: function () {
		var thisInstance = this;
		thisInstance.treeInstance = false;
		thisInstance.loadMenuTree();
		thisInstance.registerChangeRoleMenu();
		thisInstance.registerAddMenu();
	}
});
