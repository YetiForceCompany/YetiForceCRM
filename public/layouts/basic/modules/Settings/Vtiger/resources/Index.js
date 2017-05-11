/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
jQuery.Class("Settings_Vtiger_Index_Js", {
	showMessage: function (customParams) {
		var params = {};
		params.animation = "show";
		params.type = 'success';
		params.title = app.vtranslate('JS_MESSAGE');
		if (typeof customParams != 'undefined') {
			var params = jQuery.extend(params, customParams);
		}
		Vtiger_Helper_Js.showPnotify(params);
	},
	selectIcon: function () {
		var aDeferred = jQuery.Deferred();
		app.showModalWindow({
			id: 'iconsModal',
			url: 'index.php?module=Vtiger&view=IconsModal&parent=Settings',
			cb: function (container) {
				app.showSelect2ElementView(container.find('#iconsList'), {
					templateSelection: function (data) {
						if (!data.id) {
							return (data.text);
						}
						var type = $(data.element).data('type');
						container.find('.iconName').text(data.id);
						container.find('#iconName').val(data.id);
						container.find('#iconType').val(type);
						if (type === 'icon') {
							container.find('.iconExample').html('<span class="' + data.element.value + '" aria-hidden="true"></span>');
						} else if (type === 'image') {
							container.find('.iconExample').html('<img width="24px" src="' + data.element.value + '"/>')
						}
						return data.text;
					},
					templateResult: function (data) {
						if (!data.id) {
							return (data.text);
						}
						var type = $(data.element).data('type');
						var option;
						if (type === 'icon') {
							option = $('<span class="' + data.element.value + '" aria-hidden="true"> - ' + $(data.element).data('class') + '</span>');
						} else if (type === 'image') {
							option = $('<img width="24px" src="' + data.element.value + '" title="' + data.text + '" /><span> - ' + data.text + '</span>');
						}
						return option;
					},
					closeOnSelect: true
				});
				container.find('[name="saveButton"]').click(function (e) {
					aDeferred.resolve({
						type: container.find('#iconType').val(),
						name: container.find('#iconName').val(),
					});
					app.hideModalWindow(container, 'iconsModal');
				});
			}
		});
		return aDeferred.promise();
	},
	showWarnings: function () {
		jQuery('li[data-mode="systemWarnings"] a').click();
	},
}, {
	registerDeleteShortCutEvent: function (shortCutBlock) {
		var thisInstance = this;
		if (typeof shortCutBlock == 'undefined') {
			var shortCutBlock = jQuery('div#settingsShortCutsContainer')
		}
		shortCutBlock.on('click', '.unpin', function (e) {
			var actionEle = jQuery(e.currentTarget);
			var closestBlock = actionEle.closest('.moduleBlock');
			var fieldId = actionEle.data('id');
			var shortcutBlockActionUrl = closestBlock.data('actionurl');
			var actionUrl = shortcutBlockActionUrl + '&pin=false';
			var progressIndicatorElement = jQuery.progressIndicator({
				'blockInfo': {
					'enabled': true
				}
			});
			AppConnector.request(actionUrl).then(function (data) {
				if (data.result.SUCCESS == 'OK') {
					closestBlock.remove();
					thisInstance.registerSettingShortCutAlignmentEvent();
					var menuItemId = '#' + fieldId + '_menuItem';
					var shortCutActionEle = jQuery(menuItemId);
					var imagePath = shortCutActionEle.data('pinimageurl');
					shortCutActionEle.attr('src', imagePath).data('action', 'pin');
					progressIndicatorElement.progressIndicator({
						'mode': 'hide'
					});
					var params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('JS_SUCCESSFULLY_UNPINNED'),
						animation: 'show',
						type: 'info'
					};
					thisInstance.registerReAlign();
					Vtiger_Helper_Js.showPnotify(params);
				}
			});
			e.stopPropagation();
		});
	},
	registerPinShortCutEvent: function (element) {
		var thisInstance = this;
		var id = element.data('id');
		var url = 'index.php?module=Vtiger&parent=Settings&action=Basic&mode=updateFieldPinnedStatus&pin=true&fieldid=' + id;
		var progressIndicatorElement = jQuery.progressIndicator({
			'blockInfo': {
				'enabled': true
			}
		});
		AppConnector.request(url).then(function (data) {
			if (data.result.SUCCESS == 'OK') {
				var params = {
					'fieldid': id,
					'mode': 'getSettingsShortCutBlock',
					'module': 'Vtiger',
					'parent': 'Settings',
					'view': 'IndexAjax'
				}
				AppConnector.request(params).then(function (data) {
					var shortCutsMainContainer = jQuery('#settingsShortCutsContainer');
					var existingDivBlock = jQuery('#settingsShortCutsContainer div.row:last');
					var count = jQuery('#settingsShortCutsContainer div.row:last').children("div").length;
					if (count == 3) {

						var newBlock = jQuery('#settingsShortCutsContainer').append('<div class="row">' + data);
					} else {
						var newBlock = jQuery(data).appendTo(existingDivBlock);
					}
					thisInstance.registerSettingShortCutAlignmentEvent();
					progressIndicatorElement.progressIndicator({
						'mode': 'hide'
					});
					var params = {
						text: app.vtranslate('JS_SUCCESSFULLY_PINNED')
					};
					Settings_Vtiger_Index_Js.showMessage(params);
				});
			}
		});
	},
	registerSettingsShortcutClickEvent: function () {
		jQuery('#settingsShortCutsContainer').on('click', '.moduleBlock', function (e) {
			var url = jQuery(e.currentTarget).data('url');
			window.location.href = url;
		});
	},
	registerSettingShortCutAlignmentEvent: function () {
		jQuery('#settingsShortCutsContainer').find('.moduleBlock').removeClass('marginLeftZero');
		jQuery('#settingsShortCutsContainer').find('.moduleBlock:nth-child(3n+1)').addClass('marginLeftZero');
	},
	registerWidgetsEvents: function () {
		var widgets = jQuery('div.widgetContainer');
		widgets.on('shown.bs.collapse', function (e) {
			var widgetContainer = jQuery(e.currentTarget);
			var quickWidgetHeader = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader');
			var imageEle = quickWidgetHeader.find('.imageElement')
			var imagePath = imageEle.data('downimage');
			imageEle.attr('src', imagePath);
		});
		widgets.on('hidden.bs.collapse', function (e) {
			var widgetContainer = jQuery(e.currentTarget);
			var quickWidgetHeader = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader');
			var imageEle = quickWidgetHeader.find('.imageElement')
			var imagePath = imageEle.data('rightimage');
			imageEle.attr('src', imagePath);
		});
	},
	registerAddShortcutDragDropEvent: function () {
		var thisInstance = this;
		var elements = jQuery(".subMenu .menuShortcut a");
		var classes = 'ui-draggable-menuShortcut bg-primary';
		elements.draggable({
			containment: "#page",
			appendTo: "body",
			helper: "clone",
			start: function (e, ui)
			{
				$(ui.helper).addClass(classes);
			},
			zIndex: 99999
		});
		jQuery("#settingsShortCutsContainer").droppable({
			activeClass: "ui-state-default",
			hoverClass: "ui-state-hover",
			accept: ".subMenu .menuShortcut a",
			drop: function (event, ui) {
				var url = ui.draggable.attr('href');
				var isExist = false;
				jQuery('#settingsShortCutsContainer [id^="shortcut"]').each(function () {
					var shortCutUrl = jQuery(this).data('url');
					if (shortCutUrl == url) {
						isExist = true;
						return;
					}
				})
				if (isExist) {
					var params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('JS_SHORTCUT_ALREADY_ADDED'),
						animation: 'show',
						type: 'info'
					};
					Vtiger_Helper_Js.showPnotify(params);
				} else {
					thisInstance.registerPinShortCutEvent(ui.draggable.parent());
					thisInstance.registerSettingShortCutAlignmentEvent();
				}
			}
		});
	},
	registerReAlign: function ()
	{

		var params = {
			'mode': 'realignSettingsShortCutBlock',
			'module': 'Vtiger',
			'parent': 'Settings',
			'view': 'IndexAjax'
		}

		AppConnector.request(params).then(function (data) {
			jQuery('#settingsShortCutsContainer').html(data);
		});
	},
	loadCkEditorElement: function () {
		var customConfig = {};
		var noteContentElement = jQuery('.ckEditorSource');
		var ckEditorInstance = new Vtiger_CkEditor_Js();
		ckEditorInstance.loadCkEditor(noteContentElement, customConfig);
	},
	registerSaveIssues: function () {
		var container = jQuery('.addIssuesModal');
		container.validationEngine(app.validationEngineOptions);
		var title = jQuery('#titleIssues');
		var CKEditorInstance = CKEDITOR.instances['bodyIssues'];
		var thisInstance = this;
		var saveBtn = container.find('.saveIssues');
		saveBtn.click(function () {
			if (container.validationEngine('validate')) {
				var body = CKEditorInstance.document.getBody().getHtml();
				var params = {
					module: 'Github',
					parent: app.getParentModuleName(),
					action: 'SaveIssuesAjax',
					title: title.val(),
					body: body
				};
				AppConnector.request(params).then(function (data) {
					app.hideModalWindow();
					thisInstance.reloadContent();
					if (data.result.success == true) {
						var params = {
							title: app.vtranslate('JS_LBL_PERMISSION'),
							text: app.vtranslate('JS_ADDED_ISSUE_COMPLETE'),
							type: 'success',
							animation: 'show'
						};
						Vtiger_Helper_Js.showMessage(params);
					}
				});
			}
		});
		jQuery('[name="confirmRegulations"]').on('click', function () {
			var currentTarget = jQuery(this);
			if (currentTarget.is(':checked')) {
				saveBtn.removeAttr('disabled');
			} else {
				saveBtn.attr('disabled', 'disabled');
			}
		});
	},
	reloadContent: function () {
		jQuery('.massEditTabs li.active').trigger('click');
	},
	resisterSaveKeys: function (modal) {
		var thisInstance = this;
		var container = modal.find('.authModalContent');
		container.validationEngine(app.validationEngineOptions);
		container.find('.saveKeys').click(function () {
			if (container.validationEngine('validate')) {
				var params = {
					module: 'Github',
					parent: app.getParentModuleName(),
					action: 'SaveKeysAjax',
					username: $('[name="username"]').val(),
					client_id: $('[name="client_id"]').val(),
					token: $('[name="token"]').val()
				};
				container.progressIndicator({});
				AppConnector.request(params).then(function (data) {
					container.progressIndicator({mode: 'hide'});
					if (data.result.success == false) {
						var errorDiv = container.find('.errorMsg');
						errorDiv.removeClass('hide');
						errorDiv.html(app.vtranslate('JS_ERROR_KEY'));
					} else {
						app.hideModalWindow();
						thisInstance.reloadContent();
						var params = {
							title: app.vtranslate('JS_LBL_PERMISSION'),
							text: app.vtranslate('JS_AUTHORIZATION_COMPLETE'),
							type: 'success',
							animation: 'show'
						};
						Vtiger_Helper_Js.showMessage(params);
					}
				},
						function (error, err) {
							container.progressIndicator({mode: 'hide'});
							app.hideModalWindow();
						});
			}

		});
	},
	registerTabEvents: function () {
		var thisInstance = this;
		jQuery('.massEditTabs li').on('click', function () {
			thisInstance.loadContent(jQuery(this).data('mode'), false, jQuery(this).data('params'));
		});
	},
	registerPagination: function () {
		var page = jQuery('.pagination .pageNumber');
		var thisInstance = this;
		page.click(function () {
			thisInstance.loadContent('github', $(this).data('id'));
		});
	},
	registerAuthorizedEvent: function () {
		var thisInstance = this;
		jQuery('.showModal').on('click', function () {
			app.showModalWindow(jQuery('.authModal'), function (container) {
				thisInstance.resisterSaveKeys(container);
			});
		});
	},
	registerGithubEvents: function (container) {
		var thisInstance = this;
		thisInstance.registerAuthorizedEvent();
		thisInstance.registerPagination();
		app.showBtnSwitch(container.find('.switchBtn'));
		container.find('.switchAuthor').on('switchChange.bootstrapSwitch', function (e, state) {
			thisInstance.loadContent('github', 1);
		});
		container.find('.switchState').on('switchChange.bootstrapSwitch', function (e, state) {
			thisInstance.loadContent('github', 1);
		});
		$('.addIssuesBtn').on('click', function () {
			var params = {
				module: 'Github',
				parent: app.getParentModuleName(),
				view: 'AddIssue'
			};
			container.progressIndicator({});
			AppConnector.request(params).then(function (data) {
				container.progressIndicator({mode: 'hide'});
				app.showModalWindow(data, function () {
					thisInstance.loadCkEditorElement();
					thisInstance.registerSaveIssues();
				});
			});
		});
	},
	registerWarningsAlert: function () {
		var aletrsContainer = jQuery('#systemWarningAletrs');
		if (aletrsContainer.length) {
			app.showModalWindow(aletrsContainer, function (modal) {
				aletrsContainer.find('.warning').first().removeClass('hide');
				aletrsContainer.find('.warning .btn').click(function (e) {
					var btn = $(this);
					var save = true;
					if (btn.hasClass('ajaxBtn')) {
						if (btn.data('params') == undefined) {
							var form = btn.closest('form');
							if (form.hasClass('validateForm') && !form.validationEngine('validate')) {
								save = false;
							}
							var params = btn.closest('form').serializeArray().reduce(function (obj, item) {
								obj[item.name] = item.value;
								return obj;
							}, {});
						} else {
							var params = btn.data('params');
						}
						if (save) {
							AppConnector.request({
								module: app.getModuleName(),
								parent: app.getParentModuleName(),
								action: 'SystemWarnings',
								mode: 'update',
								id: btn.closest('.warning').data('id'),
								params: params,
							}).then(function (data) {
								if(data.result.result){
									Vtiger_Helper_Js.showMessage({text : data.result.message, type: 'success', animation: 'show'});
								}else{
									Vtiger_Helper_Js.showMessage({text : data.result.message, type: 'error', animation: 'show'});
								}
							})
						}
					}
					if (btn.hasClass('cancel')) {
						AppConnector.request({
							module: app.getModuleName(),
							parent: app.getParentModuleName(),
							action: 'SystemWarnings',
							mode: 'cancel'
						});
					}
					if (save) {
						aletrsContainer.find('.warning').first().remove();
						if (aletrsContainer.find('.warning').length) {
							aletrsContainer.find('.warning').first().removeClass('hide');
						} else {
							app.hideModalWindow(modal);
						}
					}
				});
				aletrsContainer.find('.input-group-addon input[type="checkbox"]').click(function (e) {
					var btn = $(this);
					var group = btn.closest('.input-group')
					if (this.checked) {
						group.find('input[type="text"]').attr("disabled", false);
					} else {
						group.find('input[type="text"]').attr("disabled", true);
					}
				});
			});
		}
	},
	registerSystemWarningsEvents: function (container) {
		var thisInstance = this;
		thisInstance.registerWarningsFolders(container);
	},
	registerWarningsFolders: function (container) {
		var thisInstance = this;
		var data = [];
		var treeValues = container.find('#treeValues').val();
		if (treeValues) {
			data = JSON.parse(treeValues);
		}
		container.find('#jstreeContainer').jstree({
			core: {
				data: data,
				themes: {
					name: 'proton',
					responsive: true,
					icons: false
				},
				check_callback: true
			},
			plugins: ["checkbox"]
		}).bind("loaded.jstree", function (event, data) {
			$(this).jstree("open_all");
		}).on('changed.jstree', function (e, data) {
			if (data.action != 'model') {
				thisInstance.getWarningsList();
			}
		});
		$.extend($.fn.dataTable.defaults, {
			language: {
				sLengthMenu: app.vtranslate('JS_S_LENGTH_MENU'),
				sZeroRecords: app.vtranslate('JS_NO_RESULTS_FOUND'),
				sInfo: app.vtranslate('JS_S_INFO'),
				sInfoEmpty: app.vtranslate('JS_S_INFO_EMPTY'),
				sSearch: app.vtranslate('JS_SEARCH'),
				sEmptyTable: app.vtranslate('JS_NO_RESULTS_FOUND'),
				sInfoFiltered: app.vtranslate('JS_S_INFO_FILTERED'),
				sLoadingRecords: app.vtranslate('JS_LOADING_OF_RECORDS'),
				sProcessing: app.vtranslate('JS_LOADING_OF_RECORDS'),
				oPaginate: {
					sFirst: app.vtranslate('JS_S_FIRST'),
					sPrevious: app.vtranslate('JS_S_PREVIOUS'),
					sNext: app.vtranslate('JS_S_NEXT'),
					sLast: app.vtranslate('JS_S_LAST')
				},
				oAria: {
					sSortAscending: app.vtranslate('JS_S_SORT_ASCENDING'),
					sSortDescending: app.vtranslate('JS_S_SORT_DESCENDING')
				}
			}
		});
		var element = app.showBtnSwitch(container.find('.switchBtn'));
		element.on('switchChange.bootstrapSwitch', function (e, state) {
			thisInstance.getWarningsList();
		});
	},
	getWarningsList: function () {
		var thisInstance = this;
		var selected = thisInstance.getSelectedFolders();
		var container = $('#warningsContent');
		var progressIndicator = jQuery.progressIndicator({message: app.vtranslate('JS_LOADING_OF_RECORDS'), blockInfo: {enabled: true}});
		var active = $('.warningsIndexPage input.switchBtn').bootstrapSwitch('state');
		AppConnector.request({
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			view: 'Index',
			mode: 'getWarningsList',
			active: active,
			folder: selected
		}).then(function (data) {
			container.html(data);
			thisInstance.registerWarningsList(container);
			progressIndicator.progressIndicator({mode: 'hide'});
		}, function (error) {
			progressIndicator.progressIndicator({mode: 'hide'});
		})
	},
	registerWarningsList: function (container) {
		var thisInstance = this;
		container.find('table').dataTable({
			order: [[2, 'desc']]
		});
		app.showPopoverElementView(container.find('.popoverTooltip'));
		container.find('.showDescription').click(function (e) {
			var html = $(this).closest('td').find('.showDescriptionContent').html();
			app.showModalWindow(html);
		});
		container.find('.setIgnore').click(function (e) {
			container.find('.popoverTooltip').popover('hide');
			var data = $(this).closest('tr').data();
			AppConnector.request({
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SystemWarnings',
				mode: 'update',
				id: data.id,
				params: data.status,
			}).then(function (data) {
				thisInstance.getWarningsList(container);
			}, function (error) {

			})
		});
	},
	getSelectedFolders: function () {
		var selected = [];
		$.each($('#jstreeContainer').jstree("get_selected", true), function (index, value) {
			selected.push(value.original.subPath);
		});
		return selected;
	},
	loadContent: function (mode, page, modeParams) {
		var thisInstance = this;
		var container = jQuery('.indexContainer');
		var state = container.find('.switchState');
		var author = container.find('.switchAuthor');
		var params = {
			mode: mode,
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			view: 'Index'
		};
		if (modeParams) {
			params.page = page;
		}
		if (modeParams) {
			params.params = modeParams;
		}
		if (state.is(':checked')) {
			params.state = 'closed';
		} else {
			params.state = 'open';
		}
		params.author = author.is(':checked');
		var progressIndicatorElement = jQuery.progressIndicator({
			position: 'html',
			'blockInfo': {
				'enabled': true,
				'elementToBlock': container
			}
		});
		AppConnector.request(params).then(function (data) {
			progressIndicatorElement.progressIndicator({mode: 'hide'});
			container.html(data);
			if (mode == 'index') {
				thisInstance.registerSettingsShortcutClickEvent();
				thisInstance.registerDeleteShortCutEvent();
				thisInstance.registerWidgetsEvents();
				thisInstance.registerAddShortcutDragDropEvent();
				thisInstance.registerSettingShortCutAlignmentEvent();
				thisInstance.registerWarningsAlert();
			} else if (mode == 'github') {
				thisInstance.registerGithubEvents(container);
			} else if (mode == 'systemWarnings') {
				thisInstance.registerSystemWarningsEvents(container);
			}
		});
	},
	registerEvents: function () {
		this.registerTabEvents();
		this.reloadContent();
	}
});
