/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_WidgetsManagement_Js',
	{},
	{
		/**
		 * Function to create the array of block roles list
		 */
		getAuthorization: function () {
			let authorization = [],
				container = jQuery('#moduleBlocks');
			container.find('.editFieldsTable').each(function () {
				authorization.push(jQuery(this).data('code').toString());
			});
			return authorization;
		},

		getCurrentDashboardId() {
			return $('.selectDashboard li a.active').parent().data('id');
		},
		registerAddedDashboard() {
			const thisInstance = this;
			$('.addDashboard').on('click', () => {
				app.showModalWindow({
					url: 'index.php?parent=Settings&module=' + app.getModuleName() + '&view=DashboardType',
					sendByAjaxCb: () => {
						let contentsDiv = $('.contentsDiv');
						thisInstance.getModuleLayoutEditor('Home').done((data) => {
							thisInstance.updateContentsDiv(contentsDiv, data);
						});
					}
				});
			});
		},
		registerSelectDashboard() {
			const thisInstance = this;
			$('.selectDashboard li').on('click', (e) => {
				let contentsDiv = $('.contentsDiv');
				thisInstance
					.getModuleLayoutEditor($('#selectedModuleName').val(), $(e.currentTarget).data('id'))
					.done((data) => {
						thisInstance.updateContentsDiv(contentsDiv, data);
					});
			});
		},
		registerDashboardAction() {
			const thisInstance = this;
			$('.editDashboard').on('click', (e) => {
				let currentTarget = $(e.currentTarget);
				e.stopPropagation();
				app.showModalWindow({
					url:
						'index.php?parent=Settings&module=' +
						app.getModuleName() +
						'&view=DashboardType&dashboardId=' +
						currentTarget.closest('li').data('id'),
					sendByAjaxCb: () => {
						let contentsDiv = $('.contentsDiv');
						thisInstance.getModuleLayoutEditor('Home', currentTarget.closest('li').data('id')).done((data) => {
							thisInstance.updateContentsDiv(contentsDiv, data);
						});
					}
				});
			});
			$('.deleteDashboard').on('click', (e) => {
				let currentTarget = $(e.currentTarget);
				e.stopPropagation();
				AppConnector.request({
					parent: 'Settings',
					module: app.getModuleName(),
					action: 'Dashboard',
					mode: 'delete',
					dashboardId: currentTarget.closest('li').data('id')
				}).done(() => {
					let contentsDiv = $('.contentsDiv');
					thisInstance.getModuleLayoutEditor('Home', 1).done((data) => {
						thisInstance.updateContentsDiv(contentsDiv, data);
					});
				});
			});
		},
		/**
		 * Function to register click event for add custom block button
		 */
		registerAddBlockDashBoard: function () {
			var thisInstance = this;
			var contents = jQuery('#layoutDashBoards');
			contents.find('.addBlockDashBoard').on('click', function (e) {
				var addBlockContainer = contents.find('.addBlockDashBoardModal').clone(true, true);
				var inUseAuthorization = thisInstance.getAuthorization();
				addBlockContainer.find('select.authorized option').each(function () {
					if (jQuery.inArray(jQuery(this).val(), inUseAuthorization) != -1) jQuery(this).remove();
				});

				var callBackFunction = function (data) {
					App.Fields.Picklist.changeSelectElementView(data.find('select'));
					var form = data.find('.addBlockDashBoardForm');
					form.validationEngine(app.validationEngineOptions);
					form.on('submit', function (e) {
						if (form.validationEngine('validate')) {
							var paramsForm = form.serializeFormData();
							delete paramsForm._csrf;
							thisInstance.save(paramsForm, 'addBlock').done(function (data) {
								var params = {};
								var response = data.result;
								if (response['success']) {
									app.hideModalWindow();
									params['text'] = app.vtranslate('JS_BLOCK_ADDED');
									thisInstance.reloadContent();
								} else {
									params['text'] = response['message'];
									params['type'] = 'error';
								}
								Settings_Vtiger_Index_Js.showMessage(params);
							});
						}
						e.preventDefault();
					});
				};
				app.showModalWindow(
					addBlockContainer,
					function (data) {
						if (typeof callBackFunction == 'function') {
							callBackFunction(data);
						}
						App.Fields.Picklist.showSelect2ElementView(data.find('.js-authorized'));
					},
					{ width: '1000px' }
				);
			});
		},
		save: function (form, mode, params = null) {
			var aDeferred = jQuery.Deferred();
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			if (!params) {
				params = {};
				params['form'] = form;
				params['module'] = app.getModuleName();
				params['parent'] = app.getParentModuleName();
				params['sourceModule'] = jQuery('#selectedModuleName').val();
				params['action'] = 'SaveAjax';
				params['mode'] = mode;
			}
			AppConnector.request(params)
				.done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					aDeferred.resolve(data);
				})
				.fail(function (error) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					aDeferred.reject(error);
				});
			return aDeferred.promise();
		},

		registerSpecialWidget: function () {
			var thisInstance = this;
			var container = jQuery('#layoutDashBoards');
			container.find('.addNotebook').on('click', function (e) {
				thisInstance.addNoteBookWidget(this, jQuery(this).data('url'));
			});
			container.find('.addMiniList').on('click', function (e) {
				thisInstance.addMiniListWidget(this, jQuery(this).data('url'));
			});
			container.find('.addRss').on('click', function (e) {
				thisInstance.addRssWidget($(e.currentTarget), jQuery(this).data('url'));
			});
		},
		addRssWidget: function (element, url) {
			var thisInstance = this;
			var objectToShowModal = {
				url: 'index.php?module=' + app.getModuleName() + '&parent=' + app.getParentModuleName() + '&view=AddRss',
				cb: function (container) {
					container.find('.removeChannel').on('click', function (e) {
						var currentTarget = $(e.currentTarget);
						var row = currentTarget.closest('.form-group');
						row.remove();
					});
					container.find('.addChannel').on('click', function (e) {
						var newRow = container.find('.newChannel').clone();
						var formContainer = container.find('.formContainer');
						formContainer.append(newRow);
						newRow.removeClass('d-none');
						newRow.removeClass('newChannel');
						newRow.find('input').removeAttr('disabled');
						newRow.find('.removeChannel').on('click', function (e) {
							var currentTarget = $(e.currentTarget);
							var row = currentTarget.closest('.form-group');
							row.remove();
						});
					});
					container.find('[name="blockId"]').val(element.data('blockId'));
					container.find('[name="linkId"]').val(element.data('linkid'));
					var form = container.find('form');
					form.on('submit', function (e) {
						e.preventDefault();
						var channels = [];
						if (form.validationEngine('validate')) {
							form.find('.channelRss:not(:disabled)').each(function () {
								channels.push(jQuery(this).val());
							});
							let paramsForm = form.serializeFormData();
							paramsForm.channels = channels;
							AppConnector.request(paramsForm).done(function (data) {
								if (data.result === true) {
									Settings_Vtiger_Index_Js.showMessage({ text: app.vtranslate('JS_WIDGET_ADDED') });
									thisInstance.reloadContent();
								}
								app.hideModalWindow();
							});
						}
					});
				}
			};
			app.showModalWindow(objectToShowModal);
		},
		addNoteBookWidget: function (element, url) {
			var thisInstance = this;
			element = jQuery(element);
			app.showModalWindow(null, 'index.php?module=Home&view=AddNotePad', function (wizardContainer) {
				var form = jQuery('form', wizardContainer);
				form.validationEngine(
					$.extend(
						true,
						{
							onValidationComplete: function (form, valid) {
								if (valid) {
									//To prevent multiple click on save
									jQuery("[name='saveButton']", wizardContainer).attr('disabled', 'disabled');
									var notePadName = form.find('[name="notePadName"]').val();
									var notePadContent = form.find('[name="notePadContent"]').val();
									var isDefault = 0;
									var linkId = element.data('linkid');
									var blockId = element.data('block-id');
									var noteBookParams = {
										module: jQuery('#selectedModuleName').val(),
										action: 'NoteBook',
										mode: 'noteBookCreate',
										notePadName: notePadName,
										notePadContent: notePadContent,
										blockid: blockId,
										linkId: linkId,
										isdefault: isDefault,
										width: 4,
										height: 3
									};
									AppConnector.request(noteBookParams).done(function (data) {
										if (data.result.success) {
											var widgetId = data.result.widgetId;
											app.hideModalWindow();
											noteBookParams['id'] = widgetId;
											noteBookParams['label'] = notePadName;
											Settings_Vtiger_Index_Js.showMessage({
												text: app.vtranslate('JS_WIDGET_ADDED')
											});
											thisInstance.reloadContent();
										}
									});
								}
								return false;
							}
						},
						app.validationEngineOptions
					)
				);
			});
		},
		addMiniListWidget: function (element, url) {
			// 1. Show popup window for selection (module, filter, fields)
			// 2. Compute the dynamic mini-list widget url
			// 3. Add widget with URL to the page.
			const thisInstance = this;
			element = $(element);
			app.showModalWindow(null, url, function (wizardContainer) {
				let form = $('form', wizardContainer),
					moduleNameSelectDOM = $('select[name="module"]', wizardContainer),
					filteridSelectDOM = $('select[name="filterid"]', wizardContainer),
					fieldHrefDOM = $('select[name="field_href"]', wizardContainer),
					fieldsSelectDOM = $('select[name="fields"]', wizardContainer),
					filterFieldsSelectDOM = $('select[name="filter_fields"]', wizardContainer),
					moduleNameSelect2 = App.Fields.Picklist.showSelect2ElementView(moduleNameSelectDOM, {
						placeholder: app.vtranslate('JS_SELECT_MODULE')
					}),
					filteridSelect2 = App.Fields.Picklist.showSelect2ElementView(filteridSelectDOM, {
						placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION')
					}),
					fieldHrefSelect2 = App.Fields.Picklist.showSelect2ElementView(fieldHrefDOM, {
						allowClear: true
					}),
					fieldsSelect2 = App.Fields.Picklist.showSelect2ElementView(fieldsSelectDOM, {
						placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION'),
						closeOnSelect: true,
						maximumSelectionLength: 6
					}),
					filterFieldsSelect2 = App.Fields.Picklist.showSelect2ElementView(filterFieldsSelectDOM, {
						placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION')
					}),
					footer = $('.modal-footer', wizardContainer);
				filteridSelectDOM.closest('tr').hide();
				fieldHrefDOM.closest('tr').hide();
				fieldsSelectDOM.closest('tr').hide();
				filterFieldsSelectDOM.closest('tr').hide();
				footer.hide();
				moduleNameSelect2.on('change', function () {
					if (!moduleNameSelect2.val()) {
						return;
					}
					AppConnector.request({
						module: 'Home',
						view: 'MiniListWizard',
						step: 'step2',
						selectedModule: moduleNameSelect2.val()
					}).done(function (res) {
						filteridSelectDOM.empty().html(res).trigger('change');
						filteridSelect2.closest('tr').show();
						fieldsSelectDOM.closest('tr').hide();
						filterFieldsSelectDOM.closest('tr').hide();
					});
				});
				filteridSelect2.on('change', function () {
					if (!filteridSelect2.val()) {
						return;
					}
					footer.hide();
					fieldsSelectDOM.closest('tr').hide();
					filterFieldsSelectDOM.closest('tr').hide();
					AppConnector.request({
						module: 'Home',
						view: 'MiniListWizard',
						step: 'step3',
						selectedModule: moduleNameSelect2.val(),
						filterid: filteridSelect2.val()
					}).done(function (res) {
						res = $(res);
						fieldsSelectDOM.empty().html(res.find('select[name="fields"]').html()).trigger('change');
						filterFieldsSelectDOM.empty().html(res.find('select[name="filter_fields"]').html()).trigger('change');
						fieldsSelect2.closest('tr').show();
						fieldHrefSelect2.closest('tr').show();
						filterFieldsSelect2.closest('tr').show();
						fieldsSelect2.data('select2').$selection.find('.select2-search__field').parent().css('width', '100%');
						filterFieldsSelect2.data('select2').$selection.find('.select2-search__field').parent().css('width', '100%');
					});
				});
				fieldsSelect2.on('change', function () {
					fieldHrefDOM.find('option:not([value=""]').remove();
					$(this)
						.find('option:checked')
						.each(function (index, element) {
							let option = $(element);
							let newOption = new Option(option.text(), option.val(), true, true);
							fieldHrefSelect2.append(newOption);
						});
					fieldHrefSelect2.val('').trigger('change');
					if (!fieldsSelect2.val()) {
						footer.hide();
					} else {
						footer.show();
					}
				});
				form.on('submit', function (e) {
					e.preventDefault();
					let selectedFields = [];
					fieldsSelect2.select2('data').map(function (obj) {
						selectedFields.push(obj.id);
					});
					let data = {
						module: moduleNameSelect2.val()
					};
					data['fields'] = selectedFields;
					data['filterFields'] = filterFieldsSelect2.val();
					data['fieldHref'] = fieldHrefSelect2.val();
					let paramsForm = {
						data: JSON.stringify(data),
						blockid: element.data('block-id'),
						title: form.find('[name="widgetTitle"]').val(),
						linkid: element.data('linkid'),
						label: moduleNameSelect2.find(':selected').text() + ' - ' + filteridSelect2.find(':selected').text(),
						name: 'Mini List',
						filterid: filteridSelect2.val(),
						isdefault: 0,
						cache: 0,
						height: 4,
						width: 4,
						owners_all: ['mine', 'all', 'users', 'groups'],
						default_owner: 'mine'
					};
					let sourceModule = $('[name="widgetsManagementEditorModules"]').val();
					let baseParams = {
						form: paramsForm,
						module: sourceModule,
						sourceModule: sourceModule,
						action: 'Widget',
						mode: 'add',
						addToUser: false,
						linkid: paramsForm.linkid,
						name: paramsForm.name
					};
					thisInstance.save(paramsForm, 'save', baseParams).done(function (data) {
						let result = data['result'],
							params = {};
						if (data['success']) {
							app.hideModalWindow();
							paramsForm['id'] = result['id'];
							paramsForm['status'] = result['status'];
							params['text'] = app.vtranslate('JS_WIDGET_ADDED');
							Settings_Vtiger_Index_Js.showMessage(params);
							thisInstance.reloadContent();
						} else {
							let message = data['error']['message'],
								errorField;
							if (data['error']['code'] !== 513) {
								errorField = form.find('[name="fieldName"]');
							} else {
								errorField = form.find('[name="fieldLabel"]');
							}
							errorField.validationEngine('showPrompt', message, 'error', 'topLeft', true);
						}
					});
				});
			});
		},
		/**
		 * Reload content
		 */
		reloadContent: function () {
			$('.selectDashboard .nav-link.active').trigger('click');
		},
		/**
		 * Function to register the click event for delete custom block
		 */
		registerDeleteCustomBlockEvent: function () {
			var thisInstance = this;
			var contents = jQuery('#layoutDashBoards');
			contents.on('click', '.js-delete-custom-block-btn', function (e) {
				var currentTarget = jQuery(e.currentTarget);
				var table = currentTarget.closest('div.editFieldsTable');
				var blockId = table.data('block-id');
				var paramsFrom = {};
				paramsFrom['blockid'] = blockId;
				app.showConfirmModal({
					title: app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE'),
					confirmedCallback: () => {
						thisInstance.save(paramsFrom, 'removeBlock').done(function (_) {
							thisInstance.reloadContent();
							Settings_Vtiger_Index_Js.showMessage({ text: app.vtranslate('JS_CUSTOM_BLOCK_DELETED') });
						});
					}
				});
			});
		},
		/**
		 * Function to register the change event for layout editor modules list
		 */
		registerModulesChangeEvent() {
			const thisInstance = this;
			let container = $('#widgetsManagementEditorContainer');
			let contentsDiv = container.closest('.contentsDiv');
			App.Fields.Picklist.changeSelectElementView(container.find('[name="widgetsManagementEditorModules"]'));
			container.on('change', '[name="widgetsManagementEditorModules"]', (e) => {
				thisInstance
					.getModuleLayoutEditor($(e.currentTarget).val(), thisInstance.getCurrentDashboardId())
					.done((data) => {
						thisInstance.updateContentsDiv(contentsDiv, data);
					});
			});
		},
		/**
		 * Update contents div and register events.
		 * @param {jQuery} contentsDiv
		 * @param {HTMLElement} data
		 */
		updateContentsDiv(contentsDiv, data) {
			contentsDiv.html(data);
			App.Fields.Picklist.showSelect2ElementView(contentsDiv.find('.select2'));
			this.registerEvents();
		},
		/**
		 * Function to get the respective module layout editor through pjax
		 */
		getModuleLayoutEditor: function (selectedModule, selectedDashboard) {
			var aDeferred = jQuery.Deferred();
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});

			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['view'] = 'Configuration';
			params['sourceModule'] = selectedModule;
			params['dashboardId'] = selectedDashboard;
			AppConnector.requestPjax(params)
				.done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					aDeferred.resolve(data);
				})
				.fail(function (error) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					aDeferred.reject();
				});
			return aDeferred.promise();
		},

		/**
		 * Function to register the click event for delete widget
		 */
		registerDeleteWidgetEvent: function (contents) {
			if (typeof contents === 'undefined') {
				contents = jQuery('#layoutDashBoards');
			}
			contents.find('.js-delete-widget').on('click', (e) => {
				let widgetId = e.currentTarget.dataset.id;
				app.showConfirmModal({
					title: app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE'),
					confirmedCallback: () => {
						let progress = $.progressIndicator({
							message: app.vtranslate('JS_SAVE_LOADER_INFO'),
							position: 'html',
							blockInfo: {
								enabled: true
							}
						});
						app.saveAjax('delete', null, { widgetId: widgetId }).done((data) => {
							if (data.result === true) {
								Settings_Vtiger_Index_Js.showMessage({
									type: 'success',
									text: app.vtranslate('JS_CUSTOM_FIELD_DELETED')
								});
							}
							progress.progressIndicator({ mode: 'hide' });
							this.reloadContent();
						});
					}
				});
			});
		},

		registerWidgetEvent: function () {
			let contents = $('#layoutDashBoards');
			contents.find('.js-edit-widget').on('click', (e) => {
				let url = e.currentTarget.dataset.url;
				this.addWidget(url);
			});

			contents.find('.js-add-widget').on('click', (e) => {
				let url = e.currentTarget.dataset.url;
				if (!url) {
					return false;
				}
				app.showModalWindow({
					url: url,
					cb: (modalContainer) => {
						modalContainer.on('click', '.js-modal__save', (_) => {
							let selectedOption = modalContainer.find('.js-widget').val();
							if (selectedOption) {
								this.addWidget(selectedOption);
							}
						});
					}
				});
			});

			this.registerDeleteWidgetEvent(contents);
		},

		addWidget: function (url) {
			app.showModalWindow({
				url: url,
				cb: (container) => {
					app.showPopoverElementView(container.find('.js-popover-tooltip'));
				},
				sendByAjaxCb: (_, response) => {
					if (response.result === true) {
						Settings_Vtiger_Index_Js.showMessage({
							type: 'success',
							text: app.vtranslate('JS_WIDGET_ADDED')
						});
						this.reloadContent();
					}
				}
			});
		},
		/**
		 * register events for layout editor
		 */
		registerEvents: function () {
			this.registerAddBlockDashBoard();
			this.registerWidgetEvent();
			this.registerSpecialWidget();
			this.registerDeleteCustomBlockEvent();
			this.registerModulesChangeEvent();
			this.registerAddedDashboard();
			this.registerSelectDashboard();
			this.registerDashboardAction();
		}
	}
);

$(function () {
	var instance = new Settings_WidgetsManagement_Js();
	instance.registerEvents();
});
