/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Project_Gantt_Js',
	{},
	{
		filterSelectElement: false,
		getFilterSelectElement: function () {
			if (this.filterSelectElement == false) {
				this.filterSelectElement = jQuery('#customFilter');
			}
			return this.filterSelectElement;
		},
		getDefaultParams: function () {
			var params = {
				module: app.getModuleName(),
				action: 'GanttData'
			};
			if (app.getParentModuleName()) {
				params.parent = app.getParentModuleName();
			}
			return params;
		},
		getCurrentCvId: function () {
			return jQuery('#customFilter').find('option:selected').data('id');
		},
		getGanttData(urlParams) {
			let aDeferred = $.Deferred();
			if (typeof urlParams === 'undefined') {
				urlParams = {};
			}
			let defaultParams = this.getDefaultParams();
			urlParams = $.extend(defaultParams, urlParams);
			const progressInstance = jQuery.progressIndicator({
				blockInfo: {
					enabled: true,
					onBlock: () => {
						AppConnector.request(urlParams)
							.done(function (data) {
								progressInstance.progressIndicator({ mode: 'hide' });
								aDeferred.resolve(data);
								app.notifyPostAjaxReady();
							})
							.fail(function (textStatus, errorThrown) {
								aDeferred.reject(textStatus, errorThrown);
							});
					}
				}
			});
			return aDeferred.promise();
		},
		/**
		 * load gantt
		 */
		loadGantt(container = '.js-gantt__container', ganttData = false) {
			container = $(container);
			this.gantt = new Gantt(container);
			const projectId = container.parent().find('input[name="projectId"]').val();
			if (!ganttData) {
				this.gantt.loadProjectFromAjax({
					module: app.getModuleName(),
					action: 'GanttData',
					projectId: projectId
				});
			}
		},
		/**
		 * reload gantt with new data
		 * @param data
		 */
		reloadData(data) {
			this.gantt.reloadData(data);
		},
		/*
		 * Function to register the event for changing the custom Filter
		 */
		registerChangeCustomFilterEvent: function () {
			var thisInstance = this;
			this.getFilterSelectElement().on('change', function (event) {
				$(`.nav-item[data-cvid='${thisInstance.getCurrentCvId()}'] .nav-link`).tab('show');
				var currentTarget = jQuery(event.currentTarget);
				var selectOption = currentTarget.find(':selected');
				app.setMainParams('pageNumber', '1');
				app.setMainParams('pageToJump', '1');
				app.setMainParams('orderBy', selectOption.data('orderby'));
				app.setMainParams('sortOrder', selectOption.data('sortorder'));
				thisInstance
					.getGanttData({
						viewname: jQuery(this).val()
					})
					.done(function (data) {
						thisInstance.reloadData(data.result);
					});
				event.stopPropagation();
			});
		},
		getSelectOptionFromChosenOption: function (liElement) {
			var id = liElement.attr('id');
			var idArr = id.split('-');
			var currentOptionId = '';
			if (idArr.length > 0) {
				currentOptionId = idArr[idArr.length - 1];
			} else {
				return false;
			}
			return jQuery('#filterOptionId_' + currentOptionId);
		},
		changeCustomFilterElementView: function () {
			var thisInstance = this;
			var filterSelectElement = this.getFilterSelectElement();
			if (filterSelectElement.length > 0 && filterSelectElement.is('select')) {
				App.Fields.Picklist.showSelect2ElementView(filterSelectElement, {
					templateSelection: function (data) {
						var resultContainer = jQuery('<span></span>');
						resultContainer.append(jQuery(jQuery('.filterImage').clone().get(0)).show());
						resultContainer.append(data.text);
						return resultContainer;
					},
					customSortOptGroup: true,
					closeOnSelect: true
				});

				var select2Instance = filterSelectElement.data('select2');
				jQuery('.filterActionsDiv')
					.appendTo(select2Instance.$dropdown.find('.select2-dropdown:last'))
					.removeClass('d-none')
					.on('click', function (e) {
						thisInstance.registerCreateFilterClickEvent(e);
					});
			}
		},
		registerEvents: function () {
			this.changeCustomFilterElementView();
			this.registerChangeCustomFilterEvent();
			Vtiger_Helper_Js.showHorizontalTopScrollBar();
			this.loadGantt();
		}
	}
);
