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
		Vtiger_Detail_Js.triggerDetailViewAction(detailActionUrl);
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
		var detailInstance = Vtiger_Detail_Js.getInstance();
		var params = {};
		if (jQuery('[name="currentPageNum"]').length > 0) {
			params.page = jQuery('[name="currentPageNum"]').val();
		}
		detailInstance.loadRelatedList(params);
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
						user: data.find('[name="user"]').val(),
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
	updateField: function (fieldName) {
		var params = {
			module: app.getModuleName(),
			action: 'UpdateField',
			record: app.getRecordId(),
			fieldName: fieldName,
		};
		AppConnector.request(params).then(
				function (response) {
					Vtiger_Helper_Js.showMessage({
						title: app.vtranslate('JS_LBL_PERMISSION'),
						text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
						type: 'success',
						animation: 'show'
					});
					location.reload();
				},
				function (error, err) {
					console.error(error, err);
				}
		);
	}
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
			'Projects': 'process',
			'ServiceContracts': 'process',
		},
		'OutsourcedProducts': {
			'Leads': 'parent_id',
			'Accounts': 'parent_id',
			'Contacts': 'parent_id'
		},
		'Assets': {
			'Accounts': 'parent_id',
			'Contacts': 'parent_id'
		},
		'OSSOutsourcedServices': {
			'Leads': 'parent_id',
			'Accounts': 'parent_id',
			'Contacts': 'parent_id'
		},
		'OSSSoldServices': {
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
		thisInstance.registerRelatedModulesRecordCount();
	},
	loadWidget: function (widgetContainer, params) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var contentHeader = jQuery('.widget_header,.widgetHeader', widgetContainer);
		var contentContainer = jQuery('.widget_contents', widgetContainer);
		var relatedModuleName = contentHeader.find('[name="relatedModule"]').val();

		if (params == undefined) {
			var urlParams = widgetContainer.data('url');
			var params = {
				type: 'GET',
				dataType: 'html',
				data: urlParams
			};
		}

		contentContainer.progressIndicator({});
		AppConnector.request(params).then(
				function (data) {
					contentContainer.progressIndicator({mode: 'hide'});
					contentContainer.html(data);
					contentContainer.trigger(thisInstance.widgetPostLoad, {'widgetName': relatedModuleName})
					app.showPopoverElementView(contentContainer.find('.popoverTooltip'));
					app.registerModal(contentContainer);
					app.registerMoreContent(contentContainer.find('button.moreBtn'));
					var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), thisInstance.getSelectedTab(), relatedModuleName);
					relatedController.registerUnreviewedCountEvent(widgetContainer);
					aDeferred.resolve(params);
				},
				function (e) {
					contentContainer.progressIndicator({mode: 'hide'});
					aDeferred.reject();
				}
		);
		return aDeferred.promise();
	},
	/**
	 * Function to load only Comments Widget.
	 */
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
		jQuery('.related .dropdown [data-reference="' + tabElement.data('reference') + '"]').addClass('active');
	},
	reloadTabContent: function () {
		this.getSelectedTab().trigger('click');
	},
	getSelectedTab: function () {
		var tabContainer = this.getTabContainer();
		return tabContainer.find('.nav li.active:not(.hide)');
	},
	getTabContainer: function () {
		return jQuery('div.related');
	},
	getTabs: function () {
		var topTabs = this.getTabContainer().find('li.baseLink:not(.hide)');
		var dropdownMenuTabs = this.getTabContainer().find('li:not(.baseLink)');
		dropdownMenuTabs.each(function (n, e) {
			var currentTarget = jQuery(this);
			var iteration = currentTarget.data('iteration');
			var className = currentTarget.hasClass('mainNav') ? 'mainNav' : 'relatedNav';
			if (iteration != undefined && topTabs.filter('.' + className + '[data-iteration="' + iteration + '"]').length < 1) {
				topTabs.push(currentTarget.get(0));
			}
		})
		return topTabs;
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
		return app.getRecordId()
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
	 * Function to save comment
	 */
	saveCommentAjax: function (element, commentMode, commentContentValue, editCommentReason, commentId, parentCommentId, aDeferred) {
		var thisInstance = this;
		var progressIndicatorElement = jQuery.progressIndicator({});
		var commentInfoBlock = element.closest('.singleComment');
		var relatedTo = commentInfoBlock.find('.related_to').val()
		if (!relatedTo) {
			relatedTo = thisInstance.getRecordId();
		}
		var postData = {
			'commentcontent': commentContentValue,
			'related_to': relatedTo,
			'module': 'ModComments'
		};

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
					if (commentMode == 'add') {
						thisInstance.addRelationBetweenRecords('ModComments', data.result.id, thisInstance.getTabByLabel(thisInstance.detailViewRecentCommentsTabLabel))
					}
					aDeferred.resolve(data);
				},
				function (textStatus, errorThrown) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					element.removeAttr('disabled');
					aDeferred.reject(textStatus, errorThrown);
				}
		);
	},
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
			aDeferred.reject(errorMsg);
			return aDeferred.promise();
		}
		if (commentMode == "edit") {
			var editCommentReason = closestCommentBlock.find('[name="reasonToEdit"]').val();
		}
		var element = jQuery(e.currentTarget);
		var commentInfoHeader = closestCommentBlock.closest('.commentDetails').find('.commentInfoHeader');
		var commentId = commentInfoHeader.data('commentid');
		var parentCommentId = commentInfoHeader.data('parentcommentid');
		thisInstance.saveCommentAjax(element, commentMode, commentContentValue, editCommentReason, commentId, parentCommentId, aDeferred);
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
	loadRelatedList: function (params) {
		var aDeferred = jQuery.Deferred();
		if (params == undefined) {
			params = {};
		}
		var relatedListInstance = new Vtiger_RelatedList_Js(this.getRecordId(), app.getModuleName(), this.getSelectedTab(), this.getRelatedModuleName());
		relatedListInstance.loadRelatedList(params).then(
				function (data) {
					aDeferred.resolve(data);
				},
				function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				}
		);
		return aDeferred.promise();
	},
	registerEventForRelatedListPagination: function () {
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click', '#relatedViewNextPageButton', function (e) {
			var element = jQuery(e.currentTarget);
			if (element.hasClass('disabled')) {
				return;
			}
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.nextPageHandler();
		});
		detailContentsHolder.on('click', '#relatedViewPreviousPageButton', function () {
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
		detailContentsHolder.on('click', '#totalCountBtn', function () {
			app.hidePopover(jQuery(this));
			var params = {
				module: app.getModuleName(),
				view: 'Pagination',
				mode: 'getRelationPagination',
				relatedModule: detailContentsHolder.find('.relatedModuleName').val(),
				noOfEntries: $('#noOfEntries').val(),
				page: detailContentsHolder.find('[name="currentPageNum"]').val(),
				record: app.getRecordId(),
			}
			AppConnector.request(params).then(function (response) {
				detailContentsHolder.find('.paginationDiv').html(response);
			});
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
							if (response.result) {
								var widget = element.closest('.widgetContentBlock');
								if (widget.length) {
									thisInstance.loadWidget(widget);
									var updatesWidget = detailContentsHolder.find("[data-type='Updates']");
									if (updatesWidget.length > 0) {
										thisInstance.loadWidget(updatesWidget);
									}
								} else {
									thisInstance.reloadTabContent();
								}
							} else {
								Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_CANNOT_REMOVE_RELATION'));
							}
						});
					},
					function (error, err) {
					}
			);
		});

		detailContentsHolder.on('click', 'a.favorites', function (e) {
			var progressInstance = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			var element = jQuery(e.currentTarget);
			var instance = Vtiger_Detail_Js.getInstance();

			var row = element.closest('tr');
			var relatedRecordid = row.data('id');
			var widget_contents = element.closest('.widget_contents');
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			if (relatedModuleName == undefined) {
				relatedModuleName = widget_contents.find('.relatedModuleName').val();
			}
			var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.favoritesRelation(relatedRecordid, element.data('state')).then(function (response) {
				if (response) {
					var state = element.data('state') ? 0 : 1;
					element.data('state', state);
					element.find('.glyphicon').each(function () {
						if (jQuery(this).hasClass('hide')) {
							jQuery(this).removeClass('hide');
						} else {
							jQuery(this).addClass('hide');
						}
					})
					progressInstance.progressIndicator({'mode': 'hide'});
					var text = app.vtranslate('JS_REMOVED_FROM_FAVORITES');
					if (state) {
						text = app.vtranslate('JS_ADDED_TO_FAVORITES');
					}
					Vtiger_Helper_Js.showPnotify({text: text, type: 'success', animation: 'show'});
				}

			});
		});
		detailContentsHolder.on('click', '.relatedContents .listViewEntries td', function (e) {
			var target = jQuery(e.target);
			var row = target.closest('tr');
			var inventoryRow = row.next();
			if (inventoryRow.hasClass('listViewInventoryEntries') && !target.closest('div').hasClass('actions') && !target.is('a') && !target.is('input')) {
				inventoryRow.toggleClass('hide');
			}
		});
		var selectedTabElement = thisInstance.getSelectedTab();
		var relatedModuleName = thisInstance.getRelatedModuleName();
		var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
		relatedController.registerUnreviewedCountEvent();
	},
	registerBlockAnimationEvent: function () {
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.find('.blockHeader').click(function () {
			var currentTarget = $(this).find('.blockToggle').not('.hide');
			var blockId = currentTarget.data('id');
			var closestBlock = currentTarget.closest('.detailViewTable');
			var bodyContents = closestBlock.find('.blockContent');
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
		var blocks = this.getContentHolder().find('.detailViewTable');
		var module = app.getModuleName();
		blocks.each(function (index, block) {
			var currentBlock = jQuery(block);
			var headerAnimationElement = currentBlock.find('.blockToggle').not('.hide');
			var bodyContents = currentBlock.find('.blockContent')
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
			var elementName = jQuery.inArray(elementTarget.data('type'), ['taxes', 'sharedOwner', 'multipicklist']) != -1 ? fieldName + '[]' : fieldName;
			var fieldElement = jQuery('[name="' + elementName + '"]', editElement);
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
				if ((element.closest('.fieldValue').is(currentTdElement))) {
					return;
				}
				fieldElement.inputmask('remove');
				currentTdElement.removeAttr('tabindex');
				var previousValue = elementTarget.data('prevValue');
				var formElement = thisInstance.getForm();
				var formData = formElement.serializeFormData();
				var ajaxEditNewValue = formData[fieldName] ? formData[fieldName] : formData[elementName];
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
				function toStr(v) {
					return v === undefined || v === null ? '' : (v + '');
				}
				fieldElement.validationEngine('hide');
				//Before saving ajax edit values we need to check if the value is changed then only we have to save
				if (toStr(previousValue) === toStr(ajaxEditNewValue)) {
					editElement.addClass('hide');
					detailViewValue.removeClass('hide');
					actionElement.removeClass('hide');
					readRecord.prop('disabled', false);
					editElement.off('clickoutside');
				} else {
					var preFieldSaveEvent = jQuery.Event(thisInstance.fieldPreSave);
					fieldElement.trigger(preFieldSaveEvent, {'fieldValue': fieldValue, 'recordId': thisInstance.getRecordId()});
					if (preFieldSaveEvent.isDefaultPrevented()) {
						//Stop the save
						saveTriggred = false;
						preventDefault = true;
						readRecord.prop('disabled', false);
						return;
					}
					preventDefault = false;
					editElement.off('clickoutside');
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
							editElement.off('clickoutside');
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
						if (postSaveRecordDetails['isEditable'] == false) {
							var progressIndicatorElement = jQuery.progressIndicator({
								'position': 'html',
								'blockInfo': {
									'enabled': true
								}
							});
							window.location.reload();
						}
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
							thisInstance.reloadTabContent();
							thisInstance.registerSummaryViewContainerEvents(detailContentsHolder);
							thisInstance.registerEventForPicklistDependencySetup(thisInstance.getForm());
							thisInstance.registerEventForRelatedList();
						} else if (selectedTabElement.data('linkKey') == thisInstance.detailViewDetailsTabLabel) {
							thisInstance.registerEventForPicklistDependencySetup(thisInstance.getForm());
						}
						thisInstance.updateRecordsPDFTemplateBtn(thisInstance.getForm());
					},
							function (error) {
								editElement.addClass('hide');
								detailViewValue.removeClass('hide');
								actionElement.removeClass('hide');
								editElement.off('clickoutside');
								readRecord.prop('disabled', false);
								currentTdElement.progressIndicator({'mode': 'hide'});
							}
					)
				}
			}
			editElement.on('clickoutside', saveHandler);
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
	registerEmailEvent: function () {
		var thisInstance = this;
		this.getContentHolder().find('.resetRelationsEmail').on('click', function (e) {
			var currentElement = jQuery(e.currentTarget);
			Vtiger_Helper_Js.showConfirmationBox({'message': app.vtranslate('JS_EMAIL_RESET_RELATIONS_CONFIRMATION')}).then(function (data) {
				AppConnector.request({
					module: 'OSSMailView',
					action: 'Relation',
					moduleName: app.getModuleName(),
					record: app.getRecordId()
				}).then(function (d) {
					Vtiger_Helper_Js.showMessage({text: d.result});
				})
			});
		})
	},
	getFiltersDataAndLoad: function (e, params) {
		var data = this.getFiltersData(e, params);
		this.loadWidget(data['container'], data['params']);
	},
	getFiltersData: function (e, params) {
		if (e.currentTarget) {
			var currentElement = jQuery(e.currentTarget);
		} else {
			currentElement = e;
		}
		var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
		var widget = summaryWidgetContainer.find('.widgetContentBlock');
		var url = '&' + widget.data('url');
		var urlParams = {};
		var parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
			urlParams[key] = value;
		});
		var urlNewParams = [];
		summaryWidgetContainer.find('.widget_header .filterField').each(function (n, item) {
			var value = '';
			var element = jQuery(item);
			var name = element.data('urlparams');
			if (element.attr('type') == 'checkbox') {
				if (element.prop('checked')) {
					value = element.data('on-val');
				} else {
					value = element.data('off-val');
				}
			} else if (element.attr('type') == 'radio') {
				if (element.is(':checked')) {
					urlNewParams[element.attr('name')] = element.val();
				}
			} else {
				var selectedFilter = element.find('option:selected').val();
				var fieldlable = element.data('fieldlable');
				var filter = element.data('filter');
				value = {};
				if (selectedFilter != fieldlable) {
					value[filter] = selectedFilter;
				} else {
					return;
				}
			}
			if (name) {
				if (name in urlNewParams) {
					urlNewParams[name].push(value);
				} else {
					urlNewParams[name] = [value];
				}
			}
		});
		if (params != undefined) {
			$.extend(urlNewParams, params);
		}
		return {'container': $(widget), 'params': $.extend(urlParams, urlNewParams)};
	},
	registerChangeFilterForWidget: function () {
		var thisInstance = this;
		jQuery('.widget_header .filterField').on('change', function (e, state) {
			thisInstance.getFiltersDataAndLoad(e);
		}).on('switchChange.bootstrapSwitch', function (e, state) {
			thisInstance.getFiltersDataAndLoad(e);
		});
	},
	registerChangeSwitchForWidget: function (summaryViewContainer) {
		var thisInstance = this;
		summaryViewContainer.find('.activityWidgetContainer').on('switchChange.bootstrapSwitch', '.switchBtn', function (e, state) {
			var currentElement = jQuery(e.currentTarget);
			var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
			var widget = summaryWidgetContainer.find('.widgetContentBlock');
			var url = widget.data('url');
			url = url.replace('&type=current', '').replace('&type=history', '');
			url += '&type=';
			if (state) {
				summaryWidgetContainer.find('.ativitiesPagination').removeClass('hide');
				url += 'current';
			} else {
				summaryWidgetContainer.find('.ativitiesPagination').addClass('hide');
				url += 'history';
			}
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
	},
	/**
	 * Function to register all the events related to summary view widgets
	 */
	registerSummaryViewContainerEvents: function (summaryViewContainer) {
		var thisInstance = this;
		this.registerEventForActivityWidget();
		this.registerChangeFilterForWidget();
		this.registerChangeSwitchForWidget(summaryViewContainer);
		this.registerFilterForAddingModuleRelatedRecordFromSummaryWidget();
		this.registerEmailEvent();
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
			var currentTdElement = currentTarget.closest('.fieldValue');
			thisInstance.ajaxEditHandling(currentTdElement);
			Vtiger_Detail_Js.SaveResultInstance.loadFormData(formData);
		});

		Vtiger_Detail_Js.SaveResultInstance.loadFormData(formData);
		/**
		 * Function to handle actions after ajax save in summary view
		 */
		summaryViewContainer.on(thisInstance.fieldUpdatedEvent, '.recordDetails', function (e, params) {
			var updatesWidget = summaryViewContainer.find("[data-type='Updates']");
			if (updatesWidget.length) {
				var params = thisInstance.getFiltersData(updatesWidget);
				updatesWidget.find('.btnChangesReviewedOn').parent().remove();
				thisInstance.loadWidget(updatesWidget, params['params']);
			}
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
		 * Register the event to edit Description for related activities
		 */
		summaryViewContainer.on('click', '.editDescription', function (e) {
			var thisInstance = this;
			var currentTarget = jQuery(e.currentTarget);
			var currentDiv = currentTarget.closest('.activityDescription');
			var editElement = currentDiv.find('.edit');
			var detailViewElement = currentDiv.find('.value');

			currentTarget.hide();
			detailViewElement.addClass('hide');
			editElement.removeClass('hide').show();

			var fieldnameElement = jQuery('.fieldname', editElement);
			var fieldName = fieldnameElement.val();
			var fieldElement = jQuery('[name="' + fieldName + '"]', editElement);

			var callbackFunction = function () {
				var previousValue = fieldnameElement.data('prevValue');
				var ajaxEditNewValue = fieldElement.val();
				var ajaxEditNewLable = fieldElement.val();
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

			fieldElement.focus();
			//adding focusout event on the currentDiv - to save the ajax edit of description values
			currentDiv.one('focusout', callbackFunction);
		});

		/*
		 * Register click event for add button in Related widgets
		 * to add record from widget
		 */

		jQuery('.changeDetailViewMode').on('click', function (e) {
			thisInstance.getTabs().filter('[data-link-key="' + thisInstance.detailViewDetailsTabLabel + '"]:not(.hide)').trigger('click');
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
	addRelationBetweenRecords: function (relatedModule, relatedModuleRecordId, selectedTabElement) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		if (selectedTabElement == undefined) {
			var selectedTabElement = thisInstance.getSelectedTab();
		}
		var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModule);
		relatedController.addRelations(relatedModuleRecordId).then(
				function (data) {
					var summaryViewContainer = thisInstance.getContentHolder();
					var updatesWidget = summaryViewContainer.find("[data-type='Updates']");
					if (updatesWidget.length > 0) {
						var params = thisInstance.getFiltersData(updatesWidget);
						updatesWidget.find('.btnChangesReviewedOn').parent().remove();
						thisInstance.loadWidget(updatesWidget, params['params']);
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
		var thisInstance = this;
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
								var relatedController = new Vtiger_RelatedList_Js(recordId, module, thisInstance.getSelectedTab(), referenceModuleName);
								relatedController.registerUnreviewedCountEvent(widgetDataContainer);
							}
					);
				}
		)
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
	registerEventForRelatedTabClick: function () {
		var thisInstance = this;
		var detailContentsHolder = thisInstance.getContentHolder();
		var detailContainer = detailContentsHolder.closest('div.detailViewInfo');

		jQuery('.related', detailContainer).on('click', 'li', function (e, urlAttributes) {
			var tabElement = jQuery(e.currentTarget);
			if (!tabElement.hasClass('dropdown')) {
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
							app.registerMoreContent(detailContentsHolder.find('button.moreBtn'));
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
							element.progressIndicator({'mode': 'hide'});
						}
				);
			}
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
					thisInstance.reloadTabContent();
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
				thisInstance.reloadTabContent();
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
			thisInstance.reloadTabContent();
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
	 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
	 */
	registerRelatedModulesRecordCount: function (tabContainer) {
		var thisInstance = this;
		if (!jQuery.isFunction(thisInstance.refreshRelatedList)) {
			thisInstance = new Vtiger_Detail_Js();
		}
		var moreList = $('.related .nav .dropdown-menu');
		var relationContainer = tabContainer;
		if (!relationContainer || (typeof relationContainer.length == 'undefined'))
			relationContainer = $('.related .nav > .relatedNav, .related .nav > .mainNav');
		relationContainer.each(function (n, item) {
			if ($(item).data('count') == '1') {
				var params = {
					module: app.getModuleName(),
					action: 'RelationAjax',
					record: app.getRecordId(),
					relatedModule: $(item).data('reference'),
					mode: 'getRelatedListPageCount',
					tab_label: $(item).data('label-key'),
				}
				AppConnector.request(params).then(function (response) {
					if (response.success) {
						$(item).find('.count').text(response.result.numberOfRecords);
						moreList.find('[data-reference="' + $(item).data('reference') + '"] .count').text(response.result.numberOfRecords);
						thisInstance.refreshRelatedList();
					}
				});
			}
		});
	},
	/**
	 * Function to display a new comments
	 */
	addComment: function (currentTarget, data) {
		var thisInstance = this;
		var mode = currentTarget.data('mode');
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
					jQuery('<ul class="liStyleNone"><li class="commentDetails">' + data + '</li></ul>').prependTo(closestAddCommentBlock.closest('.contents').find('.commentsList'));
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
		});
		detailContentsHolder.on('click', '.editComment', function (e) {
			thisInstance.removeCommentBlockIfExists();
			var currentTarget = jQuery(e.currentTarget);
			var commentInfoBlock = currentTarget.closest('.singleComment');
			var commentInfoContent = commentInfoBlock.find('.commentInfoContent');
			var commentReason = commentInfoBlock.find('[name="editReason"]');
			var editCommentBlock = thisInstance.getEditCommentBlock();
			editCommentBlock.find('.commentcontent').val(commentInfoContent.text());
			editCommentBlock.find('[name="reasonToEdit"]').val(commentReason.text());
			commentInfoContent.hide();
			commentInfoBlock.find('.commentActionsContainer').hide();
			editCommentBlock.appendTo(commentInfoBlock).show();
		});
		detailContentsHolder.on('click', '.deleteComment', function (e) {
			thisInstance.removeCommentBlockIfExists();
			var currentTarget = jQuery(e.currentTarget);
			var commentInfoBlock = currentTarget.closest('.singleComment');
			var commentInfoHeader = commentInfoBlock.find('.commentInfoHeader');
			var recordId = commentInfoHeader.data('commentid');
			var deleteUrl = "index.php?module=ModComments&action=DeleteAjax&record=" + recordId;
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
									thisInstance.reloadTabContent();
									var recentCommentsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentCommentsTabLabel);
									var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), recentCommentsTab, 'ModComments');
									relatedController.deleteRelation([recordId]);
									thisInstance.registerRelatedModulesRecordCount(recentCommentsTab);
								} else {
									Vtiger_Helper_Js.showPnotify(data.error.message);
								}
							});
				}, function (error, err) {
					app.errorLog(error, err);
				});
			}
		});
		detailContentsHolder.on('click', '.detailViewSaveComment', function (e) {
			var element = jQuery(e.currentTarget);
			if (!element.is(":disabled")) {
				thisInstance.saveComment(e).then(function () {
					thisInstance.registerRelatedModulesRecordCount();
					var commentsContainer = detailContentsHolder.find("[data-type='Comments']");
					thisInstance.loadWidget(commentsContainer).then(function () {
						element.removeAttr('disabled');
					});
				}, function (error, err) {
					element.removeAttr('disabled');
					app.errorLog(error, err);
				});
			}
		});
		detailContentsHolder.on('click', '.saveComment', function (e) {
			var element = jQuery(e.currentTarget);
			if (!element.is(":disabled")) {
				thisInstance.saveComment(e).then(function (data) {
					var recentCommentsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentCommentsTabLabel);
					thisInstance.registerRelatedModulesRecordCount(recentCommentsTab);
					thisInstance.addComment(element, data);
					element.removeAttr('disabled');
				}, function (error, err) {
					element.removeAttr('disabled');
					app.errorLog(error, err);
				});
			}
		});
		detailContentsHolder.on('click', '.moreRecentComments', function () {
			var recentCommentsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentCommentsTabLabel);
			recentCommentsTab.trigger('click');
		});
		detailContentsHolder.find('.commentsHierarchy').change(function (e) {
			var recentCommentsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentCommentsTabLabel);
			var url = recentCommentsTab.data('url');
			var regex = /&hierarchy=+([\w,]+)/;
			url = url.replace(regex, "");
			if ($(this).val()) {
				url += '&hierarchy=' + $(this).val();
			}
			recentCommentsTab.data('url', url);
			recentCommentsTab.trigger('click');
		});
		detailContentsHolder.find('.commentSearch').keyup(function (e) {
			var text = $(this).val();
			if (text) {
				detailContentsHolder.find('.commentDetails').addClass('hide');
				var contains = detailContentsHolder.find(".commentRelatedTitle:contains(" + text + ")");
				contains.each(function (e) {
					$(this).closest('.commentDetails').removeClass('hide');
				});
				if (contains.length == 0) {
					detailContentsHolder.find('.noCommentsMsgContainer').removeClass('hide');
				}
			} else {
				detailContentsHolder.find('.commentDetails').removeClass('hide');
				detailContentsHolder.find('.noCommentsMsgContainer').addClass('hide');
			}
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
				Vtiger_Index_Js.registerMailButtons(data);
				data.find('.expandAllMails').click();
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
			Vtiger_Index_Js.registerMailButtons(container);
			container.find('.showMailModal').click(function (e) {
				var progressIndicatorElement = jQuery.progressIndicator();
				var url = $(e.currentTarget).data('url') + '&noloadlibs=1';
				app.showModalWindow("", url, function (data) {
					Vtiger_Index_Js.registerMailButtons(data);
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
				});
			});
		});
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
	registerEmailEvents: function (detailContentsHolder) {
		Vtiger_Index_Js.registerMailButtons(detailContentsHolder);
	},
	registerMapsEvents: function (container) {
		var coordinates = container.find('#coordinates').val();
		if (container.find('#coordinates').length) {
			var mapView = new OpenStreetMap_Map_Js();
			mapView.registerDetailView(container);
		}
	},
	registerBasicEvents: function () {
		var thisInstance = this;
		var detailContentsHolder = thisInstance.getContentHolder();
		var selectedTabElement = thisInstance.getSelectedTab();
		//register all the events for summary view container
		thisInstance.registerSummaryViewContainerEvents(detailContentsHolder);
		thisInstance.registerCommentEvents(detailContentsHolder);
		thisInstance.registerEmailEvents(detailContentsHolder);
		thisInstance.registerMapsEvents(detailContentsHolder);
		app.registerEventForDatePickerFields(detailContentsHolder);
		//Attach time picker event to time fields
		app.registerEventForClockPicker();
		app.showSelect2ElementView(detailContentsHolder.find('select.select2'));
		detailContentsHolder.on('click', '#detailViewNextRecordButton', function (e) {
			var url = selectedTabElement.data('url');
			var currentPageNum = thisInstance.getRelatedListCurrentPageNum();
			var requestedPage = parseInt(currentPageNum) + 1;
			var nextPageUrl = url + '&page=' + requestedPage;
			thisInstance.loadContents(nextPageUrl);
		});

		detailContentsHolder.on('click', '#detailViewPreviousRecordButton', function (e) {
			var url = selectedTabElement.data('url');
			var currentPageNum = thisInstance.getRelatedListCurrentPageNum();
			var requestedPage = parseInt(currentPageNum) - 1;
			var params = {};
			var nextPageUrl = url + '&page=' + requestedPage;
			thisInstance.loadContents(nextPageUrl);
		});

		detailContentsHolder.on('click', 'div.detailViewTable div.fieldValue', function (e) {
			if (jQuery(e.target).closest('a').hasClass('btnNoFastEdit'))
				return;
			var currentTdElement = jQuery(e.currentTarget);
			thisInstance.ajaxEditHandling(currentTdElement);
		});

		detailContentsHolder.on('click', 'div.recordDetails span.squeezedWell', function (e) {
			var currentElement = jQuery(e.currentTarget);
			var relatedLabel = currentElement.data('reference');
			jQuery('.detailViewInfo .related .nav > li[data-reference="' + relatedLabel + '"]').trigger("click");
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
		detailContentsHolder.on('change', '.relatedHistoryTypes', function (e) {
			var widgetContent = jQuery(this).closest('.widgetContentBlock').find('.widgetContent');
			var types = jQuery(e.currentTarget).val();
			var pageLimit = widgetContent.find("#relatedHistoryPageLimit").val();
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					'enabled': true,
					'elementToBlock': widgetContent
				}
			});
			var params = {
				module: app.getModuleName(),
				view: 'Detail',
				record: app.getRecordId(),
				mode: 'showRecentRelation',
				page: 1,
				limit: pageLimit,
				type: types,
			};
			AppConnector.request(params).then(
					function (data) {
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
						widgetContent.find("#relatedHistoryCurrentPage").remove();
						widgetContent.find("#moreRelatedUpdates").remove();
						widgetContent.html(data);
						Vtiger_Index_Js.registerMailButtons(widgetContent);
					}
			);
		});
		detailContentsHolder.on('click', '.moreProductsService', function () {
			jQuery('.related .mainNav[data-reference="ProductsAndServices"]:not(.hide)').trigger('click');
		});
		detailContentsHolder.on('click', '.moreRelatedUpdates', function () {
			var widgetContainer = jQuery(this).closest('.widgetContentBlock');
			var widgetContent = widgetContainer.find('.widgetContent');
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					'enabled': true,
					'elementToBlock': widgetContent
				}
			});
			var currentPage = widgetContent.find("#relatedHistoryCurrentPage").val();
			var nextPage = parseInt(currentPage) + 1;
			var types = widgetContainer.find(".relatedHistoryTypes").val();
			var pageLimit = widgetContent.find("#relatedHistoryPageLimit").val();
			var params = {
				module: app.getModuleName(),
				view: 'Detail',
				record: app.getRecordId(),
				mode: 'showRecentRelation',
				page: nextPage,
				limit: pageLimit,
				type: types,
			};
			AppConnector.request(params).then(
					function (data) {
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
						widgetContent.find("#relatedHistoryCurrentPage").remove();
						widgetContent.find("#moreRelatedUpdates").remove();
						widgetContent.find('#relatedUpdates').append(data);
					}
			);
		});

		detailContentsHolder.on('click', '.moreRecentUpdates', function (e) {
			var container = $(e.currentTarget).closest('.recentActivitiesContainer');
			var newChangeInput = container.find('#newChange');
			var newChange = newChangeInput.val();
			var currentPage = container.find('#updatesCurrentPage').val();
			var nextPage = parseInt(currentPage) + 1;
			if (container.closest('.summaryWidgetContainer').length) {
				var data = thisInstance.getFiltersData(e, {'page': nextPage, 'tab_label': 'LBL_UPDATES', 'newChange': newChange}, container.find('#updates'));
				var url = data['params'];
			} else {
				var url = thisInstance.getTabByLabel(thisInstance.detailViewRecentUpdatesTabLabel).data('url');
				url = url.replace('&page=1', '&page=' + nextPage) + '&skipHeader=true&newChange=' + newChange;
				if (url.indexOf('&whereCondition') == -1) {
					var switchBtn = jQuery('.recentActivitiesSwitch');
					url += '&whereCondition=' + (switchBtn.prop('checked') ? switchBtn.data('on-val') : switchBtn.data('off-val'));
				}
			}
			AppConnector.request(url).then(
					function (data) {
						var dataContainer = jQuery(data);
						container.find('#newChange').val(dataContainer.find('#newChange').val());
						container.find('#updatesCurrentPage').val(dataContainer.find('#updatesCurrentPage').val());
						container.find('#moreLink').html(dataContainer.find('#moreLink').html());
						container.find('#updates ul').append(dataContainer.find('#updates ul').html());
						app.registerMoreContent(container.find('button.moreBtn'));
					}
			);
		});
		detailContentsHolder.on('click', '.btnChangesReviewedOn', function (e) {
			var progressInstance = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			var url = 'index.php?module=ModTracker&action=ChangesReviewedOn&record=' + app.getRecordId();
			AppConnector.request(url).then(
					function (data) {
						progressInstance.progressIndicator({mode: 'hide'});
						jQuery(e.currentTarget).parent().remove();
						thisInstance.getTabByLabel(thisInstance.detailViewRecentUpdatesTabLabel).find('.count.badge').text('');
						if (selectedTabElement.data('labelKey') == thisInstance.detailViewRecentUpdatesTabLabel) {
							thisInstance.reloadTabContent();
						} else if (selectedTabElement.data('linkKey') == thisInstance.detailViewSummaryTabLabel) {
							var updatesWidget = detailContentsHolder.find("[data-type='Updates']");
							if (updatesWidget.length > 0) {
								var params = thisInstance.getFiltersData(updatesWidget);
								thisInstance.loadWidget(updatesWidget, params['params']);
							}
						}
					}
			);
		});

		detailContentsHolder.on('click', '.moreRecentDocuments', function () {
			var recentDocumentsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentDocumentsTabLabel);
			recentDocumentsTab.trigger('click');
		});

		detailContentsHolder.find('.widgetContentBlock[data-name="Calendar"] .widget_contents').on(thisInstance.widgetPostLoad, function (e) {
			var container = $(e.currentTarget).closest('.activityWidgetContainer');
			thisInstance.reloadWidgetActivitesStats(container);
		});
		detailContentsHolder.on('click', '.moreRecentActivities', function (e) {
			var currentTarget = $(e.currentTarget);
			currentTarget.prop('disabled', true);
			var container = currentTarget.closest('.activityWidgetContainer');
			var page = container.find('.currentPage').val();
			page++;
			var url = container.find('.widgetContentBlock').data('url');
			url = url.replace('&page=1', '&page=' + page);
			url += '&totalCount=' + container.find('.totaltActivities').val();
			AppConnector.request(url).then(
					function (data) {
						currentTarget.prop('disabled', false);
						currentTarget.addClass('hide');
						var currentPage = container.find('.currentPage').val();
						container.find('.currentPage').remove();
						container.find('.countActivities').remove();
						container.find('.widget_contents').append(data);
						container.find('.countActivities').val(parseInt(container.find('.countActivities').val()) + currentPage * parseInt(container.find('.pageLimit').val()));
						thisInstance.reloadWidgetActivitesStats(container);
						app.showPopoverElementView(container.find('.popoverTooltip'));
					}
			);
		});
		detailContentsHolder.on('click', '.widgetFullscreen', function (e) {
			var currentTarget = $(e.currentTarget);
			var widgetContentBlock = currentTarget.closest('.widgetContentBlock');
			var url = widgetContentBlock.data('url');
			url = url.replace('&view=Detail&', '&view=WidgetFullscreen&');
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			app.showModalWindow(null, "index.php?" + url, function (modal) {
				progressIndicatorElement.progressIndicator({mode: 'hide'});
			});
		});
		detailContentsHolder.off('switchChange.bootstrapSwitch').on('switchChange.bootstrapSwitch', '.relatedContainer .switchBtn', function (e, state) {
			var recentActivitiesTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentActivitiesTabLabel);
			var url = recentActivitiesTab.data('url');
			url = url.replace('&time=current', '');
			url = url.replace('&time=history', '');
			url += '&time=';
			if (state) {
				url += 'current';
			} else {
				url += 'history';
			}

			recentActivitiesTab.data('url', url);
			recentActivitiesTab.trigger('click');
		});
		detailContentsHolder.find('.widgetContentBlock[data-type="HistoryRelation"] .widget_contents').on(thisInstance.widgetPostLoad, function (e) {
			thisInstance.registerEmailEvents($(e.currentTarget));
		});
		thisInstance.registerEventForRelatedList();
		thisInstance.registerEventForRelatedListPagination();
		thisInstance.registerBlockAnimationEvent();
		thisInstance.registerMailPreviewWidget(detailContentsHolder.find('.widgetContentBlock[data-type="EmailList"]'));
		thisInstance.registerMailPreviewWidget(detailContentsHolder.find('.widgetContentBlock[data-type="HistoryRelation"]'));
		detailContentsHolder.on('switchChange.bootstrapSwitch', '.recentActivitiesSwitch.switchBtn', function (e, state) {
			var currentTarget = jQuery(e.currentTarget);
			var tabElement = thisInstance.getTabByLabel(thisInstance.detailViewRecentUpdatesTabLabel);
			var url = tabElement.data('url');
			var variableName = currentTarget.data('urlparams');
			var valueOn = currentTarget.data('on-val');
			var valueOff = currentTarget.data('off-val');
			url = url.replace('&' + variableName + '=' + valueOn, '').replace('&' + variableName + '=' + valueOff, '');
			if (state) {
				url += '&' + variableName + '=' + valueOn;
			} else {
				url += '&' + variableName + '=' + valueOff;
			}
			tabElement.data('url', url);
			tabElement.trigger('click');
		});
	},
	reloadWidgetActivitesStats: function (container) {
		var countElement = container.find('.countActivities');
		var totalElement = container.find('.totaltActivities');
		if (!countElement.length || !totalElement.length) {
			return false;
		}
		var stats = ' (' + countElement.val() + '/' + totalElement.val() + ')';
		var switchBtn = container.find('.switchBtn');
		if (switchBtn.prop('checked')) {
			var text = switchBtn.data('basic-texton') + stats;
			switchBtn.data('on-text', text);
		} else {
			var text = switchBtn.data('basic-textoff') + stats;
			switchBtn.data('off-text', text);
		}
		switchBtn.bootstrapSwitch('destroy');
		switchBtn.bootstrapSwitch();
	},
	refreshRelatedList: function () {
		var container = jQuery('.related');
		var moreBtn = container.find('.dropdown');
		var moreList = container.find('.nav .dropdown-menu');
		var margin = 3;
		var widthScroll = 15;
		var totalWidth = container.width();
		var mainNavWidth = 0;
		var freeSpace = 0;
		moreBtn.removeClass('hide');
		var widthMoreBtn = moreBtn.width() + margin;
		moreBtn.addClass('hide');
		freeSpace = totalWidth - widthMoreBtn - widthScroll;
		container.find('.nav > .mainNav').each(function (e) {
			jQuery(this).removeClass('hide');
			if (freeSpace > jQuery(this).width()) {
				moreList.find('[data-reference="' + jQuery(this).data('reference') + '"]').addClass('hide');
				freeSpace -= Math.ceil(jQuery(this).width()) + margin;
			} else {
				jQuery(this).addClass('hide');
				moreList.find('[data-reference="' + jQuery(this).data('reference') + '"]').removeClass('hide');
				freeSpace = 0;
			}
		});
		if (freeSpace === 0) {
			moreList.find('.relatedNav').removeClass('hide');
			container.find('.spaceRelatedList').addClass('hide');
			moreBtn.removeClass('hide');
		} else {
			freeSpace -= container.find('.spaceRelatedList').removeClass('hide').width() + margin;
			container.find('.nav > .relatedNav').each(function () {
				jQuery(this).removeClass('hide');
				if (freeSpace > jQuery(this).width()) {
					moreList.find('[data-reference="' + jQuery(this).data('reference') + '"]').addClass('hide');
					freeSpace -= Math.ceil(jQuery(this).width()) + margin;
				} else {
					freeSpace = 0;
					jQuery(this).addClass('hide');
					moreList.find('[data-reference="' + jQuery(this).data('reference') + '"]').removeClass('hide');
				}
			});
			if (freeSpace === 0) {
				moreBtn.removeClass('hide');
			}
		}
	},
	refreshCommentContainer: function (commentId) {
		var thisInstance = this;
		var commentContainer = $('.commentsBody');
		var params = {
			module: app.getModuleName(),
			view: 'Detail',
			record: thisInstance.getRecordId(),
			mode: 'showThreadComments',
			commentid: commentId
		}
		var progressIndicatorElement = jQuery.progressIndicator({
			position: 'html',
			'blockInfo': {
				'enabled': true,
				'elementToBlock': commentContainer
			}
		});
		AppConnector.request(params).then(function (data) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			commentContainer.html(data);
		});
	},
	updateRecordsPDFTemplateBtn: function (form) {
		var params = {};
		params.data = {
			module: 'Vtiger',
			action: 'PDF',
			mode: 'hasValidTemplate',
			record: app.getRecordId(),
			modulename: app.getModuleName(),
			view: app.getViewName()
		};
		params.dataType = 'json';
		AppConnector.request(params).then(
				function (data) {
					var response = data['result'];
					var btnToolbar = jQuery('.detailViewToolbar .btn-toolbar');
					if (response.valid == false) {
						var btn = btnToolbar.find('.btn-group:eq(1) [href*="showPdfModal"]');
						if (btn.length) {
							btn.remove();
						}
					} else {
						var btnGroup = btnToolbar.find('.btn-group:eq(1)');
						var btn = btnToolbar.find('.btn-group:eq(1) [href*="showPdfModal"]');
						if (btn.length == 0) {
							btnGroup.append('<a class="btn btn-default popoverTooltip" href=\'javascript:Vtiger_Header_Js.getInstance().showPdfModal("index.php?module=' + app.getModuleName() + '&view=PDF&fromview=Detail&record=' + app.getRecordId() + '");\' data-content="' + app.vtranslate('LBL_EXPORT_PDF') + '" data-original-title="" title=""><span class="glyphicon glyphicon-save-file icon-in-button"></span></a>');
						}
					}
				},
				function (data, err) {
					app.errorLog(data, err);
				}
		);
	},
	registerEvents: function () {
		var thisInstance = this;
		thisInstance.refreshRelatedList();
		//thisInstance.triggerDisplayTypeEvent();
		this.registerHelpInfo();
		thisInstance.registerSendSmsSubmitEvent();
		thisInstance.registerAjaxEditEvent();
		this.registerRelatedRowClickEvent();
		this.registerBlockStatusCheckOnLoad();
		this.registerEmailFieldClickEvent();
		this.registerPhoneFieldClickEvent();
		this.registerEventForRelatedTabClick();
		Vtiger_Helper_Js.showHorizontalTopScrollBar();
		this.registerUrlFieldClickEvent();

		var detailViewContainer = jQuery('div.detailViewContainer');
		if (detailViewContainer.length <= 0) {
			// Not detail view page
			return;
		}
		this.registerBasicEvents();
		this.registerSetReadRecord(detailViewContainer);
		thisInstance.registerEventForPicklistDependencySetup(thisInstance.getForm());

		thisInstance.getForm().validationEngine(app.validationEngineOptionsForRecord);
		thisInstance.loadWidgets();

		this.registerEventForTotalRecordsCount();
	}
});
