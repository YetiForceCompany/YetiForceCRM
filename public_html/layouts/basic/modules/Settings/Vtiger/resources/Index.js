/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
'use strict';

$.Class("Settings_Vtiger_Index_Js", {
	showMessage: function (customParams) {
		let params = {
			type: 'success',
			title: app.vtranslate('JS_MESSAGE')
		};
		if (typeof customParams !== "undefined") {
			params = $.extend(params, customParams);
		}
		Vtiger_Helper_Js.showPnotify(params);
	},
	selectIcon: function () {
		var aDeferred = $.Deferred();
		app.showModalWindow({
			id: 'iconsModal',
			url: 'index.php?module=Vtiger&view=IconsModal&parent=Settings',
			cb: function (container) {
				App.Fields.Picklist.showSelect2ElementView(container.find('#iconsList'), {
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
							container.find('.iconName').text(data.text);
							container.find('#iconName').val(data.element.value);
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
							option = $('<span class="' + data.element.value + '" aria-hidden="true"></span><span> - ' + $(data.element).data('class') + '</span>');
						} else if (type === 'image') {
							option = $('<img width="24px" src="' + data.element.value + '" title="' + data.text + '" /><span> - ' + data.text + '</span>');
						}
						return option;
					},
					closeOnSelect: true
				});
				container.find('[name="saveButton"]').on('click', function (e) {
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
		$('li[data-mode="systemWarnings"] a').click();
	},
	showSecurity: function () {
		app.openUrl('index.php?module=Log&parent=' + app.getParentModuleName() + '&view=Index&type=access_for_admin');
	},
}, {
	registerDeleteShortCutEvent: function (shortCutBlock) {
		var thisInstance = this;
		if (typeof shortCutBlock === "undefined") {
			shortCutBlock = $('div#settingsShortCutsContainer')
		}
		shortCutBlock.on('click', '.unpin', function (e) {
			var actionEle = $(e.currentTarget);
			var closestBlock = actionEle.closest('.moduleBlock');
			var fieldId = actionEle.data('id');
			var shortcutBlockActionUrl = closestBlock.data('actionurl');
			var actionUrl = shortcutBlockActionUrl + '&pin=false';
			var progressIndicatorElement = $.progressIndicator({
				'blockInfo': {
					'enabled': true
				}
			});
			AppConnector.request(actionUrl).done(function (data) {
				if (data.result.SUCCESS == 'OK') {
					closestBlock.remove();
					thisInstance.registerSettingShortCutAlignmentEvent();
					var menuItemId = '#' + fieldId + '_menuItem';
					var shortCutActionEle = $(menuItemId);
					var imagePath = shortCutActionEle.data('pinimageurl');
					shortCutActionEle.attr('src', imagePath).data('action', 'pin');
					progressIndicatorElement.progressIndicator({
						'mode': 'hide'
					});
					var params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('JS_SUCCESSFULLY_UNPINNED'),
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
		var id = element.data('id');
		var url = 'index.php?module=Vtiger&parent=Settings&action=Basic&mode=updateFieldPinnedStatus&pin=true&fieldid=' + id;
		var progressIndicatorElement = $.progressIndicator({
			'blockInfo': {
				'enabled': true
			}
		});
		AppConnector.request(url).done(function (data) {
			if (data.result.SUCCESS == 'OK') {
				var params = {
					'fieldid': id,
					'mode': 'getSettingsShortCutBlock',
					'module': 'Vtiger',
					'parent': 'Settings',
					'view': 'IndexAjax'
				}
				AppConnector.request(params).done(function (data) {
					var existingDivBlock = $('#settingsShortCutsContainer');
					$(data).appendTo(existingDivBlock);
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
		$('#settingsShortCutsContainer').on('click', '.moduleBlock', function (e) {
			var url = $(e.currentTarget).data('url');
			window.location.href = url;
		});
	},
	registerSettingShortCutAlignmentEvent: function () {
		$('#settingsShortCutsContainer').find('.moduleBlock').removeClass('marginLeftZero');
		$('#settingsShortCutsContainer').find('.moduleBlock:nth-child(3n+1)').addClass('marginLeftZero');
	},
	registerWidgetsEvents: function () {
		var widgets = $('div.widgetContainer');
		widgets.on('shown.bs.collapse', function (e) {
			var widgetContainer = $(e.currentTarget);
			var quickWidgetHeader = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader');
			var imageEle = quickWidgetHeader.find('.imageElement')
			var imagePath = imageEle.data('downimage');
			imageEle.attr('src', imagePath);
		});
		widgets.on('hidden.bs.collapse', function (e) {
			var widgetContainer = $(e.currentTarget);
			var quickWidgetHeader = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader');
			var imageEle = quickWidgetHeader.find('.imageElement')
			var imagePath = imageEle.data('rightimage');
			imageEle.attr('src', imagePath);
		});
	},
	registerAddShortcutDragDropEvent: function () {
		var thisInstance = this;
		var elements = $(".js-menu__item .js-menu__link--draggable");
		var classes = 'ui-draggable-menuShortcut bg-warning';
		elements.draggable({
			containment: "#page",
			appendTo: "body",
			helper: "clone",
			start: function (e, ui) {
				$(ui.helper).addClass(classes);
			},
			zIndex: 99999
		});
		$("#settingsShortCutsContainer").droppable({
			activeClass: "ui-state-default",
			hoverClass: "ui-state-hover",
			accept: ".js-menu__item .js-menu__link--draggable",
			drop: function (event, ui) {
				var url = ui.draggable.attr('href');
				var isExist = false;
				$('#settingsShortCutsContainer [id^="shortcut"]').each(function () {
					var shortCutUrl = $(this).data('url');
					if (shortCutUrl == url) {
						isExist = true;
						return;
					}
				})
				if (isExist) {
					var params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('JS_SHORTCUT_ALREADY_ADDED'),
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
	registerReAlign: function () {

		var params = {
			'mode': 'realignSettingsShortCutBlock',
			'module': 'Vtiger',
			'parent': 'Settings',
			'view': 'IndexAjax'
		}

		AppConnector.request(params).done(function (data) {
			$('#settingsShortCutsContainer').html(data);
		});
	},
	loadEditorElement: function () {
		new App.Fields.Text.Editor($('.js-editor'), {});
	},
	registerSaveIssues: function () {
		var container = $('.addIssuesModal');
		container.validationEngine(app.validationEngineOptions);
		var title = $('#titleIssues');
		var CKEditorInstance = CKEDITOR.instances['bodyIssues'];
		var thisInstance = this;
		var saveBtn = container.find('.saveIssues');
		saveBtn.on('click', function () {
			if (container.validationEngine('validate')) {
				var body = CKEditorInstance.document.getBody().getHtml();
				var params = {
					module: 'Github',
					parent: app.getParentModuleName(),
					action: 'SaveIssuesAjax',
					title: title.val(),
					body: body
				};
				AppConnector.request(params).done(function (data) {
					app.hideModalWindow();
					thisInstance.reloadContent();
					if (data.result.success == true) {
						var params = {
							title: app.vtranslate('JS_LBL_PERMISSION'),
							text: app.vtranslate('JS_ADDED_ISSUE_COMPLETE'),
							type: 'success',
						};
						Vtiger_Helper_Js.showMessage(params);
					}
				});
			}
		});
		$('[name="confirmRegulations"]').on('click', function () {
			var currentTarget = $(this);
			if (currentTarget.is(':checked')) {
				saveBtn.removeAttr('disabled');
			} else {
				saveBtn.attr('disabled', 'disabled');
			}
		});
	},
	reloadContent: function () {
		$('.js-tabs li .active').trigger('click');
	},
	resisterSaveKeys: function (modal) {
		var thisInstance = this;
		var container = modal.find('.authModalContent');
		container.validationEngine(app.validationEngineOptions);
		container.find('.saveKeys').on('click', function () {
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
				AppConnector.request(params).done(function (data) {
					container.progressIndicator({mode: 'hide'});
					if (data.result.success == false) {
						var errorDiv = container.find('.errorMsg');
						errorDiv.removeClass('d-none');
						errorDiv.html(app.vtranslate('JS_ERROR_KEY'));
					} else {
						app.hideModalWindow();
						thisInstance.reloadContent();
						var params = {
							title: app.vtranslate('JS_LBL_PERMISSION'),
							text: app.vtranslate('JS_AUTHORIZATION_COMPLETE'),
							type: 'success',
						};
						Vtiger_Helper_Js.showMessage(params);
					}
				}).fail(function (error, err) {
					container.progressIndicator({mode: 'hide'});
					app.hideModalWindow();
				});
			}
		});
	},
	registerTabEvents: function () {
		var thisInstance = this;
		$('.js-tabs li').on('click', function () {
			thisInstance.loadContent($(this).data('mode'), false, $(this).data('params'));
		});
	},
	registerPagination: function () {
		var page = $('.pagination .pageNumber');
		var thisInstance = this;
		page.on('click', function () {
			thisInstance.loadContent('github', $(this).data('id'));
		});
	},
	registerAuthorizedEvent: function () {
		var thisInstance = this;
		$('.showModal').on('click', function () {
			app.showModalWindow($('.authModal'), function (container) {
				thisInstance.resisterSaveKeys(container);
			});
		});
	},
	registerGithubEvents: function (container) {
		var thisInstance = this;
		thisInstance.registerAuthorizedEvent();
		thisInstance.registerPagination();
		container.find('.js-switch--state, .js-switch--author').on('change', () => {
			thisInstance.loadContent('github', 1);
		});
		$('.addIssuesBtn').on('click', function () {
			var params = {
				module: 'Github',
				parent: app.getParentModuleName(),
				view: 'AddIssue'
			};
			container.progressIndicator({});
			AppConnector.request(params).done(function (data) {
				container.progressIndicator({mode: 'hide'});
				app.showModalWindow(data, function () {
					thisInstance.loadEditorElement();
					thisInstance.registerSaveIssues();
				});
			});
		});
	},
	registerWarningsAlert: function () {
		const alertsContainer = $('#systemWarningAletrs');
		if (alertsContainer.length) {
			app.showModalWindow(alertsContainer, function () {
				alertsContainer.find('.warning').first().removeClass('d-none');
				alertsContainer.find('.warning .btn').on('click', function (e) {
					let btn = $(this),
						save = true,
						params;
					if (btn.hasClass('ajaxBtn')) {
						if (btn.data('params') === undefined) {
							let form = btn.closest('form');
							if (form.hasClass('validateForm') && !form.validationEngine('validate')) {
								save = false;
							}
							params = btn.closest('form').serializeArray().reduce(function (obj, item) {
								obj[item.name] = item.value;
								return obj;
							}, {});
						} else {
							params = btn.data('params');
						}
						if (save) {
							AppConnector.request({
								module: app.getModuleName(),
								parent: app.getParentModuleName(),
								action: 'SystemWarnings',
								mode: 'update',
								id: btn.closest('.warning').data('id'),
								params: params,
							}).done(function (data) {
								if (data.result.result) {
									Vtiger_Helper_Js.showMessage({text: data.result.message, type: 'success'});
								} else {
									Vtiger_Helper_Js.showMessage({text: data.result.message, type: 'error'});
								}
							});
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
						alertsContainer.find('.warning').first().remove();
						if (alertsContainer.find('.warning').length) {
							alertsContainer.find('.warning').first().removeClass('d-none');
						} else {
							app.hideModalWindow(alertsContainer);
						}
					}
				});
				alertsContainer.find('.input-group-addon input[type="checkbox"]').on('click', function (e) {
					let btn = $(this),
						group = btn.closest('.input-group')
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
		}).on("loaded.jstree", function (event, data) {
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
		container.find('.js-switch--warnings').on('change', () => {
			thisInstance.getWarningsList();
		});
	},
	getWarningsList: function () {
		var thisInstance = this;
		var selected = thisInstance.getSelectedFolders();
		var container = $('#warningsContent');
		var progressIndicator = $.progressIndicator({
			message: app.vtranslate('JS_LOADING_OF_RECORDS'),
			blockInfo: {enabled: true}
		});
		var active = $('.warningsIndexPage .js-switch--warnings').first().is(':checked');
		AppConnector.request({
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			view: 'Index',
			mode: 'getWarningsList',
			active: active,
			folder: selected
		}).done(function (data) {
			container.html(data);
			thisInstance.registerWarningsList(container);
			progressIndicator.progressIndicator({mode: 'hide'});
		}).fail(function (error) {
			progressIndicator.progressIndicator({mode: 'hide'});
		});
	},
	registerWarningsList: function (container) {
		var thisInstance = this;
		container.find('table').dataTable({
			order: [[2, 'desc']]
		});
		container.find('.showDescription').on('click', function (e) {
			var html = $(this).closest('td').find('.showDescriptionContent').html();
			app.showModalWindow(html);
		});
		container.find('.setIgnore').on('click', function (e) {
			container.find('.js-popover-tooltip').popover('hide');
			var data = $(this).closest('tr').data();
			AppConnector.request({
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SystemWarnings',
				mode: 'update',
				id: data.id,
				params: data.status,
			}).done(function (data) {
				thisInstance.getWarningsList(container);
			});
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
		var container = $('.indexContainer');
		var state = container.find('.js-switch--state');
		var author = container.find('.js-switch--author');
		var params = {
			mode: mode,
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			view: 'Index'
		};
		if (page) {
			params.page = page;
		}
		if (modeParams) {
			params.params = modeParams;
		}
		if (state.last().is(':checked')) {
			params.state = 'closed';
		} else {
			params.state = 'open';
		}
		params.author = author.first().is(':checked');
		var progressIndicatorElement = $.progressIndicator({
			position: 'html',
			'blockInfo': {
				'enabled': true,
				'elementToBlock': container
			}
		});
		AppConnector.request(params).done(function (data) {
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
