/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Email_Validator_Js("Vtiger_To_Email_Validator_Js", {

	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function(field, rules, i, options){
		var toEmailInstance = new Vtiger_To_Email_Validator_Js();
		toEmailInstance.setElement(field);
		return toEmailInstance.validate();
	}
},{

	/**
	 * Function to validate the email field data
	 */
	validate: function() {
		var fieldValue = this.getFieldValue();
		var fieldValuesList = fieldValue.split(',');
		for (var i in fieldValuesList) {
			var splittedFieldValue = fieldValuesList[i];
			var emailInstance = new Vtiger_Email_Validator_Js();
			var response = emailInstance.validateValue(splittedFieldValue);
			if(response != true) {
				return emailInstance.getError();
			}
		}
	}

});

jQuery.Class("Emails_MassEdit_Js",{},{

	ckEditorInstance : false,
	massEmailForm : false,
	saved : "SAVED",
	sent : "SENT",
	attachmentsFileSize : 0,
	documentsFileSize : 0,
	
	/**
	 * Function to get ckEditorInstance
	 */
	getckEditorInstance : function(){
		if(this.ckEditorInstance == false){
			this.ckEditorInstance = new Vtiger_CkEditor_Js();
		}
		return this.ckEditorInstance;
	},

	/**
	 * function to display the email form
	 * return UI
	 */
	showComposeEmailForm : function(params,cb,windowName){
	    app.hideModalWindow();
		//var popupInstance = Vtiger_Popup_Js.getInstance();
		//return popupInstance.show(params,cb,windowName);
		var urlString = (typeof params == 'string')? params : jQuery.param(params);
		var url = 'index.php?'+urlString;
		window.location.href = url;
	},

	/*
	 * Function to get the Mass Email Form
	 */
	getMassEmailForm : function(){
		if(this.massEmailForm == false){
			this.massEmailForm = jQuery("#massEmailForm");
		}
		return this.massEmailForm;
	},

	/**
	 * function to call the registerevents of send Email step1
	 */
	registerEmailFieldSelectionEvent : function(){
		var thisInstance = this;
		var selectEmailForm = jQuery("#SendEmailFormStep1");
		selectEmailForm.on('submit',function(e){
			var form = jQuery(e.currentTarget);
			var params = form.serializeFormData();
			thisInstance.showComposeEmailForm(params,"","composeEmail");
			e.preventDefault();
		});
	},

	/*
		* Function to register the event of send email
		*/
	registerSendEmailEvent : function(){
		this.getMassEmailForm().on('submit',function(e){
			var formElement = jQuery(e.currentTarget);
			var invalidFields = formElement.data('jqv').InvalidFields;
			var progressElement = formElement.find('[name="progressIndicator"]');
			if(invalidFields.length == 0){
				jQuery('#sendEmail').attr('disabled',"disabled");
				jQuery('#saveDraft').attr('disabled',"disabled");
				progressElement.progressIndicator();
				return true;
			}
			return false;
		}).on('keypress',function(e){
			if(e.which == 13){
				e.preventDefault();
			}
		});
	},
	setAttachmentsFileSizeByElement : function(element){
		 if(jQuery.browser.msie)
		{
			var	filesize = element.fileSize;
			if(typeof fileSize != 'undefined'){
				this.attachmentsFileSize += filesize;
			}
		} else {
			this.attachmentsFileSize += element.get(0).files[0].size;
		}
	},
	
	setAttachmentsFileSizeBySize : function(fileSize){
		this.attachmentsFileSize += parseFloat(fileSize);
	},

	removeAttachmentFileSizeByElement : function(element) {
		 if(jQuery.browser.msie)
		{
			var	filesize = element.fileSize;
			if(typeof fileSize != 'undefined'){
				this.attachmentsFileSize -= filesize;
			}
		} else {
			this.attachmentsFileSize -= element.get(0).files[0].size;
		}
	},
	
	removeAttachmentFileSizeBySize : function(fileSize){
		this.attachmentsFileSize -= parseFloat(fileSize);
	},

	getAttachmentsFileSize : function(){
		return this.attachmentsFileSize;
	},
	setDocumentsFileSize : function(documentSize){
		this.documentsFileSize += parseFloat(documentSize);
	},
	getDocumentsFileSize : function(){
		return this.documentsFileSize;
	},

	getTotalAttachmentsSize : function(){
		return parseFloat(this.getAttachmentsFileSize())+parseFloat(this.getDocumentsFileSize());
	},

	getMaxUploadSize : function(){
		return jQuery('#maxUploadSize').val();
	},

	removeDocumentsFileSize : function(documentSize){
		this.documentsFileSize -= parseFloat(documentSize);
	},

	removeAttachmentsFileSize : function(){

	},

	fileAfterSelectHandler : function(element, value, master_element){
		var thisInstance = this;
		var mode = jQuery('[name="emailMode"]').val();
		var existingAttachment = JSON.parse(jQuery('[name="attachments"]').val());
		element = jQuery(element);
		thisInstance.setAttachmentsFileSizeByElement(element);
		var totalAttachmentsSize = thisInstance.getTotalAttachmentsSize();
		var maxUploadSize = thisInstance.getMaxUploadSize();
		if(totalAttachmentsSize > maxUploadSize){
			Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_MAX_FILE_UPLOAD_EXCEEDS'));
			this.removeAttachmentFileSizeByElement(jQuery(element));
			master_element.list.find('.MultiFile-label:last').find('.MultiFile-remove').trigger('click');
		}else if((mode != "") && (existingAttachment != "")){
			var pattern = /\\/;
			var val = value.split(pattern);
			if(jQuery.browser.mozilla){
				fileuploaded = value;
			} else if(jQuery.browser.webkit || jQuery.browser.msie) {
				var fileuploaded = val[2];
				fileuploaded=fileuploaded.replace(" ","_");
			}
			jQuery.each(existingAttachment,function(key,value){
				if((value['attachment'] == fileuploaded) && !(value.hasOwnProperty( "docid"))){
					var errorMsg = app.vtranslate("JS_THIS_FILE_HAS_ALREADY_BEEN_SELECTED")+fileuploaded;
					Vtiger_Helper_Js.showPnotify(app.vtranslate(errorMsg));
					thisInstance.removeAttachmentFileSizeByElement(jQuery(element),value);
					master_element.list.find('.MultiFile-label:last').find('.MultiFile-remove').trigger('click');
					return false;
				}
			})
		}
		return true;
	},
	/*
	 * Function to register the events for getting the values
	 */
	registerEventsToGetFlagValue : function(){
		var thisInstance = this;
		jQuery('#saveDraft').on('click',function(e){
			jQuery('#flag').val(thisInstance.saved);
		});
		jQuery('#sendEmail').on('click',function(e){
			jQuery('#flag').val(thisInstance.sent);
		});
	},
	
	checkHiddenStatusofCcandBcc : function(){
		var ccLink = jQuery('#ccLink');
		var bccLink = jQuery('#bccLink');
		if(ccLink.is(':hidden') && bccLink.is(':hidden')){
			ccLink.closest('div.row').addClass('hide');
		}
	},

	/*
	 * Function to register the events for bcc and cc links
	 */
	registerCcAndBccEvents : function(){
		var thisInstance = this;
		jQuery('#ccLink').on('click',function(e){
			jQuery('#ccContainer').show();
			jQuery(e.currentTarget).hide();
			thisInstance.checkHiddenStatusofCcandBcc();
		});
		jQuery('#bccLink').on('click',function(e){
			jQuery('#bccContainer').show();
			jQuery(e.currentTarget).hide();
			thisInstance.checkHiddenStatusofCcandBcc();
		});
	},

	/*
	 * Function to register the send email template event
	 */
	registerSendEmailTemplateEvent : function(){

		var thisInstance = this;
		jQuery('#selectEmailTemplate').on('click',function(e){
			var url = jQuery(e.currentTarget).data('url');
			var popupInstance = Vtiger_Popup_Js.getInstance();
			popupInstance.show(url,function(data){
				var responseData = JSON.parse(data);
				for(var id in responseData){
					var selectedName = responseData[id].name;
					var selectedTemplateBody = responseData[id].info;
				}
				var ckEditorInstance = thisInstance.getckEditorInstance();
				ckEditorInstance.loadContentsInCkeditor(selectedTemplateBody);
				jQuery('#subject').val(selectedName);
			},'tempalteWindow');
		});
	},
	getDocumentAttachmentElement : function(selectedFileName,id,selectedFileSize){
		return '<div class="MultiFile-label"><a class="removeAttachment cursorPointer" data-id='+id+' data-file-size='+selectedFileSize+'>x </a><span>'+selectedFileName+'</span></div>';
	},
	registerBrowseCrmEvent : function(){
		var thisInstance = this;
		jQuery('#browseCrm').on('click',function(e){
			var selectedDocumentId;
			var url = jQuery(e.currentTarget).data('url');
			var popupInstance = Vtiger_Popup_Js.getInstance();
			popupInstance.show(url,function(data){
				var responseData = JSON.parse(data);
				for(var id in responseData){
					selectedDocumentId = id;
					var selectedFileName = responseData[id].info['filename'];
					var selectedFileSize =  responseData[id].info['filesize'];
					var response = thisInstance.writeDocumentIds(selectedDocumentId)
					if(response){
						var attachmentElement = thisInstance.getDocumentAttachmentElement(selectedFileName,id,selectedFileSize);
						jQuery(attachmentElement).appendTo(jQuery('#attachments'));
						jQuery('.MultiFile-applied,.MultiFile').addClass('removeNoFileChosen');
						thisInstance.setDocumentsFileSize(selectedFileSize);
					}
				}
				
			},'browseCrmWindow');
		});
	},
	/**
	 * Function to check whether selected document 
	 * is already an existing attachment
	 * @param expects document id to check
	 * @return true if present false if not present
	 */
	checkIfExisitingAttachment : function(selectedDocumentId){
		var documentExist;
		var documentPresent;
		var mode = jQuery('[name="emailMode"]').val();
		var selectedDocumentIds = jQuery('#documentIds').val();
		var existingAttachment = JSON.parse(jQuery('[name="attachments"]').val());
		if((mode != "") && (existingAttachment != "")){
			jQuery.each(existingAttachment,function(key,value){
				if(value.hasOwnProperty( "docid")){
					if(value['docid'] == selectedDocumentId){
						documentExist = 1;
						return false;
					} 
				}
			})
			if(selectedDocumentIds != ""){
				selectedDocumentIds = JSON.parse(selectedDocumentIds);
			}
			if((documentExist == 1) || (jQuery.inArray(selectedDocumentId,selectedDocumentIds) != '-1')){
				documentPresent = 1;
			} else {
				documentPresent = 0;
			}
		} else if(selectedDocumentIds != ""){
			selectedDocumentIds = JSON.parse(selectedDocumentIds);
			if((jQuery.inArray(selectedDocumentId,selectedDocumentIds) != '-1')){
				documentPresent = 1;
			} else {
				documentPresent = 0;
			}
		}
		if(documentPresent == 1){
			var errorMsg = app.vtranslate("JS_THIS_DOCUMENT_HAS_ALREADY_BEEN_SELECTED");
			Vtiger_Helper_Js.showPnotify(app.vtranslate(errorMsg));
			return true;
		} else {
			return false;
		}
	},

	writeDocumentIds :function(selectedDocumentId){
		var thisInstance = this;
		var newAttachment;
		var selectedDocumentIds = jQuery('#documentIds').val();
		if(selectedDocumentIds != ""){
			selectedDocumentIds = JSON.parse(selectedDocumentIds);
			var existingAttachment = thisInstance.checkIfExisitingAttachment(selectedDocumentId);
			if(!existingAttachment){
				newAttachment = 1;
			} else {
				newAttachment = 0;
			}
		} else {
			var existingAttachment = thisInstance.checkIfExisitingAttachment(selectedDocumentId);
			if(!existingAttachment){
				newAttachment = 1;
				var selectedDocumentIds = new Array();
			}
		}
		if(newAttachment == 1){
			selectedDocumentIds.push(selectedDocumentId);
			jQuery('#documentIds').val(JSON.stringify(selectedDocumentIds));
			return true;
		} else {
			return false;
		}
	},
	
	removeDocumentIds : function(removedDocumentId){
		var documentIdsContainer = jQuery('#documentIds');
		var documentIdsArray = JSON.parse(documentIdsContainer.val());
		documentIdsArray.splice( jQuery.inArray('"'+removedDocumentId+'"', documentIdsArray), 1 );
		documentIdsContainer.val(JSON.stringify(documentIdsArray));
	},
	
	registerRemoveAttachmentEvent : function(){
		var thisInstance = this;
		this.getMassEmailForm().on('click','.removeAttachment',function(e){
			var currentTarget = jQuery(e.currentTarget);
			var id = currentTarget.data('id');
			var fileSize = currentTarget.data('fileSize');
			currentTarget.closest('.MultiFile-label').remove();
			thisInstance.removeDocumentsFileSize(fileSize);
			thisInstance.removeDocumentIds(id);
			if (jQuery('#attachments').is(':empty')){
				jQuery('.MultiFile,.MultiFile-applied').removeClass('removeNoFileChosen');
			}
		});
	},
	
	/**
	 * Function to register event for to field in compose email popup
	 */
	registerEventsForToField : function(){
		var thisInstance = this;
		this.getMassEmailForm().on('click','.selectEmail',function(e){
			var moduleSelected = jQuery('.emailModulesList').val();
			var parentElem = jQuery(e.target).closest('.toEmailField');
			var sourceModule = jQuery('[name=module]').val();
			var params = {
				'module' : moduleSelected,
				'src_module' : sourceModule,
				'view': 'EmailsRelatedModulePopup'
			}
			var popupInstance =Vtiger_Popup_Js.getInstance();
			popupInstance.show(params, function(data){
					var responseData = JSON.parse(data);
					for(var id in responseData){
						var data = {
							'name' : responseData[id].name,
							'id' : id,
							'emailid' : responseData[id].email
						}
						thisInstance.setReferenceFieldValue(parentElem, data);
                        thisInstance.addToEmailAddressData(data);
                        thisInstance.appendToSelectedIds(id);
                        thisInstance.addToEmails(data);
					}
				},'relatedEmailModules');
		});
		
		this.getMassEmailForm().on('click','[name="clearToEmailField"]',function(e){
			var element = jQuery(e.currentTarget);
			element.closest('div.toEmailField').find('.sourceField').val('');
			thisInstance.getMassEmailForm().find('[name="toemailinfo"]').val(JSON.stringify(new Array()));
			thisInstance.getMassEmailForm().find('[name="selected_ids"]').val(JSON.stringify(new Array()));
			thisInstance.getMassEmailForm().find('[name="to"]').val(JSON.stringify(new Array()));

			var preloadData = [];
			thisInstance.setPreloadData(preloadData);
			thisInstance.getMassEmailForm().find('#emailField').select2('data', preloadData);
		});
		
		
	},
	
	setReferenceFieldValue : function(container,object){
		var thisInstance = this;
		var preloadData = thisInstance.getPreloadData();

		var emailInfo = {
			'recordId' : object.id,
			'id' : object.emailid,
			'text' : object.name+' <b>('+object.emailid+')</b>'
		}
		preloadData.push(emailInfo);
		thisInstance.setPreloadData(preloadData);
		container.find('#emailField').select2('data', preloadData);

		var toEmailField = container.find('.sourceField');
		var toEmailFieldExistingValue = toEmailField.val();
		var toEmailFieldNewValue;
		if(toEmailFieldExistingValue != ""){
			toEmailFieldNewValue = toEmailFieldExistingValue+","+object.emailid;
		} else {
			toEmailFieldNewValue = object.emailid;
		}
		toEmailField.val(toEmailFieldNewValue);
	},

    addToEmailAddressData : function(mailInfo) {
        var mailInfoElement = this.getMassEmailForm().find('[name="toemailinfo"]');
        var existingToMailInfo = JSON.parse(mailInfoElement.val());
        //If it is an array then there are no previous records so make as map
        if(typeof existingToMailInfo.length != 'undefined') {
          existingToMailInfo = {};
        }
        existingToMailInfo[mailInfo.id] = new Array(mailInfo.emailid);
        mailInfoElement.val(JSON.stringify(existingToMailInfo));
    },

    appendToSelectedIds : function(selectedId) {
        var selectedIdElement = this.getMassEmailForm().find('[name="selected_ids"]');
        var previousValue = '';
        if(JSON.parse(selectedIdElement.val()) != '') {
            previousValue = JSON.parse(selectedIdElement.val());
            previousValue.push(selectedId);
        } else {
			previousValue = new Array(selectedId);
        }
		selectedIdElement.val(JSON.stringify(previousValue));

    },

    addToEmails : function(mailInfo){
        var toEmails = this.getMassEmailForm().find('[name="to"]');
        var value = JSON.parse(toEmails.val());
		if(value == ""){
			value = new Array();
		}
        value.push(mailInfo.emailid);
        toEmails.val(JSON.stringify(value));
    },
	
	/**
	 * Function to remove attachments that are added in 
	 * edit view of email in compose email form
	 */
	registerEventForRemoveCustomAttachments : function(){
		var thisInstance = this;
		var composeEmailForm = this.getMassEmailForm();
		jQuery('[name="removeAttachment"]').on('click',function(e){
			var attachmentsContainer = composeEmailForm.find('[ name="attachments"]');
			var attachmentsInfo = JSON.parse(attachmentsContainer.val());
			var element = jQuery(e.currentTarget);
			var imageContainer = element.closest('div.MultiFile-label');
			var imageContainerData = imageContainer.data();
			var fileType = imageContainerData['fileType'];
			var fileSize = imageContainerData['fileSize'];
			var fileId = imageContainerData['fileId'];
			if(fileType == "document"){
				thisInstance.removeDocumentsFileSize(fileSize);
			} else if(fileType == "file"){
				thisInstance.removeAttachmentFileSizeBySize(fileSize);
			}
			jQuery.each(attachmentsInfo,function(index,attachmentObject){
				if((typeof attachmentObject != "undefined") && (attachmentObject.fileid == fileId)){
					attachmentsInfo.splice(index,1);
				}
			})
			attachmentsContainer.val(JSON.stringify(attachmentsInfo));
			imageContainer.remove();
		})
	},
	
	/**
	 * Function to calculate upload file size
	 */
	calculateUploadFileSize : function(){
		var thisInstance = this;
		var composeEmailForm = this.getMassEmailForm();
		var attachmentsList = composeEmailForm.find('#attachments');
		var attachments = attachmentsList.find('.customAttachment');
		jQuery.each(attachments,function(){
			var element = jQuery(this);
			var fileSize = element.data('fileSize');
			var fileType = element.data('fileType');
			if(fileType == "file"){
				thisInstance.setAttachmentsFileSizeBySize(fileSize);
			} else if(fileType == "document"){
				fileSize = fileSize.replace('KB','');
				thisInstance.setDocumentsFileSize(fileSize);
			}
		})
	},
	
	/**
	 * Function to register event for saved or sent mail
	 * getting back to preview
	 */
	registerEventForGoToPreview : function(){
		jQuery('#gotoPreview').on('click',function(e){
			var recordId = jQuery('[name="parent_id"]').val();
			var parentRecordId = jQuery('[name="parent_record_id"]').val();
			var params = {};
			params['module'] = "Emails";
			params['view'] = "ComposeEmail";
			params['mode'] = "emailPreview";
			params['record'] = recordId;
			params['parentId'] = parentRecordId;
			var urlString = (typeof params == 'string')? params : jQuery.param(params);
			var url = 'index.php?'+urlString;
			self.location.href = url;
		})
	},

	preloadData : new Array(),

	getPreloadData : function() {
		return this.preloadData;
	},

	setPreloadData : function(dataInfo){
		this.preloadData = dataInfo;
		return this;
	},

	searchEmails : function(params) {
		var aDeferred = jQuery.Deferred();

		if(typeof params.module == 'undefined') {
			params.module = app.getModuleName();
		}

		if(typeof params.action == 'undefined') {
			params.action = 'BasicAjax';
		}
		AppConnector.request(params).then(
			function(data){
				aDeferred.resolve(data);
			},
			function(error){
				aDeferred.reject();
			}
		)
		return aDeferred.promise();
	},

	/**
	 * Function which will handle the reference auto complete event registrations
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerAutoCompleteFields : function(container) {
		var thisInstance = this;

		container.find('#emailField').select2({
			minimumInputLength: 3,
			closeOnSelect : false,

			tags : [],
			tokenSeparators: [","],

			createSearchChoice : function(term) {
				return {id: term, text: term};
			},

			ajax : {
                'url' : 'index.php?module=Emails&action=BasicAjax',
                'dataType' : 'json',
                'data' : function(term,page){
                     var data = {};
                     data['searchValue'] = term;
                     return data;
                },
                'results' : function(data){
					var finalResult = [];
					var results = data.result;
					var resultData = new Array();
                    for(var moduleName in results) {
						var moduleResult = [];
						moduleResult.text = moduleName;

						var children = new Array();
						for(var recordId in data.result[moduleName]) {
							var emailInfo = data.result[moduleName][recordId];
							for (var i in emailInfo) {
								var childrenInfo = [];
								childrenInfo.recordId = recordId;
								childrenInfo.id = emailInfo[i].value;
								childrenInfo.text = emailInfo[i].label;
								children.push(childrenInfo);
							}
						}
						moduleResult.children = children;
						resultData.push(moduleResult);
                    }
					finalResult.results = resultData;
                    return finalResult;
                },
				transport : function(params) {
					return jQuery.ajax(params);
				}
            }

		}).on("change", function (selectedData) {
			var addedElement = selectedData.added;
			if (typeof addedElement != 'undefined') {
				var data = {
					'id' : addedElement.recordId,
					'name' : addedElement.text,
					'emailid' : addedElement.id
				}
				thisInstance.addToEmails(data);
				if (typeof addedElement.recordId != 'undefined') {
					thisInstance.addToEmailAddressData(data);
					thisInstance.appendToSelectedIds(addedElement.recordId);
				}

				var preloadData = thisInstance.getPreloadData();
				var emailInfo = {
					'id' : addedElement.id
				}
				if (typeof addedElement.recordId != 'undefined') {
					emailInfo['text'] = addedElement.text;
					emailInfo['recordId'] = addedElement.recordId;
				} else {
					emailInfo['text'] = addedElement.id;
				}
				preloadData.push(emailInfo);
				thisInstance.setPreloadData(preloadData);
			}

			var removedElement = selectedData.removed;
			if (typeof removedElement != 'undefined') {
				var data = {
					'id' : removedElement.recordId,
					'name' : removedElement.text,
					'emailid' : removedElement.id
				}
				thisInstance.removeFromEmails(data);
				if (typeof removedElement.recordId != 'undefined') {
					thisInstance.removeFromEmailAddressData(data);
					thisInstance.removeFromSelectedIds(removedElement.recordId);
				}

				var preloadData = thisInstance.getPreloadData();
				var updatedPreloadData = [];
				for(var i in preloadData) {
					var preloadDataInfo = preloadData[i];
					var skip = false;
					if (removedElement.id == preloadDataInfo.id) {
						skip = true;
					}
					if (skip == false) {
						updatedPreloadData.push(preloadDataInfo);
					}
				}
				thisInstance.setPreloadData(updatedPreloadData);
			}
		});

		container.find('#emailField').select2("container").find("ul.select2-choices").sortable({
			containment: 'parent',
			start: function(){
				container.find('#emailField').select2("onSortStart");
			},
			update: function(){
				container.find('#emailField').select2("onSortEnd");
			}
		});

		var toEmailNamesList = JSON.parse(container.find('[name="toMailNamesList"]').val());
		var toEmailInfo = JSON.parse(container.find('[name="toemailinfo"]').val());
		var toEmails = container.find('[name="toEmail"]').val();
		var toFieldValues = Array();
		if (toEmails.length > 0) {
			toFieldValues = toEmails.split(',');
		}

		var preloadData = thisInstance.getPreloadData();
		if (typeof toEmailInfo != 'undefined') {
			for(var key in toEmailInfo) {
				if (toEmailNamesList.hasOwnProperty(key)) {
					for (var i in toEmailNamesList[key]) {
						var emailInfo = [];
						var emailId = toEmailNamesList[key][i].value;
						var emailInfo = {
							'recordId' : key,
							'id' : emailId,
							'text' : toEmailNamesList[key][i].label+' <b>('+emailId+')</b>'
						}
						preloadData.push(emailInfo);
						if (jQuery.inArray(emailId, toFieldValues) != -1) {
							var index = toFieldValues.indexOf(emailId);
							if (index !== -1) {
								toFieldValues.splice(index, 1);
							}
						}
					}
				}
			}
		}
		if (typeof toFieldValues != 'undefined') {
			for(var i in toFieldValues) {
				var emailId = toFieldValues[i];
				var emailInfo = {
					'id' : emailId,
					'text' : emailId
				}
				preloadData.push(emailInfo);
			}
		}
		if (typeof preloadData != 'undefined') {
			thisInstance.setPreloadData(preloadData);
			container.find('#emailField').select2('data', preloadData);
		}

	},

	removeFromEmailAddressData : function(mailInfo) {
        var mailInfoElement = this.getMassEmailForm().find('[name="toemailinfo"]');
        var previousValue = JSON.parse(mailInfoElement.val());

		delete previousValue[mailInfo.id];
        mailInfoElement.val(JSON.stringify(previousValue));
    },

    removeFromSelectedIds : function(selectedId) {
        var selectedIdElement = this.getMassEmailForm().find('[name="selected_ids"]');
        var previousValue = JSON.parse(selectedIdElement.val());

		var updatedValue = [];
		for (var i in previousValue) {
			var id = previousValue[i];
			var skip = false;
			if (id == selectedId) {
				skip = true;
			}
			if (skip == false) {
				updatedValue.push(id);
			}
		}
        selectedIdElement.val(JSON.stringify(updatedValue));
    },

    removeFromEmails : function(mailInfo){
        var toEmails = this.getMassEmailForm().find('[name="to"]');
        var previousValue = JSON.parse(toEmails.val());

		var updatedValue = [];
		for (var i in previousValue) {
			var email = previousValue[i];
			var skip = false;
			if (email == mailInfo.emailid) {
				skip = true;
			}
			if (skip == false) {
				updatedValue.push(email);
			}
		}
        toEmails.val(JSON.stringify(updatedValue));
    },

	registerEvents : function(){
		var thisInstance = this;
		var composeEmailForm = this.getMassEmailForm();
		if(composeEmailForm.length > 0){
			jQuery("#multiFile").MultiFile({
				list: '#attachments',
				'afterFileSelect' : function(element, value, master_element){
					var masterElement = master_element;
					var newElement = jQuery(masterElement.current);
					newElement.addClass('removeNoFileChosen');
					thisInstance.fileAfterSelectHandler(element, value, master_element);
				},
				'afterFileRemove' : function(element, value, master_element){
					if (jQuery('#attachments').is(':empty')){
						jQuery('.MultiFile,.MultiFile-applied').removeClass('removeNoFileChosen');
					}
					thisInstance.removeAttachmentFileSizeByElement(jQuery(element));
				}
			});
			this.getMassEmailForm().validationEngine(app.validationEngineOptions);
			this.registerSendEmailEvent();
			var textAreaElement = jQuery('#description');
			var ckEditorInstance = this.getckEditorInstance(textAreaElement);
			ckEditorInstance.loadCkEditor(textAreaElement);
			this.registerAutoCompleteFields(this.getMassEmailForm());
			this.registerRemoveAttachmentEvent();
			this.registerEventsToGetFlagValue();
			this.registerCcAndBccEvents();
			this.registerSendEmailTemplateEvent();
			this.registerBrowseCrmEvent();
			this.registerEventsForToField();
			this.registerEventForRemoveCustomAttachments();
			this.calculateUploadFileSize();
			this.registerEventForGoToPreview();
		}
	}
});
//On Page Load
jQuery(document).ready(function() {
	var emailMassEditInstance = new Emails_MassEdit_Js();
	emailMassEditInstance.registerEvents();
});

