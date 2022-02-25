/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 *************************************************************************************/
'use strict';

$.Class(
	'Settings_Vtiger_Index_Js',
	{
		showMessage: function (customParams) {
			let params = {
				type: 'success',
				title: app.vtranslate('JS_MESSAGE')
			};
			if (typeof customParams !== 'undefined') {
				params = $.extend(params, customParams);
			}
			app.showNotify(params);
		},
		selectIcon: function (params = {}) {
			var aDeferred = $.Deferred();
			app.showModalWindow({
				id: 'iconsModal',
				url: 'index.php?module=Vtiger&view=IconsModal&parent=Settings',
				cb: (container) => {
					this.registerIconsSelect(container, params);
					container.find('[name="saveButton"]').on('click', function (e) {
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
		registerIconsSelect(container, params) {
			AppConnector.request(
				$.extend(
					{
						module: app.getModuleName(),
						parent: app.getParentModuleName(),
						action: 'Icons'
					},
					params
				)
			).done(({ result }) => {
				let id = 0;
				const data = Object.keys(result).map((key) => {
					let resultData = result[id];
					return { id: id++, text: resultData.name, type: resultData.type, url: resultData.path };
				});
				const selectParams = {
					templateSelection: function (data) {
						if (!data.id) {
							return data.text;
						}
						container.find('.iconName').text(data.text);
						container.find('#iconName').val(data.text);
						container.find('#iconType').val(data.type);
						if (data.type === 'icon') {
							container.find('.iconExample').html(`<span class="${data.text}" aria-hidden="true"></span>`);
							return $(`<span class="${data.text}" aria-hidden="true"></span><span> - ${data.text}</span>`);
						} else if (data.type === 'image') {
							container.find('.iconName').text(data.text);
							container.find('#iconName').val(data.text);
							container.find('.iconExample').html(`<img width="24px" src="${data.url}"/>`);
						}
						return data.text;
					},
					templateResult: function (data) {
						if (data.loading) {
							return data.text;
						}
						let option;
						if (data.type === 'icon') {
							option = $(`<span class="${data.text}" aria-hidden="true"></span><span> - ${data.text}</span>`);
						} else if (data.type === 'image') {
							option = $(`<img width="24px" src="${data.url}" title="${data.text}" /><span> - ${data.text}</span>`);
						}
						return option;
					},
					closeOnSelect: true
				};
				const params = { lazyElements: 50, data, selectParams };
				App.Fields.Picklist.showLazySelect(container.find('#iconsList'), params);
			});
		},
		showWarnings: function () {
			$('li[data-mode="systemWarnings"] a').click();
		},
		showSecurity: function () {
			app.openUrl('index.php?module=Log&parent=' + app.getParentModuleName() + '&view=Index&type=access_for_admin');
		}
	},
	{
		registerDeleteShortCutEvent: function (shortcutsContainer = $('.js-shortcuts')) {
			shortcutsContainer.on('click', '.unpin', (e) => {
				e.preventDefault();
				var actionEle = $(e.currentTarget);
				var closestBlock = actionEle.closest('.js-shortcut');
				var fieldId = actionEle.data('id');
				var shortcutBlockActionUrl = closestBlock.data('actionurl');
				var actionUrl = shortcutBlockActionUrl + '&pin=false';
				var progressIndicatorElement = $.progressIndicator({
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request(actionUrl).done((data) => {
					if (data.result.SUCCESS == 'OK') {
						closestBlock.remove();
						var menuItemId = '#' + fieldId + '_menuItem';
						var shortCutActionEle = $(menuItemId);
						var imagePath = shortCutActionEle.data('pinimageurl');
						shortCutActionEle.attr('src', imagePath).data('action', 'pin');
						this.updateShortcutsStorage(shortcutsContainer);
						progressIndicatorElement.progressIndicator({
							mode: 'hide'
						});
						var params = {
							title: app.vtranslate('JS_MESSAGE'),
							text: app.vtranslate('JS_SUCCESSFULLY_UNPINNED'),
							type: 'info'
						};
						app.showNotify(params);
					}
				});
			});
		},
		registerPinShortCutEvent: function (element) {
			const id = element.data('id');
			const url =
				'index.php?module=Vtiger&parent=Settings&action=Basic&mode=updateFieldPinnedStatus&pin=true&fieldid=' + id;
			const progressIndicatorElement = $.progressIndicator({
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request(url).done((data) => {
				if (data.result.SUCCESS == 'OK') {
					AppConnector.request({
						fieldid: id,
						mode: 'getSettingsShortCutBlock',
						module: 'Vtiger',
						parent: 'Settings',
						view: 'IndexAjax'
					}).done((data) => {
						const shortcutsContainer = $('.js-shortcuts');
						$(data).appendTo(shortcutsContainer);
						this.updateShortcutsStorage(shortcutsContainer);
						progressIndicatorElement.progressIndicator({
							mode: 'hide'
						});
						Settings_Vtiger_Index_Js.showMessage({
							text: app.vtranslate('JS_SUCCESSFULLY_PINNED')
						});
					});
				}
			});
		},
		registerWidgetsEvents: function () {
			var widgets = $('div.widgetContainer');
			widgets.on('shown.bs.collapse', function (e) {
				var widgetContainer = $(e.currentTarget);
				var quickWidgetHeader = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader');
				var imageEle = quickWidgetHeader.find('.imageElement');
				var imagePath = imageEle.data('downimage');
				imageEle.attr('src', imagePath);
			});
			widgets.on('hidden.bs.collapse', function (e) {
				var widgetContainer = $(e.currentTarget);
				var quickWidgetHeader = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader');
				var imageEle = quickWidgetHeader.find('.imageElement');
				var imagePath = imageEle.data('rightimage');
				imageEle.attr('src', imagePath);
			});
		},
		registerAddShortcutDragDropEvent: function () {
			var elements = $('.js-menu__item .js-menu__link--draggable');
			var self = this;
			var classes = 'ui-draggable-menuShortcut bg-warning';
			elements.draggable({
				containment: '#page',
				appendTo: 'body',
				helper: 'clone',
				start: function (e, ui) {
					$(ui.helper).addClass(classes);
				},
				zIndex: 99999
			});
			const shortcutsContainer = $('.js-shortcuts');
			shortcutsContainer.droppable({
				activeClass: 'ui-state-default',
				hoverClass: 'ui-state-hover',
				accept: '.js-menu__item .js-menu__link--draggable',
				drop: function (event, ui) {
					var url = ui.draggable.attr('href');
					var isExist = false;
					$('.js-shortcuts [id^="shortcut"]').each(function () {
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
						app.showNotify(params);
					} else {
						self.registerPinShortCutEvent(ui.draggable.parent());
					}
				}
			});
			shortcutsContainer.sortable({
				handle: '.js-drag-handler',
				stop: (event, element) => {
					self.updateShortcutsStorage(shortcutsContainer);
				}
			});
			if (Quasar.plugins.LocalStorage.has('yf-settings-shortcuts')) {
				this.alignShortcuts(shortcutsContainer);
			}
		},
		alignShortcuts(container) {
			for (let item of Quasar.plugins.LocalStorage.getItem('yf-settings-shortcuts')) {
				container.append(container.find('#' + item));
			}
		},
		updateShortcutsStorage(container) {
			Quasar.plugins.LocalStorage.set('yf-settings-shortcuts', container.sortable('toArray'));
		},
		registerCollapsiblePanels() {
			const panels = this.container.find('.js-collapse');
			if (Quasar.plugins.LocalStorage.has('yf-settings-panels')) {
				this.setPanels(panels);
			} else {
				panels.collapse('show');
				Quasar.plugins.LocalStorage.set('yf-settings-panels', {
					'marketplace-collapse': 'shown',
					'system-monitoring-collapse': 'shown',
					'my-shortcuts-collapse': 'shown'
				});
			}
			panels.on('hidden.bs.collapse shown.bs.collapse', (e) => {
				this.updatePanelsStorage(e.target.id, e.type);
			});
		},
		updatePanelsStorage(id, type) {
			const panelsStorage = Quasar.plugins.LocalStorage.getItem('yf-settings-panels');
			panelsStorage[id] = type;
			Quasar.plugins.LocalStorage.set('yf-settings-panels', panelsStorage);
		},
		setPanels(panels) {
			const panelsStorage = Quasar.plugins.LocalStorage.getItem('yf-settings-panels');
			panels.each((i, item) => {
				if (panelsStorage[item.id] === 'shown') {
					$(item).collapse('show');
				}
			});
		},
		loadEditorElement: function () {
			App.Fields.Text.Editor.register($('.js-editor'), {});
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
								params = btn
									.closest('form')
									.serializeArray()
									.reduce(function (obj, item) {
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
								}).done(function (data) {
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
							alertsContainer.find('.warning').first().remove();
							if (alertsContainer.find('.warning').length) {
								alertsContainer.find('.warning').first().removeClass('d-none');
							} else {
								app.hideModalWindow(alertsContainer);
							}
						}
					});
					alertsContainer.find('.input-group-append input[type="checkbox"]').on('click', function (e) {
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
		registerEvents: function () {
			this.container = $('.js-dashboard-container');
			this.registerWarningsAlert();
			this.registerDeleteShortCutEvent();
			this.registerAddShortcutDragDropEvent();
			this.registerCollapsiblePanels();
			new window.Settings_YetiForce_Shop_Js().registerEvents();
		}
	}
);
