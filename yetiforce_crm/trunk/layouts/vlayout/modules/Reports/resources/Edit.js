/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_Edit_Js("Reports_Edit_Js",{

	instance : {}

},{

	currentInstance : false,

	reportsContainer : false,

	init : function() {
		var statusToProceed = this.proceedRegisterEvents();
		if(!statusToProceed){
			return;
		}
		this.initiate();
	},
	/**
	 * Function to get the container which holds all the reports elements
	 * @return jQuery object
	 */
	getContainer : function() {
		return this.reportsContainer;
	},

	/**
	 * Function to set the reports container
	 * @params : element - which represents the reports container
	 * @return : current instance
	 */
	setContainer : function(element) {
		this.reportsContainer = element;
		return this;
	},


	/*
	 * Function to return the instance based on the step of the report
	 */
	getInstance : function(step) {
		if(step in Reports_Edit_Js.instance ){
			return Reports_Edit_Js.instance[step];
		} else {
            var view = app.getViewName();
			var moduleClassName = app.getModuleName()+"_"+view+step+"_Js";
			Reports_Edit_Js.instance[step] =  new window[moduleClassName]();
			return Reports_Edit_Js.instance[step]
		}
	},

	/*
	 * Function to get the value of the step
	 * returns 1 or 2 or 3
	 */
	getStepValue : function(){
		var container = this.currentInstance.getContainer();
		return jQuery('.step',container).val();
	},

	/*
	 * Function to initiate the step 1 instance
	 */
	initiate : function(container){
		if(typeof container == 'undefined') {
			container = jQuery('.reportContents');
		}
		if(container.is('.reportContents')) {
			this.setContainer(container);
		}else{
			this.setContainer(jQuery('.reportContents',container));
		}
		this.initiateStep('1');
		this.currentInstance.registerEvents();
	},
	/*
	 * Function to initiate all the operations for a step
	 * @params step value
	 */
	initiateStep : function(stepVal) {
		var step = 'step'+stepVal;
		this.activateHeader(step);
		var currentInstance = this.getInstance(stepVal);
		this.currentInstance = currentInstance;
	},

	/*
	 * Function to activate the header based on the class
	 * @params class name
	 */
	activateHeader : function(step) {
		var headersContainer = jQuery('.crumbs');
		headersContainer.find('.active').removeClass('active');
		jQuery('#'+step,headersContainer).addClass('active');
	},
	/*
	 * Function to register the click event for next button
	 */
	registerFormSubmitEvent : function(form) {
		var thisInstance = this;
		if(jQuery.isFunction(thisInstance.currentInstance.submit)){
			form.on('submit',function(e){
				var form = jQuery(e.currentTarget);
				var specialValidation = true;
				if(jQuery.isFunction(thisInstance.currentInstance.isFormValidate)){
					var specialValidation =  thisInstance.currentInstance.isFormValidate();
				}
				if (form.validationEngine('validate') && specialValidation) {
					thisInstance.currentInstance.submit().then(function(data){
						thisInstance.getContainer().append(data);
						var stepVal = thisInstance.getStepValue();
						var nextStepVal = parseInt(stepVal) + 1;
						thisInstance.initiateStep(nextStepVal);
						thisInstance.currentInstance.initialize();
						var container = thisInstance.currentInstance.getContainer();
						thisInstance.registerFormSubmitEvent(container);
						thisInstance.currentInstance.registerEvents();
					});
				}
				e.preventDefault();
			})
		}
	},

	back : function(){
		var step = this.getStepValue();
		var prevStep = parseInt(step) - 1;
		this.currentInstance.initialize();
		var container = this.currentInstance.getContainer();
		container.remove();
		this.initiateStep(prevStep);
		this.currentInstance.getContainer().show();
	},

	/*
	 * Function to register the click event for back step
	 */
	registerBackStepClickEvent : function(){
		var thisInstance = this;
		var container = this.getContainer();
		container.on('click','.backStep',function(e){
			thisInstance.back();
		});
	},

	registerEvents : function(){
		var statusToProceed = this.proceedRegisterEvents();
		if(!statusToProceed){
			return;
		}
		var form = this.currentInstance.getContainer();
		this.registerFormSubmitEvent(form);
		this.registerBackStepClickEvent();
	}
});

