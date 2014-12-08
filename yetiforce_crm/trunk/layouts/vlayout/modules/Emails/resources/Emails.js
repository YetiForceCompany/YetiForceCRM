/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
var Vtiger_Emails_Js = {
	
	/*
	 * function to load CKEditor
	 */
	loadCkEditor : function(){
//		var aDeferred = jQuery.Deferred();
//		data = data.children();		
//		jQuery( '#editor1',data ).ckeditor(function(){
//			aDeferred.resolve(data);
//		},{});
//		return aDeferred.promise();

	var instance = CKEDITOR.instances['description'];
    if(instance)
    {
        CKEDITOR.remove(instance);
    }
	
	//configured ckeditor toolbar for vtiger
	var Vtiger_ckeditor_toolbar = 
	[
		['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		['NumberedList','BulletedList','-','Outdent','Indent'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['Link','Unlink','Anchor'],
		['Source','-','NewPage','Preview','Templates'],
		'/',
		['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker'],
		['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
		['Image','Table','HorizontalRule','SpecialChar','PageBreak','TextColor','BGColor'], //,'Smiley','UniversalKey'],
		'/',
		['Styles','Format','Font','FontSize']
	];
    CKEDITOR.replace( 'description',
	{
		fullPage : true,
		extraPlugins : 'docprops',
		toolbar : Vtiger_ckeditor_toolbar
	});
	
	jQuery('.blockPage').addClass('sendEmailBlock');
	},
	
	/**
	 * function to send the email
	 * return none 
	 */
	SendEmailStep2 : function(form){
		var massSendEmailUrl = form.serializeFormData();
		app.hideModalWindow();
		AppConnector.request(massSendEmailUrl).then(
			function(data) {
				
			},
			function(error,err){

			}
		);
	},
		
	/**
	 * function to display the email form 
	 * return UI 
	 */
	getComposeEmailForm : function(form){
		var massSendEmailUrl = form.serializeFormData();
		AppConnector.request(massSendEmailUrl).then(
			function(data) {
				app.hideModalWindow();
				app.showModalWindow(data,{'text-align' : 'left'});
				Vtiger_Emails_Js.loadCkEditor();
				Vtiger_Emails_Js.registerEvents();
			},
			function(error,err){

			}
		);
	},
		
	/**
	 * function to call the registerevents of send Email step1 
	 */
	registerComposeEmailStep1Events : function(){
		jQuery('.SendEmailFormStep1').on('submit',"#SendEmailFormStep1",function(e){
			var form = jQuery(e.currentTarget);
			var chosenElement = jQuery('#composeEmailFields_chzn');
			var result = Vtiger_MultiSelect_Validator_Js.invokeValidation(chosenElement);
			if(result == true){
				Vtiger_Emails_Js.getComposeEmailForm(form);
			} else {
				chosenElement.validationEngine('showPrompt', result , 'error','topRight',true);
				return false;
			}
			e.preventDefault();
		});
	},
	
	registerEvents : function(){
		var composeEmailContainer = jQuery('.SendEmailFormStep2');
		
		composeEmailContainer.on('submit','#SendEmailFormStep2',function(e){
			var form = jQuery(e.currentTarget);
			Vtiger_Emails_Js.SendEmailStep2(form);
			e.preventDefault();
		});
		composeEmailContainer.on('click','#saveDraft',function(){
			jQuery('#flag').val("SAVED");
		});
		composeEmailContainer.on('click','#sendEmail',function(e){
			jQuery('#flag').val("SENT");
		});
		composeEmailContainer.on('click','#ccLink',function(e){
			jQuery('#ccContainer').show();
			jQuery(e.currentTarget).hide();
		});
		composeEmailContainer.on('click','#bccLink', function(e){
			jQuery('#bccContainer').show();
			jQuery(e.currentTarget).hide();
		});
		
		composeEmailContainer.on('click','#selectEmailTemplate',function(e){
			var url = jQuery(e.currentTarget).data('url');
			var popupInstance = Vtiger_Popup_Js.getInstance();
			popupInstance.show(url,function(data){
				var responseData = JSON.parse(data);
				for(var id in responseData){
					var selectedName = responseData[id].name;
				}
				var editor = CKEDITOR.instances.description; 
				var edata = editor.getData();
				var replaced_text = edata.replace(edata, selectedName); 
				editor.setData(replaced_text);
			});
		});	
		jQuery('#SendEmailFormStep2').validationEngine(app.validationEngineOptions);
	}	
}

