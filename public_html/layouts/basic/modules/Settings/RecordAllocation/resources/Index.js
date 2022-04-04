/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_RecordAllocation_Index_Js',
	{},
	{
		container: false,
		/**
		 * Register tables
		 * @param {jQuery} contentData
		 */
		registerDataTables: function (contentData) {
			const thisInstance = this;
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
					oAria: {
						sSortAscending: app.vtranslate('JS_S_SORT_ASCENDING'),
						sSortDescending: app.vtranslate('JS_S_SORT_DESCENDING')
					}
				}
			});
			if (contentData == undefined) {
				contentData = thisInstance.getContainer();
			}
			contentData.find('.js-data-table').dataTable({
				scrollY: 200,
				deferRender: true,
				scroller: true,
				paging: false,
				info: false
			});
		},
		/**
		 * Register event drag and drop
		 * @param {jQuery} contentData
		 */
		registerDragDropEvent: function (contentData) {
			if (contentData == undefined) {
				return;
			}
			const thisInstance = this;
			let panel = contentData.closest('.js-panel'),
				index = panel.data('index'),
				width;
			contentData.find('.js-drag-drop-' + index).draggable({
				appendTo: 'body',
				helper: 'clone',
				start: function (e, ui) {
					width = $(ui.helper.context).width();
					$(ui.helper).css('width', width).addClass('dataTableDragDrop bg-primary');
				},
				zIndex: 9999999999
			});
			contentData.find('.dataTables_scrollBody .js-data-table').droppable({
				activeClass: 'ui-state-default',
				hoverClass: 'ui-state-hover',
				accept: '.js-drag-drop-' + index,
				drop: function (event, ui) {
					let tableBase = $(ui.draggable).closest('.js-data-table'),
						table = $(this);
					if (tableBase.data('mode') != table.data('mode')) {
						tableBase.DataTable().row(ui.draggable).remove().draw();
						table.DataTable().row.add(ui.draggable[0]).draw();
						table = table.data('mode') == 'active' ? table : tableBase;
						thisInstance.save(table.closest('.js-panel'));
					}
				}
			});
		},
		/**
		 * Save users for module
		 * @param {jQuery} container
		 */
		save: function (container) {
			let data = {
					module: container.data('modulename'),
					userid: container.find('.js-base-user').val(),
					type: app.getMainParams('fieldType')
				},
				userData = [],
				dataContainer = container.find('.dataTables_scrollBody:first tbody tr');
			dataContainer.each(function (e) {
				let id = $(this).data('id'),
					mode = $(this).data('type');
				if (id && mode) {
					if (!userData[mode]) {
						userData[mode] = [];
					}
					userData[mode].push(id);
				}
			});
			data['ids'] = jQuery.extend({}, userData);
			app.saveAjax('save', jQuery.extend({}, data));
		},
		/**
		 * Register event to show form
		 */
		registerModalButton: function () {
			const thisInstance = this,
				container = this.getContainer();
			container.find('button.js-add-panel').on('click', function () {
				let modalWindow = container.find('.js-modal-add-panel').clone(true, true),
					inUseModules = thisInstance.getModules();
				modalWindow.find('select option').each(function () {
					if ($.inArray($(this).val(), inUseModules) != -1) {
						$(this).remove();
					}
				});
				app.showModalWindow(modalWindow, function (data) {
					//register all select2 Elements
					let selectElement = data.find('select'),
						form = data.find('form');
					App.Fields.Picklist.showSelect2ElementView(selectElement);
					form.on('submit', function (e) {
						let currentTarget = $(e.currentTarget),
							module = currentTarget.find('.js-modules-list');
						if (module.length && module.val()) {
							thisInstance.addPanel(module.val());
						}
						e.preventDefault();
					});
				});
			});
		},
		/**
		 * Returns list of modules which are visible
		 * @returns {Array}
		 */
		getModules: function () {
			var modules = [];
			this.getContainer()
				.find('.js-panel')
				.each(function () {
					modules.push(jQuery(this).data('modulename'));
				});
			return modules;
		},
		/**
		 * Load users form modules
		 * @param {string} module
		 * @returns {jQuery.Deferred}
		 */
		addPanel: function (module) {
			const thisInstance = this;
			let aDeferred = jQuery.Deferred(),
				progressIndicatorElement = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				}),
				lastPanel = this.getContainer().find('.js-panel:last');
			AppConnector.request({
				index: lastPanel.data('index'),
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				sourceModule: module,
				view: 'Index',
				mode: 'getPanel',
				type: app.getMainParams('fieldType')
			}).done(
				function (data) {
					let elements = thisInstance.getContainer().find('.js-panels-container').append(data);
					App.Fields.Picklist.showSelect2ElementView(elements.find('.select2'));
					app.hideModalWindow();
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					aDeferred.resolve(data);
				},
				function (error) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					aDeferred.reject(error);
				}
			);
			return aDeferred.promise();
		},
		/**
		 * Register event to load users
		 */
		registerLoadData: function () {
			const thisInstance = this;
			this.getContainer().on('change', '.js-base-user', function (e) {
				let selectElement = jQuery(e.currentTarget),
					panel = selectElement.closest('.js-panel-item'),
					dataJson = panel.find('.js-module-allocation-data').val(),
					data = [];
				if (dataJson && dataJson != 'null') {
					data = JSON.parse(dataJson);
				}
				let userData = data[selectElement.val()],
					bodyContainer = panel.find('.js-active-panel');
				if (bodyContainer.length) {
					bodyContainer.remove();
				}
				let bodyContainerTable = panel.find('.js-clear-tables').clone(true, true);
				if (userData != undefined) {
					let activeData = bodyContainerTable.find('.js-data-table .dropContainer:first'),
						baseData = bodyContainerTable.find('.js-data-table .dropContainer:last');
					baseData.find('tr').each(function () {
						let mode = jQuery(this).data('type'),
							id = jQuery(this).data('id');
						if ($.inArray(id, userData[mode]) != -1 || $.inArray(id.toString(), userData[mode]) != -1) {
							activeData.append(jQuery(this));
						}
					});
				}
				panel
					.find('.js-panel-body')
					.removeClass('d-none')
					.append(bodyContainerTable.removeClass('js-clear-tables d-none').addClass('js-active-panel'));
				thisInstance.registerDataTables(bodyContainerTable);
				thisInstance.registerDragDropEvent(bodyContainerTable);
			});
		},
		/**
		 * Register basic events
		 */
		registerHeaderElements: function () {
			this.getContainer().on('click', '.js-remove-panel', function (e) {
				let panel = jQuery(e.currentTarget).closest('.js-panel');
				app.showConfirmModal({
					title: app.vtranslate('JS_ARE_YOU_SURE_YOU_WANT_TO_DELETE_PANEL'),
					confirmedCallback: () => {
						app
							.saveAjax('removePanel', {
								module: panel.data('modulename'),
								type: app.getMainParams('fieldType')
							})
							.done(function () {
								panel.fadeOut(300, function () {
									$(this).remove();
								});
							});
					}
				});
			});
			this.registerLoadData();
		},
		/**
		 * Returns container
		 * @returns {jQuery}
		 */
		getContainer: function () {
			if (this.container == false) {
				this.container = jQuery('div.contentsDiv');
			}
			return this.container;
		},
		/**
		 * Main function
		 */
		registerEvents: function () {
			this.registerModalButton();
			this.registerHeaderElements();
		}
	}
);
