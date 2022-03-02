/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Menu_Index_Js',
	{},
	{
		treeInstance: false,
		loadMenuTree: function () {
			var thisInstance = this;
			if (thisInstance.treeInstance == false) {
				var data = JSON.parse($('#treeValues').val());
				if (data.length == 0) {
					Settings_Vtiger_Index_Js.showMessage({ text: app.vtranslate('JS_NO_DATA') });
				}
				thisInstance.treeInstance = $('#treeContent');
				thisInstance.treeInstance.jstree({
					core: {
						data: data,
						check_callback: true
					},
					contextmenu: {
						items: {
							rename: {
								label: app.vtranslate('JS_EDIT'),
								action: function (data) {
									var inst = $.jstree.reference(data.reference);
									var id = inst.get_selected();
									var progress = jQuery.progressIndicator();
									app.showModalWindow(
										null,
										'index.php?module=Menu&parent=Settings&view=EditMenu&id=' + id,
										function (container) {
											thisInstance.registerEditMenu(container);
											progress.progressIndicator({ mode: 'hide' });
										}
									);
								}
							},
							remove: {
								label: app.vtranslate('JS_REMOVE'),
								action: function (data) {
									var inst = $.jstree.reference(data.reference);
									var ids = inst.get_selected();
									var showInfo = false;
									for (var i in ids) {
										var menu = inst.get_node(ids[i]);
										if (menu.children.length > 0) {
											showInfo = true;
											break;
										}
									}
									if (showInfo) {
										var modal = $('.modal.deleteAlert').clone(true, true);
										var callBackFunction = function (data) {
											data.find('.deleteAlert').removeClass('d-none');
											data.find('.btn-danger').on('click', function (e) {
												thisInstance.removeMenu(ids, inst);
											});
										};
										app.showModalWindow(modal, function (data) {
											if (typeof callBackFunction == 'function') {
												callBackFunction(data);
											}
										});
									} else {
										thisInstance.removeMenu(ids, inst);
									}
								}
							}
						}
					},
					plugins: ['contextmenu', 'dnd', 'search', 'state', 'types']
				});
			}
			thisInstance.registerMenuChanges();
			return this.treeInstance;
		},
		registerMenuChanges: function () {
			var thisInstance = this;
			thisInstance.treeInstance.on('move_node.jstree', function (obj) {
				var progress = jQuery.progressIndicator();
				var json = thisInstance.treeInstance.jstree('get_json');
				var menus = thisInstance.getChildrenMenu(json, 0);
				thisInstance.save('updateSequence', JSON.stringify(menus)).done(function (data) {
					Settings_Vtiger_Index_Js.showMessage({ type: 'success', text: data.result.message });
					thisInstance.loadContent();
					progress.progressIndicator({ mode: 'hide' });
				});
			});
		},
		getChildrenMenu: function (childrens, parent) {
			var menus = [];
			var thisInstance = this;
			var roleMenu = $('[name="roleMenu"]').val();
			$.each(childrens, function (key, value) {
				var menu = { i: value.id, s: key, p: parent, r: roleMenu };
				if (value.children.length > 0) {
					menu.c = thisInstance.getChildrenMenu(value.children, value.id);
				}
				menus.push(menu);
			});
			return menus;
		},
		registerChangeRoleMenu: function () {
			var thisInstance = this;
			$('.menuConfigContainer').on('change', '[name="roleMenu"]', function (e) {
				thisInstance.loadContent();
			});
		},
		getMenuData: function (selectedRole) {
			var aDeferred = jQuery.Deferred();
			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['view'] = app.getViewName();
			params['roleid'] = selectedRole;
			AppConnector.requestPjax(params)
				.done(function (data) {
					aDeferred.resolve(data);
				})
				.fail(function (error) {
					aDeferred.reject();
				});
			return aDeferred.promise();
		},
		registerAddMenu: function () {
			var thisInstance = this;
			$('.addMenu').on('click', function (e) {
				var progress = jQuery.progressIndicator();
				app.showModalWindow(
					null,
					'index.php?module=Menu&parent=Settings&view=CreateMenu&mode=step1',
					function (container) {
						thisInstance.registerStep1(container);
						progress.progressIndicator({ mode: 'hide' });
					}
				);
			});
		},
		loadContent: function () {
			var progress = jQuery.progressIndicator({
				position: '#treeContent',
				blockInfo: {
					enabled: true
				}
			});
			var thisInstance = this;
			var contentsDiv = $('.contentsDiv');
			thisInstance.getMenuData($('[name="roleMenu"]').val()).done(function (data) {
				contentsDiv.html(data);
				App.Fields.Picklist.showSelect2ElementView(contentsDiv.find("[name='roleMenu']"));
				thisInstance.registerEvents();
				progress.progressIndicator({ mode: 'hide' });
			});
		},
		registerEditMenu: function (container) {
			var thisInstance = this;
			container.find('form').validationEngine(app.validationEngineOptions);
			thisInstance.registerHotkeys(container);
			thisInstance.registerHiddenInput(container);
			thisInstance.registerFilters(container);
			thisInstance.registerSelectIcons(container);
			container.find('.saveButton').on('click', function (e) {
				var form = container.find('form').serializeFormData();
				var errorExists = container.find('form').validationEngine('validate');
				if (errorExists != false) {
					var progress = jQuery.progressIndicator();
					thisInstance.save('updateMenu', form).done(function (data) {
						Settings_Vtiger_Index_Js.showMessage({ type: 'success', text: data.result.message });
						app.hideModalWindow();
						thisInstance.loadContent();
						progress.progressIndicator({ mode: 'hide' });
					});
				}
			});
			container.find('form').on('submit', function (event) {
				event.preventDefault();
			});
		},
		registerStep1: function (container) {
			var thisInstance = this;
			container.find('.nextButton').on('click', function (e) {
				var progress = jQuery.progressIndicator();
				app.showModalWindow(
					null,
					'index.php?module=Menu&parent=Settings&view=CreateMenu&mode=step2&mtype=' +
						container.find('select.type').val(),
					function (container) {
						thisInstance.registerStep2(container);
						progress.progressIndicator({ mode: 'hide' });
					}
				);
			});
		},

		registerSelectIcons: function (container) {
			var iconSelect = container.find('#selectIconButton');
			var icon = container.find('[name="icon"]');
			iconSelect.on('click', function () {
				$.when(Settings_Vtiger_Index_Js.selectIcon()).done(function (data) {
					icon.val(data['name']);
				});
			});
		},
		registerStep2: function (container) {
			var thisInstance = this;
			container.find('form').validationEngine(app.validationEngineOptions);
			thisInstance.registerHotkeys(container);
			thisInstance.registerHiddenInput(container);
			thisInstance.registerFilters(container);
			thisInstance.registerSelectIcons(container);
			container.find('.saveButton').on('click', function (e) {
				var form = container.find('form').serializeFormData();
				form.role = $('[name="roleMenu"]').val();
				var errorExists = container.find('form').validationEngine('validate');
				if (errorExists != false) {
					var progress = jQuery.progressIndicator();
					thisInstance.save('createMenu', form).done(function (data) {
						Settings_Vtiger_Index_Js.showMessage({ type: 'success', text: data.result.message });
						app.hideModalWindow();
						thisInstance.loadContent();
						progress.progressIndicator({ mode: 'hide' });
					});
				}
			});
			container.find('form').on('submit', function (event) {
				event.preventDefault();
			});
		},
		save: function (mode, data) {
			var aDeferred = jQuery.Deferred();
			var params = {};
			if (typeof data['filters'] !== 'undefined' && !$.isArray(data['filters'])) {
				data['filters'] = [data['filters']];
			}
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['action'] = 'SaveAjax';
			params['source'] = $('.js-source').val();
			params['mode'] = mode;
			params['mdata'] = data;
			AppConnector.request(params)
				.done(function (data) {
					aDeferred.resolve(data);
				})
				.fail(function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				});
			return aDeferred.promise();
		},
		removeMenu: function (id, inst) {
			var thisInstance = this;
			thisInstance.save('removeMenu', id).done(function (data) {
				Settings_Vtiger_Index_Js.showMessage({ type: 'success', text: data.result.message });
				inst.delete_node(id);
				thisInstance.loadContent();
			});
		},
		registerHotkeys: function (container) {
			container.find('.testBtn').on('click', function (e) {
				var testBtn = $(this);
				var key = container.find('[name="hotkey"]').val();
				testBtn.addClass('active').removeClass('btn-default').addClass('btn-warning');
				Mousetrap.bind(key, function () {
					Settings_Vtiger_Index_Js.showMessage({
						type: 'success',
						text: app.vtranslate('JS_TEST_HOTKEY_OK')
					});
					testBtn.removeClass('btn-warning').addClass('btn-success').removeClass('active');
					Mousetrap.unbind(key);
				});
			});
		},
		registerHiddenInput: function (container) {
			if (container.find('#menuType').val() == 'CustomFilter') {
				var tabid = container.find('select[name="filterId"] option:selected').data('tabid');
				container.find('[name="module"]').val(tabid);
				container.on('change', 'select[name="filterId"]', function (e) {
					var tabid = container.find('select[name="filterId"] option:selected').data('tabid');
					container.find('[name="module"]').val(tabid);
				});
			}
		},
		cacheFilters: false,
		registerFilters: function (container) {
			var thisInstance = this;
			if (container.find('#menuType').val() == 'Module') {
				thisInstance.cacheFilters = container.find('[name="filters"]').clone(true, true);
				container.find('[name="module"]').on('change', function (e) {
					thisInstance.loadFilters(container, $(this));
				});
				thisInstance.loadFilters(container, container.find('[name="module"]'));
			}
		},
		loadFilters: function (container, module) {
			var thisInstance = this;
			container.find('[name="filters"]').html(thisInstance.cacheFilters.html());
			container.find('[name="filters"] option').each(function (index) {
				if ($(this).data('tabid') != module.val()) {
					$(this).remove();
				}
			});
			App.Fields.Picklist.showSelect2ElementView(container.find('[name="filters"]'), {
				width: '100%'
			});
		},
		registerModalButton: function () {
			var thisInstance = this;
			var container = jQuery('.menuConfigContainer');

			container.find('.copyMenu').on('click', function () {
				var myModal = container.find('.copyMenuModal').clone(true, true);
				var callBackFunction = function (data) {
					var selectElement = data.find("[name='roles']");
					App.Fields.Picklist.showSelect2ElementView(selectElement);
					var form = data.find('form');
					form.on('submit', function (e) {
						var currentTarget = jQuery(e.currentTarget);
						var role = currentTarget.find('#roleList');
						if (role.length && role.val()) {
							thisInstance.copyMenu(role.val());
						}
						e.preventDefault();
					});
				};
				app.showModalWindow(myModal, function (data) {
					if (typeof callBackFunction == 'function') {
						callBackFunction(data);
					}
				});
			});
		},
		copyMenu: function (fromRole) {
			var thisInstance = this;
			var aDeferred = jQuery.Deferred();
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var toRole = $('[name="roleMenu"]').val();
			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['action'] = 'SaveAjax';
			params['mode'] = 'copyMenu';
			params['fromRole'] = fromRole;
			params['toRole'] = toRole;
			AppConnector.request(params)
				.done(function (data) {
					app.hideModalWindow();
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					thisInstance.loadContent();
					aDeferred.resolve(data);
				})
				.fail(function (error) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					aDeferred.reject(error);
				});
			return aDeferred.promise();
		},

		registerEvents: function () {
			var thisInstance = this;
			thisInstance.treeInstance = false;
			thisInstance.loadMenuTree();
			thisInstance.registerChangeRoleMenu();
			thisInstance.registerAddMenu();
			thisInstance.registerModalButton();
		}
	}
);
