/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

$.Class("Project_Gantt_Js", {}, {
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
			action: 'GanttData',
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
		if (typeof urlParams === "undefined") {
			urlParams = {};
		}
		const progressIndicatorElement = $.progressIndicator({
			position: 'html',
			blockInfo: {
				'enabled': true
			}
		});
		let defaultParams = this.getDefaultParams();
		urlParams = $.extend(defaultParams, urlParams);
		AppConnector.request(urlParams).then(function (data) {
			progressIndicatorElement.progressIndicator({mode: 'hide'});
			aDeferred.resolve(data);
			app.notifyPostAjaxReady();
		}, function (textStatus, errorThrown) {
			aDeferred.reject(textStatus, errorThrown);
		});
		return aDeferred.promise();
	},
	/**
	 * load gantt
	 */
	loadGantt(container = '.js-gantt__container', ganttData = false) {
		container = $(container);
		let parent = container.parent();
		let html = container.html();
		container.remove();
		container = $(parent).append(html);
		if (!ganttData) {
			let ganttDataStr = parent.find('#ganttData').val();
			ganttData = JSON.parse(ganttDataStr, true);
		}
		this.gantt = new Gantt(container, ganttData);
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
			thisInstance.getGanttData({
				"viewname": jQuery(this).val(),
			}).then(function (data) {
				thisInstance.breadCrumbsFilter(selectOption.text());
				thisInstance.reloadData(data.result);
			});
			event.stopPropagation();
		});
	},
	breadCrumbsFilter: function (text) {
		var breadCrumbs = jQuery('.breadcrumbsContainer');
		var breadCrumbsLastSpan = breadCrumbs.last('span');
		var filterExist = breadCrumbsLastSpan.find('.breadCrumbsFilter');
		if (filterExist.length && text != undefined) {
			filterExist.text(' [' + app.vtranslate('JS_FILTER') + ': ' + text + ']');
		} else if (filterExist.length < 1) {
			text = (text == undefined) ? this.getFilterSelectElement().find(':selected').text() : text;
			if (breadCrumbsLastSpan.hasClass('breadCrumbsFilter')) {
				breadCrumbsLastSpan.text(': ' + text);
			} else {
				breadCrumbs.append('<small class="breadCrumbsFilter hideToHistory p-1 js-text-content" data-js="text"> [' + app.vtranslate('JS_FILTER') + ': ' + text + ']</small>');
			}
		}
	},

	getSelectOptionFromChosenOption: function (liElement) {
		var id = liElement.attr("id");
		var idArr = id.split("-");
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
		if (filterSelectElement.length > 0 && filterSelectElement.is("select")) {
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
			jQuery('.filterActionsDiv').appendTo(select2Instance.$dropdown.find('.select2-dropdown:last')).removeClass('d-none').on('click', function (e) {
				thisInstance.registerCreateFilterClickEvent(e);
			});
		}
	},
	registerEvents: function () {
		this.breadCrumbsFilter();
		this.changeCustomFilterElementView();
		this.registerChangeCustomFilterEvent();
		Vtiger_Helper_Js.showHorizontalTopScrollBar();
		this.loadGantt();
	},
});

