/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
jQuery.Class('Settings_RecordAllocation_Index_Js', {}, {
	container: false,
	/**
	 * Register tables
	 * @param {jQuery} contentData
	 */
	registerDataTables: function (contentData) {
		var thisInstance = this;
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
		var thisInstance = this;
		if (contentData == undefined) {
			return;
		}
		var panel = contentData.closest('.js-panel');
		var index = panel.data('index');
		contentData.find('.js-drag-drop-' + index).draggable({
			appendTo: 'body',
			helper: 'clone',
			start: function (e, ui) {
				var width = $(ui.helper.context).width();
				$(ui.helper).css('width', width).addClass('dataTableDragDrop bg-primary');
			},
			zIndex: 9999999999
		});
		contentData.find('.dataTables_scrollBody .js-data-table').droppable({
			activeClass: 'ui-state-default',
			hoverClass: 'ui-state-hover',
			accept: '.js-drag-drop-' + index,
			drop: function (event, ui) {
				var tableBase = $(ui.draggable).closest('.js-data-table');
				var table = $(this);
				if (tableBase.data('mode') != table.data('mode')) {
					tableBase.DataTable().row(ui.draggable).remove().draw();
					table.DataTable().row.add(ui.draggable[0]).draw();
					var table = table.data('mode') == 'active' ? table : tableBase;
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
		var data = {
			module: container.data('modulename'),
			userid: container.find('.js-base-user').val(),
			type: app.getMainParams('fieldType')
		};
		var userData = [];
		var dataContainer = container.find('.dataTables_scrollBody:first tbody tr');
		dataContainer.each(function (e) {
			var id = jQuery(this).data('id');
			var mode = jQuery(this).data('type');
			if (id && mode) {
				if (!userData[mode]) {
					userData[mode] = [];
				}
				userData[mode].push(id);
			}
		})
		data['ids'] = jQuery.extend({}, userData);
		app.saveAjax('save', jQuery.extend({}, data));
	},
	/**
	 * Register event to show form
	 */
	registerModalButton: function () {
		var thisInstance = this;
		var container = this.getContainer();
		container.find('button.js-add-panel').on('click', function () {
			var modalWindow = container.find('.js-modal-add-panel').clone(true, true);
			var inUseModules = thisInstance.getModules();
			modalWindow.find('select option').each(function () {
				if ($.inArray($(this).val(), inUseModules) != -1) {
					$(this).remove();
				}
			});
			app.showModalWindow(modalWindow, function (data) {
				//register all select2 Elements
				var selectElement = data.find('select');
				App.Fields.Picklist.showSelect2ElementView(selectElement);
				var form = data.find('form');
				form.on('submit', function (e) {
					var currentTarget = $(e.currentTarget);
					var module = currentTarget.find('.js-modules-list');
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
		this.getContainer().find('.js-panel').each(function () {
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
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var progressIndicatorElement = jQuery.progressIndicator({
			position: 'html',
			blockInfo: {
				enabled: true
			}
		});
		var lastPanel = this.getContainer().find('.js-panel:last');
		var params = {};
		params['index'] = lastPanel.data('index')
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['sourceModule'] = module;
		params['view'] = 'Index';
		params['mode'] = 'getPanel';
		params['type'] = app.getMainParams('fieldType');
		AppConnector.request(params).then(function (data) {
			var elements = thisInstance.getContainer().find('.js-panels-container').append(data);
			App.Fields.Picklist.changeSelectElementView(elements.find('.chzn-select'));
			app.hideModalWindow();
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			aDeferred.resolve(data);
		}, function (error) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			aDeferred.reject(error);
		});
		return aDeferred.promise();
	},
	/**
	 * Register event to load users
	 */
	registerLoadData: function () {
		var thisInstance = this;
		this.getContainer().on('change', '.js-base-user', function (e) {
			var selectElement = jQuery(e.currentTarget);
			var panel = selectElement.closest('.js-panel-item');
			var dataJson = panel.find('.js-module-allocation-data').val();
			var data = [];
			if (dataJson && dataJson != 'null') {
				data = JSON.parse(dataJson);
			}
			var userData = data[selectElement.val()];
			var bodyContainer = panel.find('.js-active-panel');
			if (bodyContainer.length) {
				bodyContainer.remove();
			}
			var bodyContainer = panel.find('.js-clear-tables').clone(true, true);
			if (userData != undefined) {
				var activeData = bodyContainer.find('.js-data-table .dropContainer:first');
				var baseData = bodyContainer.find('.js-data-table .dropContainer:last');
				baseData.find('tr').each(function () {
					var mode = jQuery(this).data('type');
					var id = jQuery(this).data('id');
					if (jQuery.inArray(id.toString(), userData[mode]) != -1) {
						activeData.append(jQuery(this));
					}
				})
			}
			panel.find('.js-panel-body').removeClass('d-none').append(bodyContainer.removeClass('js-clear-tables d-none').addClass('js-active-panel'));
			thisInstance.registerDataTables(bodyContainer);
			thisInstance.registerDragDropEvent(bodyContainer);
		});
	},
	/**
	 * Register basic events
	 */
	registerHeaderElements: function () {
		this.getContainer().on('click', '.js-remove-panel', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var panel = currentTarget.closest('.js-panel');
			var data = {
				module: panel.data('modulename'),
				type: app.getMainParams('fieldType')
			};
			var message = app.vtranslate('JS_ARE_YOU_SURE_YOU_WANT_TO_DELETE_PANEL');
			Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(function (e) {
				app.saveAjax('removePanel', data).then(function () {
					panel.fadeOut(300, function () {
						$(this).remove();
					});
				})
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
});
