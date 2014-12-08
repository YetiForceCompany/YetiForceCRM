/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Reports_Edit3_Js("Reports_ChartEdit3_Js",{

	registerFieldForChosen : function() {
		app.changeSelectElementView(jQuery('#groupbyfield'), 'select2');
		app.changeSelectElementView(jQuery('#datafields'), 'select2');
	},

	initSelectValues : function() {
		var groupByField = jQuery('#groupbyfield');
		var dataFields = jQuery('#datafields');

		var groupByFieldValue = jQuery('input[name=groupbyfield]').val();
		var dataFieldsValue = jQuery('input[name=datafields]').val();

		var groupByHTML = jQuery('#groupbyfield_element').clone().html();
		var dataFieldsHTML = jQuery('#datafields_element').clone().html();

		groupByField.html(groupByHTML);
		dataFields.html(dataFieldsHTML);

		if(dataFieldsValue)
			dataFieldsValue = JSON.parse(dataFieldsValue);

		var selectedChartType = jQuery('input[name=charttype]').val();

		groupByField.select2().select2("val", groupByFieldValue);

		if(selectedChartType == 'pieChart') {
			dataFields.attr('multiple', false).select2().select2("val", dataFieldsValue);
		} else if(dataFieldsValue && dataFieldsValue[0]) {
			dataFields.attr('multiple', true).select2({maximumSelectionSize: 3, closeOnSelect: false}).select2("val", dataFieldsValue);
		}

		if(selectedChartType) {
			jQuery('ul[name=charttab] li.active').removeClass('active');
			jQuery('ul[name=charttab] li a[data-type='+selectedChartType+']').addClass('active contentsBackground backgroundColor').trigger('click');
		} else {
			jQuery('ul[name=charttab] li a[data-type=pieChart]').addClass('contentsBackground backgroundColor').trigger('click'); // by default piechart should be selected
		}
	}

},{
	initialize : function(container) {
		if(typeof container == 'undefined') {
			container = jQuery('#chart_report_step3');
		}
		if(container.is('#chart_report_step3')) {
			this.setContainer(container);
		} else {
			this.setContainer(jQuery('#chart_report_step3'));
		}
	},

	registerForChartTabClick : function() {
		var dataFields = jQuery('#datafields');

		jQuery('ul[name=charttab] li a').on('click', function(e){
			var chartType = jQuery(e.currentTarget).data('type');
			if(chartType == 'pieChart') {
				dataFields.attr('multiple', false).select2().select2("val", "");
			} else {
				dataFields.attr('multiple', true).select2({maximumSelectionSize: 3});
			}
			jQuery('input[name=charttype]').val(chartType);
			jQuery('ul[name=charttab] li.active a').removeClass('contentsBackground backgroundColor');
			jQuery(this).addClass('contentsBackground backgroundColor');
		});
	},
    
     calculateValues : function(){
		//handled advanced filters saved values.
		var advfilterlist = jQuery('#advanced_filter','#chart_report_step2').val();// value from step2
		jQuery('#advanced_filter','#chart_report_step3').val(advfilterlist);
	},

	registerSubmitEvent : function() {
		var thisInstance = this;
		jQuery('#generateReport').on('click', function(e) {
			var legend = jQuery('#groupbyfield').val();
			var sector = jQuery('#datafields').val();
			var form = thisInstance.getContainer();
			if(sector != '' && legend != '') {
				jQuery('#s2id_groupbyfield').validationEngine('hideAll');
				form.submit();
			} else {
				jQuery('#s2id_groupbyfield').validationEngine('showPrompt',app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION'),'',"bottomRight",true);
				e.preventDefault();
			}
		});
	},

	registerEvents : function(){
		this._super();
        this.calculateValues();
		this.registerForChartTabClick();
		Reports_ChartEdit3_Js.registerFieldForChosen();
		Reports_ChartEdit3_Js.initSelectValues();
	}
});