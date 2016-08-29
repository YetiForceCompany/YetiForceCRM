/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class('Settings_RecordAllocation_Index_Js', {}, {
	container: false,
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
		contentData.find('.dataTable').dataTable({
			scrollY: 200,
			deferRender: true,
			scroller: true,
			paging: false,
//			scrollCollapse: true,
			info: false
		});
	},
	registerDragDropEvent: function (contentData) {
		var thisInstance = this;
		if (contentData == undefined) {
			return;
		}
		var panel = contentData.closest('.panel');
		var index = panel.data('index');
		contentData.find('.dragDrop' + index).draggable({
			appendTo: 'body',
			helper: 'clone',
			start: function (e, ui)
			{
				var width = $(ui.helper.context).width();
				$(ui.helper).css('width', width).addClass('dataTableDragDrop bg-primary');
			},
			zIndex: 9999999999
		});
		contentData.find('.dataTables_scrollBody .dataTable').droppable({
			activeClass: 'ui-state-default',
			hoverClass: 'ui-state-hover',
			accept: '.dragDrop' + index,
			drop: function (event, ui) {
				var tableBase = $(ui.draggable).closest('.dataTable');
				var table = $(this);
				if (tableBase.data('mode') != table.data('mode')) {
					tableBase.DataTable().row(ui.draggable).remove().draw();
					table.DataTable().row.add(ui.draggable[0]).draw();
					var table = table.data('mode') == 'active' ? table : tableBase;
					thisInstance.save(table.closest('.panel'));
				}
			}
		});
	},
	save: function (container) {
		var data = {
			module: container.data('modulename'),
			userid: container.find('select.baseUser').val(),
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
		app.saveAjax('save', jQuery.extend({}, data))
	},
	registerModalButton: function () {
		var thisInstance = this;
		var container = this.getContainer();
		container.find('button.addPanel').on('click', function () {
			var myModal = container.find('#myModal').clone(true, true);
			var inUseModules = thisInstance.getModules();
			myModal.find('select option').each(function () {
				if (jQuery.inArray(jQuery(this).val(), inUseModules) != -1) {
					jQuery(this).remove();
				}
			});
			var callBackFunction = function (data) {
				//register all select2 Elements
				var selectElement = data.find('select');
				app.showSelect2ElementView(selectElement);
				var form = data.find('form');

				form.submit(function (e) {
					var currentTarget = jQuery(e.currentTarget);
					var module = currentTarget.find('#modulesList');
					if (module.length && module.val()) {
						thisInstance.addPanel(module.val());
					}
					e.preventDefault();
				})
			}
			app.showModalWindow(myModal, function (data) {
				if (typeof callBackFunction == 'function') {
					callBackFunction(data);
				}
			});
		})
	},
	getModules: function () {
		var thisInstance = this;
		var modules = [];
		this.getContainer().find('.panel').each(function () {
			modules.push(jQuery(this).data('modulename'));
		});
		return modules;
	},
	addPanel: function (module) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});

		var lastPanel = this.getContainer().find('.panel:last');
		var params = {};
		params['index'] = lastPanel.data('index')
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['sourceModule'] = module;
		params['view'] = 'Index';
		params['mode'] = 'getPanel';
		params['type'] = app.getMainParams('fieldType');
		AppConnector.request(params).then(
				function (data) {
					var elements = thisInstance.getContainer().find('.panelsContainer').append(data);
					app.changeSelectElementView(elements.find('.chzn-select'));
					app.hideModalWindow();
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					aDeferred.resolve(data);
				},
				function (error) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					aDeferred.reject(error);
				}
		);
		return aDeferred.promise();
	},
	registerLoadData: function () {
		var thisInstance = this;
		this.getContainer().on('change', 'select.baseUser', function (e) {
			var selectElement = jQuery(e.currentTarget);
			var panel = selectElement.closest('.panelItem');
			var dataJson = panel.find('.moduleAllocationData').val();
			var data = [];
			if (dataJson && dataJson != 'null') {
				data = JSON.parse(dataJson);
			}
			var userData = data[selectElement.val()];
			var bodyContainer = panel.find('.activePanel');
			if (bodyContainer.length) {
				bodyContainer.remove();
			}
			var bodyContainer = panel.find('.clearTables').clone(true, true);
			if (userData != undefined) {
				var activeData = bodyContainer.find('.dataTable .dropContainer:first');
				var baseData = bodyContainer.find('.dataTable .dropContainer:last');
				baseData.find('tr').each(function () {
					var mode = jQuery(this).data('type')
					var id = jQuery(this).data('id')
					if (jQuery.inArray(id.toString(), userData[mode]) != -1) {
						activeData.append(jQuery(this));
					}
				})
			}
			panel.find('.panel-body').removeClass('hide').append(bodyContainer.removeClass('clearTables hide').addClass('activePanel'));
			thisInstance.registerDataTables(bodyContainer);
			thisInstance.registerDragDropEvent(bodyContainer);
		});
	},
	registerHeaderElements: function () {
		var thisInstance = this;
		this.getContainer().on('click', '.removePanel', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var panel = currentTarget.closest('.panel');
			var data = {
				module: panel.data('modulename'),
				type: app.getMainParams('fieldType')
			};
			var message = app.vtranslate('JS_ARE_YOU_SURE_YOU_WANT_TO_DELETE_PANEL');
			Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
					function (e) {
						app.saveAjax('removePanel', data).then(function () {
							panel.fadeOut(300, function () {
								$(this).remove();
							});
						})
					},
					function (error, err) {
					}
			);
		});
		this.registerLoadData();
	},
	getContainer: function () {
		if (this.container == false) {
			this.container = jQuery('div.contentsDiv');
		}
		return this.container;
	},
	registerEvents: function () {
		this.registerModalButton();
		this.registerHeaderElements();
	}
})
