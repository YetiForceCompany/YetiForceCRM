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

var App = (window.App = {
	Components: {
		Tree: {
			Basic: class {
				constructor(container = $('.js-tree-container')) {
					this.treeInstance = false;
					this.treeData = false;
					this.generateTree(container);
				}

				generateTree(container) {
					const slef = this;
					if (slef.treeInstance === false) {
						slef.treeInstance = container;
						slef.treeInstance
							.on('select_node.jstree', function (e, data) {
								if (data.event !== undefined && $(data.event.target).hasClass('jstree-checkbox')) {
									return;
								}
								data.instance.select_node(data.node.children_d);
							})
							.on('deselect_node.jstree', function (e, data) {
								if (data.event !== undefined && $(data.event.target).hasClass('jstree-checkbox')) {
									return;
								}
								data.instance.deselect_node(data.node.children_d);
							})
							.jstree({
								core: {
									data: slef.getRecords(container),
									themes: {
										name: 'proton',
										responsive: true
									}
								},
								plugins: ['search', 'checkbox']
							});
						this.registerSearchEvent();
					}
				}

				registerSearchEvent() {
					const self = this;
					let searchTimeout = false,
						treeSearch = $('.js-tree-search');
					treeSearch.on('keyup', () => {
						if (searchTimeout) {
							clearTimeout(searchTimeout);
						}
						searchTimeout = setTimeout(function () {
							var searchValue = treeSearch.val();
							self.treeInstance.jstree(true).search(searchValue);
						}, 250);
					});
				}

				getRecords(container) {
					if (this.treeData === false && container !== 'undefined') {
						this.treeData = JSON.parse(container.find('.js-tree-data').val());
					}
					return this.treeData;
				}
			}
		},
		/**
		 * Quick create object used by Header.js and yf plugins
		 *
		 */
		QuickCreate: {
			/**
			 * module quick create data cache
			 */
			moduleCache: {},
			/**
			 * Register function
			 * @param {jQuery} container
			 */
			register(container) {
				if (typeof container === 'undefined') {
					container = $('body');
				} else {
					container = $(container);
				}
				container.on('click', '.js-quick-create-modal', function (e) {
					e.preventDefault();
					let element = $(this);
					if (element.data('module')) {
						App.Components.QuickCreate.createRecord(element.data('module'));
					}
					if (element.data('url')) {
						let url = element.data('url');
						let urlObject = app.convertUrlToObject(url);
						let params = { callbackFunction: function () {} };
						const progress = $.progressIndicator({ blockInfo: { enabled: true } });
						App.Components.QuickCreate.getForm(url, urlObject.module, params).done((data) => {
							progress.progressIndicator({
								mode: 'hide'
							});
							App.Components.QuickCreate.showModal(data, params);
							app.registerEventForClockPicker();
						});
					}
				});
			},
			/**
			 * createRecord
			 *
			 * @param   {string}  moduleName
			 * @param   {object}  params
			 */
			createRecord(moduleName, params = {}) {
				if ('parentIframe' === CONFIG.modalTarget) {
					window.parent.App.Components.QuickCreate.createRecord(moduleName, params);
					return;
				}
				let url = 'index.php?module=' + moduleName + '&view=QuickCreateAjax';
				if (undefined === params.callbackFunction) {
					params.callbackFunction = function () {};
				}
				if (
					(app.getViewName() === 'Detail' || (app.getViewName() === 'Edit' && app.getRecordId() !== undefined)) &&
					app.getParentModuleName() != 'Settings'
				) {
					url += '&sourceModule=' + app.getModuleName();
					url += '&sourceRecord=' + app.getRecordId();
				}
				const progress = $.progressIndicator({ blockInfo: { enabled: true } });
				this.getForm(url, moduleName, params).done((data) => {
					progress.progressIndicator({
						mode: 'hide'
					});
					this.showModal(data, params);
					app.registerEventForClockPicker();
				});
			},
			/**
			 * Get quick create form
			 *
			 * @param   {string}  url
			 * @param   {string}  moduleName
			 * @param   {object}  params
			 *
			 * @return  {Promise} aDeferred
			 */
			getForm(url, moduleName, params = {}) {
				const aDeferred = $.Deferred();
				let requestParams;
				let isCacheActive = !params.noCache || undefined === params.noCache;
				if (isCacheActive) {
					if (App.Components.QuickCreate.moduleCache[moduleName]) {
						aDeferred.resolve(App.Components.QuickCreate.moduleCache[moduleName]);
						return aDeferred.promise();
					}
				}
				requestParams = url;
				if (typeof params.data !== 'undefined') {
					requestParams = {};
					requestParams['data'] = params.data;
					requestParams['url'] = url;
				}
				AppConnector.request(requestParams).done(function (data) {
					if (isCacheActive) {
						App.Components.QuickCreate.moduleCache[moduleName] = data;
					}
					aDeferred.resolve(data);
				});
				return aDeferred.promise();
			},
			/**
			 * Show modal
			 *
			 * @param   {string}  html
			 * @param   {object}  params
			 */
			showModal(html, params = {}) {
				app.showModalWindow(html, (container) => {
					const quickCreateForm = container.find('form.js-form');
					const moduleName = quickCreateForm.find('[name="module"]').val();
					const editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName);
					const moduleClassName = moduleName + '_QuickCreate_Js';
					editViewInstance.setForm(quickCreateForm);
					editViewInstance.registerBasicEvents(quickCreateForm);
					if (typeof window[moduleClassName] !== 'undefined') {
						new window[moduleClassName]().registerEvents(container);
					}
					quickCreateForm.validationEngine(app.validationEngineOptionsForRecord);
					if (typeof params.callbackPostShown !== 'undefined') {
						params.callbackPostShown(quickCreateForm);
					}
					this.registerPostLoadEvents(quickCreateForm, params);
				});
			},
			/**
			 * Register post load events
			 *
			 * @param   {object}  form jQuery
			 * @param   {object}  params
			 *
			 * @return  {boolean}
			 */
			registerPostLoadEvents(form, params) {
				const submitSuccessCallback = params.callbackFunction || function () {};
				const goToFullFormCallBack = params.goToFullFormcallback || function () {};
				form.on('submit', (e) => {
					const form = $(e.currentTarget);
					if (form.hasClass('not_validation')) {
						return true;
					}
					const moduleName = form.find('[name="module"]').val();
					//Form should submit only once for multiple clicks also
					if (typeof form.data('submit') !== 'undefined') {
						return false;
					} else {
						if (form.data('jqv').InvalidFields.length > 0) {
							//If validation fails, form should submit again
							form.removeData('submit');
							$.progressIndicator({ mode: 'hide' });
							e.preventDefault();
							return;
						} else {
							//Once the form is submiting add data attribute to that form element
							form.data('submit', 'true');
							$.progressIndicator({ mode: 'hide' });
						}

						const recordPreSaveEvent = $.Event(Vtiger_Edit_Js.recordPreSave);
						form.trigger(recordPreSaveEvent, {
							value: 'edit',
							module: moduleName
						});
						if (!recordPreSaveEvent.isDefaultPrevented()) {
							const moduleInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName);
							const saveHandler = !!moduleInstance.quickCreateSave ? moduleInstance.quickCreateSave : this.save;
							let progress = $.progressIndicator({
								message: app.vtranslate('JS_SAVE_LOADER_INFO'),
								position: 'html',
								blockInfo: {
									enabled: true
								}
							});
							saveHandler(form)
								.done((data) => {
									let modalContainer = form.closest('.modalContainer');
									if (modalContainer.length) {
										app.hideModalWindow(false, modalContainer[0].id);
									}
									submitSuccessCallback(data);
									app.event.trigger('QuickCreate.AfterSaveFinal', data, form);
									progress.progressIndicator({ mode: 'hide' });
									if (data.success) {
										app.showNotify({
											text: app.vtranslate('JS_SAVE_NOTIFY_SUCCESS'),
											type: 'success'
										});
										if ('List' === app.getViewName()) {
											let listInstance = new Vtiger_List_Js();
											listInstance.getListViewRecords();
										} else if ('Detail' === app.getViewName()) {
											if (app.getUrlVar('mode') === 'showRelatedList') {
												Vtiger_Detail_Js.getInstance().loadRelatedList();
											} else {
												window.location.reload();
											}
										}
									}
								})
								.fail(function (textStatus, errorThrown) {
									app.showNotify({
										text: errorThrown,
										title: app.vtranslate('JS_ERROR'),
										type: 'error'
									});
								});
						} else {
							//If validation fails in recordPreSaveEvent, form should submit again
							form.removeData('submit');
							$.progressIndicator({ mode: 'hide' });
						}
						e.preventDefault();
					}
				});

				form.find('.js-full-editlink').on('click', (e) => {
					const form = $(e.currentTarget).closest('form');
					const editViewUrl = $(e.currentTarget).data('url');
					goToFullFormCallBack(form);
					this.goToFullForm(form, editViewUrl);
				});

				this.registerTabEvents(form);
			},
			/**
			 * Function to navigate from quick create to edit iew full form
			 *
			 * @param   {object}  form  jQuery
			 */
			goToFullForm(form) {
				//As formData contains information about both view and action removed action and directed to view
				form.find('input[name="action"]').remove();
				form.append('<input type="hidden" name="view" value="Edit" />');
				$.each(form.find('[data-validation-engine]'), function (key, data) {
					$(data).removeAttr('data-validation-engine');
				});
				form.addClass('not_validation');
				form.submit();
			},
			/**
			 * Register tab events
			 *
			 * @param   {object}  form  jQuery
			 */
			registerTabEvents(form) {
				const tabElements = form.find('.nav.nav-pills , .nav.nav-tabs').find('a');
				//This will remove the name attributes and assign it to data-element-name . We are doing this to avoid
				//Multiple element to send as in calendar
				const quickCreateTabOnHide = function (target) {
					$(target)
						.find('[name]')
						.each(function (index, element) {
							element = $(element);
							element.attr('data-element-name', element.attr('name')).removeAttr('name');
						});
				};
				//This will add the name attributes and get value from data-element-name . We are doing this to avoid
				//Multiple element to send as in calendar
				const quickCreateTabOnShow = function (target) {
					$(target)
						.find('[data-element-name]')
						.each(function (index, element) {
							element = $(element);
							element.attr('name', element.attr('data-element-name')).removeAttr('data-element-name');
						});
				};
				tabElements.on('click', function (e) {
					quickCreateTabOnHide(tabElements.not('[aria-expanded="false"]').attr('data-target'));
					quickCreateTabOnShow($(this).attr('data-target'));
					//while switching tabs we have to clear the invalid fields list
					form.data('jqv').InvalidFields = [];
				});
				//To show aleady non active element , this we are doing so that on load we can remove name attributes for other fields
				tabElements.filter('a:not(.active)').each(function (e) {
					quickCreateTabOnHide($(this).attr('data-target'));
				});
			},
			/**
			 * Save quick create form
			 *
			 * @param   {object}  form  jQuery
			 *
			 * @return  {Promise}        aDeferred
			 */
			save(form) {
				let aDeferred = $.Deferred();
				AppConnector.request(form.serializeFormData())
					.done((data) => {
						aDeferred.resolve(data);
					})
					.fail(function (textStatus, errorThrown) {
						aDeferred.reject(textStatus, errorThrown);
					});
				return aDeferred.promise();
			}
		},
		QuickEdit: {
			/**
			 * Show modal
			 *
			 * @param   {string}  html
			 * @param   {object}  params
			 */
			showModal(params = {}, element) {
				const self = this;
				params['view'] = 'QuickEditModal';
				AppConnector.request(params).done(function (html) {
					app.showModalWindow(html, (container) => {
						let form = container.find('form[name="QuickEdit"]');
						let moduleName = form.find('[name="module"]').val();
						let editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName);
						let moduleClassName = moduleName + '_QuickEdit_Js';
						editViewInstance.setForm(form);
						editViewInstance.registerBasicEvents(form);
						if (typeof window[moduleClassName] !== 'undefined') {
							new window[moduleClassName]().registerEvents(container);
						}
						form.validationEngine(app.validationEngineOptionsForRecord);
						if (typeof params.callbackPostShown !== 'undefined') {
							params.callbackPostShown(form, params);
						}
						self.registerPostLoadEvents(form, params, element);
					});
				});
			},
			/**
			 * Register post load events
			 *
			 * @param   {object}  form jQuery
			 * @param   {object}  params
			 *
			 * @return  {boolean}
			 */
			registerPostLoadEvents(form, params, element) {
				const submitSuccessCallback = params.callbackFunction || function () {};
				const goToFullFormCallBack = params.goToFullFormcallback || function () {};
				form.on('submit', (e) => {
					const form = $(e.currentTarget);
					if (form.hasClass('not_validation')) {
						return true;
					}
					const moduleName = form.find('[name="module"]').val();
					//Form should submit only once for multiple clicks also
					if (typeof form.data('submit') !== 'undefined') {
						return false;
					} else {
						if (form.data('jqv').InvalidFields.length > 0) {
							//If validation fails, form should submit again
							form.removeData('submit');
							$.progressIndicator({ mode: 'hide' });
							e.preventDefault();
							return;
						} else {
							//Once the form is submiting add data attribute to that form element
							form.data('submit', 'true');
							$.progressIndicator({ mode: 'hide' });
						}

						const recordPreSaveEvent = $.Event(Vtiger_Edit_Js.recordPreSave);
						form.trigger(recordPreSaveEvent, {
							value: 'edit',
							module: moduleName
						});
						if (!recordPreSaveEvent.isDefaultPrevented()) {
							const moduleInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName);
							const saveHandler = !!moduleInstance.quickEditSave ? moduleInstance.quickEditSave : this.save;
							let progress = $.progressIndicator({
								message: app.vtranslate('JS_SAVE_LOADER_INFO'),
								position: 'html',
								blockInfo: {
									enabled: true
								}
							});
							saveHandler(form).done((data) => {
								const modalContainer = form.closest('.modalContainer');
								const parentModuleName = app.getModuleName();
								const viewName = app.getViewName();
								if (modalContainer.length) {
									app.hideModalWindow(false, modalContainer[0].id);
								}
								if (moduleName === parentModuleName && 'List' === viewName) {
									const listInstance = new Vtiger_List_Js();
									listInstance.getListViewRecords();
								}
								submitSuccessCallback(data);
								app.event.trigger('QuickEdit.AfterSaveFinal', data, form, element);
								progress.progressIndicator({ mode: 'hide' });
								if (data.success) {
									app.showNotify({
										text: app.vtranslate('JS_SAVE_NOTIFY_SUCCESS'),
										type: 'success'
									});
								}
								if ('Detail' === viewName && app.getRecordId() === form.find('[name="record"]').val()) {
									if (data.result._isViewable == false) {
										if (window !== window.parent) {
											window.parent.location.href = 'index.php?module=' + moduleName + '&view=ListPreview';
										} else {
											window.location.href = 'index.php?module=' + moduleName + '&view=List';
										}
									} else if (params.removeFromUrl) {
										let searchParams = new URLSearchParams(window.location.search);
										searchParams.delete('step');
										window.location.href = 'index.php?' + searchParams.toString();
									} else {
										window.location.reload();
									}
								}
							});
						} else {
							//If validation fails in recordPreSaveEvent, form should submit again
							form.removeData('submit');
							$.progressIndicator({ mode: 'hide' });
						}
						e.preventDefault();
					}
				});
				form.find('.js-full-editlink').on('click', (e) => {
					const form = $(e.currentTarget).closest('form');
					const editViewUrl = $(e.currentTarget).data('url');
					goToFullFormCallBack(form);
					this.goToFullForm(form, editViewUrl);
				});
			},
			/**
			 * Function to navigate from quick create to edit iew full form
			 *
			 * @param   {object}  form  jQuery
			 */
			goToFullForm(form) {
				form.find('input[name="action"]').remove();
				form.append('<input type="hidden" name="view" value="Edit" />');
				$.each(form.find('[data-validation-engine]'), function (key, data) {
					$(data).removeAttr('data-validation-engine');
				});
				form.addClass('not_validation');
				form.submit();
			},
			/**
			 * Save quick create form
			 *
			 * @param   {object}  form  jQuery
			 *
			 * @return  {Promise}        aDeferred
			 */
			save(form) {
				const aDeferred = $.Deferred();
				form.serializeFormData();
				let formData = new FormData(form[0]);
				AppConnector.request({
					url: 'index.php',
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false
				})
					.done(function (data) {
						aDeferred.resolve(data);
					})
					.fail(function (textStatus, errorThrown) {
						aDeferred.reject(textStatus, errorThrown);
					});
				return aDeferred.promise();
			}
		},
		Scrollbar: {
			active: true,
			defaults: {
				scrollbars: {
					autoHide: 'leave'
				}
			},
			page: {
				instance: {},
				element: null
			},
			initPage() {
				let scrollbarContainer = $('.mainBody');
				if (!scrollbarContainer.length) {
					scrollbarContainer = $('#page');
				}
				if (!scrollbarContainer.length) {
					scrollbarContainer = $('body');
				}
				if (this.active) {
					this.page.instance = this.y(scrollbarContainer);
					this.page.element = $(this.page.instance.getElements().viewport);
				}
			},
			xy(element, options = {}) {
				return element.overlayScrollbars(options).overlayScrollbars();
			},
			y(element, options) {
				const yOptions = {
					overflowBehavior: {
						x: 'h'
					}
				};
				const mergedOptions = Object.assign(this.defaults, options, yOptions);
				return element.overlayScrollbars(mergedOptions).overlayScrollbars();
			}
		},
		DropFile: class {
			constructor(container, params) {
				this.container = container;
				this.init(params);
			}
			/**
			 * Register function
			 * @param {jQuery} container
			 * @param {Object} params
			 */
			static register(container, params = {}) {
				if (typeof container === 'undefined') {
					container = $('body');
				}
				if (container.hasClass('js-drop-container') && !container.prop('disabled')) {
					return new App.Components.DropFile(container, params);
				}
				const instances = [];
				container.find('.js-drop-container').each((_, e) => {
					instances.push(new App.Components.DropFile($(e), params));
				});
				return instances;
			}
			/**
			 * Initiation
			 * @param {Object} params
			 */
			init(params) {
				let css = {
					border: this.container.css('border'),
					opacity: 'unset'
				};
				this.container.bind('dragenter dragover', (e) => {
					$(e.currentTarget).css({
						border: '2px dashed #4aa1f3',
						opacity: 0.4
					});
					e.preventDefault();
				});
				this.container.bind('dragleave', (e) => {
					$(e.currentTarget).css(css);
					e.preventDefault();
				});
				this.container.bind('drop', (e) => {
					let element = $(e.currentTarget).css(css);
					e.preventDefault();
					const files = e.originalEvent.dataTransfer.files;
					if (files.length < 1) {
						return false;
					}
					params.callback =
						params.callback ||
						function () {
							let progressIndicatorElement = $.progressIndicator({
								blockInfo: { enabled: true }
							});
							let formData = new FormData();
							for (let file of files) {
								formData.append(element.data('field-name'), file, file.name);
							}
							formData.append('action', 'SaveAjax');
							formData.append('record', element.data('id'));
							formData.append('module', element.data('module'));
							AppConnector.request({
								method: 'POST',
								data: formData,
								processData: false,
								contentType: false
							})
								.done(function (data) {
									if (data.success) {
										progressIndicatorElement.progressIndicator({ mode: 'hide' });
										app.showNotify({ text: app.vtranslate('JS_SAVE_NOTIFY_SUCCESS'), type: 'success' });
										if (element.closest('.js-detail-widget').length) {
											Vtiger_Detail_Js.getInstance().getFiltersDataAndLoad(e);
										}
									} else {
										app.showNotify({ text: app.vtranslate('JS_UNEXPECTED_ERROR'), type: 'error' });
										progressIndicatorElement.progressIndicator({ mode: 'hide' });
									}
								})
								.fail(function (error, err) {
									app.showNotify({ text: app.vtranslate('JS_ERROR'), type: 'error' });
									progressIndicatorElement.progressIndicator({ mode: 'hide' });
									app.errorLog(error, err);
								});
						};
					app.showConfirmModal(app.vtranslate('JS_CHANGE_CONFIRMATION'), (result) => {
						if (result) {
							params.callback(e, this);
						}
					});
				});
			}
		}
	},
	Notify: {
		/**
		 * Check if notifications are allowed
		 */
		isDesktopPermitted: function () {
			return typeof Notification !== 'undefined' && Notification.permission === 'granted';
		},
		/**
		 * Show desktop notification
		 * @param {Object} params
		 */
		desktop: function (params) {
			let type = 'error';
			params.modules = new Map([
				...PNotify.defaultModules,
				[
					PNotifyDesktop,
					{
						fallback: false,
						icon: params.icon
					}
				]
			]);
			if (typeof params.type !== 'undefined') {
				type = params.type;
				if (params.type != 'error') {
					params.hide = true;
				}
			}
			return PNotify[type](params);
		}
	}
});

var app = (window.app = {
	/**
	 * variable stores client side language strings
	 */
	languageString: [],
	breakpoints: {
		xs: 0,
		sm: 576,
		md: 768,
		lg: 992,
		xl: 1200,
		xxl: 1300,
		xxxl: 1700
	},
	cacheParams: [],
	modalEvents: [],
	mousePosition: { x: 0, y: 0 },
	childFrame: false,
	touchDevice: false,
	event: new (function () {
		this.el = $({});
		this.trigger = function () {
			this.el.trigger(arguments[0], Array.prototype.slice.call(arguments, 1));
		};
		this.on = function () {
			this.el.on.apply(this.el, arguments);
		};
		this.one = function () {
			this.el.one.apply(this.el, arguments);
		};
		this.off = function () {
			this.el.off.apply(this.el, arguments);
		};
	})(),
	/**
	 * Function to get the module name. This function will get the value from element which has id module
	 * @return : string - module name
	 */
	getModuleName: function () {
		return this.getMainParams('module');
	},
	/**
	 * Function to get the module name. This function will get the value from element which has id module
	 * @return : string - module name
	 */
	getParentModuleName: function () {
		return this.getMainParams('parent');
	},
	/**
	 * Function returns the current view name
	 */
	getViewName: function () {
		return this.getMainParams('view');
	},
	/**
	 * Function returns the record id
	 */
	getRecordId: function () {
		var view = this.getViewName();
		var recordId;
		if ($.inArray(view, ['Edit', 'PreferenceEdit', 'Detail', 'PreferenceDetail', 'DetailPreview']) !== -1) {
			recordId = this.getMainParams('recordId');
		}
		return recordId;
	},
	/**
	 * Function which will give you all details of the selected record
	 * @params {object} params - an object of values like {'record' : recordId, 'module' : searchModule, 'fieldType' : 'email'}
	 */
	getRecordDetails: function (params) {
		let aDeferred = $.Deferred();
		if (app.getParentModuleName() === 'Settings') {
			params.parent = 'Settings';
		}
		AppConnector.request(Object.assign(params, { action: 'GetData' }))
			.done(function (data) {
				if (data.success) {
					aDeferred.resolve(data);
				} else {
					aDeferred.reject(data.message);
				}
			})
			.fail(function (error) {
				aDeferred.reject();
			});
		return aDeferred.promise();
	},
	/**
	 * Function to get language
	 */
	getLanguage: function () {
		return $('body').data('language');
	},
	/**
	 * Function to get page title
	 */
	getPageTitle: function () {
		return document.title;
	},
	/**
	 * Function gets current window parent
	 * @returns {object}
	 */
	getWindowParent() {
		if (
			typeof window.frames[0] !== 'undefined' &&
			typeof window.frames[0].app !== 'undefined' &&
			window.frames[0].app.childFrame
		) {
			return window.frames[0];
		} else {
			return window;
		}
	},
	/**
	 * Check if current window is window top
	 */
	isWindowTop() {
		return window.top === window.self;
	},
	/**
	 * Function gets current window parent
	 * @returns {boolean}
	 */
	isTouchDevice() {
		let supportsTouch = false;
		if ('ontouchstart' in window) {
			// iOS & android
			supportsTouch = true;
		} else if (window.navigator.msPointerEnabled) {
			// Win8
			supportsTouch = true;
		} else if ('ontouchstart' in document.documentElement) {
			//additional check
			supportsTouch = true;
		}
		if (supportsTouch) {
			//remove browser scrollbar visibility (doesn't work in firefox, edge and ie)
			$("<style type='text/css'> ::-webkit-scrollbar { display: none;} </style>").appendTo('head');
		}
		return supportsTouch;
	},
	/**
	 * Check if string is json
	 * @param {string} str
	 * @returns {boolean}
	 */
	isJsonString(str) {
		try {
			JSON.parse(str);
		} catch (e) {
			return false;
		}
		return true;
	},
	/**
	 * Function to set page title
	 */
	setPageTitle: function (title) {
		document.title = title;
	},
	/**
	 * Function to get the contents container
	 * @returns jQuery object
	 */
	getContentsContainer: function () {
		return $('.bodyContents');
	},
	hidePopover: function (element) {
		if (typeof element === 'undefined') {
			element = $('body .js-popover-tooltip');
		}
		element.popover('hide');
	},
	hidePopoversAfterClick(popoverParent) {
		popoverParent.on('mousedown', (e) => {
			setTimeout(() => {
				popoverParent.popover('hide');
			}, 100);
		});
	},
	registerPopoverManualTrigger(element, manualTriggerDelay) {
		const hideDelay = 500;
		element.on('mouseleave', (e) => {
			setTimeout(() => {
				let currentPopover = this.getBindedPopover(element);
				if (
					!$(':hover').filter(currentPopover).length &&
					!currentPopover.find('.js-popover-tooltip--record[aria-describedby]').length
				) {
					currentPopover.popover('hide');
				}
			}, hideDelay);
		});

		element.on('mouseenter', () => {
			setTimeout(() => {
				if (element.is(':hover')) {
					element.popover('show');
					let currentPopover = this.getBindedPopover(element);
					currentPopover.on('mouseleave', () => {
						setTimeout(() => {
							if (
								!$(':hover').filter(currentPopover).length &&
								!currentPopover.find('.js-popover-tooltip--record[aria-describedby]').length
							) {
								currentPopover.popover('hide'); //close current popover
							}
							if (!$(':hover').filter($('.popover')).length) {
								$('.popover').popover('hide'); //close all popovers
							}
						}, hideDelay);
					});
				}
			}, manualTriggerDelay);
		});

		app.hidePopoversAfterClick(element);
	},
	isEllipsisActive(element) {
		let clone = element
			.clone()
			.addClass('u-text-ellipsis--not-active')
			.css(element.css(['font-size', 'font-weight', 'font-family']))
			.appendTo('body');
		clone.find('.u-text-ellipsis').removeClass('u-text-ellipsis').addClass('u-text-ellipsis--not-active');
		if (clone.width() - 1 > element.width()) {
			clone.remove();
			return true;
		}
		clone.remove();
		return false;
	},
	showPopoverElementView: function (selectElement = $('.js-popover-tooltip'), params = {}) {
		let defaultParams = {
			trigger: 'manual',
			manualTriggerDelay: 500,
			placement: 'auto',
			html: true,
			template:
				'<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
			container: 'body',
			boundary: 'viewport',
			delay: { show: 300, hide: 100 }
		};
		selectElement.each(function (index, domElement) {
			let element = $(domElement);
			let elementParams = $.extend(true, defaultParams, params, element.data());
			if (element.data('class')) {
				elementParams.template =
					'<div class="popover ' +
					element.data('class') +
					'" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>';
			}
			if (element.hasClass('delay0')) {
				elementParams.delay = { show: 0, hide: 0 };
			}
			element.popover(elementParams);
			if (elementParams.trigger === 'manual' || typeof elementParams.trigger === 'undefined') {
				app.registerPopoverManualTrigger(element, elementParams.manualTriggerDelay);
			}
			if (elementParams.callbackShown) {
				element.on('shown.bs.popover', function (e) {
					elementParams.callbackShown(e);
				});
			}
			element.addClass('popover-triggered');
		});
		return selectElement;
	},
	registerPopoverEllipsis({
		element = $('.js-popover-tooltip--ellipsis'),
		params = { trigger: 'hover focus' },
		container = $(window)
	} = {}) {
		const self = this;
		params = {
			callbackShown: () => {
				self.setPopoverPosition(element, container);
			},
			trigger: 'manual',
			placement: 'right',
			template:
				'<div class="popover js-popover--before-positioned" role="tooltip"><div class="popover-body"></div></div>'
		};
		let popoverText = element.find('.js-popover-text').length ? element.find('.js-popover-text') : element;
		if (!app.isEllipsisActive(popoverText)) {
			element.addClass('popover-triggered');
			return;
		}
		app.showPopoverElementView(element, params);
	},
	registerPopoverEllipsisIcon(
		selectElement = $('.js-popover-tooltip--ellipsis-icon'),
		params = { trigger: 'hover focus' }
	) {
		selectElement.each(function (index, domElement) {
			let element = $(domElement);
			let popoverText = element.find('.js-popover-text').length ? element.find('.js-popover-text') : element;
			if (!app.isEllipsisActive(popoverText)) {
				return;
			}
			let iconElement = element.find('.js-popover-icon');
			if (iconElement.length) {
				element.find('.js-popover-icon').removeClass('d-none');
				params.selector = '.js-popover-icon';
			}
			app.showPopoverElementView(element, params);
		});
	},
	/**
	 * Register popover record
	 * @param {jQuery} selectElement
	 * @param {object} customParams
	 */
	registerPopoverRecord: function (
		selectElement = $('a.js-popover-tooltip--record'),
		customParams = {},
		container = $(document)
	) {
		const self = this;
		app.showPopoverElementView(selectElement, {
			template:
				'<div class="popover c-popover--link js-popover--before-positioned" role="tooltip"><div class="popover-body"></div></div>',
			content: '<div class="d-none"></div>',
			manualTriggerDelay: app.getMainParams('recordPopoverDelay'),
			placement: 'right',
			callbackShown: () => {
				if (!selectElement.attr('href')) {
					return false;
				}
				let link = new URL(selectElement.eq(0).attr('href'), window.location.origin);
				if (!link.searchParams.get('record') || !link.searchParams.get('view')) {
					return false;
				}
				let url = link.href;
				url = url.replace('view=', 'xview=') + '&view=RecordPopover';
				let currentPopover = self.getBindedPopover(selectElement);
				let popoverBody = currentPopover.find('.popover-body');
				popoverBody.progressIndicator({});
				let appendPopoverData = (data) => {
					popoverBody.progressIndicator({ mode: 'hide' }).html(data);
					if (typeof customParams.callback === 'function') {
						customParams.callback(popoverBody);
					}
					self.setPopoverPosition(selectElement, container);
				};
				let cacheData = window.popoverCache[url];
				if (typeof cacheData !== 'undefined') {
					appendPopoverData(cacheData);
				} else {
					AppConnector.request(url).done((data) => {
						window.popoverCache[url] = data;
						appendPopoverData(data);
					});
				}
			}
		});
	},
	/**
	 * Update popover record position (overwrite bootstrap positioning, failing on huge elements)
	 * @param {jQuery} popover
	 * @param {number} offsetLeft
	 */
	setPopoverPosition(popoverElement, container = $(window)) {
		let popover = this.getBindedPopover(popoverElement);
		if (!popover.length) {
			return;
		}
		const iframeOffset = this.computePopoverIframeOffset(popoverElement);
		let windowHeight = $(window).height(),
			windowWidth = $(window).width(),
			popoverPadding = 10,
			popoverBody = popover.find('.popover-body'),
			popoverHeight = popoverBody.height(),
			popoverWidth = popoverBody.width(),
			offsetTop = app.mousePosition.y + iframeOffset.top,
			offsetLeft = app.mousePosition.x + iframeOffset.left;
		if (popoverHeight + offsetTop + popoverPadding > windowHeight) {
			offsetTop = offsetTop - popoverHeight - popoverPadding;
		}
		if (popoverWidth + offsetLeft + popoverPadding > windowWidth) {
			offsetLeft = windowWidth - popoverWidth;
		}
		popover.css({
			transform: `translate3d(${offsetLeft}px, ${offsetTop}px, 0)`
		});
		popover.removeClass('js-popover--before-positioned');
		popoverElement.one('hide.bs.popover', () => {
			popover.addClass('js-popover--before-positioned');
		});
	},
	/**
	 * Compute popover iframe offset
	 *
	 * @param   {Object}  popoverElement  jquery
	 *
	 * @return  {Object}                  offset top and left
	 */
	computePopoverIframeOffset(popoverElement) {
		let iframeOffsetTop = 0;
		let iframeOffsetLeft = 0;
		if (!$(document).find(popoverElement).length) {
			let iframe = $(document).find('iframe');
			const iframeOffset = iframe.offset();
			iframeOffsetTop += iframeOffset.top;
			iframeOffsetLeft += iframeOffset.left;
			if (!iframe.contents().find(popoverElement).length) {
				let iframe2 = iframe.contents().find('iframe');
				const iframeOffset2 = iframe2.offset();
				iframeOffsetTop += iframeOffset2.top;
				iframeOffsetLeft += iframeOffset2.left;
			}
		}
		return { top: iframeOffsetTop, left: iframeOffsetLeft };
	},
	/**
	 * Get binded popover
	 * @param {jQuery} element
	 * @returns {Mixed|jQuery|HTMLElement}
	 */
	getBindedPopover(element) {
		return $(`#${element.attr('aria-describedby')}`);
	},
	/**
	 * Function to check the maximum selection size of multiselect and update the results
	 * @params <object> multiSelectElement
	 * @params <object> select2 params
	 */
	registerChangeEventForMultiSelect: function (selectElement, params) {
		if (typeof selectElement === 'undefined') {
			return;
		}
		var instance = selectElement.data('select2');
		var limit = params.maximumSelectionLength;
		selectElement.on('change', function (e) {
			var data = instance.data();
			if ($.isArray(data) && data.length >= limit) {
				instance.updateResults();
			}
		});
	},
	/**
	 * Function to get data of the child elements in serialized format
	 * @params <object> parentElement - element in which the data should be serialized. Can be selector , domelement or jquery object
	 * @params <String> returnFormat - optional which will indicate which format return value should be valid values "object" and "string"
	 * @return <object> - encoded string or value map
	 */
	getSerializedData: function (parentElement, returnFormat) {
		if (typeof returnFormat === 'undefined') {
			returnFormat = 'string';
		}

		parentElement = $(parentElement);

		var encodedString = parentElement.children().serialize();
		if (returnFormat == 'string') {
			return encodedString;
		}
		var keyValueMap = {};
		var valueList = encodedString.split('&');

		for (var index in valueList) {
			var keyValueString = valueList[index];
			var keyValueArr = keyValueString.split('=');
			var nameOfElement = keyValueArr[0];
			var valueOfElement = keyValueArr[1];
			keyValueMap[nameOfElement] = decodeURIComponent(valueOfElement);
		}
		return keyValueMap;
	},
	showModalData(data, container, paramsObject, cb, url, sendByAjaxCb) {
		const thisInstance = this;
		let params = {
			show: true
		};
		if (!app.getMainParams('backgroundClosingModal')) {
			params.backdrop = 'static';
			params.keyboard = false;
		}
		if (typeof paramsObject === 'object') {
			container.css(paramsObject);
			params = $.extend(params, paramsObject);
		}
		container.html(data);
		if (container.find('.modal').hasClass('static')) {
			params.backdrop = 'static';
		}
		// In a modal dialog elements can be specified which can receive focus even though they are not descendants of the modal dialog.
		$.fn.modal.Constructor.prototype.enforceFocus = function (e) {
			$(document)
				.off('focusin.bs.modal') // guard against infinite focus loop
				.on(
					'focusin.bs.modal',
					$.proxy(function (e) {
						if ($(e.target).hasClass('select2-search__field')) {
							return true;
						}
					}, this)
				);
		};
		const modalContainer = container.find('.modal:first');
		modalContainer.one('shown.bs.modal', function () {
			thisInstance.registerDataTables(modalContainer.find('.js-modal-data-table'));
			cb(modalContainer);
			App.Fields.Picklist.changeSelectElementView(modalContainer);
			App.Fields.Date.register(modalContainer);
			App.Fields.Text.Editor.register(modalContainer.find('.js-editor'), {
				height: '5em',
				toolbar: 'Min'
			});
			app.registesterScrollbar(modalContainer);
			app.registerIframeEvents(modalContainer);
			modalContainer.find('.modal-dialog').draggable({
				handle: '.modal-title'
			});
			modalContainer.find('.modal-title').css('cursor', 'move');
		});
		$('body').append(container);
		modalContainer.modal(params);
		thisInstance.registerModalEvents(modalContainer, sendByAjaxCb);
	},
	showModalWindow: function (data, url, cb, paramsObject = {}) {
		if (!app.isCurrentWindowTarget('app.showModalWindow', arguments)) {
			return false;
		}
		const thisInstance = this;
		let sendByAjaxCb;
		Window.lastModalId = 'modal_' + Math.random().toString(36).substr(2, 9);
		//null is also an object
		if (typeof data === 'object' && data != null && !(data instanceof $)) {
			if (data.id != undefined) {
				Window.lastModalId = data.id;
			}
			paramsObject = data.css;
			cb = data.cb;
			url = data.url;
			if (data.sendByAjaxCb !== 'undefined') {
				sendByAjaxCb = data.sendByAjaxCb;
			}
			data = data.data;
		}
		if (typeof url === 'function') {
			if (typeof cb === 'object') {
				paramsObject = cb;
			}
			cb = url;
			url = false;
		} else if (typeof url === 'object') {
			cb = function () {};
			paramsObject = url;
			url = false;
		}
		if (typeof cb !== 'function') {
			cb = function () {};
		}
		if (typeof sendByAjaxCb !== 'function') {
			sendByAjaxCb = function () {};
		}
		if (paramsObject !== undefined && paramsObject.modalId !== undefined) {
			Window.lastModalId = paramsObject.modalId;
		}
		// prevent duplicate hash generation
		let container = $('#' + Window.lastModalId);
		if (container.length) {
			container.remove();
		}
		container = $('<div></div>');
		container.attr('id', Window.lastModalId).addClass('modalContainer js-modal-container');
		container.one('hidden.bs.modal', function () {
			container.remove();
			let backdrop = $('.modal-backdrop');
			if (!$('.modal.show').length) {
				backdrop.remove();
			}
			if (backdrop.length > 0) {
				$('body').addClass('modal-open');
			}
		});
		if (data) {
			thisInstance.showModalData(data, container, paramsObject, cb, url, sendByAjaxCb);
		} else {
			$.get(url).done(function (response) {
				thisInstance.showModalData(response, container, paramsObject, cb, url, sendByAjaxCb);
			});
		}
		return container;
	},
	/**
	 * Check if current window is target for a modal and trigger in correct window if not
	 *
	 * @param   {String}  sourceFunction  source function name in dot prop notation object
	 * @param   {Array}  args            source function arguments
	 *
	 * @return  {Boolean}                  isCurrentWindowTarget
	 */
	isCurrentWindowTarget(sourceFunction, args) {
		let isCurrentWindowTarget = true;
		if (CONFIG.modalTarget === 'parentIframe') {
			this.childFrame = true;
			sourceFunction = sourceFunction.split('.');
			sourceFunction.unshift('parent');
			sourceFunction = sourceFunction.reduce((o, i) => o[i], window);
			sourceFunction.apply(window.parent.app, args);
			isCurrentWindowTarget = false;
		}
		return isCurrentWindowTarget;
	},
	/**
	 * Function which you can use to hide the modal
	 * This api assumes that we are using block ui plugin and uses unblock api to unblock it
	 */
	hideModalWindow: function (callback, id) {
		if (!app.isCurrentWindowTarget('app.hideModalWindow', arguments)) {
			return false;
		}
		let container;
		if (callback && typeof callback === 'object') {
			container = callback;
		} else if (id == undefined) {
			container = $('.modalContainer');
		} else {
			container = $('#' + id);
		}
		if (container.length <= 0) {
			return;
		}
		if (typeof callback !== 'function') {
			callback = function () {};
		}
		let modalContainer = container.find('.modal');
		modalContainer.modal('hide');
		let backdrop = $('.modal-backdrop:last');
		if ($('.modalContainer').length == 0 && backdrop.length) {
			backdrop.remove();
		}
		modalContainer.one('hidden.bs.modal', callback);
	},
	registerModalController: function (modalId, modalContainer, cb) {
		let windowParent = this.childFrame ? window.parent : window;
		if (modalId === undefined) {
			modalId = Window.lastModalId;
		}
		if (modalContainer === undefined) {
			modalContainer = $('#' + modalId + ' .js-modal-data');
		}
		let moduleName = modalContainer.data('module') || 'Base';
		let modalClass = moduleName.replace(':', '_') + '_' + modalContainer.data('view') + '_JS';
		if (typeof windowParent[modalClass] === 'undefined') {
			modalClass = 'Base_' + modalContainer.data('view') + '_JS';
		}
		if (typeof windowParent[modalClass] !== 'undefined') {
			let instance = new windowParent[modalClass]();
			if (typeof cb === 'function') {
				cb(modalContainer, instance);
			}
			instance.registerEvents(modalContainer);
			if (modalId && app.modalEvents[modalId]) {
				app.modalEvents[modalId](modalContainer, instance);
			}
		}
	},
	registerModalEvents: function (container, sendByAjaxCb) {
		let form = container.find('form');
		let validationForm = false;
		if (form.hasClass('validateForm') || form.hasClass('js-validate-form')) {
			form.validationEngine(app.validationEngineOptions);
			validationForm = true;
		}
		if (container.data('view') === 'QuickDetailModal') {
			this.registerBlockAnimationEvent(container);
		}
		if (form.hasClass('sendByAjax') || form.hasClass('js-send-by-ajax')) {
			form.on('submit', function (e) {
				let save = true;
				e.preventDefault();
				if (validationForm && form.data('jqv').InvalidFields.length > 0) {
					app.formAlignmentAfterValidation(form);
					save = false;
				}
				if (save) {
					let progressIndicatorElement = $.progressIndicator({
						blockInfo: { enabled: true }
					});
					let formData = form.serializeFormData();
					AppConnector.request(formData)
						.done(function (responseData) {
							sendByAjaxCb(formData, responseData);
							if (responseData.success && responseData.result) {
								if (responseData.result.notify) {
									Vtiger_Helper_Js.showMessage(responseData.result.notify);
								}
								if (responseData.result.procesStop) {
									progressIndicatorElement.progressIndicator({ mode: 'hide' });
									return false;
								}
							}
							app.hideModalWindow();
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
						})
						.fail(function () {
							app.showNotify({
								text: app.vtranslate('JS_UNEXPECTED_ERROR'),
								type: 'error'
							});
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
						});
				}
			});
		}
	},
	registerFormsEvents: function (container) {
		let forms = container.find('form.js-form-ajax-submit,form.js-form-single-save');
		forms.each((i, form) => {
			form = $(form);
			let validationForm = false;
			if (form.hasClass('js-validate-form')) {
				form.validationEngine(app.validationEngineOptions);
				validationForm = true;
			}
			if (form.hasClass('js-form-single-save')) {
				form.find('select,input').on('change', function () {
					let element = $(this);
					if (validationForm && element.validationEngine('validate')) {
						return;
					}
					let progressIndicatorElement = $.progressIndicator({
						blockInfo: { enabled: true }
					});
					let formData = form.serializeFormData();
					let name = element.attr('name').replace('[]', '');
					formData['updateField'] = name;
					formData['updateValue'] = formData[name];
					AppConnector.request(formData)
						.done(function (responseData) {
							if (responseData.success && responseData.result) {
								if (responseData.result.notify) {
									app.showNotify(responseData.result.notify);
								}
							}
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
						})
						.fail(function (error) {
							app.showNotify({
								title: app.vtranslate('JS_UNEXPECTED_ERROR'),
								text: error,
								type: 'error'
							});
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
						});
				});
			}
			if (form.hasClass('js-form-ajax-submit')) {
				form.on('submit', function (e) {
					let save = true;
					e.preventDefault();
					if (validationForm && form.data('jqv').InvalidFields.length > 0) {
						app.formAlignmentAfterValidation(form);
						save = false;
					}
					if (save) {
						let progressIndicatorElement = $.progressIndicator({
							blockInfo: { enabled: true }
						});
						AppConnector.request(form.serializeFormData())
							.done(function (responseData) {
								if (responseData.success && responseData.result) {
									if (responseData.result.notify) {
										Vtiger_Helper_Js.showMessage(responseData.result.notify);
									}
								}
								progressIndicatorElement.progressIndicator({ mode: 'hide' });
							})
							.fail(function () {
								app.showNotify({
									text: app.vtranslate('JS_UNEXPECTED_ERROR'),
									type: 'error'
								});
								progressIndicatorElement.progressIndicator({ mode: 'hide' });
							});
					}
				});
			}
		});
	},
	isHidden: function (element) {
		if (element.css('display') == 'none') {
			return true;
		}
		return false;
	},
	isInvisible: function (element) {
		if (element.css('visibility') == 'hidden') {
			return true;
		}
		return false;
	},
	/**
	 * Default validation eninge options
	 */
	validationEngineOptions: {
		// Avoid scroll decision and let it scroll up page when form is too big
		// Reference: http://www.position-absolute.com/articles/jquery-form-validator-because-form-validation-is-a-mess/
		scroll: false,
		promptPosition: 'topLeft',
		//to support validation for select2 select box
		prettySelect: true,
		usePrefix: 's2id_'
	},
	validationEngineOptionsForRecord: {
		scroll: false,
		promptPosition: 'topLeft',
		//to support validation for select2 select box
		prettySelect: true,
		usePrefix: 's2id_',
		onBeforePromptType: function (field) {
			var block = field.closest('.js-toggle-panel');
			if (block.find('.blockContent').is(':hidden')) {
				block.find('.blockHeader').click();
			}
		}
	},
	/**
	 * Default scroll options
	 */
	scrollOptions: {
		wheelSpeed: 0.1
	},
	/**
	 * Function to push down the error message size when validation is invoked
	 * @params : form Element
	 */
	formAlignmentAfterValidation: function (form) {
		// to avoid hiding of error message under the fixed nav bar
		var formError = form.find(".formError:not('.greenPopup'):first");
		if (formError.length > 0) {
			var destination = formError.offset().top;
			var resizedDestnation = destination - 105;
			$('html').animate(
				{
					scrollTop: resizedDestnation
				},
				'slow'
			);
		}
	},
	convertToDatePickerFormat: function (dateFormat) {
		switch (dateFormat) {
			case 'yyyy-mm-dd':
				return 'Y-m-d';
				break;
			case 'mm-dd-yyyy':
				return 'm-d-Y';
				break;
			case 'dd-mm-yyyy':
				return 'd-m-Y';
				break;
			case 'yyyy.mm.dd':
				return 'Y.m.d';
				break;
			case 'mm.dd.yyyy':
				return 'm.d.Y';
				break;
			case 'dd.mm.yyyy':
				return 'd.m.Y';
				break;
			case 'yyyy/mm/dd':
				return 'Y/m/d';
				break;
			case 'mm/dd/yyyy':
				return 'm/d/Y';
				break;
			case 'dd/mm/yyyy':
				return 'd/m/Y';
				break;
		}
	},
	convertTojQueryDatePickerFormat: function (dateFormat) {
		let i,
			dotMode = '-';
		if (dateFormat.indexOf('-') !== -1) {
			dotMode = '-';
		}
		if (dateFormat.indexOf('.') !== -1) {
			dotMode = '.';
		}
		if (dateFormat.indexOf('/') !== -1) {
			dotMode = '/';
		}
		let splitDateFormat = dateFormat.split(dotMode);
		for (i in splitDateFormat) {
			let sectionDate = splitDateFormat[i];
			if (sectionDate.length === 4) {
				splitDateFormat[i] = sectionDate.substring(0, 2);
			}
		}
		return splitDateFormat.join(dotMode);
	},
	/*
	 * Converts user formated date to database format yyyy-mm-dd
	 */
	getDateInDBInsertFormat: function (dateFormat, dateString) {
		var i = 0;
		var dotMode = '-';
		if (dateFormat.indexOf('-') !== -1) {
			dotMode = '-';
		} else if (dateFormat.indexOf('.') !== -1) {
			dotMode = '.';
		} else if (dateFormat.indexOf('/') !== -1) {
			dotMode = '/';
		}
		var dateFormatParts = dateFormat.split(dotMode);
		var day = '',
			month = '',
			year = '';
		var dateParts = dateString.split(dotMode);
		for (i in dateFormatParts) {
			var sectionDate = dateFormatParts[i];
			switch (sectionDate) {
				case 'dd':
					day = dateParts[i];
					break;

				case 'mm':
					month = dateParts[i];
					break;

				case 'yyyy':
					year = dateParts[i];
					break;
			}
		}
		return year + '-' + month + '-' + day;
	},

	registerBlockAnimationEvent: function (container = false) {
		let detailViewContentHolder = $('div.details div.contents');
		let blockHeader = detailViewContentHolder.find('.blockHeader');
		if (container !== false) {
			blockHeader = container.find('.blockHeader');
		}
		blockHeader.on('click', function (e) {
			const target = $(e.target);
			if (
				target.is('input') ||
				target.is('button') ||
				target.parents().is('button') ||
				target.hasClass('js-stop-propagation') ||
				target.parents().hasClass('js-stop-propagation')
			) {
				return false;
			}
			let currentTarget = $(this).find('.js-block-toggle').not('.d-none');
			let blockId = currentTarget.data('id');
			let closestBlock = currentTarget.closest('.js-toggle-panel');
			let bodyContents = closestBlock.find('.blockContent');
			let data = currentTarget.data();
			let module = app.getModuleName();
			if (data.mode === 'show') {
				bodyContents.addClass('d-none');
				app.cacheSet(module + '.' + blockId, 0);
				currentTarget.addClass('d-none');
				closestBlock.find('[data-mode="hide"]').removeClass('d-none');
			} else {
				bodyContents.removeClass('d-none');
				app.cacheSet(module + '.' + blockId, 1);
				currentTarget.addClass('d-none');
				closestBlock.find('[data-mode="show"]').removeClass('d-none');
			}
		});
	},

	registerEventForDateFields: function (parentElement) {
		if (typeof parentElement === 'undefined') {
			parentElement = $('body');
		}

		parentElement = $(parentElement);
		let element;
		if (parentElement.hasClass('dateField')) {
			element = parentElement;
		} else {
			element = $('.dateField', parentElement);
		}
		element.datepicker({ autoclose: true }).on('changeDate', function (ev) {
			let currentElement = $(ev.currentTarget),
				dateFormat = currentElement.data('dateFormat').toUpperCase(),
				date = $.datepicker.formatDate(moment(ev.date).format(dateFormat), ev.date);
			currentElement.val(date);
		});
		App.Fields.Utils.hideMobileKeyboard(element);
	},
	registerEventForClockPicker: function (timeInputs = $('.clockPicker')) {
		if (!timeInputs.hasClass('clockPicker')) {
			timeInputs = timeInputs.find('.clockPicker');
		}
		if (!timeInputs.length) {
			return;
		}
		let params = {
			placement: 'bottom',
			autoclose: true,
			minutestep: 5
		};

		$('.js-clock__btn').on('click', (e) => {
			e.stopPropagation();
			let tempElement = $(e.currentTarget).closest('.time').find('input.clockPicker');
			if (tempElement.attr('disabled') !== 'disabled' && tempElement.attr('readonly') !== 'readonly') {
				tempElement.clockpicker('show');
			}
		});

		let formatTimeString = (timeInput) => {
			if (params.twelvehour) {
				let meridiemTime = '';
				params.afterDone = () => {
					//format time string after picking a value
					let timeString = timeInput.val(),
						timeStringFormatted = timeString.slice(0, timeString.length - 2) + ' ' + meridiemTime;
					timeInput.val(timeStringFormatted);
					app.event.trigger('Clockpicker.changed', timeInput);
				};
				params.beforeHide = () => {
					meridiemTime = $('.clockpicker-buttons-am-pm:visible').find('a:not(.text-white-50)').text();
				};
			} else {
				params.afterDone = () => {
					app.event.trigger('Clockpicker.changed', timeInput);
				};
			}
		};

		timeInputs.each((i, e) => {
			let timeInput = $(e);
			let formatTime = timeInputs.data('format') || CONFIG.hourFormat;
			params.twelvehour = parseInt(formatTime) === 12 ? true : false;
			formatTimeString(timeInput);
			timeInput.clockpicker(params);
		});
		App.Fields.Utils.hideMobileKeyboard(timeInputs);
	},
	registerDataTables: function (table, options = {}) {
		if ($.fn.dataTable == undefined) {
			return false;
		}
		if (table.length == 0) {
			return false;
		}
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
		return table.DataTable(options);
	},
	/**
	 * Function to get the select2 element from the raw select element
	 * @params: select element
	 * @return : select2Element - corresponding select2 element
	 */
	getSelect2ElementFromSelect: function (selectElement) {
		var selectId = selectElement.attr('id');
		//since select2 will add s2id_ to the id of select element
		var select2EleId = 'select2-' + selectId + '-container';
		return $('#' + select2EleId).closest('.select2-container');
	},
	/**
	 * Function to set with of the element to parent width
	 * @params : jQuery element for which the action to take place
	 */
	setInheritWidth: function (elements) {
		$(elements).each(function (index, element) {
			var parentWidth = $(element).parent().width();
			$(element).width(parentWidth);
		});
	},
	showNewScrollbar: function (element, options = { wheelPropagation: true }) {
		if (typeof element === 'undefined' || !element.length) return;
		return new PerfectScrollbar(element[0], Object.assign(this.scrollOptions, options));
	},
	showNewScrollbarTopBottomRight: function (element, options = {}) {
		if (typeof element === 'undefined' || !element.length) return;
		options = Object.assign(this.scrollOptions, options);
		let scrollbarTopLeftInit = new PerfectScrollbar(element[0], options);
		let scrollbarTopElement = element.find('.ps__rail-x').first();
		scrollbarTopElement.css({
			top: 0,
			bottom: 'auto'
		});
		scrollbarTopElement.find('.ps__thumb-x').css({
			top: 2,
			bottom: 'auto'
		});
		let scrollbarBottomRightInit = new PerfectScrollbar(element[0], options);
		return [scrollbarTopLeftInit, scrollbarBottomRightInit];
	},
	showNewScrollbarTopBottom: function (element, options = { wheelPropagation: true, suppressScrollY: true }) {
		if (typeof element === 'undefined' || !element.length) return;
		options = Object.assign(this.scrollOptions, options);
		new PerfectScrollbar(element[0], options);
		new PerfectScrollbar(element[0], options);
		var scrollbarTopElement = element.find('.ps__rail-x').first();
		scrollbarTopElement.css({
			top: 0,
			bottom: 'auto'
		});
		scrollbarTopElement.find('.ps__thumb-x').css({
			top: 2,
			bottom: 'auto'
		});
	},
	showNewScrollbarTop: function (element, options = { wheelPropagation: true, suppressScrollY: true }) {
		if (typeof element === 'undefined' || !element.length) return;
		options = Object.assign(this.scrollOptions, options);
		new PerfectScrollbar(element[0], options);
		var scrollbarTopElement = element.find('.ps__rail-x').first();
		scrollbarTopElement.css({
			top: 0,
			bottom: 'auto'
		});
		scrollbarTopElement.find('.ps__thumb-x').css({
			top: 2,
			bottom: 'auto'
		});
	},
	showNewScrollbarLeft: function (element, options = { wheelPropagation: true }) {
		if (typeof element === 'undefined' || !element.length) return;
		options = Object.assign(this.scrollOptions, options);
		new PerfectScrollbar(element[0], options);
		var scrollbarLeftElement = element.children('.ps__rail-y').first();
		scrollbarLeftElement.css({
			left: 0,
			right: 'auto'
		});
		scrollbarLeftElement.find('.ps__thumb-y').css({
			left: 2,
			right: 'auto'
		});
	},
	showScrollBar: function (element, options = {}) {
		if (typeof options.height === 'undefined') options.height = element.css('height');
		return element.slimScroll(options);
	},
	/**
	 * Register middle scroll hack for scrollbar libraries
	 * @param {jQuery} container
	 */
	registerMiddleClickScroll(container) {
		let middleScroll = false;
		container.on('mousedown', (e) => {
			let clickedMouseButton = e.which; // get clicked button id
			if (clickedMouseButton == 2 && middleScroll == false) {
				middleScroll = true;
				let mouseY = e.pageY,
					mouseX = e.pageX;
				$(document).on('mousemove', (e) => {
					if (middleScroll == true) {
						$('body').addClass('u-cursor-scroll-all');
						let mouseMoveY = mouseY - e.pageY,
							scrollSlowerRate = 100, // higher number = slower scroll
							contentScrollY = container.scrollTop(),
							scrollerY = contentScrollY - mouseMoveY - scrollSlowerRate,
							mouseMoveX = mouseX - e.pageX,
							contentScrollX = container.scrollLeft(),
							scrollerX = contentScrollX - mouseMoveX - scrollSlowerRate;
						container.scrollTop(scrollerY);
						container.scrollLeft(scrollerX);
					}
				});
			}
		});
		container.on('mouseup', () => {
			$('body').removeClass('u-cursor-scroll-all');
			middleScroll = false;
		});
	},
	/**
	 * Function returns translated string
	 */
	vtranslate: function (key) {
		if (key in LANG) {
			return LANG[key];
		}
		return key;
	},
	/*
	 * Cache API on client-side
	 */
	cacheNSKey: function (key) {
		// Namespace in client-storage
		return 'yf.' + key;
	},
	cacheGet: function (key) {
		key = this.cacheNSKey(key);
		return store.get(key);
	},
	cacheSet: function (key, value) {
		key = this.cacheNSKey(key);
		store.set(key, value);
	},
	cacheClear: function (key) {
		key = this.cacheNSKey(key);
		return store.remove(key);
	},
	moduleCacheSet: function (key, value) {
		var orgKey = key;
		key = this.getModuleName() + '_' + key;
		this.cacheSet(key, value);

		var cacheKey = 'mCache' + this.getModuleName();
		var moduleCache = this.cacheGet(cacheKey);
		if (moduleCache == null) {
			moduleCache = [];
		} else {
			moduleCache = moduleCache.split(',');
		}
		moduleCache.push(orgKey);
		this.cacheSet(cacheKey, Vtiger_Helper_Js.unique(moduleCache).join(','));
	},
	moduleCacheGet: function (key) {
		return this.cacheGet(this.getModuleName() + '_' + key);
	},
	moduleCacheKeys: function () {
		var cacheKey = 'mCache' + this.getModuleName();
		var modules = this.cacheGet(cacheKey);
		if (modules) {
			return modules.split(',');
		}
		return [];
	},
	moduleCacheClear: function (key) {
		var thisInstance = this;
		var moduleName = this.getModuleName();
		var cacheKey = 'mCache' + moduleName;
		var moduleCache = this.cacheGet(cacheKey);
		if (moduleCache == null) {
			moduleCache = [];
		} else {
			moduleCache = moduleCache.split(',');
		}
		$.each(moduleCache, function (index, value) {
			thisInstance.cacheClear(moduleName + '_' + value);
		});
		thisInstance.cacheClear(cacheKey);
	},
	htmlEncode: function (value) {
		if (value) {
			return $('<div />').text(value).html();
		} else {
			return '';
		}
	},
	htmlDecode: function (value) {
		if (value) {
			return $('<div />').html(value).text();
		} else {
			return '';
		}
	},
	/**
	 * Function places an element at the center of the page
	 * @param <jQuery Element> element
	 */
	placeAtCenter: function (element) {
		element.css('position', 'absolute');
		element.css('top', ($(window).height() - element.outerHeight()) / 2 + $(window).scrollTop() + 'px');
		element.css('left', ($(window).width() - element.outerWidth()) / 2 + $(window).scrollLeft() + 'px');
	},
	getvalidationEngineOptions: function (select2Status) {
		return Object.assign({}, app.validationEngineOptions);
	},
	/**
	 * Function to notify UI page ready after AJAX changes.
	 * This can help in re-registering the event handlers (which was done during ready event).
	 */
	notifyPostAjaxReady: function () {
		$(document).trigger('postajaxready');
	},
	/**
	 * Listen to xready notiications.
	 */
	listenPostAjaxReady: function (callback) {
		$(document).on('postajaxready', callback);
	},
	/**
	 * Form function handlers
	 */
	setFormValues: function (kv) {
		for (var k in kv) {
			$(k).val(kv[k]);
		}
	},
	setRTEValues: function (kv) {
		for (var k in kv) {
			var rte = CKEDITOR.instances[k];
			if (rte) rte.setData(kv[k]);
		}
	},
	/**
	 * Function returns the javascript controller based on the current view
	 */
	getPageController: function () {
		if (window.pageController) {
			return window.pageController;
		}
		const moduleName = app.getModuleName();
		const view = app.getViewName();
		const parentModule = app.getParentModuleName();
		let moduleClassName = parentModule + '_' + moduleName + '_' + view + '_Js';
		if (typeof window[moduleClassName] === 'undefined') {
			moduleClassName = parentModule + '_Vtiger_' + view + '_Js';
		}
		if (typeof window[moduleClassName] === 'undefined') {
			moduleClassName = moduleName + '_' + view + '_Js';
		}
		var extendModules = $('#extendModules').val();
		if (typeof window[moduleClassName] === 'undefined' && extendModules != undefined) {
			moduleClassName = extendModules + '_' + view + '_Js';
		}
		if (typeof window[moduleClassName] === 'undefined') {
			moduleClassName = 'Vtiger_' + view + '_Js';
		}
		if (typeof window[moduleClassName] !== 'undefined') {
			if (typeof window[moduleClassName] === 'function') {
				return (window.pageController = new window[moduleClassName]());
			}
			if (typeof window[moduleClassName] === 'object') {
				return (window.pageController = window[moduleClassName]);
			}
		}
		let moduleBaseClassName = parentModule + '_' + moduleName + '_' + 'Index_Js';
		if (typeof window[moduleBaseClassName] !== 'undefined') {
			if (typeof window[moduleBaseClassName] === 'function') {
				return (window.pageController = new window[moduleBaseClassName]());
			}
			if (typeof window[moduleBaseClassName] === 'object') {
				return (window.pageController = window[moduleBaseClassName]);
			}
		}
	},
	/**
	 * Function to decode the encoded htmlentities values
	 */
	getDecodedValue: function (value) {
		return $('<div></div>').html(value).text();
	},
	getCookie: function (c_name) {
		var c_value = document.cookie;
		var c_start = c_value.indexOf(' ' + c_name + '=');
		if (c_start === -1) {
			c_start = c_value.indexOf(c_name + '=');
		}
		if (c_start === -1) {
			c_value = null;
		} else {
			c_start = c_value.indexOf('=', c_start) + 1;
			var c_end = c_value.indexOf(';', c_start);
			if (c_end === -1) {
				c_end = c_value.length;
			}
			c_value = unescape(c_value.substring(c_start, c_end));
		}
		return c_value;
	},
	setCookie: function (c_name, value, exdays) {
		var exdate = new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value = escape(value) + (exdays == null ? '' : '; expires=' + exdate.toUTCString());
		document.cookie = c_name + '=' + c_value;
	},
	getUrlVar: function (varName) {
		var getVar = function () {
			var vars = {};
			window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
				vars[key] = value;
			});
			return vars;
		};

		return getVar()[varName];
	},
	getStringDate: function (date) {
		var d = date.getDate();
		var m = date.getMonth() + 1;
		var y = date.getFullYear();

		d = d <= 9 ? '0' + d : d;
		m = m <= 9 ? '0' + m : m;
		return y + '-' + m + '-' + d;
	},
	formatDate: function (date) {
		var y = date.getFullYear(),
			m = date.getMonth() + 1,
			d = date.getDate(),
			h = date.getHours(),
			i = date.getMinutes(),
			s = date.getSeconds();
		return (
			y +
			'-' +
			this.formatDateZ(m) +
			'-' +
			this.formatDateZ(d) +
			' ' +
			this.formatDateZ(h) +
			':' +
			this.formatDateZ(i) +
			':' +
			this.formatDateZ(s)
		);
	},
	formatDateZ: function (i) {
		return i <= 9 ? '0' + i : i;
	},
	howManyDaysFromDate: function (time) {
		var fromTime = time.getTime();
		var today = new Date();
		var toTime = new Date(today.getFullYear(), today.getMonth(), today.getDate()).getTime();
		return Math.floor((toTime - fromTime) / (1000 * 60 * 60 * 24)) + 1;
	},
	saveAjax: function (mode, param, addToParams) {
		var aDeferred = $.Deferred();
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'SaveAjax';
		if (mode) {
			params['mode'] = mode;
		}
		params['param'] = param;
		if (addToParams != undefined) {
			for (var i in addToParams) {
				params[i] = addToParams[i];
			}
		}
		AppConnector.request(params)
			.done(function (data) {
				aDeferred.resolve(data);
			})
			.fail(function (textStatus, errorThrown) {
				aDeferred.reject(textStatus, errorThrown);
			});
		return aDeferred.promise();
	},
	/**
	 * Hack for Safari breaking down, when sending empty file input
	 * @param html
	 */
	removeEmptyFilesInput(form) {
		for (let i = 0; i < form.elements.length; i++) {
			if (form.elements[i].type === 'file') {
				if (form.elements[i].value === '') {
					form.elements[i].parentNode.removeChild(form.elements[i]);
				}
			}
		}
	},
	getMainParams: function (param, json) {
		if (param in CONFIG) {
			return CONFIG[param];
		}
		if (app.cacheParams[param] === undefined) {
			app.cacheParams[param] = $('#' + param).val();
		}
		let value = app.cacheParams[param];
		if (json) {
			if (value) {
				value = JSON.parse(value);
			} else {
				value = [];
			}
		}
		return value;
	},
	setMainParams: function (param, value) {
		app.cacheParams[param] = value;
		$('#' + param).val(value);
	},
	errorLog: function (error, err, errorThrown) {
		if (!CONFIG.debug) {
			return;
		}
		console.warn(
			'%cYetiForce debug mode!!!',
			'color: red; font-family: sans-serif; font-size: 1.5em; font-weight: bolder; text-shadow: #000 1px 1px;'
		);
		if (typeof error === 'object' && error.responseText) {
			error = error.responseText;
		}
		if (typeof error === 'object' && error.statusText) {
			error = error.statusText;
		}
		if (error) {
			console.error(error);
		}
		if (err && err !== 'error') {
			console.error(err);
		}
		if (errorThrown) {
			console.error(errorThrown);
		}
	},
	registerQuickEditModal: function (container) {
		if (typeof container === 'undefined') {
			container = $('body');
		}
		container.on('click', '.js-quick-edit-modal', function (e) {
			e.preventDefault();
			let element = $(this);
			let data = {
				module: element.data('module'),
				record: element.data('record'),
				removeFromUrl: 'step'
			};
			if (element.data('values')) {
				$.extend(data, element.data('values'));
			}
			if (element.data('mandatoryFields')) {
				data.mandatoryFields = element.data('mandatoryFields');
			}
			if (element.data('showLayout')) {
				data.showLayout = element.data('showLayout');
			}
			if (element.data('editFields')) {
				data.editFields = element.data('editFields');
			}
			if (element.data('picklistValues')) {
				data.picklistValues = element.data('picklistValues');
			}
			App.Components.QuickEdit.showModal(data, element);
		});
	},
	registerModal: function (container) {
		if (typeof container === 'undefined') {
			container = $('body');
		}
		container
			.off('click', 'button.showModal, a.showModal, .js-show-modal')
			.on('click', 'button.showModal, a.showModal, .js-show-modal', function (e) {
				e.preventDefault();
				var currentElement = $(e.currentTarget);
				var url = currentElement.data('url');

				if (typeof url !== 'undefined') {
					if (currentElement.hasClass('js-popover-tooltip')) {
						currentElement.popover('hide');
					}
					if (currentElement.hasClass('disabledOnClick')) {
						currentElement.attr('disabled', true);
					}
					var modalWindowParams = {
						url: url,
						cb: function (container) {
							var call = currentElement.data('cb');
							if (typeof call !== 'undefined') {
								if (call.indexOf('.') !== -1) {
									var callerArray = call.split('.');
									if (typeof window[callerArray[0]] === 'object' || typeof window[callerArray[0]] === 'function') {
										window[callerArray[0]][callerArray[1]](container);
									}
								} else {
									if (typeof window[call] === 'function') {
										window[call](container);
									}
								}
							}
							currentElement.removeAttr('disabled');
						}
					};
					if (currentElement.data('modalid')) {
						modalWindowParams['id'] = currentElement.data('modalid');
					}
					app.showModalWindow(modalWindowParams);
				}
				e.stopPropagation();
			});
		container.off('click', '.js-show-modal-content').on('click', '.js-show-modal-content', function (e) {
			e.preventDefault();
			let currentElement = $(e.currentTarget);
			let content = currentElement.data('content');
			let title = '',
				modalClass = '';
			if (currentElement.data('title')) {
				title = currentElement.data('title');
			}
			if (currentElement.data('class')) {
				modalClass = currentElement.data('class');
			}
			app.showModalWindow(`<div class="modal" tabindex="-1" role="dialog"><div class="modal-dialog ${modalClass}" role="document"><div class="modal-content">
			<div class="modal-header"> <h5 class="modal-title">${title}</h5>
			  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div><div class="modal-body text-break">${content}</div></div></div></div>`);
			e.stopPropagation();
		});
	},
	playSound: function (action) {
		var soundsConfig = app.getMainParams('sounds');
		if (soundsConfig['IS_ENABLED']) {
			var audio = new Audio(app.getMainParams('soundFilesPath') + soundsConfig[action]);
			audio.play();
		}
	},
	registerIframeAndMoreContent(container = $(document)) {
		let showMoreModal = (e) => {
			e.preventDefault();
			e.stopPropagation();
			const btn = $(e.currentTarget);
			const message = btn.data('iframe')
				? btn.siblings('iframe').clone().show()
				: btn.closest('.js-more-content').find('.fullContent').html();
			bootbox.dialog({
				message,
				title: '<span class="mdi mdi-overscan"></span>  ' + app.vtranslate('JS_FULL_TEXT'),
				className: 'u-word-break modal-fullscreen',
				buttons: {
					danger: {
						label: '<span class="fas fa-times mr-1"></span>' + app.vtranslate('JS_CLOSE'),
						className: 'btn-danger',
						callback: function () {}
					}
				}
			});
		};
		container.on('click', '.js-more', showMoreModal);
	},
	registerIframeEvents(content) {
		content.find('.js-iframe-full-height').each(function () {
			let iframe = $(this);
			iframe.on('load', (e) => {
				iframe.height(iframe.contents().find('body').height() + 50);
			});
		});
		content.find('.js-modal-iframe').each(function () {
			let iframe = $(this);
			iframe.on('load', (e) => {
				let height = iframe.contents().find('body').height();
				if (height && height < iframe.height()) {
					iframe.height(height + 50);
				}
			});
		});
	},
	registerMenu: function () {
		const self = this;
		self.keyboard = { DOWN: 40, ESCAPE: 27, LEFT: 37, RIGHT: 39, SPACE: 32, UP: 38 };
		self.sidebarBtn = $('.js-sidebar-btn').first();
		self.sidebar = $('.js-sidebar').first();
		self.sidebarBtn.on('click', self.toggleSidebar.bind(self));
		$(`a.nav-link,[tabindex],input,select,textarea,button`).on('focus', (e) => {
			if (self.sidebarBtn[0] == e.target || self.sidebar.find(e.target).length) return;
			if (self.sidebar.find(':focus').length) {
				self.openSidebar();
			} else if (self.sidebar.hasClass('js-expand')) {
				self.closeSidebar();
			}
		});
		self.sidebar.on('mouseenter', self.openSidebar.bind(self)).on('mouseleave', self.closeSidebar.bind(self));
		self.sidebar.find('.js-menu__content').on('keydown', self.sidebarKeyboard.bind(self));
		self.sidebar.on('keydown', (e) => {
			if (e.which == self.keyboard.ESCAPE) {
				self.closeSidebar();
				if (self.sidebarBtn.is(':tabbable')) self.sidebarBtn.focus();
				else
					$(':tabbable')
						.eq(parseInt($(':tabbable').index(self.sidebar.find(':tabbable').last())) + 1)
						.focus();
			}
		});
		$('.js-submenu-toggler').on('click', (e) => {
			if (!$(e.currentTarget).hasClass('collapsed') && !$(e.target).closest('.toggler').length) {
				window.location = $(e.currentTarget).attr('href');
			}
		});
		self.registerPinEvent();
	},
	openSidebar: function () {
		this.sidebar.addClass('js-expand');
		this.sidebarBtn.attr('aria-expanded', true);
	},
	closeSidebar: function () {
		this.sidebar.removeClass('js-expand');
		this.sidebarBtn.attr('aria-expanded', false);
	},
	toggleSidebar: function () {
		if (this.sidebar.hasClass('js-expand')) {
			this.closeSidebar();
		} else {
			this.openSidebar();
			this.sidebar.find('.js-menu__content :tabbable').first().focus();
		}
	},
	registerPinEvent: function () {
		const self = this;
		let pinButton = self.sidebar.find('.js-menu--pin');
		let baseContainer = self.sidebar.closest('.js-base-container');
		pinButton.on('click', () => {
			let hideMenu = 0;
			baseContainer.removeClass('c-menu--animation');
			if (pinButton.attr('data-show') === '0') {
				hideMenu = 'on';
				pinButton.removeClass('u-opacity-muted');
				baseContainer.addClass('c-menu--open');
				self.sidebar.off('mouseleave mouseenter');
			} else {
				pinButton.addClass('u-opacity-muted');
				baseContainer.removeClass('c-menu--open');
				self.sidebar.on('mouseenter', self.openSidebar.bind(self)).on('mouseleave', self.closeSidebar.bind(self));
				self.closeSidebar.bind(self);
			}
			AppConnector.request({
				module: 'Users',
				action: 'SaveAjax',
				field: 'leftpanelhide',
				record: CONFIG.userId,
				value: hideMenu
			}).done(function (responseData) {
				if (responseData.success && responseData.result) {
					pinButton.attr('data-show', hideMenu);
				}
			});
			setTimeout(() => {
				baseContainer.addClass('c-menu--animation');
			}, 300);
		});
	},
	sidebarKeyboard: function (e) {
		let target = $(e.target);
		if (e.which == this.keyboard.LEFT) {
			if (target.hasClass('js-submenu-toggler') && !target.hasClass('collapsed')) {
				target.click();
				return false;
			} else {
				let toggler = $(e.target).closest('.js-submenu').prev('.js-submenu-toggler');
				if (toggler.length && !toggler.hasClass('collapsed')) {
					toggler.click().focus();
					return false;
				}
			}
		} else if (
			(target.hasClass('js-submenu-toggler') && e.which == this.keyboard.RIGHT && target.hasClass('collapsed')) ||
			(target.hasClass('js-submenu-toggler') && e.which == this.keyboard.SPACE)
		) {
			target.click();
			return false;
		} else if (e.which == this.keyboard.UP) {
			this.sidebar
				.find('.js-menu__content :tabbable')
				.eq(parseInt(this.sidebar.find('.js-menu__content :tabbable').index(target)) - 1)
				.focus();
			return false;
		} else if (e.which == this.keyboard.DOWN) {
			this.sidebar
				.find('.js-menu__content :tabbable')
				.eq(parseInt(this.sidebar.find('.js-menu__content :tabbable').index(target)) + 1)
				.focus();
			return false;
		}
	},
	registerTabdrop: function () {
		let tabs = $('.js-tabdrop');
		if (!tabs.length) return;
		let tab = tabs.find('> li');
		tab.each(function () {
			$(this).removeClass('d-none');
		});
		tabs.tabdrop({
			text: app.vtranslate('JS_MORE')
		});
		//change position to the last element (wcag keyboard navigation)
		let dropdown = tabs.find('> li.dropdown');
		dropdown.appendTo(tabs);
		//fix for toggle button text not changing
		tab.on('click', function (e) {
			setTimeout(function () {
				$(window).trigger('resize');
			}, 500);
		});
		$(window).trigger('resize');
	},
	getScreenHeight: function (percantage) {
		if (typeof percantage === 'undefined') {
			percantage = 100;
		}
		return ($(window).height() * percantage) / 100;
	},
	clearBrowsingHistory: function () {
		AppConnector.request({
			module: 'Home',
			action: 'BrowsingHistory'
		}).done(function (response) {
			$('.historyList').html(
				`<a class="item dropdown-item" href="#" role="listitem">${app.vtranslate('JS_NO_RECORDS')}</a>`
			);
		});
	},
	/**
	 * Open url in top window
	 * @param string url
	 */
	openUrl(url) {
		if (CONFIG.openUrlTarget === 'parentIframe') {
			window.parent.location.href = url;
		} else {
			window.location.href = url;
		}
	},
	openUrlMethodPost(url, postData = {}, formAttr = {}) {
		$.extend(formAttr, {
			method: 'post',
			action: url,
			style: 'display:none;'
		});
		let form = $('<form></form>', formAttr);
		if (typeof csrfMagicName !== 'undefined') {
			postData[csrfMagicName] = csrfMagicToken;
		}
		$.each(postData, (index, value) => {
			let input = $(document.createElement('input'));
			input.attr('type', 'hidden');
			input.attr('name', index);
			input.val(value);
			form.append(input);
		});
		$('body').append(form);
		form.submit();
		form.remove();
	},
	/**
	 * Convert url string to object
	 *
	 * @param   {string}  url  example: index.php?module=LayoutEditor&parent=Settings
	 *
	 * @return  {object}       urlObject
	 */
	convertUrlToObject(url) {
		let urlObject = {};
		if (url.indexOf('index.php?') !== -1) {
			url = url.split('index.php?')[1];
		}
		url.split('&').forEach((el) => {
			if (el.includes('=')) {
				let values = el.split('=');
				urlObject[values[0]] = values[1];
			}
		});
		return urlObject;
	},
	/**
	 * Convert object to url string
	 *
	 * @param   {object}  urlData
	 * @param   {string}  entryFile
	 *
	 * @return  {string}          url
	 */
	convertObjectToUrl(urlData = {}, entryFile = 'index.php?') {
		let url = entryFile;
		Object.keys(urlData).forEach((key) => {
			let value = urlData[key];
			if (typeof value === 'object' || (typeof value === 'string' && value.startsWith('<'))) {
				return;
			}
			url += key + '=' + value + '&';
		});
		return url;
	},
	showConfirmation: function (data, element) {
		var params = {};
		if (data) {
			params = $.extend(params, data);
		}
		if (element) {
			element = $(element);
			if (!params.title) {
				params.title = element.html() + ' ' + (element.data('content') ? element.data('content') : '');
			}
			if (!params.message) {
				params.message = element.data('confirm');
			}
			if (!params.url) {
				params.url = element.data('url');
			}
		}
		Vtiger_Helper_Js.showConfirmationBox(params).done(function () {
			if (params.type == 'href') {
				AppConnector.request(params.url).done(function (data) {
					app.openUrl(data.result);
				});
			} else if (params.type == 'reloadTab') {
				AppConnector.request(params.url).done(function (data) {
					Vtiger_Detail_Js.getInstance().reloadTabContent();
				});
			}
		});
	},
	formatToHourText: function (decTime, type = 'short', withSeconds = false, withMinutes = true) {
		const short = type === 'short';
		const hour = Math.floor(decTime);
		const min = Math.floor((decTime - hour) * 60);
		const sec = Math.round(((decTime - hour) * 60 - min) * 60);
		let result = '';
		if (hour) {
			result += short ? hour + app.vtranslate('JS_H') : `${hour} ` + app.vtranslate('JS_H_LONG');
		}
		if ((hour || min) && withMinutes) {
			result += short ? ` ${min}` + app.vtranslate('JS_M') : ` ${min} ` + app.vtranslate('JS_M_LONG');
		}
		if (withSeconds !== false) {
			result += short ? ` ${sec}` + app.vtranslate('JS_S') : ` ${sec} ` + app.vtranslate('JS_S_LONG');
		}
		if (!hour && !min && withSeconds === false && withMinutes) {
			result += short ? '0' + app.vtranslate('JS_M') : '0 ' + app.vtranslate('JS_M_LONG');
		}
		if (!hour && !min && withSeconds === false && !withMinutes) {
			result += short ? '0' + app.vtranslate('JS_H') : '0 ' + app.vtranslate('JS_H_LONG');
		}
		return result.trim();
	},
	showRecordsList: function (params, cb, afterShowModal) {
		if (typeof params === 'object' && !params.view) {
			params.view = 'RecordsList';
		}
		this.showRecordsListModal(params).done(function (modal) {
			if (typeof afterShowModal === 'function') {
				afterShowModal(modal);
			}
			app.registerModalController(false, modal, cb);
		});
	},
	/**
	 * Show records list modal
	 * @param {object} params
	 * @returns {Promise}
	 */
	showRecordsListModal: function (params) {
		const aDeferred = $.Deferred();
		AppConnector.request(params)
			.done(function (requestData) {
				app.showModalWindow(requestData, function (modal) {
					aDeferred.resolve(modal);
				});
			})
			.fail(function (textStatus, errorThrown) {
				aDeferred.reject(textStatus, errorThrown);
			});
		return aDeferred.promise();
	},
	/**
	 * Convert html content to base64 image
	 * This function can be used in promise chain or with callback if specified
	 *
	 * @param {HTMLElement} element
	 * @param {function} callback with imageString argument which contains an image in base64 string format
	 * @param {object} options see: https://html2canvas.hertzen.com/configuration , imageType is our custom option
	 * @return {Promise} with base64 string image as argument
	 */
	htmlToImage(element, callback, options = { imageType: 'image/png', logging: false }) {
		element = $(element).get(0); // make sure we have HTMLElement not jQuery because it will not work
		const imageType = options.imageType;
		delete options.imageType;
		return html2canvas(element, options).then((canvas) => {
			const base64Image = canvas.toDataURL(imageType);
			if (typeof callback === 'function') {
				callback(base64Image);
			}
			return base64Image;
		});
	},
	registerHtmlToImageDownloader: function (container) {
		const self = this;
		container.on('click', '.js-download-html', function (e) {
			let element = $(this);
			let fileName = element.data('fileName');
			self.htmlToImage($(element.data('html'))).then((img) => {
				$(`<a href="${img}" download="${fileName}.png"></a>`).get(0).click();
			});
		});
	},
	decodeHTML(html) {
		var txt = document.createElement('textarea');
		txt.innerHTML = html;
		return txt.value;
	},
	showAlert: function (text) {
		return this.showNotify({
			title: text,
			type: 'error',
			closer: false,
			sticker: false,
			destroy: false,
			modules: new Map([
				...PNotify.defaultModules,
				[
					PNotifyConfirm,
					{
						confirm: true,
						buttons: [
							{
								text: app.vtranslate('JS_OK'),
								primary: true,
								click: (notice) => notice.close()
							}
						]
					}
				]
			]),
			stack: new PNotify.Stack({
				dir1: 'down',
				modal: true,
				firstpos1: 25,
				overlayClose: false
			})
		});
	},
	showNotify: function (customParams) {
		let params = {
			hide: false
		};
		let userParams = customParams;
		let type = 'info';
		if (typeof customParams === 'string') {
			userParams = {
				title: customParams
			};
		}
		if (typeof customParams.type !== 'undefined') {
			type = customParams.type;
		}
		if (type !== 'error') {
			params.hide = true;
		}
		return PNotify[type]($.extend(params, userParams));
	},
	/**
	 * Set Pnotify defaults options
	 */
	setPnotifyDefaultOptions() {
		PNotify.defaults.textTrusted = true; // *Trusted option enables html as parameter's value
		PNotify.defaults.titleTrusted = true;
		PNotify.defaults.sticker = false;
		PNotify.defaults.styling = 'bootstrap4';
		PNotify.defaults.icons = 'fontawesome5';
		PNotify.defaults.delay = 3000;
		PNotify.defaults.stack.maxOpen = 10;
		PNotify.defaults.stack.spacing1 = 5;
		PNotify.defaults.stack.spacing2 = 5;
		PNotify.defaults.labels.close = app.vtranslate('JS_CLOSE');
		PNotify.defaultModules.set(PNotifyBootstrap4, {});
		PNotify.defaultModules.set(PNotifyFontAwesome5, {});
		PNotify.defaultModules.set(PNotifyMobile, {});
	},
	showConfirmModal: function (text, callback) {
		return this.showNotify({
			icon: 'fas fa-question-circle',
			title: text,
			closer: false,
			sticker: false,
			destroy: false,
			modules: new Map([
				...PNotify.defaultModules,
				[
					PNotifyConfirm,
					{
						confirm: true,
						buttons: [
							{
								text: app.vtranslate('JS_OK'),
								primary: true,
								promptTrigger: true,
								click: function (notice) {
									notice.close();
									callback(true);
								}
							},
							{
								text: app.vtranslate('JS_CANCEL'),
								click: function (notice) {
									notice.close();
									callback(false);
								}
							}
						]
					}
				]
			]),
			stack: new PNotify.Stack({
				dir1: 'down',
				modal: true,
				firstpos1: 25,
				overlayClose: false
			})
		});
	},
	registesterScrollbar(container) {
		container.find('.js-scrollbar').each(function () {
			let element = $(this),
				scrollbarFnName = element.data('scrollbarFnName');

			if (typeof app[scrollbarFnName] === 'function') {
				app[scrollbarFnName](element);
			} else {
				app.showNewScrollbar(element);
			}
		});
	},
	registerPopover(container = $(document)) {
		window.popoverCache = {};
		container.on('mousemove', (e) => {
			app.mousePosition = { x: e.pageX, y: e.pageY };
		});
		container.on(
			'mouseenter',
			'.js-popover-tooltip, .js-popover-tooltip--record, .js-popover-tooltip--ellipsis, [data-field-type="reference"], [data-field-type="multireference"]',
			(e) => {
				let currentTarget = $(e.currentTarget);
				if (currentTarget.find('.js-popover-tooltip--record').length) {
					return;
				}
				if (!currentTarget.hasClass('popover-triggered')) {
					if (currentTarget.hasClass('js-popover-tooltip--record')) {
						app.registerPopoverRecord(currentTarget, {}, container);
						currentTarget.trigger('mouseenter');
					} else if (!currentTarget.hasClass('js-popover-tooltip--record') && currentTarget.data('field-type')) {
						app.registerPopoverRecord(currentTarget.children('a'), {}, container); //popoverRecord on children doesn't need triggering
					} else if (
						!currentTarget.hasClass('js-popover-tooltip--record') &&
						!currentTarget.find('.js-popover-tooltip--record').length &&
						!currentTarget.data('field-type')
					) {
						if (currentTarget.hasClass('js-popover-tooltip--ellipsis')) {
							app.registerPopoverEllipsis({ element: currentTarget, container });
						} else {
							app.showPopoverElementView(currentTarget);
						}
						currentTarget.trigger('mouseenter');
					}
				}
			}
		);
	},
	/**
	 * Register auto format number value
	 */
	registerFormatNumber() {
		$(document).on('focusout', '.js-format-numer', (e) => {
			$(e.currentTarget).formatNumber();
		});
	},
	/**
	 * Register toggle icon click event
	 * @param container
	 */
	registerToggleIconClick(container) {
		container.on('click', '.js-toggle-icon, .js-toggle-icon__container', (e) => {
			let icon = $(e.target);
			if (icon.hasClass('js-toggle-icon__container')) {
				icon = icon.find('.js-toggle-icon');
			}
			let iconData = icon.data();
			icon.toggleClass(`${iconData.active} ${iconData.inactive}`);
			e.stopPropagation();
		});
	},
	stripHtml(html) {
		const temporalDiv = document.createElement('div');
		temporalDiv.innerHTML = html;
		return temporalDiv.textContent || temporalDiv.innerText || '';
	},
	registerShowHideBlock(container) {
		container.on('click', '.js-hb__btn', (e) => {
			$(e.currentTarget).closest('.js-hb__container').toggleClass('u-hidden-block__opened');
		});
		container.find('.js-fab__container').on('clickoutside', (e) => {
			$(e.currentTarget).removeClass('u-hidden-block__opened');
		});
	}
});
CKEDITOR.disableAutoInline = true;
$(document).ready(function () {
	Quasar.iconSet.set(Quasar.iconSet.mdiV3);
	let document = $(this);
	app.registerToggleIconClick(document);
	app.touchDevice = app.isTouchDevice();
	app.setPnotifyDefaultOptions();
	App.Fields.Picklist.changeSelectElementView();
	app.registerPopoverEllipsisIcon();
	app.registerPopover();
	app.registerFormatNumber();
	app.registerIframeAndMoreContent();
	app.registerModal();
	app.registerQuickEditModal(document);
	app.registerMenu();
	app.registerTabdrop();
	app.registerIframeEvents(document);
	app.registesterScrollbar(document);
	app.registerHtmlToImageDownloader(document);
	app.registerShowHideBlock(document);
	app.registerFormsEvents(document);
	App.Components.QuickCreate.register(document);
	App.Components.Scrollbar.initPage();
	String.prototype.toCamelCase = function () {
		let value = this.valueOf();
		return value.charAt(0).toUpperCase() + value.slice(1).toLowerCase();
	};
	// in IE resize option for textarea is not there, so we have to use .resizable() api
	if (/MSIE/.test(navigator.userAgent) || /Trident/.test(navigator.userAgent)) {
		$('textarea').resizable();
	}
	// Instantiate Page Controller
	let pageController = app.getPageController();
	if (pageController) {
		pageController.registerEvents();
	}
});
(function ($) {
	$.fn.getNumberFromValue = function () {
		return App.Fields.Double.formatToDb($(this).val());
	};
	$.fn.getNumberFromText = function () {
		return App.Fields.Double.formatToDb($(this).text());
	};
	$.fn.setValue = function (value, type = 'value') {
		return App.Fields.Utils.setValue($(this), value, type);
	};
	$.fn.formatNumber = function () {
		let element = $(this);
		element.val(App.Fields.Double.formatToDisplay(App.Fields.Double.formatToDb(element.val()), false));
	};
	$.fn.disable = function () {
		this.attr('disabled', 'disabled');
	};
	$.fn.enable = function () {
		this.removeAttr('disabled');
	};
	$.fn.serializeFormData = function () {
		let form = $(this);
		for (var instance in CKEDITOR.instances) {
			CKEDITOR.instances[instance].updateElement();
		}
		let values = form.serializeArray();
		let data = {};
		if (values) {
			$(values).each(function (k, v) {
				let element = form.find('[name="' + v.name + '"]');
				if (element.is('select') && element.attr('multiple') != undefined) {
					if (data[v.name] == undefined) {
						data[v.name] = [];
					}
					data[v.name].push(v.value);
				} else {
					data[v.name] = v.value;
				}
			});
		}
		// If data-type="autocomplete", pickup data-value="..." set
		let autocompletes = $('[data-type="autocomplete"]', $(this));
		$(autocompletes).each(function (i) {
			let ac = $(autocompletes[i]);
			data[ac.attr('name')] = ac.data('value');
		});
		return data;
	};
	// Case-insensitive :icontains expression
	$.expr[':'].icontains = function (obj, index, meta, stack) {
		return (
			(obj.textContent || obj.innerText || $(obj).text() || '').toLowerCase().indexOf(meta[3].toLowerCase()) !== -1
		);
	};
	$.fn.removeTextNode = function () {
		$(this)
			.contents()
			.filter(function () {
				return this.nodeType == 3; //Node.TEXT_NODE
			})
			.remove();
	};
	bootbox.setLocale(CONFIG.langKey);
})($);
