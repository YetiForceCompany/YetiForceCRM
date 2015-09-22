/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Settings_PDF_Edit_Js("Settings_PDF_Edit1_Js",{},{
	
	advanceFilterInstance : false,

	init : function() {
		this.initialize();
	},
	/**
	 * Function to get the container which holds all the reports step1 elements
	 * @return jQuery object
	 */
	getContainer : function() {
		return this.step1Container;
	},

	/**
	 * Function to set the reports step1 container
	 * @params : element - which represents the reports step1 container
	 * @return : current instance
	 */
	setContainer : function(element) {
		this.step1Container = element;
		return this;
	},

	/**
	 * Function  to intialize the reports step1
	 */
	initialize : function(container) {
		if(typeof container === 'undefined') {
			container = jQuery('#pdf_step1');
		}
		if(container.is('#pdf_step1')) {
			this.setContainer(container);
		}else{
			this.setContainer(jQuery('#pdf_step1'));
		}
	},

	calculateValues : function(){
		//handled advanced filters saved values.
		var enableFilterElement = jQuery('#enableAdvanceFilters');
		if(enableFilterElement.length > 0 && enableFilterElement.is(':checked') == false) {
			jQuery('#advanced_filter').val(jQuery('#olderConditions').val());
		} else {
			jQuery('[name="filtersavedinnew"]').val("6");
			var advfilterlist = this.advanceFilterInstance.getValues();
			jQuery('#advanced_filter').val(JSON.stringify(advfilterlist));
		}
	},

	submit : function(){
		var aDeferred = jQuery.Deferred();
		this.calculateValues();
		var form = this.getContainer();
		var formData = form.serializeFormData();
		formData['async'] = false;
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		
		var saveData = form.serializeFormData();
		saveData['action'] = 'Save';
		saveData['step'] = 1;
		saveData['view'] = '';
		saveData['async'] = false;
		AppConnector.request(saveData).then(
			function(data) {
				data = JSON.parse(data);
				if(data.success == true) {
					Settings_Vtiger_Index_Js.showMessage({text : app.vtranslate('JS_PDF_SAVED_SUCCESSFULLY')});
					var pdfRecordElement = jQuery('[name="record"]',form);
					if(pdfRecordElement.val() === '') {
						pdfRecordElement.val(data.result.id);
						formData['record'] = data.result.id;
					}
					
					formData['record'] = data.result.id;
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
				}
			},
			function(error,err){
				app.errorLog(error, err);
			}
		);

		return aDeferred.promise();
	},
	
	registerCancelStepClickEvent: function(form) {
		jQuery('button.cancelLink', form).on('click', function() {
			window.history.back();
		});
	},
	
	registerEvents : function(){
		var container = this.getContainer();
		//After loading 1st step only, we will enable the Next button
		container.find('[type="submit"]').removeAttr('disabled');
		
		var opts = app.validationEngineOptions;
		// to prevent the page reload after the validation has completed
		opts['onValidationComplete'] = function(form,valid) {
            //returns the valid status
            return valid;
        };
		opts['promptPosition'] = "bottomRight";
		container.validationEngine(opts);
		this.registerCancelStepClickEvent(container);
		this.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('.filterContainer',container));
	}
});
