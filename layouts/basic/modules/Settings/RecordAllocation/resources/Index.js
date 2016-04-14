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
		var test = contentData.find('.dataTable').dataTable({
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
			contentData = thisInstance.getContainer();
		}
		contentData.find('.panel').each(function () {
			var index = jQuery(this).data('index');
			jQuery(this).find('.dragDrop' + index).draggable({
				appendTo: 'body',
				helper: 'clone',
				start: function (e, ui)
				{
					var width = $(ui.helper.context).width();
					$(ui.helper).css('width', width).addClass('dataTableDragDrop bg-primary');
				},
				zIndex: 9999999999
			});
			jQuery(this).find('.dataTables_scrollBody .dataTable').droppable({
				activeClass: 'ui-state-default',
				hoverClass: 'ui-state-hover',
				accept: '.dragDrop' + index,
				drop: function (event, ui) {
					var tableBase = $(ui.draggable).closest('.dataTable');
					var table = $(this);
					if (tableBase.data('mode') != table.data('mode')) {
						tableBase.DataTable().row(ui.draggable).remove().draw();
						table.DataTable().row.add(ui.draggable[0]).draw();
						thisInstance.save();
					}
				}
			});
		})
	},
	save: function () {
		var data = [];
		var moduleData = [];
		this.getContainer().find('.panel').each(function () {
			moduleData['users'] = [];
			moduleData['groups'] = [];
			var moduleId = jQuery(this).data('module');
			var dataContainer = jQuery(this).find('.dataTables_scrollBody:first tbody tr');
			dataContainer.each(function (e) {
				var id = jQuery(this).data('id');
				var mode = jQuery(this).data('type');
				if (id && mode) {
					moduleData[mode].push(id);
				}
			})
			data[moduleId] = jQuery.extend({}, moduleData);
		});
		app.saveAjax('save', JSON.stringify(data))

	},
	registerModalButton: function () {
		var thisInstance = this;
		var container = this.getContainer();
		container.find('button.addPanel').on('click', function () {
			var myModal = container.find('#myModal').clone(true, true);
			var inUseModules = thisInstance.getModules();
			myModal.find('select option').each(function () {
				if (jQuery.inArray(jQuery(this).val(), inUseModules) != -1)
					jQuery(this).remove();
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
						thisInstance.addPanel(module.val()).then(function (addPanelResult) {
							if (addPanelResult) {
								thisInstance.save();
							}
						});
					} else {
						var result = app.vtranslate('JS_FIELD_EMPTY');
						module.prev('div').validationEngine('showPrompt', result, 'error', 'bottomLeft', true);
						e.preventDefault();
						return;
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
			modules.push(jQuery(this).data('module'));
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
		params['moduleId'] = module;
		params['view'] = 'Index';
		params['mode'] = 'getPanel';
		AppConnector.request(params).then(
				function (data) {
					thisInstance.getContainer().find('.panelsContainer').append(data);
					var panel = thisInstance.getContainer().find('.panelItem:last');
					thisInstance.registerDataTables(panel);
					thisInstance.registerDragDropEvent(panel);
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
	getContainer: function () {
		if (this.container == false) {
			this.container = jQuery('div.contentsDiv');
		}
		return this.container;
	},
	registerEvents: function () {
		this.registerDataTables();
		this.registerDragDropEvent();
		this.registerModalButton();
	}
})
