/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("Reports_Detail_Js",{},{
	advanceFilterInstance : false,
	detailViewContentHolder : false,
	HeaderContentsHolder : false, 
	
	
	getContentHolder : function() {
		if(this.detailViewContentHolder == false) {
			this.detailViewContentHolder = jQuery('div.contentsDiv');
		}
		return this.detailViewContentHolder;
	},
	
	getHeaderContentsHolder : function(){
		if(this.HeaderContentsHolder == false) {
			this.HeaderContentsHolder = jQuery('div.reportsDetailHeader ');
		}
		return this.HeaderContentsHolder;
	},
	
	calculateValues : function(){
		//handled advanced filters saved values.
		var advfilterlist = this.advanceFilterInstance.getValues();
		return JSON.stringify(advfilterlist);
	},
		
	registerSaveOrGenerateReportEvent : function(){
		var thisInstance = this;
		jQuery('.generateReport').on('click',function(e){
            e.preventDefault();
			var advFilterCondition = thisInstance.calculateValues();
            var recordId = thisInstance.getRecordId();
            var currentMode = jQuery(e.currentTarget).data('mode');
            var postData = {
                'advanced_filter': advFilterCondition,
                'record' : recordId,
                'view' : "SaveAjax",
                'module' : app.getModuleName(),
                'mode' : currentMode
            };
            var form = thisInstance.getForm();
            var result = form.validationEngine('validate');
            if(result === true) {
                var progressIndicatorElement = jQuery.progressIndicator({
                });
                AppConnector.request(postData).then(
                    function(data){
                        progressIndicatorElement.progressIndicator({mode:'hide'})
                        thisInstance.getContentHolder().find('#reportContentsDiv').html(data);
                        Vtiger_Helper_Js.showHorizontalTopScrollBar();

                        // To get total records count
                        var count  = parseInt(jQuery('#updatedCount').val());
                        if(count < 1000){
                            jQuery('#countValue').text(count);
                            jQuery('#moreRecordsText').hide();
                        }else{        
                            jQuery('#countValue').html('<img src="layouts/vlayout/skins/images/loading.gif">');
                            var params = {
                                'module' : app.getModuleName(),
                                'advanced_filter': advFilterCondition,
                                'record' : recordId,
                                'action' : "DetailAjax",
                                'mode': "getRecordsCount"
                            };
                            AppConnector.request(params).then(
                                function(data){
                                    var count = parseInt(data.result);
                                    jQuery('#countValue').text(count);
                                    if(count > 1000)
                                        jQuery('#moreRecordsText').show();
                                    else
                                        jQuery('#moreRecordsText').hide();
                                }
                            );
                        }
                    }
                );
            }
		});
	},
	
    registerEventsForActions : function() {
      var thisInstance = this;
      jQuery('.reportActions').click(function(e){
        var element = jQuery(e.currentTarget); 
        var href = element.data('href');
        var type = element.attr("name");
        var advFilterCondition = thisInstance.calculateValues();
        var headerContainer = thisInstance.getHeaderContentsHolder();
        if(type.indexOf("Print") != -1){
            var newEle = '<form action='+href+' method="POST" target="_blank">'+
                    '<input type = "hidden" name ="'+csrfMagicName+'"  value=\''+csrfMagicToken+'\'>'+
 	            '<input type="hidden" value="" name="advanced_filter" id="advanced_filter" /></form>'; 
        }else{
            newEle = '<form action='+href+' method="POST">'+
                    '<input type = "hidden" name ="'+csrfMagicName+'"  value=\''+csrfMagicToken+'\'>'+
 	            '<input type="hidden" value="" name="advanced_filter" id="advanced_filter" /></form>'; 
        }
        var ele = jQuery(newEle); 
        var form = ele.appendTo(headerContainer);
        form.find('#advanced_filter').val(advFilterCondition); 
        form.submit();
      })  
    },
	
	registerEvents : function(){
		this._super();
		this.registerSaveOrGenerateReportEvent();
        this.registerEventsForActions();
		var container = this.getContentHolder();
		this.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('.filterContainer',container));
	}
});