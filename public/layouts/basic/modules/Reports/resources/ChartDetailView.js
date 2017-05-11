/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

Reports_Detail_Js("Reports_ChartDetailView_Js", {
	/**
	 * Function used to display message when there is no data from the server
	 */
	displayNoDataMessage: function () {
		$('.chartcontent').html('<div>' + app.vtranslate('JS_NO_CHART_DATA_AVAILABLE') + '</div>').css(
				{'text-align': 'center', 'position': 'relative', 'top': '100px'});
	},
	/**
	 * Function returns if there is no data from the server
	 */
	isEmptyData: function () {
		var jsonData = jQuery('input[name=data]').val();
		var data = JSON.parse(jsonData);
		var values = data['values'];
		if (jsonData == '' || values == '') {
			return true;
		}
		return false;
	}
}, {
	/**
	 * Function returns instance of the chart type
	 */
	getInstance: function () {
		var chartType = jQuery('input[name=charttype]').val();
		var chartClassName = chartType.toCamelCase();
		var chartClass = window["Report_" + chartClassName + "_Js"];

		var instance = false;
		if (typeof chartClass != 'undefined') {
			instance = new chartClass();
			instance.postInitializeCalls();
		}
		return instance;
	},
	registerSaveOrGenerateReportEvent: function () {
		var thisInstance = this;
		jQuery('.generateReport').on('click', function (e) {
			if (!jQuery('#chartDetailForm').validationEngine('validate')) {
				e.preventDefault();
				return false;
			}

			var advFilterCondition = thisInstance.calculateValues();
			var recordId = $('[name="recordId"').val();
			var currentMode = jQuery(e.currentTarget).data('mode');
			var postData = {
				'advanced_filter': advFilterCondition,
				'record': recordId,
				'view': "ChartSaveAjax",
				'module': app.getModuleName(),
				'mode': currentMode,
				'charttype': jQuery('input[name=charttype]').val(),
				'groupbyfield': jQuery('#groupbyfield').val(),
				'datafields': jQuery('#datafields').val()
			};

			var reportChartContents = thisInstance.getContentHolder().find('#reportContentsDiv');
			var element = jQuery('<div></div>');
			element.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true,
					'elementToBlock': reportChartContents
				}
			});

			e.preventDefault();

			AppConnector.request(postData).then(
					function (data) {
						element.progressIndicator({'mode': 'hide'});
						reportChartContents.html(data);
						thisInstance.registerEventForChartGeneration();
					}
			);
		});


	},
	registerEventForChartGeneration: function () {
		var thisInstance = this;
		try {
			thisInstance.getInstance();	// instantiate the object and calls init function
			jQuery('.chartcontent').trigger(Vtiger_Widget_Js.widgetPostLoadEvent);
		} catch (error) {
			Reports_ChartDetailView_Js.displayNoDataMessage();
			return;
		}
	},
	registerEventForModifyCondition: function () {
		jQuery('button[name=modify_condition]').on('click', function () {
			var filter = jQuery('#filterContainer');
			var icon = jQuery(this).find('span');
			var classValue = icon.attr('class');
			if (classValue.indexOf('glyphicon-chevron-right') != -1) {
				icon.removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');
				filter.removeClass('hide').show('slow');
			} else {
				icon.removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
				filter.hide('slow');
			}
			return false;
		});
	},
	registerEvents: function () {
		this._super();
		this.registerEventForModifyCondition();
		this.registerEventForChartGeneration();
		Reports_ChartEdit3_Js.registerFieldForChosen();
		Reports_ChartEdit3_Js.initSelectValues();
		jQuery('#chartDetailForm').validationEngine(app.validationEngineOptions);
	}
});
$( document ).ready(function() {
	(new Reports_ChartDetailView_Js()).registerEvents();
});
