/*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*/
Vtiger_Edit_Js("OSSTimeControl_Edit_Js",{},{


	registerGenerateTCFieldTimeAndCost : function () {	
		var thisInstance = this;
		$('input[name="sum_time"]').attr('readonly','readonly').css('width', '80px');
		var sumeTime = thisInstance.differenceDays();
		var hours = (Math.round( (sumeTime/3600000) * 100 ) / 100).toFixed(2);

		jQuery('input[name="sum_time"]').val(hours);
		jQuery('.dateField').change(function(){
			sumeTime = thisInstance.differenceDays();
			if(sumeTime == 'Error'){
				return false;
			}
			hours = (Math.round( (sumeTime/3600000) * 100 ) / 100).toFixed(2);
			jQuery('input[name="sum_time"]').val(hours);
		});
		jQuery('.clockPicker').change(function(){
			sumeTime = thisInstance.differenceDays();
			if(sumeTime == 'Error'){
				return false;
			} 
			hours = (Math.round( (sumeTime/3600000) * 100 ) / 100).toFixed(2);
			jQuery('input[name="sum_time"]').val(hours);
		});
 
	},

	differenceDays : function(){
		var firstDate = jQuery('input[name="date_start"]');
		var firstDateFormat = firstDate.data('date-format');
		var firstDateValue = firstDate.val();
		var secondDate = jQuery('input[name="due_date"]');
		var secondDateFormat = secondDate.data('date-format');
		var secondDateValue = secondDate.val();
		var firstTime = jQuery('input[name="time_start"]');
		var secondTime = jQuery('input[name="time_end"]');
		var firstTimeValue = firstTime.val();
		var secondTimeValue = secondTime.val();
		var firstDateTimeValue = firstDateValue + ' ' + firstTimeValue;
		var secondDateTimeValue = secondDateValue + ' ' + secondTimeValue;

			var firstDateInstance = Vtiger_Helper_Js.getDateInstance(firstDateTimeValue,firstDateFormat);
			var secondDateInstance = Vtiger_Helper_Js.getDateInstance(secondDateTimeValue,secondDateFormat);

		var timeBetweenDates =  secondDateInstance - firstDateInstance;
		if(timeBetweenDates >= 0){
			return timeBetweenDates;
		}
        return 'Error';
		
	},

	/**
	 * Function to register recordpresave event
	 */
	registerRecordPreSaveEvent : function(){
		var thisInstance = this;
		form = this.getForm();
	
		form.on(Vtiger_Edit_Js.recordPreSave, function(e, data) {
			var sumeTime2 = thisInstance.differenceDays();
			if(sumeTime2 == 'Error'){
				var parametry = {
					text: app.vtranslate('JS_DATE_SHOULD_BE_GREATER_THAN'),
					type: 'error'
				};
				Vtiger_Helper_Js.showPnotify(parametry);
				return false;
			}else{
			send = true;
			form.submit();
			}
		});	
	},


	    registerGenerateTCFromHelpDesk : function() {
		var thisInstance = this;
        var sourceDesk = jQuery('input[name="sourceRecord"]').val();
		var moduleName = jQuery('input[name="sourceModule"]').val();
        if (typeof sourceDesk != 'undefined' ) {  
	
            var params = {}
            params.data = {module: 'OSSTimeControl', action: 'GetTCInfo', id: sourceDesk, sourceModule: moduleName}
            params.async = false;
            params.dataType = 'json';
            AppConnector.request(params).then(
                function(data) {
                    var response = data['result'];
                    if ( response['success'] ) {    
                        var sourceD = response.sourceData;
						
                        if(moduleName == 'HelpDesk'){
						//console.log(moduleName);
							if ( 'contact_id' in sourceD ){
								jQuery('[name="contactid"]').val( sourceD.contact_id );
								jQuery('[name="contactid_display"]').val(thisInstance.replaceAll( sourceD.contact_label, '&oacute;', 'ó' )).prop('readonly', true);
							}
							if ( 'parent_id' in sourceD ){
								jQuery('[name="accountid"]').val( sourceD.parent_id );
								jQuery('[name="accountid_display"]').val(thisInstance.replaceAll( sourceD.account_label, '&oacute;', 'ó' )).prop('readonly', true);
							}
						} else if(moduleName == 'Project'){
							if ( 'contact_label' in sourceD ){
								jQuery('[name="contactid"]').val( sourceD.contact_id );
								jQuery('[name="contactid_display"]').val(thisInstance.replaceAll( sourceD.contact_label, '&oacute;', 'ó' )).prop('readonly', true);
							}
							if ( 'account_label' in sourceD ){
								//console.log(moduleName);
								jQuery('[name="accountid"]').val( sourceD.linktoaccountscontacts );
								jQuery('[name="accountid_display"]').val(thisInstance.replaceAll( sourceD.account_label, '&oacute;', 'ó' )).prop('readonly', true);
							}
						}

					}
                    else {
                        var params = {
                            text: app.vtranslate('message'),
                            animation: 'show',
                            type: 'error',
                            sticker: false,
                            hover_sticker: false,
                        };
                        Vtiger_Helper_Js.showPnotify(params);
                    }
                },
                function(data,err){
					var parametry = {
					text: app.vtranslate('JS_ERROR_CONNECTING'),
					type: 'error'
					};
				Vtiger_Helper_Js.showPnotify(parametry);
				}
			);
		}
	},

	escapeRegExp : function(string) {
        return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
    },

    replaceAll : function(string, find, replace) {
        var thisInstance = this;
        string = string.replace(new RegExp(thisInstance.escapeRegExp('&Oacute;'), 'g'), 'Ó');
        return string.replace(new RegExp(thisInstance.escapeRegExp(find), 'g'), replace);
    },
	registerEvents: function(){
		this._super();
		this.registerGenerateTCFieldTimeAndCost();
		this.registerGenerateTCFromHelpDesk();
		this.registerRecordPreSaveEvent();
	}
});
