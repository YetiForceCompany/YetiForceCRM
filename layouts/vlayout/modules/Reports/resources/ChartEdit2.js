
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Reports_Edit3_Js("Reports_ChartEdit2_Js",{},{

	calculateValues : function(){
		//handled advanced filters saved values.
		var advfilterlist = this.advanceFilterInstance.getValues();
		jQuery('#advanced_filter').val(JSON.stringify(advfilterlist));
	},

	initialize : function(container) {
		if(typeof container == 'undefined') {
			container = jQuery('#chart_report_step2');
		}

		if(container.is('#chart_report_step2')) {
			this.setContainer(container);
		}else{
			this.setContainer(jQuery('#chart_report_step2'));
		}
	},

	submit : function(){
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		thisInstance.calculateValues();
		var form = this.getContainer();
		var formData = form.serializeFormData();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		AppConnector.request(formData).then(
			function(data) {
				form.hide();
				progressIndicatorElement.progressIndicator({
					'mode' : 'hide'
				})
				aDeferred.resolve(data);
			},
			function(error,err){

			}
		);
		return aDeferred.promise();
	}
});