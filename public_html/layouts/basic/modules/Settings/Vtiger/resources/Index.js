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

$.Class(
	'Settings_Vtiger_Index_Js',
	{
		showMessage: function(customParams) {
			let params = {
				type: 'success',
				title: app.vtranslate('JS_MESSAGE')
			};
			if (typeof customParams !== 'undefined') {
				params = $.extend(params, customParams);
			}
			Vtiger_Helper_Js.showPnotify(params);
		},
		selectIcon: function() {
			var aDeferred = $.Deferred();
			app.showModalWindow({
				id: 'iconsModal',
				url: 'index.php?module=Vtiger&view=IconsModal&parent=Settings',
				cb: function(container) {
					App.Fields.Picklist.showSelect2ElementView(container.find('#iconsList'), {
						templateSelection: function(data) {
							if (!data.id) {
								return data.text;
							}
							var type = $(data.element).data('type');
							container.find('.iconName').text(data.id);
							container.find('#iconName').val(data.id);
							container.find('#iconType').val(type);
							if (type === 'icon') {
								container
									.find('.iconExample')
									.html('<span class="' + data.element.value + '" aria-hidden="true"></span>');
							} else if (type === 'image') {
								container.find('.iconName').text(data.text);
								container.find('#iconName').val(data.element.value);
								container.find('.iconExample').html('<img width="24px" src="' + data.element.value + '"/>');
							}
							return data.text;
						},
						templateResult: function(data) {
							if (!data.id) {
								return data.text;
							}
							var type = $(data.element).data('type');
							var option;
							if (type === 'icon') {
								option = $(
									'<span class="' +
										data.element.value +
										'" aria-hidden="true"></span><span> - ' +
										$(data.element).data('class') +
										'</span>'
								);
							} else if (type === 'image') {
								option = $(
									'<img width="24px" src="' +
										data.element.value +
										'" title="' +
										data.text +
										'" /><span> - ' +
										data.text +
										'</span>'
								);
							}
							return option;
						},
						closeOnSelect: true
					});
					container.find('[name="saveButton"]').on('click', function(e) {
						aDeferred.resolve({
							type: container.find('#iconType').val(),
							name: container.find('#iconName').val()
						});
						app.hideModalWindow(container, 'iconsModal');
					});
				}
			});
			return aDeferred.promise();
		},
		showWarnings: function() {
			$('li[data-mode="systemWarnings"] a').click();
		},
		showSecurity: function() {
			app.openUrl('index.php?module=Log&parent=' + app.getParentModuleName() + '&view=Index&type=access_for_admin');
		}
	},
	{
		registerDeleteShortCutEvent: function(shortCutBlock) {
			var thisInstance = this;
			if (typeof shortCutBlock === 'undefined') {
				shortCutBlock = $('div#settingsShortCutsContainer');
			}
			shortCutBlock.on('click', '.unpin', function(e) {
				var actionEle = $(e.currentTarget);
				var closestBlock = actionEle.closest('.moduleBlock');
				var fieldId = actionEle.data('id');
				var shortcutBlockActionUrl = closestBlock.data('actionurl');
				var actionUrl = shortcutBlockActionUrl + '&pin=false';
				var progressIndicatorElement = $.progressIndicator({
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request(actionUrl).done(function(data) {
					if (data.result.SUCCESS == 'OK') {
						closestBlock.remove();
						thisInstance.registerSettingShortCutAlignmentEvent();
						var menuItemId = '#' + fieldId + '_menuItem';
						var shortCutActionEle = $(menuItemId);
						var imagePath = shortCutActionEle.data('pinimageurl');
						shortCutActionEle.attr('src', imagePath).data('action', 'pin');
						progressIndicatorElement.progressIndicator({
							mode: 'hide'
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
		registerPinShortCutEvent: function(element) {
			var id = element.data('id');
			var url =
				'index.php?module=Vtiger&parent=Settings&action=Basic&mode=updateFieldPinnedStatus&pin=true&fieldid=' + id;
			var progressIndicatorElement = $.progressIndicator({
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request(url).done(function(data) {
				if (data.result.SUCCESS == 'OK') {
					var params = {
						fieldid: id,
						mode: 'getSettingsShortCutBlock',
						module: 'Vtiger',
						parent: 'Settings',
						view: 'IndexAjax'
					};
					AppConnector.request(params).done(function(data) {
						var existingDivBlock = $('#settingsShortCutsContainer');
						$(data).appendTo(existingDivBlock);
						progressIndicatorElement.progressIndicator({
							mode: 'hide'
						});
						var params = {
							text: app.vtranslate('JS_SUCCESSFULLY_PINNED')
						};
						Settings_Vtiger_Index_Js.showMessage(params);
					});
				}
			});
		},
		registerSettingsShortcutClickEvent: function() {
			$('#settingsShortCutsContainer').on('click', '.moduleBlock', function(e) {
				var url = $(e.currentTarget).data('url');
				window.location.href = url;
			});
		},
		registerSettingShortCutAlignmentEvent: function() {
			$('#settingsShortCutsContainer')
				.find('.moduleBlock')
				.removeClass('marginLeftZero');
			$('#settingsShortCutsContainer')
				.find('.moduleBlock:nth-child(3n+1)')
				.addClass('marginLeftZero');
		},
		registerWidgetsEvents: function() {
			var widgets = $('div.widgetContainer');
			widgets.on('shown.bs.collapse', function(e) {
				var widgetContainer = $(e.currentTarget);
				var quickWidgetHeader = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader');
				var imageEle = quickWidgetHeader.find('.imageElement');
				var imagePath = imageEle.data('downimage');
				imageEle.attr('src', imagePath);
			});
			widgets.on('hidden.bs.collapse', function(e) {
				var widgetContainer = $(e.currentTarget);
				var quickWidgetHeader = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader');
				var imageEle = quickWidgetHeader.find('.imageElement');
				var imagePath = imageEle.data('rightimage');
				imageEle.attr('src', imagePath);
			});
		},
		registerAddShortcutDragDropEvent: function() {
			var elements = $('.js-menu__item .js-menu__link--draggable');
			var thisInstance = this;
			var classes = 'ui-draggable-menuShortcut bg-warning';
			elements.draggable({
				containment: '#page',
				appendTo: 'body',
				helper: 'clone',
				start: function(e, ui) {
					$(ui.helper).addClass(classes);
				},
				zIndex: 99999
			});
			$('#settingsShortCutsContainer').droppable({
				activeClass: 'ui-state-default',
				hoverClass: 'ui-state-hover',
				accept: '.js-menu__item .js-menu__link--draggable',
				drop: function(event, ui) {
					var url = ui.draggable.attr('href');
					var isExist = false;
					$('#settingsShortCutsContainer [id^="shortcut"]').each(function() {
						var shortCutUrl = $(this).data('url');
						if (shortCutUrl == url) {
							isExist = true;
							return;
						}
					});
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
		registerReAlign: function() {
			AppConnector.request({
				mode: 'realignSettingsShortCutBlock',
				module: 'Vtiger',
				parent: 'Settings',
				view: 'IndexAjax'
			}).done(function(data) {
				$('#settingsShortCutsContainer').html(data);
			});
		},
		loadEditorElement: function() {
			new App.Fields.Text.Editor($('.js-editor'), {});
		},
		registerSaveIssues: function() {
			var container = $('.addIssuesModal');
			container.validationEngine(app.validationEngineOptions);
			var title = $('#titleIssues');
			var CKEditorInstance = CKEDITOR.instances['bodyIssues'];
			var thisInstance = this;
			var saveBtn = container.find('.saveIssues');
			saveBtn.on('click', function() {
				if (container.validationEngine('validate')) {
					var body = CKEditorInstance.document.getBody().getHtml();
					var params = {
						module: 'Github',
						parent: app.getParentModuleName(),
						action: 'SaveIssuesAjax',
						title: title.val(),
						body: body
					};
					AppConnector.request(params).done(function(data) {
						app.hideModalWindow();
						thisInstance.reloadContent();
						if (data.result.success == true) {
							var params = {
								title: app.vtranslate('JS_LBL_PERMISSION'),
								text: app.vtranslate('JS_ADDED_ISSUE_COMPLETE'),
								type: 'success'
							};
							Vtiger_Helper_Js.showMessage(params);
						}
					});
				}
			});
			$('[name="confirmRegulations"]').on('click', function() {
				var currentTarget = $(this);
				if (currentTarget.is(':checked')) {
					saveBtn.removeAttr('disabled');
				} else {
					saveBtn.attr('disabled', 'disabled');
				}
			});
		},
		reloadContent: function() {
			$('.js-tabs li .active').trigger('click');
		},
		registerTabEvents: function() {
			var thisInstance = this;
			$('.js-tabs li').on('click', function() {
				thisInstance.loadContent($(this).data('mode'), false, $(this).data('params'));
			});
		},
		registerWarningsAlert: function() {
			const alertsContainer = $('#systemWarningAletrs');
			if (alertsContainer.length) {
				app.showModalWindow(alertsContainer, function() {
					alertsContainer
						.find('.warning')
						.first()
						.removeClass('d-none');
					alertsContainer.find('.warning .btn').on('click', function(e) {
						let btn = $(this),
							save = true,
							params;
						if (btn.hasClass('ajaxBtn')) {
							if (btn.data('params') === undefined) {
								let form = btn.closest('form');
								if (form.hasClass('validateForm') && !form.validationEngine('validate')) {
									save = false;
								}
								params = btn
									.closest('form')
									.serializeArray()
									.reduce(function(obj, item) {
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
									params: params
								}).done(function(data) {
									if (data.result.result) {
										Vtiger_Helper_Js.showMessage({ text: data.result.message, type: 'success' });
									} else {
										Vtiger_Helper_Js.showMessage({ text: data.result.message, type: 'error' });
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
							alertsContainer
								.find('.warning')
								.first()
								.remove();
							if (alertsContainer.find('.warning').length) {
								alertsContainer
									.find('.warning')
									.first()
									.removeClass('d-none');
							} else {
								app.hideModalWindow(alertsContainer);
							}
						}
					});
					alertsContainer.find('.input-group-addon input[type="checkbox"]').on('click', function(e) {
						let btn = $(this),
							group = btn.closest('.input-group');
						if (this.checked) {
							group.find('input[type="text"]').attr('disabled', false);
						} else {
							group.find('input[type="text"]').attr('disabled', true);
						}
					});
				});
			}
		},
		getSelectedFolders: function() {
			var selected = [];
			$.each($('#jstreeContainer').jstree('get_selected', true), function(index, value) {
				selected.push(value.original.subPath);
			});
			return selected;
		},
		loadContent: function(mode, page, modeParams) {
			const thisInstance = this;
			let container = $('.indexContainer');
			let state = container.find('.js-switch--state');
			let author = container.find('.js-switch--author');
			let params = {
				mode: mode,
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				view: app.getViewName()
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
			const progressIndicatorElement = $.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true,
					elementToBlock: container
				}
			});
			AppConnector.request(params).done(function(data) {
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
				container.html(data);
				thisInstance.registerEventsLoadContent(thisInstance, mode, container);
			});
		},
		registerEventsLoadContent: function(thisInstance, mode, container) {
			if (mode == 'index') {
				thisInstance.registerWidgetsEvents();
				thisInstance.registerSettingsShortcutClickEvent();
				thisInstance.registerDeleteShortCutEvent();
				thisInstance.registerAddShortcutDragDropEvent();
				thisInstance.registerSettingShortCutAlignmentEvent();
				thisInstance.registerWarningsAlert();
			}
		},
		registerEvents: function() {
			this.registerTabEvents();
			this.reloadContent();
			this.registerWarningsAlert();
			this.registerSettingsShortcutClickEvent();
			this.registerDeleteShortCutEvent();
			this.registerAddShortcutDragDropEvent();
			this.registerSettingShortCutAlignmentEvent();
		}
	}
);
