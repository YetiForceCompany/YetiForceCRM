/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
jQuery.Class("Vtiger_Detail_Js", {
	detailInstance: false,
	SaveResultInstance: false,
	getInstance: function () {
		if (Vtiger_Detail_Js.detailInstance == false) {
			var module = app.getModuleName();
			var view = app.getViewName();
			var moduleClassName = module + "_" + view + "_Js";
			var fallbackClassName = Vtiger_Detail_Js;
			if (typeof window[moduleClassName] != 'undefined') {
				var instance = new window[moduleClassName]();
			} else {
				var instance = new fallbackClassName();
			}
			Vtiger_Detail_Js.detailInstance = instance;
		}
		return Vtiger_Detail_Js.detailInstance;
	},
	// function to remove in the future
	/*
	 * function to trigger send Email
	 * @params: send email url , module name.
	 */
	triggerSendEmail: function (detailActionUrl, module) {
		Vtiger_Helper_Js.checkServerConfig(module).then(function (data) {
			if (data == true) {
				var currentInstance = Vtiger_Detail_Js.getInstance();
				var parentRecord = new Array();
				var params = {};
				parentRecord.push(currentInstance.getRecordId());
				params['module'] = app.getModuleName();
				params['view'] = "MassActionAjax";
				params['selected_ids'] = parentRecord;
				params['mode'] = "showComposeEmailForm";
				params['step'] = "step1";
				params['relatedLoad'] = true;
				Vtiger_Index_Js.showComposeEmailPopup(params);
			} else {
				alert(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
			}
		});
	},
	/*
	 * function to trigger Detail view actions
	 * @params: Action url , callback function.
	 */
	triggerDetailViewAction: function (detailActionUrl, callBackFunction) {
		var detailInstance = Vtiger_Detail_Js.getInstance();
		var selectedIds = new Array();
		selectedIds.push(detailInstance.getRecordId());
		var postData = {
			"selected_ids": JSON.stringify(selectedIds)
		};
		var actionParams = {
			"type": "POST",
			"url": detailActionUrl,
			"dataType": "html",
			"data": postData
		};

		AppConnector.request(actionParams).then(
				function (data) {
					if (data) {
						app.showModalWindow(data, {'text-align': 'left'});
						if (typeof callBackFunction == 'function') {
							callBackFunction(data);
						}
					}
				},
				function (error, err) {

				}
		);
	},
	/*
	 * function to trigger send Sms
	 * @params: send sms url , module name.
	 */
	triggerSendSms: function (detailActionUrl, module) {
		Vtiger_Helper_Js.checkServerConfig(module).then(function (data) {
			if (data == true) {
				Vtiger_Detail_Js.triggerDetailViewAction(detailActionUrl);
			} else {
				alert(app.vtranslate('JS_SMS_SERVER_CONFIGURATION'));
			}
		});
	},
	triggerTransferOwnership: function (massActionUrl) {
		var thisInstance = this;
		thisInstance.getRelatedModulesContainer = false;
		var actionParams = {
			"type": "POST",
			"url": massActionUrl,
			"dataType": "html",
			"data": {}
		};
		AppConnector.request(actionParams).then(
				function (data) {
					if (data) {
						var callback = function (data) {
							var params = app.validationEngineOptions;
							params.onValidationComplete = function (form, valid) {
								if (valid) {
									if (form.attr("name") == "changeOwner") {
										thisInstance.transferOwnershipSave(form)
									}
								}
								return false;
							}
							jQuery('#changeOwner').validationEngine(app.validationEngineOptions);
						}
						app.showModalWindow(data, function (data) {
							var selectElement = thisInstance.getRelatedModuleContainer();
							app.changeSelectElementView(selectElement, 'select2');
							if (typeof callback == 'function') {
								callback(data);
							}
						});
					}
				}
		);
	},
	transferOwnershipSave: function (form) {
		var thisInstance = this;
		var transferOwner = jQuery('#transferOwnerId').val();
		var relatedModules = jQuery('#related_modules').val();
		var recordId = jQuery('#recordId').val();
		var params = {
			'module': app.getModuleName(),
			'action': 'TransferOwnership',
			'record': recordId,
			'transferOwnerId': transferOwner,
			'related_modules': relatedModules
		}
		AppConnector.request(params).then(
				function (data) {
					if (data.success) {
						app.hideModalWindow();
						var params = {
							title: app.vtranslate('JS_MESSAGE'),
							text: app.vtranslate('JS_RECORDS_TRANSFERRED_SUCCESSFULLY'),
							animation: 'show',
							type: 'info'
						};
						var oldvalue = jQuery('.assigned_user_id').val();
						var element = jQuery(".assigned_user_id ");

						element.find('option[value="' + oldvalue + '"]').removeAttr("selected");
						element.find('option[value="' + transferOwner + '"]').attr('selected', 'selected');
						element.trigger("liszt:updated");
						var Fieldname = element.find('option[value="' + transferOwner + '"]').data("picklistvalue");
						element.closest(".row-fluid").find(".value").html('<a href="index.php?module=Users&amp;parent=Settings&amp;view=Detail&amp;record=' + transferOwner + '">' + Fieldname + '</a>');

						Vtiger_Helper_Js.showPnotify(params);
					}
				}
		);
	},
	/*
	 * Function to get the related module container 
	 */
	getRelatedModuleContainer: function () {
		if (this.getRelatedModulesContainer == false) {
			this.getRelatedModulesContainer = jQuery('#related_modules');
		}
		return this.getRelatedModulesContainer;
	},
	/*
	 * function to trigger delete record action
	 * @params: delete record url.
	 */
	deleteRecord: function (deleteRecordActionUrl) {
		var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
		Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(function (data) {
			AppConnector.request(deleteRecordActionUrl + '&ajaxDelete=true').then(
					function (data) {
						if (data.success == true) {
							window.location.href = data.result;
						} else {
							Vtiger_Helper_Js.showPnotify(data.error.message);
						}
					});
		},
				function (error, err) {
				}
		);
	},
	reloadRelatedList: function () {
		var pageNumber = jQuery('[name="currentPageNum"]').val();
		var detailInstance = Vtiger_Detail_Js.getInstance();
		detailInstance.loadRelatedList(pageNumber);
	},
	showWorkflowTriggerView: function (instance) {
		$(instance).popover('hide');
		var detailInstance = Vtiger_Detail_Js.getInstance();
		var params = {
			module: app.getModuleName(),
			view: 'WorkflowTrigger',
			record: detailInstance.getRecordId()
		}
		var callback = function (data) {
			data.find('[type="submit"]').click(function (e) {
				var ids = [];
				data.find('input[type="checkbox"]:checked').each(function (index) {
					ids.push($(this).val());
				});
				if (ids.length == 0) {
					var params = {
						title: app.vtranslate('JS_INFORMATION'),
						text: app.vtranslate('JS_NOT_SELECTED_WORKFLOW_TRIGGER'),
						type: 'error',
						animation: 'show'
					};
					Vtiger_Helper_Js.showPnotify(params);
				} else {
					var params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('JS_STARTED_PERFORM_WORKFLOW'),
						type: 'info',
						animation: 'show'
					};
					Vtiger_Helper_Js.showPnotify(params);
					var postData = {
						module: app.getModuleName(),
						action: 'Workflow',
						mode: 'execute',
						record: detailInstance.getRecordId(),
						ids: ids
					}
					AppConnector.request(postData).then(
							function (data) {
								var params = {
									title: app.vtranslate('JS_MESSAGE'),
									text: app.vtranslate('JS_COMPLETED_PERFORM_WORKFLOW'),
									type: 'success',
									animation: 'show'
								};
								Vtiger_Helper_Js.showPnotify(params);
								app.hideModalWindow();
								detailInstance.loadWidgets();
							},
							function (error, err) {
								var params = {
									title: app.vtranslate('JS_ERROR'),
									text: app.vtranslate('JS_ERROR_DURING_TRIGGER_OF_WORKFLOW'),
									type: 'error',
									animation: 'show'
								};
								Vtiger_Helper_Js.showPnotify(params);
								app.hideModalWindow();
							}
					);
				}
			});
		}
		AppConnector.request(params).then(
				function (data) {
					if (data) {
						app.showModalWindow(data, '', callback);
					}
				},
				function (error, err) {
				}
		);
	},
}, {
	targetPicklistChange: false,
	targetPicklist: false,
	detailViewContentHolder: false,
	detailViewForm: false,
	detailViewDetailsTabLabel: 'LBL_RECORD_DETAILS',
	detailViewSummaryTabLabel: 'LBL_RECORD_SUMMARY',
	detailViewRecentCommentsTabLabel: 'ModComments',
	detailViewRecentActivitiesTabLabel: 'Activities',
	detailViewRecentUpdatesTabLabel: 'LBL_UPDATES',
	detailViewRecentDocumentsTabLabel: 'Documents',
	fieldUpdatedEvent: 'Vtiger.Field.Updated',
	widgetPostLoad: 'Vtiger.Widget.PostLoad',
	//Filels list on updation of which we need to upate the detailview header
	updatedFields: ['company', 'designation', 'title'],
	//Event that will triggered before saving the ajax edit of fields
	fieldPreSave: 'Vtiger.Field.PreSave',
	tempData: [],
	referenceFieldNames: {
		'Calendar': {
			'Accounts': 'link',
			'Leads': 'link',
			'Contacts': 'link',
			'Vendors': 'link',
			'OSSEmployees': 'link',
			'Campaigns': 'process',
			'HelpDesk': 'process',
			'Potentials': 'process',
			'Projects': 'process',
			'ServiceContracts': 'process',
		},
		'OutsourcedProducts': {
			'Potentials': 'potential',
			'Leads': 'parent_id',
			'Accounts': 'parent_id',
			'Contacts': 'parent_id'
		},
		'Assets': {
			'Potentials': 'potential',
			'Accounts': 'parent_id',
			'Contacts': 'parent_id'
		},
		'OSSOutsourcedServices': {
			'Potentials': 'potential',
			'Leads': 'parent_id',
			'Accounts': 'parent_id',
			'Contacts': 'parent_id'
		},
		'OSSSoldServices': {
			'Potentials': 'potential',
			'Accounts': 'parent_id',
			'Contacts': 'parent_id'
		},
	},
	//constructor
	init: function () {

	},
	getDeleteMessageKey: function () {
		return 'JS_DELETE_CONFIRMATION';
	},
	loadWidgets: function () {
		var thisInstance = this;
		var widgetList = jQuery('[class^="widgetContainer_"]');
		widgetList.each(function (index, widgetContainerELement) {
			var widgetContainer = jQuery(widgetContainerELement);
			thisInstance.loadWidget(widgetContainer);
		});
	},
	loadWidget: function (widgetContainer) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var contentHeader = jQuery('.widget_header,.widgetHeader', widgetContainer);
		var contentContainer = jQuery('.widget_contents', widgetContainer);
		var urlParams = widgetContainer.data('url');
		var relatedModuleName = contentHeader.find('[name="relatedModule"]').val();

		var params = {
			'type': 'GET',
			'dataType': 'html',
			'data': urlParams
		};
		contentContainer.progressIndicator({});
		AppConnector.request(params).then(
				function (data) {
					contentContainer.progressIndicator({'mode': 'hide'});
					contentContainer.html(data);
					app.registerEventForTextAreaFields(jQuery(".commentcontent"))
					contentContainer.trigger(thisInstance.widgetPostLoad, {'widgetName': relatedModuleName})
					app.showPopoverElementView(contentContainer.find('.popoverTooltip'));
					aDeferred.resolve(params);
				},
				function (e) {
					contentContainer.progressIndicator({'mode': 'hide'});
					aDeferred.reject();
				}
		);
		return aDeferred.promise();
	},
	/**
	 * Function to load only Comments Widget.
	 */
	//TODO improve this API.
	loadCommentsWidget: function () {

	},
	loadContents: function (url, data) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();

		var detailContentsHolder = this.getContentHolder();
		var params = url;
		if (typeof data != 'undefined') {
			params = {};
			params.url = url;
			params.data = data;
		}
		AppConnector.requestPjax(params).then(
				function (responseData) {
					detailContentsHolder.html(responseData);
					responseData = detailContentsHolder.html();
					//thisInstance.triggerDisplayTypeEvent();
					thisInstance.registerBlockStatusCheckOnLoad();
					//Make select box more usability
					app.changeSelectElementView(detailContentsHolder);
					//Attach date picker event to date fields
					app.registerEventForDatePickerFields(detailContentsHolder);
					app.registerEventForTextAreaFields(jQuery(".commentcontent"));
					jQuery('.commentcontent').autosize();
					thisInstance.getForm().validationEngine();
					detailContentsHolder.trigger(jQuery.Event('Detail.LoadContents.PostLoad'), responseData);
					aDeferred.resolve(responseData);
				},
				function () {

				}
		);

		return aDeferred.promise();
	},
	getUpdatefFieldsArray: function () {
		return this.updatedFields;
	},
	/**
	 * Function to return related tab.
	 * @return : jQuery Object.
	 */
	getTabByLabel: function (tabLabel) {
		var tabs = this.getTabs();
		var targetTab = false;
		tabs.each(function (index, element) {
			var tab = jQuery(element);
			var labelKey = tab.data('labelKey');
			if (labelKey == tabLabel) {
				targetTab = tab;
				return false;
			}
		});
		return targetTab;
	},
	selectModuleTab: function () {
		var relatedTabContainer = this.getTabContainer();
		var moduleTab = relatedTabContainer.find('li.module-tab');
		this.deSelectAllrelatedTabs();
		this.markTabAsSelected(moduleTab);
	},
	deSelectAllrelatedTabs: function () {
		var relatedTabContainer = this.getTabContainer();
		this.getTabs().removeClass('active');
	},
	markTabAsSelected: function (tabElement) {
		tabElement.addClass('active');
	},
	getSelectedTab: function () {
		var tabContainer = this.getTabContainer();
		return tabContainer.find('li.active');
	},
	getTabContainer: function () {
		return jQuery('div.related');
	},
	getTabs: function () {
		return this.getTabContainer().find('li');
	},
	getContentHolder: function () {
		if (this.detailViewContentHolder == false) {
			this.detailViewContentHolder = jQuery('div.details div.contents');
		}
		return this.detailViewContentHolder;
	},
	/**
	 * Function which will give the detail view form
	 * @return : jQuery element
	 */
	getForm: function () {
		if (this.detailViewForm == false) {
			this.detailViewForm = jQuery('#detailView');
		}
		return this.detailViewForm;
	},
	getRecordId: function () {
		return jQuery('#recordId').val();
	},
	getRelatedModuleName: function () {
		if (jQuery('.relatedModuleName', this.getContentHolder()).length == 1) {
			return jQuery('.relatedModuleName', this.getContentHolder()).val();
		}
	},
	saveFieldValues: function (fieldDetailList) {
		var aDeferred = jQuery.Deferred();

		var recordId = this.getRecordId();

		var data = {};
		if (typeof fieldDetailList != 'undefined') {
			data = fieldDetailList;
		}
		data['record'] = recordId;
		data['module'] = app.getModuleName();
		data['action'] = 'SaveAjax';

		var params = {};
		params.data = data;
		params.async = false;
		params.dataType = 'json';
		AppConnector.request(params).then(
				function (reponseData) {
					aDeferred.resolve(reponseData);
				}
		);

		return aDeferred.promise();
	},
	getRelatedListCurrentPageNum: function () {
		return jQuery('input[name="currentPageNum"]', this.getContentHolder()).val();
	},
	/**
	 * function to remove comment block if its exists.
	 */
	removeCommentBlockIfExists: function () {
		var detailContentsHolder = this.getContentHolder();
		var Commentswidget = jQuery('.commentsBody', detailContentsHolder);
		jQuery('.addCommentBlock', Commentswidget).remove();
	},
	/**
	 * function to get the Comment thread for the given parent.
	 * params: Url to get the Comment thread
	 */
	getCommentThread: function (url) {
		var aDeferred = jQuery.Deferred();
		AppConnector.request(url).then(
				function (data) {
					aDeferred.resolve(data);
				},
				function (error, err) {

				}
		)
		return aDeferred.promise();
	},
	/**
	 * function to save comment
	 * return json response
	 */
	saveComment: function (e) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var currentTarget = jQuery(e.currentTarget);
		var commentMode = currentTarget.data('mode');
		var closestCommentBlock = currentTarget.closest('.addCommentBlock');
		var commentContent = closestCommentBlock.find('.commentcontent');
		var commentContentValue = commentContent.val();
		var errorMsg;
		if (commentContentValue == "") {
			errorMsg = app.vtranslate('JS_LBL_COMMENT_VALUE_CANT_BE_EMPTY')
			commentContent.validationEngine('showPrompt', errorMsg, 'error', 'bottomLeft', true);
			aDeferred.reject();
			return aDeferred.promise();
		}
		if (commentMode == "edit") {
			var editCommentReason = closestCommentBlock.find('[name="reasonToEdit"]').val();
		}

		var progressIndicatorElement = jQuery.progressIndicator({});
		var element = jQuery(e.currentTarget);
		element.attr('disabled', 'disabled');

		var commentInfoHeader = closestCommentBlock.closest('.commentDetails').find('.commentInfoHeader');
		var commentId = commentInfoHeader.data('commentid');
		var parentCommentId = commentInfoHeader.data('parentcommentid');
		var postData = {
			'commentcontent': commentContentValue,
			'related_to': thisInstance.getRecordId(),
			'module': 'ModComments'
		}

		if (commentMode == "edit") {
			postData['record'] = commentId;
			postData['reasontoedit'] = editCommentReason;
			postData['parent_comments'] = parentCommentId;
			postData['mode'] = 'edit';
			postData['action'] = 'Save';
		} else if (commentMode == "add") {
			postData['parent_comments'] = commentId;
			postData['action'] = 'SaveAjax';
		}
		AppConnector.request(postData).then(
				function (data) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					aDeferred.resolve(data);
				},
				function (textStatus, errorThrown) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					element.removeAttr('disabled');
					aDeferred.reject(textStatus, errorThrown);
				}
		);

		return aDeferred.promise();
	},
	/**
	 * function to return the UI of the comment.
	 * return html
	 */
	getCommentUI: function (commentId) {
		var aDeferred = jQuery.Deferred();
		var postData = {
			'view': 'DetailAjax',
			'module': 'ModComments',
			'record': commentId
		}
		AppConnector.request(postData).then(
				function (data) {
					aDeferred.resolve(data);
				},
				function (error, err) {

				}
		);
		return aDeferred.promise();
	},
	/**
	 * function to return cloned add comment block
	 * return jQuery Obj.
	 */
	getCommentBlock: function () {
		var detailContentsHolder = this.getContentHolder();
		var clonedCommentBlock = jQuery('.basicAddCommentBlock', detailContentsHolder).clone(true, true).removeClass('basicAddCommentBlock hide').addClass('addCommentBlock');
		clonedCommentBlock.find('.commentcontenthidden').removeClass('commentcontenthidden').addClass('commentcontent');
		return clonedCommentBlock;
	},
	/**
	 * function to return cloned edit comment block
	 * return jQuery Obj.
	 */
	getEditCommentBlock: function () {
		var detailContentsHolder = this.getContentHolder();
		var clonedCommentBlock = jQuery('.basicEditCommentBlock', detailContentsHolder).clone(true, true).removeClass('basicEditCommentBlock hide').addClass('addCommentBlock');
		clonedCommentBlock.find('.commentcontenthidden').removeClass('commentcontenthidden').addClass('commentcontent');
		return clonedCommentBlock;
	},
	/*
	 * Function to register the submit event for Send Sms
	 */
	registerSendSmsSubmitEvent: function () {
		var thisInstance = this;
		jQuery('body').on('submit', '#massSave', function (e) {
			var form = jQuery(e.currentTarget);
			var smsTextLength = form.find('#message').val().length;
			if (smsTextLength > 160) {
				var params = {
					title: app.vtranslate('JS_MESSAGE'),
					text: app.vtranslate('LBL_SMS_MAX_CHARACTERS_ALLOWED'),
					animation: 'show',
					type: 'error'
				};
				Vtiger_Helper_Js.showPnotify(params);
				return false;
			}
			var submitButton = form.find(':submit');
			submitButton.attr('disabled', 'disabled');
			thisInstance.SendSmsSave(form);
			e.preventDefault();
		});
	},
	/*
	 * Function to Save and sending the Sms and hide the modal window of send sms
	 */
	SendSmsSave: function (form) {
		var progressInstance = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		var SendSmsUrl = form.serializeFormData();
		AppConnector.request(SendSmsUrl).then(
				function (data) {
					app.hideModalWindow();
					progressInstance.progressIndicator({
						'mode': 'hide'
					});
				},
				function (error, err) {

				}
		);
	},
	/**
	 * Function which will register events to update the record name in the detail view when any of
	 * the name field is changed
	 */
	registerNameAjaxEditEvent: function () {
		var thisInstance = this;
		var detailContentsHolder = thisInstance.getContentHolder();
		detailContentsHolder.on(thisInstance.fieldUpdatedEvent, '.nameField', function (e, params) {
			var form = thisInstance.getForm();
			var nameFields = form.data('nameFields');
			var recordLabel = '';
			for (var index in nameFields) {
				if (index != 0) {
					recordLabel += ' '
				}

				var nameFieldName = nameFields[index];
				recordLabel += form.find('[name="' + nameFieldName + '"]').val();
			}
			var recordLabelElement = detailContentsHolder.closest('.contentsDiv').find('.recordLabel');
			recordLabelElement.text(recordLabel);
		});
	},
	updateHeaderNameFields: function () {
		var thisInstance = this;
		var detailContentsHolder = thisInstance.getContentHolder();
		var form = thisInstance.getForm();
		var nameFields = form.data('nameFields');
		var recordLabelElement = detailContentsHolder.closest('.contentsDiv').find('.recordLabel');
		var title = '';
		for (var index in nameFields) {
			var nameFieldName = nameFields[index];
			var nameField = form.find('[name="' + nameFieldName + '"]');
			if (nameField.length > 0) {
				var recordLabel = nameField.val();
				title += recordLabel + " ";
				recordLabelElement.find('[class="' + nameFieldName + '"]').text(recordLabel);
			}
		}
		var salutatioField = recordLabelElement.find('.salutation');
		if (salutatioField.length > 0) {
			var salutatioValue = salutatioField.text();
			title = salutatioValue + title;
		}
		recordLabelElement.attr('title', title);
	},
	registerAjaxEditEvent: function () {
		var thisInstance = this;
		var detailContentsHolder = thisInstance.getContentHolder();
		detailContentsHolder.on(thisInstance.fieldUpdatedEvent, 'input,select,textarea', function (e) {
			thisInstance.updateHeaderValues(jQuery(e.currentTarget));
		});
	},
	updateHeaderValues: function (currentElement) {
		var thisInstance = this;
		if (currentElement.hasClass('nameField')) {
			thisInstance.updateHeaderNameFields();
			return true;
		}

		var name = currentElement.attr('name');
		var updatedFields = this.getUpdatefFieldsArray();
		var detailContentsHolder = thisInstance.getContentHolder();
		if (jQuery.inArray(name, updatedFields) != '-1') {
			var recordLabel = currentElement.val();
			var recordLabelElement = detailContentsHolder.closest('.contentsDiv').find('.' + name + '_label');
			recordLabelElement.text(recordLabel);
		}
	},
	/*
	 * Function to register the click event of email field
	 */
	registerEmailFieldClickEvent: function () {
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click', '.emailField', function (e) {
			e.stopPropagation();
		})
	},
	/*
	 * Function to register the click event of phone field
	 */
	registerPhoneFieldClickEvent: function () {
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click', '.phoneField', function (e) {
			e.stopPropagation();
		})
	},
	/*
	 * Function to register the click event of url field
	 */
	registerUrlFieldClickEvent: function () {
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click', '.urlField', function (e) {
			e.stopPropagation();
		})
	},
	/**
	 * Function to register event for related list row click
	 */
	registerRelatedRowClickEvent: function () {
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click', '.listViewEntries', function (e) {
			var targetElement = jQuery(e.target, jQuery(e.currentTarget));
			if (targetElement.is('td:first-child') && (targetElement.children('input[type="checkbox"]').length > 0))
				return;
			if (jQuery(e.target).is('input[type="checkbox"]'))
				return;
			var elem = jQuery(e.currentTarget);
			var recordUrl = elem.data('recordurl');
			if (typeof recordUrl != "undefined") {
				window.location.href = recordUrl;
			}
		});

	},
	loadRelatedList: function (pageNumber) {
		var relatedListInstance = new Vtiger_RelatedList_Js(this.getRecordId(), app.getModuleName(), this.getSelectedTab(), this.getRelatedModuleName());
		var params = {'page': pageNumber};
		relatedListInstance.loadRelatedList(params);
	},
	registerEventForRelatedListPagination: function () {
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click', '#relatedListNextPageButton', function (e) {
			var element = jQuery(e.currentTarget);
			if (element.attr('disabled') == "disabled") {
				return;
			}
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.nextPageHandler();
		});
		detailContentsHolder.on('click', '#relatedListPreviousPageButton', function () {
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.previousPageHandler();
		});
		detailContentsHolder.on('click', '#relatedListPageJump', function (e) {
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.getRelatedPageCount();
		});
		detailContentsHolder.on('click', '#relatedListPageJumpDropDown > li', function (e) {
			e.stopImmediatePropagation();
		}).on('keypress', '#pageToJump', function (e) {
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.pageJumpHandler(e);
		});
		detailContentsHolder.on('click', '.pageNumber', function () {
			var disabled = $(this).hasClass("disabled")
			if (disabled)
				return false;
			var pageNumber = $(this).data("id");
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.selectPageHandler(pageNumber);
		});
	},
	/**
	 * Function to register Event for Sorting
	 */
	registerEventForRelatedList: function () {

		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		thisInstance.registerEventForAddingRelatedRecord(detailContentsHolder);
		detailContentsHolder.on('click', '.relatedListHeaderValues', function (e) {
			var element = jQuery(e.currentTarget);
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.sortHandler(element);
		});

		detailContentsHolder.on('click', 'button.selectRelation', function (e) {
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			if (relatedModuleName == undefined) {
				relatedModuleName = jQuery(e.currentTarget).data('modulename');
			}
			var restrictionsField = jQuery(e.currentTarget).data('rf');
			var params = {};

			if (restrictionsField && Object.keys(restrictionsField).length > 0) {
				params = {search_key: restrictionsField.key, search_value: restrictionsField.name};
			}
			var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);

			relatedController.showSelectRelationPopup(params).then(function (data) {
				//thisInstance.loadWidgets();
				var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
				if (emailEnabledModule) {
					thisInstance.registerEventToEditRelatedStatus();
				}
			});
		});

		detailContentsHolder.on('click', 'a.relationDelete', function (e) {
			e.stopImmediatePropagation();
			var element = jQuery(e.currentTarget);
			var instance = Vtiger_Detail_Js.getInstance();
			var key = instance.getDeleteMessageKey();
			var message = app.vtranslate(key);
			Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
					function (e) {
						var row = element.closest('tr');
						var relatedRecordid = row.data('id');
						var widget_contents = element.closest('.widget_contents');
						var selectedTabElement = thisInstance.getSelectedTab();
						var relatedModuleName = thisInstance.getRelatedModuleName();
						if (relatedModuleName == undefined) {
							relatedModuleName = widget_contents.find('.relatedModuleName').val();
						}
						var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
						relatedController.deleteRelation([relatedRecordid]).then(function (response) {
							relatedController.loadRelatedList();
						});
					},
					function (error, err) {
					}
			);
		});
	},
	registerBlockAnimationEvent: function () {
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.find('.blockHeader').click(function() {
			var currentTarget = $(this).find('.blockToggle').not('.hide');
			var blockId = currentTarget.data('id');
			var closestBlock = currentTarget.closest('.detailview-table');
			var bodyContents = closestBlock.find('tbody');
			var data = currentTarget.data();
			var module = app.getModuleName();
			var hideHandler = function () {
				bodyContents.addClass('hide');
				app.cacheSet(module + '.' + blockId, 0)
			}
			var showHandler = function () {
				bodyContents.removeClass('hide');
				app.cacheSet(module + '.' + blockId, 1)
			}
			var data = currentTarget.data();
			if (data.mode == 'show') {
				hideHandler();
				currentTarget.addClass('hide');
				closestBlock.find('[data-mode="hide"]').removeClass('hide');
			} else {
				showHandler();
				currentTarget.addClass('hide');
				closestBlock.find("[data-mode='show']").removeClass('hide');
			}
		});
	},
	registerBlockStatusCheckOnLoad: function () {
		var blocks = this.getContentHolder().find('.detailview-table');
		var module = app.getModuleName();
		blocks.each(function (index, block) {
			var currentBlock = jQuery(block);
			var headerAnimationElement = currentBlock.find('.blockToggle').not('.hide');
			var bodyContents = currentBlock.find('tbody')
			var blockId = headerAnimationElement.data('id');
			var cacheKey = module + '.' + blockId;
			var value = app.cacheGet(cacheKey, null);
			if (value != null) {
				if (value == 1) {
					headerAnimationElement.addClass('hide');
					currentBlock.find("[data-mode='show']").removeClass('hide');
					bodyContents.removeClass('hide');
				} else {
					headerAnimationElement.addClass('hide');
					currentBlock.find("[data-mode='hide']").removeClass('hide');
					bodyContents.addClass('hide');
				}
			}
		});
	},
	/**
	 * Function to register event for adding related record for module
	 */
	registerEventForAddingRelatedRecord: function () {
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click', '[name="addButton"]', function (e) {
			var element = jQuery(e.currentTarget);
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			if (element.hasClass('quickCreateSupported') != true) {
				window.location.href = element.data('url');
				return;
			}

			var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.addRelatedRecord(element);
		})
	},
	/**
	 * Function to handle the ajax edit for detailview and summary view fields
	 * which will expects the currentTdElement
	 */
	ajaxEditHandling: function (currentTdElement) {
		var thisInstance = this;
		var readRecord = jQuery('.setReadRecord');
		readRecord.prop('disabled', true);
		var detailViewValue = jQuery('.value', currentTdElement);
		var editElement = jQuery('.edit', currentTdElement);
		var actionElement = jQuery('.summaryViewEdit', currentTdElement);
		var fieldElement = jQuery('.fieldname', editElement);
		jQuery(fieldElement).each(function (index, element) {
			var fieldName = jQuery(element).val();
			var elementTarget = jQuery(element);
			var fieldElement = jQuery('[name="' + fieldName + '"]', editElement);
			if (fieldElement.attr('disabled') == 'disabled') {
				return;
			}

			if (editElement.length <= 0) {
				return;
			}

			if (editElement.is(':visible')) {
				return;
			}

			fieldElement.inputmask();
			var hasMaskedValue = false;
			if (fieldElement.inputmask("hasMaskedValue")) {
				hasMaskedValue = true;
			}
			detailViewValue.addClass('hide');
			actionElement.addClass('hide');
			editElement.removeClass('hide').children().filter('input[type!="hidden"]input[type!="image"],select').filter(':first').focus();

			var saveTriggred = false;
			var preventDefault = false;
			var saveHandler = function (e) {
				var element = jQuery(e.target);
				if ((element.closest('td').is(currentTdElement))) {
					return;
				}
				fieldElement.inputmask('remove');
				currentTdElement.removeAttr('tabindex');
				var previousValue = elementTarget.data('prevValue');
				var formElement = thisInstance.getForm();
				var formData = formElement.serializeFormData();
				var ajaxEditNewValue = formData[fieldName];
				//value that need to send to the server
				var fieldValue = ajaxEditNewValue;
				var fieldInfo = Vtiger_Field_Js.getInstance(fieldElement.data('fieldinfo'));
				var dateTimeField = [];
				var dateTime = false;
				if (editElement.find('[data-fieldinfo]').length == 2) {
					editElement.find('[data-fieldinfo]').each(function () {
						var field = [];
						field['name'] = jQuery(this).attr('name');
						field['type'] = jQuery(this).data('fieldinfo').type;
						if (field['type'] == 'datetime') {
							dateTime = true;
						}
						dateTimeField.push(field);
					})
				}
				// Since checkbox will be sending only on and off and not 1 or 0 as currrent value
				if (fieldElement.is('input:checkbox')) {
					if (fieldElement.is(':checked')) {
						ajaxEditNewValue = '1';
					} else {
						ajaxEditNewValue = '0';
					}
					fieldElement = fieldElement.filter('[type="checkbox"]');
				}
				var errorExists = fieldElement.validationEngine('validate');
				//If validation fails
				if (errorExists) {
					if (hasMaskedValue) {
						fieldElement.inputmask();
					}
					return;
				}
				fieldElement.validationEngine('hide');
				//Before saving ajax edit values we need to check if the value is changed then only we have to save
				if (previousValue == ajaxEditNewValue) {
					editElement.addClass('hide');
					detailViewValue.removeClass('hide');
					actionElement.removeClass('hide');
					readRecord.prop('disabled', false);
					jQuery(document).off('click', '*', saveHandler);
				} else {
					var preFieldSaveEvent = jQuery.Event(thisInstance.fieldPreSave);
					fieldElement.trigger(preFieldSaveEvent, {'fieldValue': fieldValue, 'recordId': thisInstance.getRecordId()});
					if (preFieldSaveEvent.isDefaultPrevented()) {
						//Stop the save
						saveTriggred = false;
						preventDefault = true;
						readRecord.prop('disabled', false);
						return
					}
					preventDefault = false;
					jQuery(document).off('click', '*', saveHandler);
					if (!saveTriggred && !preventDefault) {
						saveTriggred = true;
						if (Vtiger_Detail_Js.SaveResultInstance == false) {
							Vtiger_Detail_Js.SaveResultInstance = new SaveResult();
						}
						formData.record = thisInstance.getRecordId();
						formData.module = app.getModuleName();
						formData.view = 'quick_edit';
						if (Vtiger_Detail_Js.SaveResultInstance.checkData(formData) == false) {
							editElement.addClass('hide');
							detailViewValue.removeClass('hide');
							actionElement.removeClass('hide');
							jQuery(document).off('click', '*', saveHandler);
							readRecord.prop('disabled', false);
							fieldElement.val(previousValue);
							return;
						}
					} else {
						return;
					}

					currentTdElement.progressIndicator();
					editElement.addClass('hide');
					var fieldNameValueMap = {};
					if (fieldInfo.getType() == 'multipicklist' || fieldInfo.getType() == 'sharedOwner') {
						var multiPicklistFieldName = fieldName.split('[]');
						fieldName = multiPicklistFieldName[0];
					}

					fieldNameValueMap["value"] = fieldValue;
					fieldNameValueMap["field"] = fieldName;
					fieldNameValueMap = thisInstance.getCustomFieldNameValueMap(fieldNameValueMap);
					thisInstance.saveFieldValues(fieldNameValueMap).then(function (response) {
						readRecord.prop('disabled', false);
						var postSaveRecordDetails = response.result;
						currentTdElement.progressIndicator({'mode': 'hide'});
						detailViewValue.removeClass('hide');
						actionElement.removeClass('hide');
						var displayValue = postSaveRecordDetails[fieldName].display_value;
						if (dateTimeField.length && dateTime) {
							displayValue = postSaveRecordDetails[dateTimeField[0].name].display_value + ' ' + postSaveRecordDetails[dateTimeField[1].name].display_value;
						}
						detailViewValue.html(displayValue);
						fieldElement.trigger(thisInstance.fieldUpdatedEvent, {'old': previousValue, 'new': fieldValue});
						elementTarget.data('prevValue', ajaxEditNewValue);
						fieldElement.data('selectedValue', ajaxEditNewValue);
						//After saving source field value, If Target field value need to change by user, show the edit view of target field. 
						if (thisInstance.targetPicklistChange) {
							if (jQuery('.summaryView', thisInstance.getForm()).length > 0) {
								thisInstance.targetPicklist.find('.summaryViewEdit').trigger('click');
							} else {
								thisInstance.targetPicklist.trigger('click');
							}
							thisInstance.targetPicklistChange = false;
							thisInstance.targetPicklist = false;
						}
						var selectedTabElement = thisInstance.getSelectedTab();
						if (selectedTabElement.data('linkKey') == thisInstance.detailViewSummaryTabLabel) {
							var detailContentsHolder = thisInstance.getContentHolder();
							jQuery('.detailViewInfo .related li.active').trigger("click");
							thisInstance.registerSummaryViewContainerEvents(detailContentsHolder);
							thisInstance.registerEventForPicklistDependencySetup(thisInstance.getForm());
							thisInstance.registerEventForRelatedList();
						} else if (selectedTabElement.data('linkKey') == thisInstance.detailViewDetailsTabLabel) {
							thisInstance.registerEventForPicklistDependencySetup(thisInstance.getForm());
						}
					},
							function (error) {
								//TODO : Handle error
								readRecord.prop('disabled', false);
								currentTdElement.progressIndicator({'mode': 'hide'});
							}
					)
				}
			}

			jQuery(document).on('click', '*', saveHandler);
		})
	},
	triggerDisplayTypeEvent: function () {
		var widthType = app.cacheGet('widthType', 'narrowWidthType');
		if (widthType) {
			var elements = jQuery('#detailView').find('td');
			elements.addClass(widthType);
		}
	},
	/**
	 * Function updates the hidden elements which is used for creating relations
	 */
	addElementsToQuickCreateForCreatingRelation: function (container, customParams) {
		jQuery('<input type="hidden" name="relationOperation" value="true" >').appendTo(container);
		jQuery.each(customParams, function (index, value) {
			jQuery('<input type="hidden" name="' + index + '" value="' + value + '" >').appendTo(container);
		});
	},
	/**
	 * Function to register event for activity widget for adding
	 * event and task from the widget
	 */
	registerEventForActivityWidget: function () {
		var thisInstance = this;

		/*
		 * Register click event for add button in Related Activities widget
		 */
		jQuery('.createActivity').on('click', function (e) {
			var referenceModuleName = "Calendar";
			var recordId = thisInstance.getRecordId();
			var module = app.getModuleName();
			var element = jQuery(e.currentTarget);

			var customParams = {};
			customParams['sourceModule'] = module;
			customParams['sourceRecord'] = recordId;
			if (module != '' && referenceModuleName != '' && typeof thisInstance.referenceFieldNames[referenceModuleName] != 'undefined' && typeof thisInstance.referenceFieldNames[referenceModuleName][module] != 'undefined') {
				var relField = thisInstance.referenceFieldNames[referenceModuleName][module];
				customParams[relField] = recordId;
			}
			var fullFormUrl = element.data('url');
			var preQuickCreateSave = function (data) {
				thisInstance.addElementsToQuickCreateForCreatingRelation(data, customParams);

				var taskGoToFullFormButton = data.find('[class^="CalendarQuikcCreateContents"]').find('#goToFullForm');
				var eventsGoToFullFormButton = data.find('[class^="EventsQuikcCreateContents"]').find('#goToFullForm');
				var taskFullFormUrl = taskGoToFullFormButton.data('edit-view-url') + "&" + fullFormUrl;
				var eventsFullFormUrl = eventsGoToFullFormButton.data('edit-view-url') + "&" + fullFormUrl;
				taskGoToFullFormButton.data('editViewUrl', taskFullFormUrl);
				eventsGoToFullFormButton.data('editViewUrl', eventsFullFormUrl);

			}

			var callbackFunction = function () {
				var widgetContainer = element.closest('.summaryWidgetContainer');
				var widgetContentBlock = widgetContainer.find('.widgetContentBlock');
				var urlParams = widgetContentBlock.data('url');
				var params = {
					'type': 'GET',
					'dataType': 'html',
					'data': urlParams
				};
				AppConnector.request(params).then(
						function (data) {
							var activitiesWidget = widgetContainer.find('.widget_contents');
							activitiesWidget.html(data);
							app.changeSelectElementView(activitiesWidget);
							thisInstance.registerEventForActivityWidget();
						}
				);
				thisInstance.loadWidgets();
			}

			var QuickCreateParams = {};
			QuickCreateParams['callbackPostShown'] = preQuickCreateSave;
			QuickCreateParams['callbackFunction'] = callbackFunction;
			QuickCreateParams['data'] = customParams;
			QuickCreateParams['noCache'] = false;
			Vtiger_Header_Js.getInstance().quickCreateModule(referenceModuleName, QuickCreateParams);
		});
	},
	getEndDate: function (startDate) {
		var dateTab = startDate.split('-');
		var date = new Date(dateTab[0], dateTab[1], dateTab[2]);
		var newDate = new Date();

		newDate.setDate(date.getDate() + 2);
		return app.getStringDate(newDate);
	},
	getSingleEventType: function (modDay, id, type) {
		var dateStartEl = jQuery('[name="date_start"]');
		var dateStartVal = jQuery(dateStartEl).val();
		var dateStartFormat = jQuery(dateStartEl).data('date-format');
		var validDateFromat = Vtiger_Helper_Js.convertToDateString(dateStartVal, dateStartFormat, modDay, type);
		var map = jQuery.extend({}, ['#b6a996,black'])
		var thisInstance = this;

		var params = {
			module: 'Calendar',
			action: 'Feed',
			start: validDateFromat,
			end: this.getEndDate(validDateFromat),
			type: type,
			mapping: map
		}

		AppConnector.request(params).then(function (events) {
			var testDate = Vtiger_Helper_Js.convertToDateString(dateStartVal, dateStartFormat, modDay);
			if (!jQuery.isEmptyObject(events)) {
				if (events[0]['activitytype'] === 'Task') {
					for (var ev in events) {
						if (events[ev]['start'].indexOf(testDate) > -1) {
							jQuery('#' + id + ' .table').append('<tr><td><a target="_blank" href="' + events[ev]['url'] + '">' + events[ev]['title'] + '</a></td></tr>')
						}
					}

				} else {
					for (var i = 0; i < events[0].length; i++) {
						if (events[0][i]['start'].indexOf(testDate) > -1) {
							jQuery('#' + id + ' .table').append('<tr><td><a target="_blank" href="' + events[0][i]['url'] + '">' + events[0][i]['title'] + '</a></td></tr>')
						}
					}
				}
			}
		})
	},
	/**
	 * Function to add module related record from summary widget
	 */
	registerFilterForAddingModuleRelatedRecordFromSummaryWidget: function () {
		var thisInstance = this;
		jQuery('.createRecordFromFilter').on('click', function (e) {
			var currentElement = jQuery(e.currentTarget);
			var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
			var widgetDataContainer = summaryWidgetContainer.find('.widget_contents');
			var referenceModuleName = widgetDataContainer.find('[name="relatedModule"]').val();
			var quickcreateUrl = currentElement.data('url');
			var parentId = thisInstance.getRecordId();
			var quickCreateParams = {};
			var relatedField = currentElement.data('prf');
			var autoCompleteFields = currentElement.data('acf');
			var moduleName = currentElement.closest('.widget_header').find('[name="relatedModule"]').val();
			var relatedParams = {};
			var postQuickCreateSave = function (data) {
				thisInstance.postSummaryWidgetAddRecord(data, currentElement);
				if (referenceModuleName == "ProjectTask") {
					thisInstance.loadModuleSummary();
				}
			}
			if (typeof relatedField != "undefined") {
				relatedParams[relatedField] = parentId;
			}
			if (typeof autoCompleteFields != "undefined") {
				$.each(autoCompleteFields, function (index, value) {
					relatedParams[index] = value;
				});
			}
			if (Object.keys(relatedParams).length > 0) {
				quickCreateParams['data'] = relatedParams;
			}

			quickCreateParams['noCache'] = true;
			quickCreateParams['callbackFunction'] = postQuickCreateSave;
			var progress = jQuery.progressIndicator();
			var headerInstance = new Vtiger_Header_Js();
			headerInstance.getQuickCreateForm(quickcreateUrl, moduleName, quickCreateParams).then(function (data) {
				headerInstance.handleQuickCreateData(data, quickCreateParams);
				progress.progressIndicator({'mode': 'hide'});
			});
		})
	},
	/**
	 * Function to get records according to ticket status
	 */
	registerChangeFilterForWidget: function () {
		var thisInstance = this;
		jQuery('.widget_header .filterField').on('change', function (e) {
			var picklistName = this.name;
			var statusCondition = {};
			var params = {};
			var currentElement = jQuery(e.currentTarget);
			var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
			var widgetDataContainer = summaryWidgetContainer.find('.widget_contents');
			widgetDataContainer.progressIndicator();
			var referenceModuleName = widgetDataContainer.find('[name="relatedModule"]').val();
			var recordId = thisInstance.getRecordId();
			var module = app.getModuleName();
			var selectedFilter = currentElement.find('option:selected').val();
			var fieldlable = currentElement.data('fieldlable');
			var filter_data = summaryWidgetContainer.find('[name="filter_data"]').val()
			if (selectedFilter != fieldlable) {
				statusCondition[filter_data] = selectedFilter;
				params['whereCondition'] = statusCondition;
			}
			params['record'] = recordId;
			params['view'] = 'Detail';
			params['module'] = module;
			params['page'] = widgetDataContainer.find('[name="page"]').val();
			params['limit'] = widgetDataContainer.find('[name="pageLimit"]').val();
			params['col'] = widgetDataContainer.find('[name="col"]').val();
			params['relatedModule'] = referenceModuleName;
			params['mode'] = 'showRelatedRecords';
			AppConnector.request(params).then(
					function (data) {
						widgetDataContainer.html(data);
						currentDiv.progressIndicator({'mode': 'hide'});
					}
			);
		})
	},
	registerChangeSwitchForWidget: function () {
		var thisInstance = this;
		$('.summaryWidgetContainer .widget_header .switchBtnReload').on('switchChange.bootstrapSwitch', function (e, state) {
			var currentElement = jQuery(e.currentTarget);
			var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
			var widget = summaryWidgetContainer.find('.widgetContentBlock');
			var url = widget.data('url');
			var urlparams = currentElement.data('urlparams');
			if (urlparams != '') {
				var onval = currentElement.data('on-val');
				var offval = currentElement.data('off-val');

				url = url.replace('&' + urlparams + '=' + onval, '');
				url = url.replace('&' + urlparams + '=' + offval, '');

				if (state)
					var newUrl = onval;
				else
					var newUrl = offval;
				if (newUrl != '') {
					url += '&' + urlparams + '=' + newUrl;
				}
				widget.data('url', url);
				thisInstance.loadWidget($(widget));
			}
		});
		$('.activityWidgetContainer .switchBtn').on('switchChange.bootstrapSwitch', function (e, state) {
			var currentElement = jQuery(e.currentTarget);
			var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
			var widget = summaryWidgetContainer.find('.widgetContentBlock');
			var url = widget.data('url');
			url = url.replace('&type=current', '');
			url += '&type=';
			if (state)
				url += 'current';
			else
				url += 'history';
			widget.data('url', url);
			thisInstance.loadWidget($(widget));
		});
		$('.calculationsWidgetContainer .calculationsSwitch').on('switchChange.bootstrapSwitch', function (e, state) {
			var currentElement = jQuery(e.currentTarget);
			var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
			var widget = summaryWidgetContainer.find('.widgetContentBlock');
			var url = widget.data('url');
			url = url.replace('&showtype=open', '');
			url = url.replace('&showtype=archive', '');
			url += '&showtype=';
			if (state)
				url += 'open';
			else
				url += 'archive';
			widget.data('url', url);
			thisInstance.loadWidget($(widget));
		});
		$('.potentialsWidgetContainer .potentialsSwitch').on('switchChange.bootstrapSwitch', function (e, state) {
			var currentElement = jQuery(e.currentTarget);
			var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
			var widget = summaryWidgetContainer.find('.widgetContentBlock');
			var url = widget.data('url');
			url = url.replace('&showtype=open', '');
			url = url.replace('&showtype=archive', '');
			url += '&showtype=';
			if (state)
				url += 'open';
			else
				url += 'archive';
			widget.data('url', url);
			thisInstance.loadWidget($(widget));
		});
	},
	/**
	 * Function to register all the events related to summary view widgets
	 */
	registerSummaryViewContainerEvents: function (summaryViewContainer) {
		var thisInstance = this;
		this.registerEventForActivityWidget();
		this.registerChangeFilterForWidget();
		this.registerChangeSwitchForWidget();
		this.registerFilterForAddingModuleRelatedRecordFromSummaryWidget();
		if (Vtiger_Detail_Js.SaveResultInstance == false) {
			Vtiger_Detail_Js.SaveResultInstance = new SaveResult();
		}
		/**
		 * Function to handle the ajax edit for summary view fields
		 */
		var formElement = thisInstance.getForm();
		var formData = formElement.serializeFormData();
		summaryViewContainer.off('click').on('click', '.row .summaryViewEdit', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			currentTarget.addClass('hide');
			var currentTdElement = currentTarget.closest('td.fieldValue');
			thisInstance.ajaxEditHandling(currentTdElement);
			Vtiger_Detail_Js.SaveResultInstance.loadFormData(formData);
		});

		Vtiger_Detail_Js.SaveResultInstance.loadFormData(formData);
		/**
		 * Function to handle actions after ajax save in summary view
		 */
		summaryViewContainer.on(thisInstance.fieldUpdatedEvent, '.recordDetails', function (e, params) {
			var updatesWidget = summaryViewContainer.find("[data-type='Updates']");
			thisInstance.loadWidget(updatesWidget);
		});

		summaryViewContainer.on('click', '.editDefaultStatus', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			currentTarget.popover('hide');
			var url = currentTarget.data('url');
			if (url && typeof url != 'undefined') {
				app.showModalWindow(null, url);
			}
		});

		/*
		 * Register the event to edit the status for for related activities
		 */
		summaryViewContainer.on('click', '.editStatus', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var currentDiv = currentTarget.closest('.activityStatus');
			var editElement = currentDiv.find('.edit');
			var detailViewElement = currentDiv.find('.value');

			currentTarget.hide();
			detailViewElement.addClass('hide');
			editElement.removeClass('hide').show();

			var callbackFunction = function () {
				var fieldnameElement = jQuery('.fieldname', editElement);
				var fieldName = fieldnameElement.val();
				var fieldElement = jQuery('[name="' + fieldName + '"]', editElement);
				var previousValue = fieldnameElement.data('prevValue');
				var ajaxEditNewValue = fieldElement.find('option:selected').val();
				var ajaxEditNewLable = fieldElement.find('option:selected').text();
				var activityDiv = currentDiv.closest('.activityEntries');
				var activityId = activityDiv.find('.activityId').val();
				var moduleName = activityDiv.find('.activityModule').val();
				var activityType = activityDiv.find('.activityType').val();

				if (Vtiger_Detail_Js.SaveResultInstance == false) {
					Vtiger_Detail_Js.SaveResultInstance = new SaveResult();
				}
				var formData2 = {};
				formData2.record = activityId;
				formData2.module = moduleName;
				formData2.view = 'quick_edit';
				formData2[fieldName] = ajaxEditNewValue;
				formData2['p_' + fieldName] = previousValue;
				if (Vtiger_Detail_Js.SaveResultInstance.checkData(formData2) == false) {
					return;
				}
				if (previousValue == ajaxEditNewValue) {
					editElement.addClass('hide');
					detailViewElement.removeClass('hide');
					currentTarget.show();
				} else {
					var errorExists = fieldElement.validationEngine('validate');
					//If validation fails  
					if (errorExists) {
						Vtiger_Helper_Js.addClickOutSideEvent(currentDiv, callbackFunction);
						return;
					}
					editElement.addClass('hide');
					var params = {
						action: 'SaveAjax',
						record: activityId,
						field: fieldName,
						value: ajaxEditNewValue,
						module: moduleName,
						activitytype: activityType
					};

					AppConnector.request(params).then(
							function (data) {
								detailViewElement.removeClass('hide');
								currentTarget.show();
								detailViewElement.html(ajaxEditNewLable);
								fieldnameElement.data('prevValue', ajaxEditNewValue);
								if ('Held' == ajaxEditNewValue || 'Completed' == ajaxEditNewValue) {
									var recordWidget = currentTarget.closest('.activityEntries');
									recordWidget.find('popoverTooltip').popover('hide');
									var widget = currentTarget.closest('.widgetContentBlock');
									var widgetContainer = jQuery(widget);
									thisInstance.loadWidget(widgetContainer);
								}
							}
					);
				}
			}

			//adding clickoutside event on the currentDiv - to save the ajax edit of status values
			Vtiger_Helper_Js.addClickOutSideEvent(currentDiv, callbackFunction);
		});

		/*
		 * Register the event to edit Description for related activities
		 */
		summaryViewContainer.on('click', '.editDescription', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var currentDiv = currentTarget.closest('.activityDescription');
			var editElement = currentDiv.find('.edit');
			var detailViewElement = currentDiv.find('.value');

			currentTarget.hide();
			detailViewElement.addClass('hide');
			editElement.removeClass('hide').show();

			var callbackFunction = function () {
				var fieldnameElement = jQuery('.fieldname', editElement);
				var fieldName = fieldnameElement.val();
				var fieldElement = jQuery('[name="' + fieldName + '"]', editElement);
				var previousValue = fieldnameElement.data('prevValue');
				var ajaxEditNewValue = fieldElement.val();
				var ajaxEditNewLable = fieldElement.val();
				;
				var activityDiv = currentDiv.closest('.activityEntries');
				var activityId = activityDiv.find('.activityId').val();
				var moduleName = activityDiv.find('.activityModule').val();
				var activityType = activityDiv.find('.activityType').val();

				if (Vtiger_Detail_Js.SaveResultInstance == false) {
					Vtiger_Detail_Js.SaveResultInstance = new SaveResult();
				}
				var formData2 = {};
				formData2.record = activityId;
				formData2.module = moduleName;
				formData2.view = 'quick_edit';
				formData2[fieldName] = ajaxEditNewValue;
				formData2['p_' + fieldName] = previousValue;
				if (Vtiger_Detail_Js.SaveResultInstance.checkData(formData2) == false) {
					return;
				}
				if (previousValue == ajaxEditNewValue) {
					editElement.addClass('hide');
					detailViewElement.removeClass('hide');
					currentTarget.show();
				} else {
					var errorExists = fieldElement.validationEngine('validate');
					//If validation fails  
					if (errorExists) {
						Vtiger_Helper_Js.addClickOutSideEvent(currentDiv, callbackFunction);
						return;
					}
					currentDiv.progressIndicator();
					editElement.addClass('hide');
					var params = {
						action: 'SaveAjax',
						record: activityId,
						field: fieldName,
						value: ajaxEditNewValue,
						module: moduleName,
						activitytype: activityType
					};

					AppConnector.request(params).then(
							function (data) {
								currentDiv.progressIndicator({'mode': 'hide'});
								detailViewElement.removeClass('hide');
								currentTarget.show();
								detailViewElement.html(ajaxEditNewLable);
								fieldnameElement.data('prevValue', ajaxEditNewValue);
							}
					);
				}
			}

			//adding clickoutside event on the currentDiv - to save the ajax edit of description values
			Vtiger_Helper_Js.addClickOutSideEvent(currentDiv, callbackFunction);
		});

		/*
		 * Register click event for add button in Related widgets
		 * to add record from widget
		 */

		jQuery('.changeDetailViewMode').on('click', function (e) {
			var detailContentsHolder = jQuery('.detailViewContainer');
			detailContentsHolder.find('.nav li[data-link-key="LBL_RECORD_DETAILS"]').trigger('click');
		});

		/*
		 * Register click event for add button in Related widgets
		 * to add record from widget
		 */
		jQuery('.createRecord').on('click', function (e) {
			var currentElement = jQuery(e.currentTarget);
			var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
			var widgetHeaderContainer = summaryWidgetContainer.find('.widget_header');
			var referenceModuleName = widgetHeaderContainer.find('[name="relatedModule"]').val();
			var recordId = thisInstance.getRecordId();
			var module = app.getModuleName();
			var customParams = {};
			customParams['sourceModule'] = module;
			customParams['sourceRecord'] = recordId;
			if (module != '' && referenceModuleName != '' && typeof thisInstance.referenceFieldNames[referenceModuleName] != 'undefined' && typeof thisInstance.referenceFieldNames[referenceModuleName][module] != 'undefined') {
				var fieldName = thisInstance.referenceFieldNames[referenceModuleName][module];
				customParams[fieldName] = recordId;
			}

			var postQuickCreateSave = function (data) {
				thisInstance.postSummaryWidgetAddRecord(data, currentElement);
			}

			var goToFullFormcallback = function (data) {
				thisInstance.addElementsToQuickCreateForCreatingRelation(data, customParams);
			}

			var QuickCreateParams = {};
			QuickCreateParams['callbackFunction'] = postQuickCreateSave;
			QuickCreateParams['goToFullFormcallback'] = goToFullFormcallback;
			QuickCreateParams['data'] = customParams;
			QuickCreateParams['noCache'] = false;
			Vtiger_Header_Js.getInstance().quickCreateModule(referenceModuleName, QuickCreateParams);
		});
		this.registerFastEditingFiels();
	},
	addRelationBetweenRecords: function (relatedModule, relatedModuleRecordId) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var selectedTabElement = thisInstance.getSelectedTab();
		var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModule);
		relatedController.addRelations(relatedModuleRecordId).then(
				function (data) {
					var summaryViewContainer = thisInstance.getContentHolder();
					var updatesWidget = summaryViewContainer.find("[data-type='Updates']");
					if (updatesWidget.length > 0) {
						thisInstance.loadWidget(updatesWidget);
					}
					aDeferred.resolve(data);
				},
				function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				}
		)
		return aDeferred.promise();
	},
	/**
	 * Function to handle Post actions after adding record from
	 * summary view widget
	 */
	postSummaryWidgetAddRecord: function (data, currentElement) {
		var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
		var widgetHeaderContainer = summaryWidgetContainer.find('.widget_header');
		var widgetDataContainer = summaryWidgetContainer.find('.widget_contents');
		var referenceModuleName = widgetHeaderContainer.find('[name="relatedModule"]').val();
		var recordId = this.getRecordId();
		var module = app.getModuleName();
		var idList = new Array();
		idList.push(data.result._recordId);
		widgetDataContainer.progressIndicator({});
		this.addRelationBetweenRecords(referenceModuleName, idList).then(
				function (data) {
					var params = {};
					params['record'] = recordId;
					params['view'] = 'Detail';
					params['module'] = module;
					params['page'] = widgetDataContainer.find('[name="page"]').val();
					params['limit'] = widgetDataContainer.find('[name="pageLimit"]').val();
					params['col'] = widgetDataContainer.find('[name="col"]').val();
					params['relatedModule'] = referenceModuleName;
					params['mode'] = 'showRelatedRecords';

					AppConnector.request(params).then(
							function (data) {
								var documentsWidget = jQuery('#relatedDocuments');
								widgetDataContainer.progressIndicator({'mode': 'hide'});
								widgetDataContainer.html(data);
								app.changeSelectElementView(documentsWidget);
							}
					);
				}
		)
	},
	/**
	 * Function to register event for emails related record click
	 */
	registerEventForEmailsRelatedRecord: function () {
		var detailContentsHolder = this.getContentHolder();
		var emailsRelatedContainer = detailContentsHolder.find('[name="emailsRelatedRecord"]');
		var parentId = this.getRecordId();
		var popupInstance = Vtiger_Popup_Js.getInstance();
		detailContentsHolder.on('click', '[name="emailsRelatedRecord"]', function (e) {
			var element = jQuery(e.currentTarget);
			var recordId = element.data('id');
			var params = {};
			params['module'] = "Emails";
			params['view'] = "ComposeEmail";
			params['mode'] = "emailPreview";
			params['record'] = recordId;
			params['parentId'] = parentId;
			params['relatedLoad'] = true;
			popupInstance.show(params);
		})
		detailContentsHolder.on('click', '[name="emailsEditView"]', function (e) {
			e.stopPropagation();
			var module = "Emails";
			Vtiger_Helper_Js.checkServerConfig(module).then(function (data) {
				if (data == true) {
					var element = jQuery(e.currentTarget);
					var closestROw = element.closest('tr');
					var recordId = closestROw.data('id');
					var parentRecord = new Array();
					parentRecord.push(parentId);
					var params = {};
					params['module'] = "Emails";
					params['view'] = "ComposeEmail";
					params['mode'] = "emailEdit";
					params['record'] = recordId;
					params['selected_ids'] = parentRecord;
					params['parentId'] = parentId;
					params['relatedLoad'] = true;
					popupInstance.show(params);
				} else {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
				}
			})
		})
	},
	/**
	 * Function to register event for adding email from related list
	 */
	registerEventForAddingEmailFromRelatedList: function () {
		var detailContentsHolder = this.getContentHolder();
		var parentId = this.getRecordId();
		detailContentsHolder.on('click', '[name="composeEmail"]', function (e) {
			e.stopPropagation();
			var element = jQuery(e.currentTarget);
			var parentRecord = new Array();
			var params = {};
			parentRecord.push(parentId);
			params['module'] = app.getModuleName();
			params['view'] = "MassActionAjax";
			params['selected_ids'] = parentRecord;
			params['mode'] = "showComposeEmailForm";
			params['step'] = "step1";
			params['relatedLoad'] = true;
			Vtiger_Index_Js.showComposeEmailPopup(params);
		})
	},
	registerEnterClickEventForTagRecord: function () {
		jQuery('#tagRecordText').keypress(function (e) {
			if (e.which == 13) {
				jQuery('#tagRecord').trigger('click');
			}
		});
	},
	checkTagExists: function (tagText) {
		var tagsArray = tagText.split(' ');
		for (var i = 0; i < tagsArray.length; i++) {
			var tagElement = jQuery('#tagsList').find("[data-tagname='" + tagsArray[i] + "']");
			if (tagElement.length > 0) {
				tagsArray.splice(i, 1);
				i--;
			}
		}
		var tagName = tagsArray.join(' ');
		if (tagName == '') {
			return true;
		} else {
			return tagName;
		}

	},
	addTagsToList: function (data) {
		for (var key in data.result[1]) {
			var tagId = data.result[1][key];
			var tagElement = jQuery('#tagsList').find("[data-tagid='" + tagId + "']");
			if (tagElement.length == 0) {
				jQuery('#tagsList').prepend('<div class="tag btn-info btn-xs pull-right" data-tagname="' + key + '" data-tagid="' + tagId + '"><span class="tagName textOverflowEllipsis"><a class="cursorPointer">' + key + '</a></span><span id="deleteTag" class="glyphicon glyphicon-remove cursorPointer deleteTag" aria-hidden="true"></span></div>');
			}
		}
	},
	checkTagMaxLengthExceeds: function (tagText) {
		var tagsArray = tagText.split(' ');
		var maxTagLength = jQuery('#maxTagLength').val();

		for (var i = 0; i < tagsArray.length; i++) {
			if (tagsArray[i].length > parseInt(maxTagLength)) {
				return true;
			}
		}
		return false;
	},
	registerClickEventForAddingTagRecord: function () {
		var thisInstance = this;
		jQuery('#tagRecord').on('click', function () {
			var textElement = jQuery('#tagRecordText');
			var tagText = textElement.val();
			tagTextSplit = tagText.split(' ');
			if ((tagTextSplit.length + $('#tagsList').children().length) > $('#maxTag').val()) {
				var maxTag = jQuery('#maxTag').val();
				textElement.validationEngine('showPrompt', app.vtranslate('JS_MAX_TAG_EXCEEDS') + ' ' + maxTag, 'error', 'bottomLeft', true);
				return;
			}
			if (tagText == '') {
				textElement.validationEngine('showPrompt', app.vtranslate('JS_PLEASE_ENTER_A_TAG'), 'error', 'bottomLeft', true);
				return;
			}
			var maxLengthExceeds = thisInstance.checkTagMaxLengthExceeds(tagText);
			if (maxLengthExceeds == true) {
				var maxTagLenth = jQuery('#maxTagLength').val();
				textElement.validationEngine('showPrompt', app.vtranslate('JS_MAX_TAG_LENGTH_EXCEEDS') + ' ' + maxTagLenth, 'error', 'bottomLeft', true);
				return;
			}
			var tagExistResult = thisInstance.checkTagExists(tagText);
			if (tagExistResult == true) {
				textElement.validationEngine('showPrompt', app.vtranslate('JS_TAG_NAME_ALREADY_EXIST'), 'error', 'bottomLeft', true);
				return;
			} else {
				tagText = tagExistResult;
			}
			var params = {
				module: app.getModuleName(),
				action: 'TagCloud',
				mode: 'save',
				tagname: tagText,
				record: thisInstance.getRecordId()
			}
			AppConnector.request(params).then(
					function (data) {
						thisInstance.addTagsToList(data);
						textElement.val('');
					}
			);
		});
	},
	registerRemovePromptEventForTagCloud: function (data) {
		jQuery('#tagRecordText').on('focus', function (e) {
			var errorPrompt = jQuery('.formError', data);
			if (errorPrompt.length > 0) {
				errorPrompt.remove();
			}
		});
	},
	registerDeleteEventForTag: function (data) {
		var thisInstance = this;
		jQuery(data).on('click', '.deleteTag', function (e) {
			var tag = jQuery(e.currentTarget).closest('.tag');
			var tagId = tag.data('tagid');
			tag.fadeOut('slow', function () {
				tag.remove();
			});
			var params = {
				module: app.getModuleName(),
				action: 'TagCloud',
				mode: 'delete',
				tag_id: tagId,
				record: thisInstance.getRecordId()
			}
			AppConnector.request(params).then(
					function (data) {
					});
		});
	},
	registerTagClickEvent: function (data) {
		var thisInstance = this;
		jQuery(data).on('click', '.tagName', function (e) {
			var tagElement = jQuery(e.currentTarget);
			var tagId = tagElement.closest('.tag').data('tagid');
			var params = {
				'module': app.getModuleName(),
				'view': 'TagCloudSearchAjax',
				'tag_id': tagId,
				'tag_name': tagElement.find('a').text()
			}
			AppConnector.request(params).then(
					function (data) {
						var params = {
							'data': data
						}
						app.showModalWindow(params);
						thisInstance.registerChangeEventForModulesList();
					}
			)
		});
	},
	registerChangeEventForModulesList: function () {
		jQuery('#tagSearchModulesList').on('change', function (e) {
			var modulesSelectElement = jQuery(e.currentTarget);
			if (modulesSelectElement.val() == 'all') {
				jQuery('[name="tagSearchModuleResults"]').removeClass('hide');
			} else {
				jQuery('[name="tagSearchModuleResults"]').removeClass('hide');
				var selectedOptionValue = modulesSelectElement.val();
				jQuery('[name="tagSearchModuleResults"]').filter(':not(#' + selectedOptionValue + ')').addClass('hide');
			}
		});
	},
	registerPostTagCloudWidgetLoad: function () {
		var thisInstance = this;
		app.getContentsContainer().on('Vtiger.Widget.Load.LBL_TAG_CLOUD', function (e, data) {
			thisInstance.registerClickEventForAddingTagRecord();
			thisInstance.registerEnterClickEventForTagRecord();
			thisInstance.registerDeleteEventForTag(data);
			thisInstance.registerRemovePromptEventForTagCloud(data);
			thisInstance.registerTagClickEvent(data);
		});
	},
	registerGetAllTagCloudWidgetLoad: function () {
		var thisInstance = this;
		var params = {
			module: app.getModuleName(),
			mode: 'showTags',
			source_module: app.getModuleName(),
			record: this.getRecordId(),
			view: 'ShowTagCloudTop'
		};

		AppConnector.request(params).then(function (data) {
			if (data.length > 0) {
				data = $(data);
				$(".detailViewTitle .detailViewToolbar").append(data);
				thisInstance.registerDeleteEventForTag(data);
				thisInstance.registerRemovePromptEventForTagCloud(data);
				thisInstance.registerTagClickEvent(data);
				thisInstance.registerClickEventForAddingTagRecord();
				thisInstance.registerEnterClickEventForTagRecord();
			}
		});
	},
	registerEventForRelatedTabClick: function () {
		var thisInstance = this;
		var detailContentsHolder = thisInstance.getContentHolder();
		var detailContainer = detailContentsHolder.closest('div.detailViewInfo');

		jQuery('.related', detailContainer).on('click', 'li', function (e, urlAttributes) {
			var tabElement = jQuery(e.currentTarget);
			var element = jQuery('<div></div>');
			element.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true,
					'elementToBlock': detailContainer
				}
			});
			var url = tabElement.data('url');
			if (typeof urlAttributes != 'undefined') {
				var callBack = urlAttributes.callback;
				delete urlAttributes.callback;
			}
			thisInstance.loadContents(url, urlAttributes).then(
					function (data) {
						thisInstance.deSelectAllrelatedTabs();
						thisInstance.markTabAsSelected(tabElement);
						app.showBtnSwitch(detailContentsHolder.find('.switchBtn'));
						Vtiger_Helper_Js.showHorizontalTopScrollBar();
						element.progressIndicator({'mode': 'hide'});
						thisInstance.registerHelpInfo();
						app.registerModal(detailContentsHolder);
						if (typeof callBack == 'function') {
							callBack(data);
						}
						//Summary tab is clicked
						if (tabElement.data('linkKey') == thisInstance.detailViewSummaryTabLabel) {
							thisInstance.loadWidgets();
						}
						thisInstance.registerBasicEvents();
						// Let listeners know about page state change.
						app.notifyPostAjaxReady();
					},
					function () {
						//TODO : handle error
						element.progressIndicator({'mode': 'hide'});
					}
			);
		});
	},
	/** 
	 * Function to register event for setting up picklistdependency 
	 * for a module if exist on change of picklist value 
	 */
	registerEventForPicklistDependencySetup: function (container) {
		var thisInstance = this;
		var picklistDependcyElemnt = jQuery('[name="picklistDependency"]', container);
		if (picklistDependcyElemnt.length <= 0) {
			return;
		}
		var picklistDependencyMapping = JSON.parse(picklistDependcyElemnt.val());
		var sourcePicklists = Object.keys(picklistDependencyMapping);
		if (sourcePicklists.length <= 0) {
			return;
		}

		var sourcePickListNames = [];
		for (var i = 0; i < sourcePicklists.length; i++) {
			sourcePickListNames.push('[name="' + sourcePicklists[i] + '"]');
		}
		sourcePickListNames = sourcePickListNames.join(',');
		var sourcePickListElements = container.find(sourcePickListNames);
		sourcePickListElements.on('change', function (e) {
			var currentElement = jQuery(e.currentTarget);
			var sourcePicklistname = currentElement.attr('name');

			var configuredDependencyObject = picklistDependencyMapping[sourcePicklistname];
			var selectedValue = currentElement.val();
			var targetObjectForSelectedSourceValue = configuredDependencyObject[selectedValue];
			var picklistmap = configuredDependencyObject["__DEFAULT__"];

			if (typeof targetObjectForSelectedSourceValue == 'undefined') {
				targetObjectForSelectedSourceValue = picklistmap;
			}
			jQuery.each(picklistmap, function (targetPickListName, targetPickListValues) {
				var targetPickListMap = targetObjectForSelectedSourceValue[targetPickListName];
				if (typeof targetPickListMap == "undefined") {
					targetPickListMap = targetPickListValues;
				}
				var targetPickList = jQuery('[name="' + targetPickListName + '"]', container);
				if (targetPickList.length <= 0) {
					return;
				}

				//On change of SourceField value, If TargetField value is not there in mapping, make user to select the new target value also. 
				var selectedValue = targetPickList.data('selectedValue');
				if (jQuery.inArray(selectedValue, targetPickListMap) == -1) {
					thisInstance.targetPicklistChange = true;
					thisInstance.targetPicklist = targetPickList.closest('td');
				} else {
					thisInstance.targetPicklistChange = false;
					thisInstance.targetPicklist = false;
				}

				var listOfAvailableOptions = targetPickList.data('availableOptions');
				if (typeof listOfAvailableOptions == "undefined") {
					listOfAvailableOptions = jQuery('option', targetPickList);
					targetPickList.data('available-options', listOfAvailableOptions);
				}

				var targetOptions = new jQuery();
				var optionSelector = [];
				optionSelector.push('');
				for (var i = 0; i < targetPickListMap.length; i++) {
					optionSelector.push(targetPickListMap[i]);
				}

				jQuery.each(listOfAvailableOptions, function (i, e) {
					var picklistValue = jQuery(e).val();
					if (jQuery.inArray(picklistValue, optionSelector) != -1) {
						targetOptions = targetOptions.add(jQuery(e));
					}
				})
				var targetPickListSelectedValue = '';
				targetPickListSelectedValue = targetOptions.filter('[selected]').val();
				if (targetPickListMap.length == 1) {
					targetPickListSelectedValue = targetPickListMap[0]; // to automatically select picklist if only one picklistmap is present. 
				}
				targetPickList.html(targetOptions).val(targetPickListSelectedValue).trigger("chosen:updated");
			})

		});
		//To Trigger the change on load 
		sourcePickListElements.trigger('change');
	},
	/**
	 * Function to get child comments
	 */
	getChildComments: function (commentId) {
		var aDeferred = jQuery.Deferred();
		var url = 'module=' + app.getModuleName() + '&view=Detail&record=' + this.getRecordId() + '&mode=showChildComments&commentid=' + commentId;
		var dataObj = this.getCommentThread(url);
		dataObj.then(function (data) {
			aDeferred.resolve(data);
		});
		return aDeferred.promise();
	},
	/**
	 * Function to show total records count in listview on hover
	 * of pageNumber text
	 */
	registerEventForTotalRecordsCount: function () {
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click', '.totalNumberOfRecords', function (e) {
			var element = jQuery(e.currentTarget);
			var totalNumberOfRecords = jQuery('#totalCount').val();
			element.addClass('hide');
			element.parent().progressIndicator({});
			if (totalNumberOfRecords == '') {
				var selectedTabElement = thisInstance.getSelectedTab();
				var relatedModuleName = thisInstance.getRelatedModuleName();
				var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
				relatedController.getRelatedPageCount().then(function () {
					thisInstance.showPagingInfo();
				});
			} else {
				thisInstance.showPagingInfo();
			}
			element.parent().progressIndicator({'mode': 'hide'});
		})
	},
	showPagingInfo: function () {
		var totalNumberOfRecords = jQuery('#totalCount').val();
		var pageNumberElement = jQuery('.pageNumbersText');
		var pageRange = pageNumberElement.text();
		var newPagingInfo = pageRange + " (" + totalNumberOfRecords + ")";
		var listViewEntriesCount = parseInt(jQuery('#noOfEntries').val());
		if (listViewEntriesCount != 0) {
			jQuery('.pageNumbersText').html(newPagingInfo);
		} else {
			jQuery('.pageNumbersText').html("");
		}
	},
	getCustomFieldNameValueMap: function (fieldNameValueMap) {
		return fieldNameValueMap;
	},
	registerSetReadRecord: function (detailContentsHolder) {
		var thisInstance = this;
		detailContentsHolder.on('click', '.setReadRecord', function (e) {
			var currentElement = jQuery(e.currentTarget);
			currentElement.closest('.btn-group').addClass('hide');
			jQuery('#Accounts_detailView_fieldValue_was_read').find('.value').text(app.vtranslate('LBL_YES'));
			var params = {
				'module': app.getModuleName(),
				'action': 'SaveAjax',
				'record': thisInstance.getRecordId(),
				'field': 'was_read',
				'value': 'on',
			}
			AppConnector.request(params).then(function (data) {
				var params = {
					text: app.vtranslate('JS_SET_READ_RECORD'),
					title: app.vtranslate('System'),
					type: 'info',
					animation: 'show'
				};
				Vtiger_Helper_Js.showPnotify(params);
				var relatedTabKey = jQuery('.related li.active');
				if (relatedTabKey.data('linkKey') == thisInstance.detailViewSummaryTabLabel || relatedTabKey.data('linkKey') == thisInstance.detailViewDetailsTabLabel) {
					relatedTabKey.trigger('click');
				}
			});
		});
	},
	registerFastEditingFiels: function () {
		var thisInstance = this;
		var fastEditingFiels = jQuery('.summaryWidgetFastEditing select');
		fastEditingFiels.on('change', function (e) {
			var fieldElement = jQuery(e.currentTarget);
			var fieldContainer = fieldElement.closest('.editField');
			var progressIndicatorElement = jQuery.progressIndicator({
				'message': app.vtranslate('JS_SAVE_LOADER_INFO'),
				'position': 'summaryWidgetFastEditing',
				'blockInfo': {
					'enabled': true
				}
			});
			var fieldName = fieldContainer.data('fieldname');
			fieldName = fieldName.replace("q_", "");
			var prevValue = fieldContainer.data('prevvalue');
			var fieldValue = fieldElement.val();
			var errorExists = fieldElement.validationEngine('validate');
			if (errorExists) {
				fieldContainer.progressIndicator({'mode': 'hide'});
				return;
			}
			var preFieldSaveEvent = jQuery.Event(thisInstance.fieldPreSave);
			fieldElement.trigger(preFieldSaveEvent, {'fieldValue': fieldValue, 'recordId': thisInstance.getRecordId()});
			if (Vtiger_Detail_Js.SaveResultInstance == false) {
				Vtiger_Detail_Js.SaveResultInstance = new SaveResult();
			}
			var formData = {};
			formData.record = thisInstance.getRecordId();
			formData.module = app.getModuleName();
			formData.view = 'quick_edit';
			formData[fieldName] = fieldValue;
			formData['p_' + fieldName] = prevValue;

			if (Vtiger_Detail_Js.SaveResultInstance.checkData(formData) == false) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				jQuery('.detailViewInfo .related li.active').trigger("click");
				return;
			}
			var fieldNameValueMap = {};
			fieldNameValueMap["value"] = fieldValue;
			fieldNameValueMap["field"] = fieldName;
			fieldNameValueMap = thisInstance.getCustomFieldNameValueMap(fieldNameValueMap);
			thisInstance.saveFieldValues(fieldNameValueMap);
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			var params = {
				title: app.vtranslate('JS_SAVE_NOTIFY_OK'),
				type: 'success',
				animation: 'show'
			};
			Vtiger_Helper_Js.showPnotify(params);
			jQuery('.detailViewInfo .related li.active').trigger("click");
		});
	},
	registerHelpInfo: function () {
		var form = this.getForm();
		app.showPopoverElementView(form.find('.HelpInfoPopover'));
	},
	/**
	 * Counting the number of records in related modules
	 * @license licenses/License.html
	 * @package YetiForce.Detail
	 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
	 */
	registerRelatedModulesRecordCount: function () {
		var thisInstance = new Vtiger_Detail_Js();
		$('.related .nav li').each(function (n, item) {
			if ($(item).hasClass('relatedNav') && $(item).data('count') == '1') {
				var params = {
					module: app.getModuleName(),
					action: 'RelationAjax',
					record: thisInstance.getRecordId(),
					relatedModule: $(item).data('reference'),
					mode: 'getRelatedListPageCount',
					tab_label: $(item).data('label-key'),
				}
				AppConnector.request(params).then(function (response) {
					if (response.success) {
						$(item).find('.count').text("(" + response.result.numberOfRecords + ")");
					}
				});

			}
		});
	},
	registerCommentEvents: function (detailContentsHolder) {
		var thisInstance = this;
		detailContentsHolder.on('click', '.addCommentBtn', function (e) {
			thisInstance.removeCommentBlockIfExists();
			var addCommentBlock = thisInstance.getCommentBlock();
			addCommentBlock.appendTo('.commentBlock');
		});

		detailContentsHolder.on('click', '.closeCommentBlock', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var commentInfoBlock = currentTarget.closest('.singleComment');
			commentInfoBlock.find('.commentActionsContainer').show();
			commentInfoBlock.find('.commentInfoContent').show();
			thisInstance.removeCommentBlockIfExists();
		});

		detailContentsHolder.on('click', '.replyComment', function (e) {
			thisInstance.removeCommentBlockIfExists();
			var currentTarget = jQuery(e.currentTarget);
			var commentInfoBlock = currentTarget.closest('.singleComment');
			var addCommentBlock = thisInstance.getCommentBlock();
			commentInfoBlock.find('.commentActionsContainer').hide();
			addCommentBlock.appendTo(commentInfoBlock).show();
			app.registerEventForTextAreaFields(jQuery('.commentcontent', commentInfoBlock));
		});

		detailContentsHolder.on('click', '.editComment', function (e) {
			thisInstance.removeCommentBlockIfExists();
			var currentTarget = jQuery(e.currentTarget);
			var commentInfoBlock = currentTarget.closest('.singleComment');
			var commentInfoContent = commentInfoBlock.find('.commentInfoContent');
			var commentReason = commentInfoBlock.find('[name="editReason"]');
			var editCommentBlock = thisInstance.getEditCommentBlock();
			editCommentBlock.find('.commentcontent').text(commentInfoContent.text());
			editCommentBlock.find('[name="reasonToEdit"]').val(commentReason.text());
			commentInfoContent.hide();
			commentInfoBlock.find('.commentActionsContainer').hide();
			editCommentBlock.appendTo(commentInfoBlock).show();
			app.registerEventForTextAreaFields(jQuery('.commentcontent', commentInfoBlock));
		});

		detailContentsHolder.on('click', '.deleteComment', function (e) {
			thisInstance.removeCommentBlockIfExists();
			var currentTarget = jQuery(e.currentTarget);
			var commentInfoBlock = currentTarget.closest('.singleComment');
			var commentInfoHeader = commentInfoBlock.find('.commentInfoHeader');
			var deleteUrl = "index.php?module=ModComments&action=DeleteAjax&record=" + commentInfoHeader.data('commentid')
			var commentDetails = currentTarget.closest('.commentDetails');
			var relatedComments = commentDetails.find('.commentDetails');
			var viewThreadBlock = commentDetails.find('.viewThreadBlock');
			if (relatedComments.length > 0 || viewThreadBlock.length > 0) {
				Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_CAN_NOT_REMOVE_COMMENT'));
			} else {
				Vtiger_Helper_Js.showConfirmationBox({'message': app.vtranslate('JS_DELETE_COMMENT_CONFIRMATION')}).then(function (data) {
					AppConnector.request(deleteUrl).then(
							function (data) {
								if (data.success == true) {
									commentDetails.fadeOut(400, function () {
										commentDetails.remove();
									});
								} else {
									Vtiger_Helper_Js.showPnotify(data.error.message);
								}
							});
				},
						function (error, err) {
						}
				);
			}
		});

		detailContentsHolder.on('click', '.detailViewSaveComment', function (e) {
			var element = jQuery(e.currentTarget);
			if (!element.is(":disabled")) {
				var dataObj = thisInstance.saveComment(e);
				dataObj.then(function () {
					var commentsContainer = detailContentsHolder.find("[data-type='Comments']");
					thisInstance.loadWidget(commentsContainer).then(function () {
						element.removeAttr('disabled');
					});
				});
			}
		});

		detailContentsHolder.on('click', '.saveComment', function (e) {
			var element = jQuery(e.currentTarget);
			if (!element.is(":disabled")) {
				var currentTarget = jQuery(e.currentTarget);
				var mode = currentTarget.data('mode');
				var dataObj = thisInstance.saveComment(e);
				dataObj.then(function (data) {
					var closestAddCommentBlock = currentTarget.closest('.addCommentBlock');
					var commentTextAreaElement = closestAddCommentBlock.find('.commentcontent');
					var commentInfoBlock = currentTarget.closest('.singleComment');
					commentTextAreaElement.val('');
					if (mode == "add") {
						var commentId = data['result']['id'];
						var commentHtml = thisInstance.getCommentUI(commentId);
						commentHtml.then(function (data) {
							var commentBlock = closestAddCommentBlock.closest('.commentDetails');
							var detailContentsHolder = thisInstance.getContentHolder();
							var noCommentsMsgContainer = jQuery('.noCommentsMsgContainer', detailContentsHolder);
							noCommentsMsgContainer.remove();
							if (commentBlock.length > 0) {
								closestAddCommentBlock.remove();
								var childComments = commentBlock.find('ul');
								if (childComments.length <= 0) {
									var currentChildCommentsCount = commentInfoBlock.find('.viewThreadBlock').data('childCommentsCount');
									var newChildCommentCount = currentChildCommentsCount + 1;
									commentInfoBlock.find('.childCommentsCount').text(newChildCommentCount);
									var parentCommentId = commentInfoBlock.find('.commentInfoHeader').data('commentid');
									thisInstance.getChildComments(parentCommentId).then(function (responsedata) {
										jQuery(responsedata).appendTo(commentBlock);
										commentInfoBlock.find('.viewThreadBlock').hide();
										commentInfoBlock.find('.hideThreadBlock').show();
									});
								} else {
									jQuery('<ul class="liStyleNone"><li class="commentDetails">' + data + '</li></ul>').appendTo(commentBlock);
								}
							} else {
								jQuery('<ul class="liStyleNone"><li class="commentDetails">' + data + '</li></ul>').prependTo(closestAddCommentBlock.closest('.commentContainer').find('.commentsList'));
								commentTextAreaElement.css({height: '71px'});
							}
							commentInfoBlock.find('.commentActionsContainer').show();
						});
					} else if (mode == "edit") {
						var modifiedTime = commentInfoBlock.find('.commentModifiedTime');
						var commentInfoContent = commentInfoBlock.find('.commentInfoContent');
						var commentEditStatus = commentInfoBlock.find('[name="editStatus"]');
						var commentReason = commentInfoBlock.find('[name="editReason"]');
						commentInfoContent.html(data.result.commentcontent);
						commentReason.html(data.result.reasontoedit);
						modifiedTime.text(data.result.modifiedtime);
						modifiedTime.attr('title', data.result.modifiedtimetitle)
						if (commentEditStatus.hasClass('hide')) {
							commentEditStatus.removeClass('hide');
						}
						if (data.result.reasontoedit != "") {
							commentInfoBlock.find('.editReason').removeClass('hide')
						}
						commentInfoContent.show();
						commentInfoBlock.find('.commentActionsContainer').show();
						closestAddCommentBlock.remove();
					}
					element.removeAttr('disabled');
				});
			}
		});

		detailContentsHolder.on('click', '.moreRecentComments', function () {
			var recentCommentsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentCommentsTabLabel);
			recentCommentsTab.trigger('click');
		});
	},
	registerMailPreviewWidget: function (container) {
		var thisInstance = this;
		container.on('click', '.showMailBody', function (e) {
			var row = $(e.currentTarget).closest('.row');
			var mailBody = row.find('.mailBody');
			var mailTeaser = row.find('.mailTeaser');
			var glyphicon = $(e.currentTarget).find('.glyphicon');
			if (mailBody.hasClass('hide')) {
				mailBody.removeClass('hide');
				mailTeaser.addClass('hide');
				glyphicon.removeClass("glyphicon-triangle-bottom").addClass("glyphicon-triangle-top");
			} else {
				mailBody.addClass('hide');
				mailTeaser.removeClass('hide');
				glyphicon.removeClass("glyphicon-triangle-top").addClass("glyphicon-triangle-bottom");
			}
		});
		container.find('[name="mail-type"]').change(function (e) {
			thisInstance.loadMailPreviewWidget(container);
		});
		container.find('[name="mailFilter"]').change(function (e) {
			thisInstance.loadMailPreviewWidget(container);
		});
		container.on('click', '.showMailsModal', function (e) {
			var url = $(e.currentTarget).data('url');
			url += '&type=' + container.find('[name="mail-type"]').val();
			if (container.find('[name="mailFilter"]').length > 0) {
				url += '&mailFilter=' + container.find('[name="mailFilter"]').val();
			}
			var progressIndicatorElement = jQuery.progressIndicator();
			app.showModalWindow("", url, function (data) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				thisInstance.registerMailPreviewWidget(data);
				data.find('.expandAllMails').click();
				data.find('.showMailModal').click(function (e) {
					var progressIndicatorElement = jQuery.progressIndicator();
					app.showModalWindow("", $(e.currentTarget).data('url'), function () {
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
					});
				});
			});
		});
		container.find('.expandAllMails').click(function (e) {
			container.find('.mailBody').removeClass('hide');
			container.find('.mailTeaser').addClass('hide');
			container.find('.showMailBody .glyphicon').removeClass("glyphicon-triangle-bottom").addClass("glyphicon-triangle-top");
		});
		container.find('.collapseAllMails').click(function (e) {
			container.find('.mailBody').addClass('hide');
			container.find('.mailTeaser').removeClass('hide');
			container.find('.showMailBody .glyphicon').removeClass("glyphicon-triangle-top").addClass("glyphicon-triangle-bottom");
		});
		container.find('.widget_contents').on(thisInstance.widgetPostLoad, function (e, widgetName) {
			container.find('.showMailModal').click(function (e) {
				var progressIndicatorElement = jQuery.progressIndicator();
				app.showModalWindow("", $(e.currentTarget).data('url'), function () {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
				});
			});
		});
		container.find('.sendMailBtn').click(function (e) {
			var sendButton = jQuery(e.currentTarget);
			var url = sendButton.data("url");
			var mod = sendButton.data("mod");
			var record = sendButton.data("record");
			var popup = sendButton.data("popup");
			if (mod == 'Contacts' || mod == 'Leads' || mod == 'Accounts') {
				var params = {};
				var resp = {};
				params.data = {module: 'OSSMail', action: 'getContactMail', mod: mod, ids: record}
				params.async = false;
				params.dataType = 'json';
				AppConnector.request(params).then(
						function (response) {
							resp = response['result'];
							if (resp.length > 1) {
								var getConfig = jQuery.ajax({
									type: "GET",
									async: false,
									url: 'index.php?module=OSSMail&view=selectEmail',
									data: {resp: resp}
								});
								var callback = function (container) {
									$('#sendEmailContainer #selectEmail').click(function (e) {
										url += '&to=' + $('input[name=selectedFields]:checked').val();
										thisInstance.sendMailWindow(url, popup);
									});
								}
								getConfig.done(function (cfg) {
									var data = {}
									data.css = {'width': '700px'};
									data.cb = callback;
									data.data = cfg;
									app.showModalWindow(data);
								});
							}
							if (resp.length == 1) {
								url += '&to=' + resp[0].email;
								thisInstance.sendMailWindow(url, popup);
							}
							if (resp.length == 0) {
								thisInstance.sendMailWindow(url, popup);
							}
						}
				);
			} else {
				thisInstance.sendMailWindow(url, popup);
			}
		});
	},
	sendMailWindow: function (url, popup) {
		if (popup) {
			window.open(url, '_blank', 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no');
		} else {
			window.location.href = url;
		}
	},
	loadMailPreviewWidget: function (widgetContent) {
		var thisInstance = this;
		var widgetDataContainer = widgetContent.find('.widget_contents');
		var recordId = $('#recordId').val();
		var progress = widgetDataContainer.progressIndicator();
		var params = {};
		params['module'] = 'OSSMailView';
		params['view'] = 'widget';
		params['smodule'] = $('#module').val();
		params['srecord'] = recordId;
		params['mode'] = 'showEmailsList';
		params['type'] = $('[name="mail-type"]').val();
		params['mailFilter'] = $('[name="mailFilter"]').val();
		AppConnector.request(params).then(
				function (data) {
					widgetDataContainer.html(data);
					widgetDataContainer.trigger(thisInstance.widgetPostLoad, {widgetName: 'Emails'})
					progress.progressIndicator({'mode': 'hide'});
				}
		);
	},
	registerBasicEvents: function () {
		var thisInstance = this;
		var detailContentsHolder = thisInstance.getContentHolder();
		//register all the events for summary view container
		thisInstance.registerSummaryViewContainerEvents(detailContentsHolder);
		thisInstance.registerCommentEvents(detailContentsHolder);
		app.registerEventForDatePickerFields(detailContentsHolder);
		//Attach time picker event to time fields
		app.registerEventForTimeFields(detailContentsHolder);

		detailContentsHolder.on('click', '#detailViewNextRecordButton', function (e) {
			var selectedTabElement = thisInstance.getSelectedTab();
			var url = selectedTabElement.data('url');
			var currentPageNum = thisInstance.getRelatedListCurrentPageNum();
			var requestedPage = parseInt(currentPageNum) + 1;
			var nextPageUrl = url + '&page=' + requestedPage;
			thisInstance.loadContents(nextPageUrl);
		});

		detailContentsHolder.on('click', '#detailViewPreviousRecordButton', function (e) {
			var selectedTabElement = thisInstance.getSelectedTab();
			var url = selectedTabElement.data('url');
			var currentPageNum = thisInstance.getRelatedListCurrentPageNum();
			var requestedPage = parseInt(currentPageNum) - 1;
			var params = {};
			var nextPageUrl = url + '&page=' + requestedPage;
			thisInstance.loadContents(nextPageUrl);
		});

		detailContentsHolder.on('click', 'table.detailview-table td.fieldValue', function (e) {
			if (jQuery(e.target).closest('a').hasClass('btnNoFastEdit'))
				return;
			var currentTdElement = jQuery(e.currentTarget);
			thisInstance.ajaxEditHandling(currentTdElement);
		});

		detailContentsHolder.on('click', 'div.recordDetails span.squeezedWell', function (e) {
			var currentElement = jQuery(e.currentTarget);
			var relatedLabel = currentElement.data('reference');
			jQuery('.detailViewInfo .related li[data-reference="' + relatedLabel + '"]').trigger("click");
		});

		detailContentsHolder.on('click', '.relatedPopup', function (e) {
			var editViewObj = new Vtiger_Edit_Js();
			editViewObj.openPopUp(e);
			return false;
		});

		detailContentsHolder.on('click', '.viewThread', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var currentTargetParent = currentTarget.parent();
			var commentActionsBlock = currentTarget.closest('.commentActions');
			var currentCommentBlock = currentTarget.closest('.commentDetails');
			var ulElements = currentCommentBlock.find('ul');
			if (ulElements.length > 0) {
				ulElements.show();
				commentActionsBlock.find('.hideThreadBlock').show();
				currentTargetParent.hide();
				return;
			}
			var commentId = currentTarget.closest('.commentDiv').find('.commentInfoHeader').data('commentid');
			thisInstance.getChildComments(commentId).then(function (data) {
				jQuery(data).appendTo(jQuery(e.currentTarget).closest('.commentDetails'));
				commentActionsBlock.find('.hideThreadBlock').show();
				currentTargetParent.hide();
			});
		});

		detailContentsHolder.on('click', '.hideThread', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var currentTargetParent = currentTarget.parent();
			var commentActionsBlock = currentTarget.closest('.commentActions');
			var currentCommentBlock = currentTarget.closest('.commentDetails');
			currentCommentBlock.find('ul').hide();
			currentTargetParent.hide();
			commentActionsBlock.find('.viewThreadBlock').show();
		});

		detailContentsHolder.on('click', '.detailViewThread', function (e) {
			var recentCommentsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentCommentsTabLabel);
			var commentId = jQuery(e.currentTarget).closest('.singleComment').find('.commentInfoHeader').data('commentid');
			var commentLoad = function (data) {
				window.location.href = window.location.href + '#' + commentId;
			}
			recentCommentsTab.trigger('click', {'commentid': commentId, 'callback': commentLoad});
		});

		detailContentsHolder.on('click', '.moreRecentRecords', function (e) {
			e.preventDefault();
			var element = jQuery(e.currentTarget);
			var recentCommentsTab = thisInstance.getTabByLabel(element.data('label-key'));
			recentCommentsTab.trigger('click');
		});
		detailContentsHolder.on('click', '.moreRecentUpdates', function () {
			var currentPage = jQuery("#updatesCurrentPage").val();
			var recordId = jQuery("#recordId").val();
			var nextPage = parseInt(currentPage) + 1;
			var url = "index.php?module=" + app.getModuleName() + "&view=Detail&record=" + recordId + "&mode=showRecentActivities&page=" + nextPage + "&tab_label=LBL_UPDATES";
			AppConnector.request(url).then(
					function (data) {
						jQuery("#updatesCurrentPage").remove();
						jQuery("#moreLink").remove();
						jQuery('#updates').append(data);
					},
					function (error, err) {

					}
			);
		});

		detailContentsHolder.on('click', '.moreRecentDocuments', function () {
			var recentDocumentsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentDocumentsTabLabel);
			recentDocumentsTab.trigger('click');
		});

		detailContentsHolder.on('click', '.moreRecentActivities', function () {
			var recentActivitiesTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentActivitiesTabLabel);
			recentActivitiesTab.trigger('click');
		});

		detailContentsHolder.off('switchChange.bootstrapSwitch').on('switchChange.bootstrapSwitch', '.relatedContainer .switchBtn', function (e, state) {
			var recentActivitiesTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentActivitiesTabLabel);
			var url = recentActivitiesTab.data('url');
			url = url.replace('&time=current', '');
			url = url.replace('&time=history', '');
			url += '&time=';
			if (state)
				url += 'current';
			else
				url += 'history';
			recentActivitiesTab.data('url', url);
			recentActivitiesTab.trigger('click');
		});
		thisInstance.registerEventForRelatedList();
		thisInstance.registerEventForRelatedListPagination();
		thisInstance.registerBlockAnimationEvent();
		thisInstance.registerMailPreviewWidget(detailContentsHolder.find('.widgetContentBlock[data-type="EmailList"]'));
	},
	registerEvents: function () {
		var thisInstance = this;
		//thisInstance.triggerDisplayTypeEvent();
		this.registerHelpInfo();
		thisInstance.registerSendSmsSubmitEvent();
		thisInstance.registerAjaxEditEvent();
		this.registerRelatedRowClickEvent();
		this.registerBlockStatusCheckOnLoad();
		this.registerEmailFieldClickEvent();
		this.registerPhoneFieldClickEvent();
		this.registerEventForEmailsRelatedRecord();
		this.registerEventForAddingEmailFromRelatedList();
		this.registerPostTagCloudWidgetLoad();
		this.registerEventForRelatedTabClick();
		Vtiger_Helper_Js.showHorizontalTopScrollBar();
		this.registerUrlFieldClickEvent();

		var detailViewContainer = jQuery('div.detailViewContainer');
		if (detailViewContainer.length <= 0) {
			// Not detail view page
			return;
		}
		this.registerBasicEvents()
		this.registerSetReadRecord(detailViewContainer);
		thisInstance.registerEventForPicklistDependencySetup(thisInstance.getForm());

		thisInstance.getForm().validationEngine(app.validationEngineOptionsForRecord);
		thisInstance.loadWidgets();

		app.registerEventForTextAreaFields(jQuery('.commentcontent'));
		this.registerEventForTotalRecordsCount();
		this.registerGetAllTagCloudWidgetLoad();
		this.registerRelatedModulesRecordCount();
		var header = Vtiger_Header_Js.getInstance();
		header.registerQuickCreateCallBack(this.registerRelatedModulesRecordCount);
	}
});

