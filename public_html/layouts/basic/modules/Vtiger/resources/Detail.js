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
	getInstance: function () {
		if (Vtiger_Detail_Js.detailInstance == false) {
			var module = app.getModuleName();
			var view = app.getViewName();
			var moduleClassName = module + "_" + view + "_Js";
			var fallbackClassName = Vtiger_Detail_Js;
			if (typeof window[moduleClassName] !== "undefined") {
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
		var selectedIds = [];
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

		AppConnector.request(actionParams).then(function (data) {
			if (data) {
				app.showModalWindow(data, {'text-align': 'left'});
				if (typeof callBackFunction == 'function') {
					callBackFunction(data);
				}
			}
		}, function (error, err) {
		});
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
		AppConnector.request(actionParams).then(function (data) {
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
						App.Fields.Picklist.changeSelectElementView(selectElement, 'select2');
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
		AppConnector.request(params).then(function (data) {
				if (data.success) {
					app.hideModalWindow();
					var params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('JS_RECORDS_TRANSFERRED_SUCCESSFULLY'),
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
			data.find('[type="submit"]').on('click', function (e) {
				var ids = [];
				data.find('input[type="checkbox"]:checked').each(function (index) {
					ids.push($(this).val());
				});
				if (ids.length == 0) {
					var params = {
						title: app.vtranslate('JS_INFORMATION'),
						text: app.vtranslate('JS_NOT_SELECTED_WORKFLOW_TRIGGER'),
						type: 'error',
					};
					Vtiger_Helper_Js.showPnotify(params);
				} else {
					var params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('JS_STARTED_PERFORM_WORKFLOW'),
						type: 'info',
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
					AppConnector.request(postData).then(function (data) {
							var params = {
								title: app.vtranslate('JS_MESSAGE'),
								text: app.vtranslate('JS_COMPLETED_PERFORM_WORKFLOW'),
								type: 'success',
							};
							Vtiger_Helper_Js.showPnotify(params);
							app.hideModalWindow();
							detailInstance.loadWidgets();
						}, function (error, err) {
							var params = {
								title: app.vtranslate('JS_ERROR'),
								text: app.vtranslate('JS_ERROR_DURING_TRIGGER_OF_WORKFLOW'),
								type: 'error',
							};
							Vtiger_Helper_Js.showPnotify(params);
							app.hideModalWindow();
						}
					);
				}
			});
		}
		AppConnector.request(params).then(function (data) {
			if (data) {
				app.showModalWindow(data, '', callback);
			}
		}, function (error, err) {
		});
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
	//Filels list on updation of which we need to upate the detailview header
	updatedFields: ['company', 'designation', 'title'],
	//Event that will triggered before saving the ajax edit of fields
	fieldPreSave: 'Vtiger.Field.PreSave',
	tempData: [],
	referenceFieldNames: {
		'Calendar': {
			'Accounts': 'link',
			'Leads': 'link',
			'Vendors': 'link',
			'OSSEmployees': 'link',
			'Contacts': 'linkextend',
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
		var contentContainer = jQuery('.js-detail-widget-content', widgetContainer);
		if (widgetContainer.find('[name="relatedModule"]').length) {
			var relatedModuleName = widgetContainer.find('[name="relatedModule"]').val();
		} else {
			var relatedModuleName = widgetContainer.data('name');
		}
		if (params == undefined) {
			var urlParams = widgetContainer.data('url');
			if (urlParams == undefined) {
				return;
			}
			var queryParameters = urlParams.split('&');
			var keyValueMap = {};
			for (var index = 0; index < queryParameters.length; index++) {
				var queryParam = queryParameters[index];
				var queryParamComponents = queryParam.split('=');
				keyValueMap[queryParamComponents[0]] = queryParamComponents[1];
			}
			params = keyValueMap;
		}
		var aDeferred = $.Deferred();
		contentContainer.progressIndicator({});
		AppConnector.request({
			type: 'POST',
			async: false,
			dataType: 'html',
			data: params
		}).then(function (data) {
			contentContainer.progressIndicator({mode: 'hide'});
			contentContainer.html(data);
			App.Fields.Picklist.showSelect2ElementView(widgetContainer.find('.select2'));
			app.showPopoverElementView(contentContainer.find('.js-popover-tooltip'));
			app.registerModal(contentContainer);
			app.registerMoreContent(contentContainer.find('button.moreBtn'));
			if (relatedModuleName) {
				var relatedController = Vtiger_RelatedList_Js.getInstance(thisInstance.getRecordId(), app.getModuleName(), thisInstance.getSelectedTab(), relatedModuleName);
				relatedController.setRelatedContainer(contentContainer);
				relatedController.registerRelatedEvents();
				thisInstance.widgetRelatedRecordView(widgetContainer, true);
			}
			app.event.trigger("DetailView.Widget.AfterLoad", contentContainer, relatedModuleName, thisInstance);
			aDeferred.resolve(params);
		}, function (e) {
			contentContainer.progressIndicator({mode: 'hide'});
			aDeferred.reject();
		});
		return aDeferred.promise();
	},
	widgetRelatedRecordView: function (container, load) {
		var cacheKey = this.getRecordId() + '_' + container.data('id');
		var relatedRecordCacheID = app.moduleCacheGet(cacheKey);
		if (relatedRecordCacheID !== null) {
			var newActive = container.find(".js-carousel-item[data-id = '" + relatedRecordCacheID + "']");
			if (newActive.length) {
				container.find('.js-carousel-item.active').removeClass('active');
				container.find(".js-carousel-item[data-id = '" + relatedRecordCacheID + "']").addClass('active');
			}
		}
		var controlBox = container.find('.control-widget');
		var prev = controlBox.find('.prev');
		var next = controlBox.find('.next');
		var active = container.find('.js-carousel-item.active');
		if (container.find('.js-carousel-item').length <= 1 || !active.next().length) {
			next.addClass('disabled');
		} else {
			next.removeClass('disabled');
		}
		if (container.find('.js-carousel-item').length <= 1 || !active.prev().length) {
			prev.addClass('disabled');
		} else {
			prev.removeClass('disabled');
		}
		if (load) {
			next.on('click', function () {
				if ($(this).hasClass('disabled')) {
					return;
				}
				var active = container.find('.js-carousel-item.active');
				active.removeClass('active');
				var nextElement = active.next();
				nextElement.addClass('active');
				if (!nextElement.next().length) {
					next.addClass('disabled');
				}
				if (active.prev()) {
					prev.removeClass('disabled');
				}
				app.moduleCacheSet(cacheKey, nextElement.data('id'));
			});
			prev.on('click', function () {
				if ($(this).hasClass('disabled')) {
					return;
				}
				var active = container.find('.js-carousel-item.active');
				active.removeClass('active');
				var prevElement = active.prev();
				prevElement.addClass('active');
				if (!prevElement.prev().length) {
					prev.addClass('disabled');
				}
				if (active.next()) {
					next.removeClass('disabled');
				}
				app.moduleCacheSet(cacheKey, prevElement.data('id'));
			});
		}
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
		if (typeof data !== "undefined") {
			params = {};
			params.url = url;
			params.data = data;
		}
		AppConnector.requestPjax(params).then(function (responseData) {
			detailContentsHolder.html(responseData);
			responseData = detailContentsHolder.html();
			//thisInstance.triggerDisplayTypeEvent();
			thisInstance.registerBlockStatusCheckOnLoad();
			//Make select box more usability
			App.Fields.Picklist.changeSelectElementView(detailContentsHolder);
			//Attach date picker event to date fields
			App.Fields.Date.register(detailContentsHolder);
			thisInstance.getForm().validationEngine();
			app.event.trigger("DetailView.LoadContents.AfterLoad", responseData);
			aDeferred.resolve(responseData);
		});
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
	getTabByModule: function (moduleName) {
		var tabs = this.getTabs();
		var targetTab = false;
		tabs.each(function (index, element) {
			var tab = jQuery(element);
			if (tab.data('reference') == moduleName) {
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
		return tabContainer.find('.nav li.active:not(.d-none)');
	},
	getTabContainer: function () {
		return jQuery('div.related');
	},
	getTabs: function () {
		var topTabs = this.getTabContainer().find('li.baseLink:not(.d-none)');
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
		if (typeof fieldDetailList !== "undefined") {
			data = fieldDetailList;
		}
		data['record'] = recordId;
		data['module'] = app.getModuleName();
		data['action'] = 'SaveAjax';

		var params = {};
		params.data = data;
		params.async = false;
		params.dataType = 'json';
		AppConnector.request(params).then(function (reponseData) {
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
		AppConnector.request(url).then(function (data) {
			aDeferred.resolve(data);
		}, function (error, err) {
		})
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
		AppConnector.request(postData).then(function (data) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			if (commentMode == 'add') {
				thisInstance.addRelationBetweenRecords('ModComments', data.result.id, thisInstance.getTabByLabel(thisInstance.detailViewRecentCommentsTabLabel))
			}
			app.event.trigger("DetailView.SaveComment.AfterAjax", commentInfoBlock, postData, data);
			aDeferred.resolve(data);
		}, function (textStatus, errorThrown) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			element.removeAttr('disabled');
			aDeferred.reject(textStatus, errorThrown);
		});
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
		AppConnector.request(postData).then(function (data) {
			aDeferred.resolve(data);
		}, function (error, err) {
		});
		return aDeferred.promise();
	},
	/**
	 * function to return cloned add comment block
	 * return jQuery Obj.
	 */
	getCommentBlock: function () {
		var detailContentsHolder = this.getContentHolder();
		var clonedCommentBlock = jQuery('.basicAddCommentBlock', detailContentsHolder).clone(true, true).removeClass('basicAddCommentBlock d-none').addClass('addCommentBlock');
		clonedCommentBlock.find('.commentcontenthidden').removeClass('commentcontenthidden').addClass('commentcontent');
		return clonedCommentBlock;
	},
	/**
	 * function to return cloned edit comment block
	 * return jQuery Obj.
	 */
	getEditCommentBlock: function () {
		var detailContentsHolder = this.getContentHolder();
		var clonedCommentBlock = jQuery('.basicEditCommentBlock', detailContentsHolder).clone(true, true).removeClass('basicEditCommentBlock d-none').addClass('addCommentBlock');
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
		AppConnector.request(SendSmsUrl).then(function (data) {
			app.hideModalWindow();
			progressInstance.progressIndicator({
				'mode': 'hide'
			});
		}, function (error, err) {
		});
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
			if (typeof recordUrl !== "undefined") {
				window.location.href = recordUrl;
			}
		});

	},
	loadRelatedList: function (params) {
		var aDeferred = jQuery.Deferred();
		if (params == undefined) {
			params = {};
		}
		var relatedListInstance = Vtiger_RelatedList_Js.getInstance(this.getRecordId(), app.getModuleName(), this.getSelectedTab(), this.getRelatedModuleName());
		relatedListInstance.loadRelatedList(params).then(function (data) {
				aDeferred.resolve(data);
			}, function (textStatus, errorThrown) {
				aDeferred.reject(textStatus, errorThrown);
			}
		);
		return aDeferred.promise();
	},
	/**
	 * Function to register Event for Sorting
	 */
	registerEventForRelatedList: function () {
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		var relatedModuleName = thisInstance.getRelatedModuleName();
		if (relatedModuleName) {
			var relatedController = Vtiger_RelatedList_Js.getInstance(thisInstance.getRecordId(), app.getModuleName(), thisInstance.getSelectedTab(), relatedModuleName);
			relatedController.setRelatedContainer(detailContentsHolder);
			relatedController.registerRelatedEvents();
		}
		detailContentsHolder.find('.detailViewBlockLink').each(function (n, block) {
			block = $(block);
			var blockContent = block.find('.blockContent');
			if (blockContent.is(':visible')) {
				AppConnector.request({
					type: 'GET',
					dataType: 'html',
					data: block.data('url')
				}).then(function (response) {
					blockContent.html(response);
					var relatedController = Vtiger_RelatedList_Js.getInstance(thisInstance.getRecordId(), app.getModuleName(), thisInstance.getSelectedTab(), block.data('reference'));
					relatedController.setRelatedContainer(blockContent);
					relatedController.registerRelatedEvents();
				});
			}
		});
		detailContentsHolder.find('.detailViewBlockLink .blockHeader').on('click', function () {
			var block = $(this).closest('.js-toggle-panel');
			var blockContent = block.find('.blockContent');
			var isEmpty = blockContent.is(':empty');
			if (!blockContent.is(':visible')) {
				blockContent.progressIndicator();
				AppConnector.request({
					type: 'GET',
					dataType: 'html',
					data: block.data('url')
				}).then(function (response) {
					blockContent.html(response);
					var relatedController = Vtiger_RelatedList_Js.getInstance(thisInstance.getRecordId(), app.getModuleName(), thisInstance.getSelectedTab(), block.data('reference'));
					relatedController.setRelatedContainer(blockContent);
					if (isEmpty) {
						relatedController.registerRelatedEvents();
					} else {
						relatedController.registerPostLoadEvents();
					}
				});
			}
		});
	},
	registerBlockAnimationEvent: function () {
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.find(".blockHeader").on('click', function () {
			var currentTarget = $(this).find(".js-block-toggle").not(".d-none");
			var blockId = currentTarget.data("id");
			var closestBlock = currentTarget.closest(".js-toggle-panel");
			var bodyContents = closestBlock.find(".blockContent");
			var data = currentTarget.data();
			var module = app.getModuleName();
			if (data.mode === "show") {
				bodyContents.addClass("d-none");
				app.cacheSet(module + "." + blockId, 0);
				currentTarget.addClass("d-none");
				closestBlock.find('[data-mode="hide"]').removeClass("d-none");
			} else {
				bodyContents.removeClass("d-none");
				app.cacheSet(module + "." + blockId, 1);
				currentTarget.addClass("d-none");
				closestBlock.find('[data-mode="show"]').removeClass("d-none");
			}
			app.event.trigger("DetailView.js-block-toggle.PostLoad", bodyContents, data, thisInstance);
		});
	},
	registerBlockStatusCheckOnLoad: function () {
		var blocks = this.getContentHolder().find('.blockHeader');
		var module = app.getModuleName();
		blocks.each(function (index, block) {
			var currentBlock = jQuery(block);
			var headerAnimationElement = currentBlock.find('.js-block-toggle').not('.d-none');
			var bodyContents = currentBlock.closest('.js-toggle-panel').find('.blockContent');
			var blockId = headerAnimationElement.data('id');
			var cacheKey = module + '.' + blockId;
			var value = app.cacheGet(cacheKey, null);
			if (value != null) {
				if (value == 1) {
					headerAnimationElement.addClass('d-none');
					currentBlock.find("[data-mode='show']").removeClass('d-none');
					bodyContents.removeClass('d-none');
				} else {
					headerAnimationElement.addClass('d-none');
					currentBlock.find("[data-mode='hide']").removeClass('d-none');
					bodyContents.addClass('d-none');
				}
			}
		});
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
		var actionElement = jQuery('.js-detail-quick-edit', currentTdElement);
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
			if (fieldElement.attr('data-inputmask')) {
				fieldElement.inputmask();
			}
			detailViewValue.addClass('d-none');
			actionElement.addClass('d-none');
			editElement.removeClass('d-none').children().filter('input[type!="hidden"]input[type!="image"],select').filter(':first').focus();
			var saveHandler = function (e) {
				thisInstance.registerNameAjaxEditEvent();
				var element = jQuery(e.target);
				if ((element.closest('.fieldValue').is(currentTdElement))) {
					return;
				}
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
				//If validation fails
				if (fieldElement.validationEngine('validate')) {
					if (fieldElement.attr('data-inputmask')) {
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
					editElement.addClass('d-none');
					detailViewValue.removeClass('d-none');
					actionElement.removeClass('d-none');
					readRecord.prop('disabled', false);
					editElement.off('clickoutside');
				} else {
					var preFieldSaveEvent = jQuery.Event(thisInstance.fieldPreSave);
					fieldElement.trigger(preFieldSaveEvent, {
						'fieldValue': fieldValue,
						'recordId': thisInstance.getRecordId()
					});
					if (preFieldSaveEvent.isDefaultPrevented()) {
						//Stop the save
						readRecord.prop('disabled', false);
						return;
					}
					currentTdElement.progressIndicator();
					editElement.addClass('d-none');
					var fieldNameValueMap = {};

					fieldNameValueMap["value"] = fieldValue;
					fieldNameValueMap["field"] = fieldName;
					fieldNameValueMap = thisInstance.getCustomFieldNameValueMap(fieldNameValueMap);
					thisInstance.saveFieldValues(fieldNameValueMap).then(function (response) {
							readRecord.prop('disabled', false);
							var postSaveRecordDetails = response.result;
							currentTdElement.progressIndicator({'mode': 'hide'});
							detailViewValue.removeClass('d-none');
							actionElement.removeClass('d-none');
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
								if (jQuery('.js-widget-general-info', thisInstance.getForm()).length > 0) {
									thisInstance.targetPicklist.find('.js-detail-quick-edit').trigger('click');
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
						}, function (error) {
							editElement.addClass('d-none');
							detailViewValue.removeClass('d-none');
							actionElement.removeClass('d-none');
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

			let customParams = {};
			customParams['sourceModule'] = module;
			customParams['sourceRecord'] = recordId;
			if (module != '' && referenceModuleName != '' && typeof thisInstance.referenceFieldNames[referenceModuleName] !== "undefined" && typeof thisInstance.referenceFieldNames[referenceModuleName][module] !== "undefined") {
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
				var widgetContainer = element.closest('.js-detail-widget');
				var widgetContentBlock = widgetContainer.find('.widgetContentBlock');
				var urlParams = widgetContentBlock.data('url');
				var params = {
					'type': 'GET',
					'dataType': 'html',
					'data': urlParams
				};
				AppConnector.request(params).then(function (data) {
						var activitiesWidget = widgetContainer.find('.js-detail-widget-content');
						activitiesWidget.html(data);
						App.Fields.Picklist.changeSelectElementView(activitiesWidget);
						thisInstance.registerEventForActivityWidget();
					}
				);
				thisInstance.loadWidgets();
			}



			var QuickCreateParams = {};
			QuickCreateParams['callbackPostShown'] = preQuickCreateSave;
			QuickCreateParams['callbackFunction'] = callbackFunction;
			QuickCreateParams['data'] = {...customParams};
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
			var summaryWidgetContainer = currentElement.closest('.js-detail-widget');
			var widgetDataContainer = summaryWidgetContainer.find('.js-detail-widget-content');
			var referenceModuleName = widgetDataContainer.find('[name="relatedModule"]').val();
			var quickcreateUrl = currentElement.data('url');
			var parentId = thisInstance.getRecordId();
			var quickCreateParams = {};
			var relatedField = currentElement.data('prf');
			var autoCompleteFields = currentElement.data('acf');
			var moduleName = currentElement.closest('.js-detail-widget-header').find('[name="relatedModule"]').val();
			var relatedParams = {};
			var postQuickCreateSave = function (data) {
				thisInstance.postSummaryWidgetAddRecord(data, currentElement);
				if (referenceModuleName == "ProjectTask") {
					thisInstance.loadModuleSummary();
				}
			}
			if (typeof relatedField !== "undefined") {
				relatedParams[relatedField] = parentId;
			}
			if (typeof autoCompleteFields !== "undefined") {
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
			let headerInstance;
			if (window !== window.parent) {
				headerInstance = window.parent.Vtiger_Header_Js.getInstance();
			} else {
				headerInstance = Vtiger_Header_Js.getInstance();
			}
			headerInstance.getQuickCreateForm(quickcreateUrl, moduleName, quickCreateParams).then(function (data) {
				headerInstance.handleQuickCreateData(data, quickCreateParams);
				progress.progressIndicator({'mode': 'hide'});
			});
		});
		$('.js-detail-widget button.selectRelation').on('click', function (e) {
			let summaryWidgetContainer = jQuery(e.currentTarget).closest('.js-detail-widget');
			let referenceModuleName = summaryWidgetContainer.find('.js-detail-widget-content [name="relatedModule"]').val();
			let restrictionsField = $(this).data('rf');
			let params = {
				module: referenceModuleName,
				src_module: app.getModuleName(),
				src_record: thisInstance.getRecordId(),
				multi_select: true
			};
			if (restrictionsField && Object.keys(restrictionsField).length > 0) {
				params['search_key'] = restrictionsField.key;
				params['search_value'] = restrictionsField.name;
			}
			app.showRecordsList(params, (modal, instance) => {
				instance.setSelectEvent((responseData) => {
					thisInstance.addRelationBetweenRecords(referenceModuleName, Object.keys(responseData)).then(function (data) {
						thisInstance.loadWidget(summaryWidgetContainer.find('.widgetContentBlock'));
					});
				});
			});
		});
	},
	registerAddingInventoryRecords: function () {
		var thisInstance = this;
		jQuery('.createInventoryRecordFromFilter').on('click', function (e) {
			var currentElement = jQuery(e.currentTarget);
			var createUrl = currentElement.data('url');
			var autoCompleteFields = currentElement.data('acf');
			var addidtionalParams = ''
			if (typeof autoCompleteFields !== "undefined") {
				$.each(autoCompleteFields, function (index, value) {
					addidtionalParams = '&' + index + '=' + value;
					createUrl = createUrl.concat(addidtionalParams);

				});
			}
			window.location.href = createUrl;
		});
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
		var summaryWidgetContainer = currentElement.closest('.js-detail-widget');
		var widget = summaryWidgetContainer.find('.widgetContentBlock');
		var url = '&' + widget.data('url');
		var urlParams = {};
		var parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
			urlParams[key] = value;
		});
		var urlNewParams = [];
		summaryWidgetContainer.find('.js-detail-widget-header .js-switch').each(function (n, item) {
			var value = '';
			var element = jQuery(item);
			var name = element.data('urlparams');
			if (element.attr('type') == 'radio') {
				if (element.prop('checked')) {
					value = typeof element.data('on-val') !== "undefined" ? element.data('on-val') : element.data('off-val')
				}
			} else {
				var selectedFilter = element.find('option:selected').val();
				var fieldlable = element.data('fieldlable');
				var filter = element.data('filter');
				value = {};
				if (selectedFilter != fieldlable) {
					value = [[filter, 'e', selectedFilter]];
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
		jQuery('.js-switch').on('change', function (e, state) {
			thisInstance.getFiltersDataAndLoad(e);
		})
	},
	registerChangeSwitchForWidget: function (summaryViewContainer) {
		var thisInstance = this;
		summaryViewContainer.find('.activityWidgetContainer').on('change', '.js-switch', function (e) {
			var currentElement = jQuery(e.currentTarget);
			var summaryWidgetContainer = currentElement.closest('.js-detail-widget');
			var widget = summaryWidgetContainer.find('.widgetContentBlock');
			var url = widget.data('url');
			url = url.replace('&type=current', '').replace('&type=history', '');
			url += '&type=';
			if (typeof currentElement.data('on-val') !== "undefined") {
				summaryWidgetContainer.find('.ativitiesPagination').removeClass('d-none');
				url += 'current';
				url = url.replace('&sortorder=DESC', '&sortorder=ASC');
			} else if (typeof currentElement.data('off-val') !== "undefined") {
				summaryWidgetContainer.find('.ativitiesPagination').addClass('d-none');
				url += 'history';
				url = url.replace('&sortorder=ASC', '&sortorder=DESC');
			}
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
		this.registerAddingInventoryRecords();
		this.registerEmailEvent();
		/**
		 * Function to handle the ajax edit for summary view fields
		 */
		var formElement = thisInstance.getForm();
		summaryViewContainer.off('click').on('click', '.row .js-detail-quick-edit', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			currentTarget.addClass('d-none');
			var currentTdElement = currentTarget.closest('.fieldValue');
			thisInstance.ajaxEditHandling(currentTdElement);
		});
		/**
		 * Function to handle actions after ajax save in summary view
		 */
		summaryViewContainer.on(thisInstance.fieldUpdatedEvent, '.js-widget-general-info', function (e, params) {
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
			if (url && typeof url !== "undefined") {
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
			detailViewElement.addClass('d-none');
			editElement.removeClass('d-none').show();

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
				if (previousValue == ajaxEditNewValue) {
					editElement.addClass('d-none');
					detailViewElement.removeClass('d-none');
					currentTarget.show();
				} else {
					var errorExists = fieldElement.validationEngine('validate');
					//If validation fails
					if (errorExists) {
						Vtiger_Helper_Js.addClickOutSideEvent(currentDiv, callbackFunction);
						return;
					}
					currentDiv.progressIndicator();
					editElement.addClass('d-none');
					AppConnector.request({
						action: 'SaveAjax',
						record: activityId,
						field: fieldName,
						value: ajaxEditNewValue,
						module: moduleName,
						activitytype: activityType
					}).then(function (data) {
							currentDiv.progressIndicator({'mode': 'hide'});
							detailViewElement.removeClass('d-none');
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
			thisInstance.getTabs().filter('[data-link-key="' + thisInstance.detailViewDetailsTabLabel + '"]:not(.d-none)').trigger('click');
		});

		/*
		 * Register click event for add button in Related widgets
		 * to add record from widget
		 */
		jQuery('.createRecord').on('click', function (e) {
			var currentElement = jQuery(e.currentTarget);
			var summaryWidgetContainer = currentElement.closest('.js-detail-widget');
			var widgetHeaderContainer = summaryWidgetContainer.find('.js-detail-widget-header');
			var referenceModuleName = widgetHeaderContainer.find('[name="relatedModule"]').val();
			var recordId = thisInstance.getRecordId();
			var module = app.getModuleName();
			var customParams = {};
			customParams['sourceModule'] = module;
			customParams['sourceRecord'] = recordId;
			if (module != '' && referenceModuleName != '' && typeof thisInstance.referenceFieldNames[referenceModuleName] !== "undefined" && typeof thisInstance.referenceFieldNames[referenceModuleName][module] !== "undefined") {
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
		var relatedController = Vtiger_RelatedList_Js.getInstance(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModule);
		relatedController.addRelations(relatedModuleRecordId).then(function (data) {
				var summaryViewContainer = thisInstance.getContentHolder();
				var updatesWidget = summaryViewContainer.find("[data-type='Updates']");
				if (updatesWidget.length > 0) {
					var params = thisInstance.getFiltersData(updatesWidget);
					updatesWidget.find('.btnChangesReviewedOn').parent().remove();
					thisInstance.loadWidget(updatesWidget, params['params']);
				}
				aDeferred.resolve(data);
			}, function (textStatus, errorThrown) {
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
		var summaryWidgetContainer = currentElement.closest('.js-detail-widget');
		var widgetHeaderContainer = summaryWidgetContainer.find('.js-detail-widget-header');
		var referenceModuleName = widgetHeaderContainer.find('[name="relatedModule"]').val();
		var idList = [];
		idList.push(data.result._recordId);
		this.addRelationBetweenRecords(referenceModuleName, idList).then(function (data) {
			thisInstance.loadWidget(summaryWidgetContainer.find('.widgetContentBlock'));
		});
	},
	registerChangeEventForModulesList: function () {
		jQuery('#tagSearchModulesList').on('change', function (e) {
			var modulesSelectElement = jQuery(e.currentTarget);
			if (modulesSelectElement.val() == 'all') {
				jQuery('[name="tagSearchModuleResults"]').removeClass('d-none');
			} else {
				jQuery('[name="tagSearchModuleResults"]').removeClass('d-none');
				var selectedOptionValue = modulesSelectElement.val();
				jQuery('[name="tagSearchModuleResults"]').filter(':not(#' + selectedOptionValue + ')').addClass('d-none');
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
				if (typeof urlAttributes !== "undefined") {
					var callBack = urlAttributes.callback;
					delete urlAttributes.callback;
				}
				thisInstance.loadContents(url, urlAttributes).then(function (data) {
					thisInstance.deSelectAllrelatedTabs();
					thisInstance.markTabAsSelected(tabElement);
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
					app.event.trigger("DetailView.Tab.AfterLoad", data, thisInstance);
				}, function () {
					element.progressIndicator({mode: 'hide'});
				});
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

			if (typeof targetObjectForSelectedSourceValue === "undefined") {
				targetObjectForSelectedSourceValue = picklistmap;
			}
			jQuery.each(picklistmap, function (targetPickListName, targetPickListValues) {
				var targetPickListMap = targetObjectForSelectedSourceValue[targetPickListName];
				if (typeof targetPickListMap === "undefined") {
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
				if (typeof listOfAvailableOptions === "undefined") {
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
			element.addClass('d-none');
			element.parent().progressIndicator({});
			if (totalNumberOfRecords == '') {
				var selectedTabElement = thisInstance.getSelectedTab();
				var relatedModuleName = thisInstance.getRelatedModuleName();
				var relatedController = Vtiger_RelatedList_Js.getInstance(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
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
			currentElement.closest('.btn-group').addClass('d-none');
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
			var fieldNameValueMap = {};
			fieldNameValueMap["value"] = fieldValue;
			fieldNameValueMap["field"] = fieldName;
			fieldNameValueMap = thisInstance.getCustomFieldNameValueMap(fieldNameValueMap);
			thisInstance.saveFieldValues(fieldNameValueMap);
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			var params = {
				title: app.vtranslate('JS_SAVE_NOTIFY_OK'),
				type: 'success',
			};
			Vtiger_Helper_Js.showPnotify(params);
			thisInstance.reloadTabContent();
		});
	},
	registerHelpInfo: function () {
		var form = this.getForm();
		app.showPopoverElementView(form.find('.js-help-info'));
	},
	registerRelatedModulesRecordCount: function (tabContainer) {
		var thisInstance = this;
		var counter = [];
		var moreList = $('.related .nav .dropdown-menu');
		var relationContainer = tabContainer;
		if (!relationContainer || (typeof relationContainer.length === "undefined")) {
			relationContainer = $('.related .nav > .relatedNav, .related .nav > .mainNav, .detailViewBlockLink');
		}
		relationContainer.each(function (n, item) {
			item = $(item);
			if (item.data('count') == '1') {
				AppConnector.request({
					module: app.getModuleName(),
					action: 'RelationAjax',
					record: app.getRecordId(),
					relatedModule: item.data('reference'),
					mode: 'getRelatedListPageCount',
					tab_label: item.data('label-key'),
				}).then(function (response) {
					if (response.success) {
						if (response.result.numberOfRecords === 0) {
							response.result.numberOfRecords = '';
						}
						item.find('.count').text(response.result.numberOfRecords);
						moreList.find('[data-reference="' + item.data('reference') + '"] .count').text(response.result.numberOfRecords);
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
				app.event.trigger("DetailView.SaveComment.AfterLoad", commentInfoBlock, data);
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
			if (commentEditStatus.hasClass('d-none')) {
				commentEditStatus.removeClass('d-none');
			}
			if (data.result.reasontoedit != "") {
				commentInfoBlock.find('.editReason').removeClass('d-none')
			}
			commentInfoContent.show();
			commentInfoBlock.find('.commentActionsContainer').show();
			closestAddCommentBlock.remove();
			app.event.trigger("DetailView.SaveComment.AfterUpdate", commentInfoBlock, data);
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
		detailContentsHolder.find('.commentsHierarchy').on('change', function (e) {
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
		detailContentsHolder.find('.commentSearch').on('keyup', function (e) {
			var text = $(this).val();
			if (text) {
				detailContentsHolder.find('.commentDetails').addClass('d-none');
				var contains = detailContentsHolder.find(".commentRelatedTitle:contains(" + text + ")");
				contains.each(function (e) {
					$(this).closest('.commentDetails').removeClass('d-none');
				});
				if (contains.length == 0) {
					detailContentsHolder.find('.noCommentsMsgContainer').removeClass('d-none');
				}
			} else {
				detailContentsHolder.find('.commentDetails').removeClass('d-none');
				detailContentsHolder.find('.noCommentsMsgContainer').addClass('d-none');
			}
		});
	},
	registerCommentEventsInDetail: function (widgetContainer) {
		var thisInstance = this;
		widgetContainer.find('.hierarchyComments').on('change', function (e) {
			var progressIndicatorElement = jQuery.progressIndicator();
			AppConnector.request({
				module: app.getModuleName(),
				view: 'Detail',
				mode: 'showRecentComments',
				hierarchy: $(this).val(),
				record: app.getRecordId(),
			}).then(function (data) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				var widgetDataContainer = widgetContainer.find('.js-detail-widget-content');
				widgetDataContainer.html(data);
				App.Fields.Picklist.showSelect2ElementView(widgetDataContainer.find('.select2'));
			});
		});
	},
	registerMailPreviewWidget: function (container) {
		var thisInstance = this;
		container.on('click', '.showMailBody', function (e) {
			var row = $(e.currentTarget).closest('.row');
			var mailBody = row.find('.mailBody');
			var mailTeaser = row.find('.mailTeaser');
			var faCaretIcon = $(e.currentTarget).find('[data-fa-i2svg]');
			if (mailBody.hasClass('d-none')) {
				mailBody.removeClass('d-none');
				mailTeaser.addClass('d-none');
				faCaretIcon.removeClass("fa-caret-down").addClass("fa-caret-up");
			} else {
				mailBody.addClass('d-none');
				mailTeaser.removeClass('d-none');
				faCaretIcon.removeClass("fa-caret-up").addClass("fa-caret-down");
			}
		});
		container.find('[name="mail-type"]').on('change', function (e) {
			thisInstance.loadMailPreviewWidget(container);
		});
		container.find('[name="mailFilter"]').on('change', function (e) {
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
		container.find('.expandAllMails').on('click', function (e) {
			container.find('.mailBody').removeClass('d-none');
			container.find('.mailTeaser').addClass('d-none');
			container.find('.showMailBody [data-fa-i2svg]').removeClass("fa-caret-down").addClass("fa-caret-up");
		});
		container.find('.collapseAllMails').on('click', function (e) {
			container.find('.mailBody').addClass('d-none');
			container.find('.mailTeaser').removeClass('d-none');
			container.find('.showMailBody [data-fa-i2svg]').removeClass("fa-caret-up").addClass("fa-caret-down");
		});
	},
	loadMailPreviewWidget: function (widgetContent) {
		var thisInstance = this;
		var widgetDataContainer = widgetContent.find('.js-detail-widget-content');
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
		AppConnector.request(params).then(function (data) {
			widgetDataContainer.html(data);
			app.event.trigger("DetailView.Widget.AfterLoad", widgetDataContainer, 'Emails', thisInstance);
			progress.progressIndicator({'mode': 'hide'});
		});
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
	registerShowSummary: function (container) {
		container.on('click', '.showSummaryRelRecord', function (e) {
			var currentTarget = $(e.currentTarget);
			var id = currentTarget.data('id');
			var summaryView = container.find('.summaryRelRecordView' + id);
			container.find('.listViewEntriesTable').css('display', 'none');
			summaryView.show();
		});
		container.on('click', '.hideSummaryRelRecordView', function (e) {
			var summaryView = container.find(".summaryRelRecordView");
			container.find('.listViewEntriesTable').css('display', 'table');
			summaryView.hide();
		});
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
		App.Fields.Date.register(detailContentsHolder);
		App.Fields.DateTime.register(detailContentsHolder);
		App.Fields.MultiImage.register(detailContentsHolder);
		//Attach time picker event to time fields
		app.registerEventForClockPicker();
		App.Fields.Picklist.showSelect2ElementView(detailContentsHolder.find('select.select2'));
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
			editViewObj.showRecordsList(e);
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
			var recentCommentsTab = thisInstance.getTabByModule($(this).data('label-key'));
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
			AppConnector.request({
				module: app.getModuleName(),
				view: 'Detail',
				record: app.getRecordId(),
				mode: 'showRecentRelation',
				page: 1,
				limit: pageLimit,
				type: types,
			}).then(function (data) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				widgetContent.find("#relatedHistoryCurrentPage").remove();
				widgetContent.find("#moreRelatedUpdates").remove();
				widgetContent.html(data);
				Vtiger_Index_Js.registerMailButtons(widgetContent);
			});
		});
		detailContentsHolder.on('click', '.moreProductsService', function () {
			jQuery('.related .mainNav[data-reference="ProductsAndServices"]:not(.d-none)').trigger('click');
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
			AppConnector.request({
				module: app.getModuleName(),
				view: 'Detail',
				record: app.getRecordId(),
				mode: 'showRecentRelation',
				page: nextPage,
				limit: pageLimit,
				type: types,
			}).then(function (data) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				widgetContent.find("#relatedHistoryCurrentPage").remove();
				widgetContent.find("#moreRelatedUpdates").remove();
				widgetContent.find('#relatedUpdates').append(data);
			});
		});
		detailContentsHolder.on('click', '.moreRecentUpdates', function (e) {
			var container = $(e.currentTarget).closest('.recentActivitiesContainer');
			var newChangeInput = container.find('#newChange');
			var newChange = newChangeInput.val();
			var currentPage = container.find('#updatesCurrentPage').val();
			var nextPage = parseInt(currentPage) + 1;
			if (container.closest('.js-detail-widget').length) {
				var data = thisInstance.getFiltersData(e, {
					'page': nextPage,
					'tab_label': 'LBL_UPDATES',
					'newChange': newChange
				}, container.find('#updates'));
				var url = data['params'];
			} else {
				var url = thisInstance.getTabByLabel(thisInstance.detailViewRecentUpdatesTabLabel).data('url');
				url = url.replace('&page=1', '&page=' + nextPage) + '&skipHeader=true&newChange=' + newChange;
				if (url.indexOf('&whereCondition') == -1) {
					var switchBtn = jQuery('.active .js-switch--recentActivities');
					url += '&whereCondition=' + (typeof switchBtn.data('on-val') === "undefined" ? switchBtn.data('off-val') : switchBtn.data('on-val'));
				}
			}
			AppConnector.request(url).then(function (data) {
					var dataContainer = jQuery(data);
					container.find('#newChange').val(dataContainer.find('#newChange').val());
					container.find('#updatesCurrentPage').val(dataContainer.find('#updatesCurrentPage').val());
					container.find('.js-more-link').html(dataContainer.find('.js-more-link').html());
					container.find('#updates ul').append(dataContainer.find('#updates ul').html());
					app.registerMoreContent(container.find('button.moreBtn'));
					app.event.trigger("DetailView.UpdatesWidget.AddMore", data, thisInstance);
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
			AppConnector.request(url).then(function (data) {
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
		app.event.on("DetailView.Widget.AfterLoad", function (e, widgetContent, relatedModuleName, instance) {
			if (relatedModuleName === 'Calendar') {
				var container = widgetContent.closest('.activityWidgetContainer');
				thisInstance.reloadWidgetActivitesStats(container);
			}
			if (relatedModuleName === 'ModComments') {
				var container = widgetContent.closest('.updatesWidgetContainer');
				thisInstance.registerCommentEventsInDetail(container);
			}
			if (widgetContent.find('[name="relatedModule"]').length) {
				thisInstance.registerShowSummary(widgetContent);
			}
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
			AppConnector.request(url).then(function (data) {
					currentTarget.prop('disabled', false);
					currentTarget.addClass('d-none');
					var currentPage = container.find('.currentPage').val();
					container.find('.currentPage').remove();
					container.find('.countActivities').remove();
					container.find('.js-detail-widget-content').append(data);
					container.find('.countActivities').val(parseInt(container.find('.countActivities').val()) + currentPage * parseInt(container.find('.pageLimit').val()));
					thisInstance.reloadWidgetActivitesStats(container);
					app.showPopoverElementView(container.find('.js-popover-tooltip'));
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
		app.event.on("DetailView.Widget.AfterLoad", function (e, widgetContent, relatedModuleName, instance) {
			thisInstance.registerEmailEvents(widgetContent);
		});
		thisInstance.registerEventForRelatedList();
		thisInstance.registerBlockAnimationEvent();
		thisInstance.registerMailPreviewWidget(detailContentsHolder.find('.widgetContentBlock[data-type="EmailList"]'));
		thisInstance.registerMailPreviewWidget(detailContentsHolder.find('.widgetContentBlock[data-type="HistoryRelation"]'));
		app.event.on("DetailView.Widget.AfterLoad", function (e, widgetContent, relatedModuleName, instance, widgetContainer) {
			if (relatedModuleName == 'Emails') {
				Vtiger_Index_Js.registerMailButtons(widgetContent);
				widgetContent.find('.showMailModal').on('click', function (e) {
					var progressIndicatorElement = jQuery.progressIndicator();
					var url = $(e.currentTarget).data('url') + '&noloadlibs=1';
					app.showModalWindow("", url, function (data) {
						Vtiger_Index_Js.registerMailButtons(data);
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
					});
				});
			}
		});
		detailContentsHolder.find('.js-switch--recentActivities').off().on('change', function (e) {
			const currentTarget = jQuery(e.currentTarget),
				tabElement = thisInstance.getTabByLabel(thisInstance.detailViewRecentUpdatesTabLabel),
				variableName = currentTarget.data('urlparams'),
				valueOn = $(this).data('on-val'),
				valueOff = $(this).data('off-val');
			let url = tabElement.data('url');
			url = url.replace('&' + variableName + '=' + valueOn, '').replace('&' + variableName + '=' + valueOff, '');
			if (typeof currentTarget.data('on-val') !== "undefined") {
				url += '&' + variableName + '=' + valueOn;
			} else if (typeof currentTarget.data('off-val') !== "undefined") {
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
		var switchBtn = container.find('.active .js-switch');
		var switchBtnParent = switchBtn.parent();
		var text = switchBtn.data('basic-text') + stats;
		switchBtnParent.removeTextNode();
		switchBtnParent.append(text);
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
			module: app.getModuleName(),
			action: 'PDF',
			mode: 'hasValidTemplate',
			record: app.getRecordId(),
			view: app.getViewName()
		};
		params.dataType = 'json';
		AppConnector.request(params).then(function (data) {
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
						btnGroup.append('<a class="btn btn-default js-popover-tooltip" href=\'javascript:Vtiger_Header_Js.getInstance().showPdfModal("index.php?module=' + app.getModuleName() + '&view=PDF&fromview=Detail&record=' + app.getRecordId() + '");\' data-content="' + app.vtranslate('LBL_EXPORT_PDF') + '" data-original-title="" title=""><span class="fas fa-file-excel icon-in-button"></span></a>');
					}
				}
			}, function (data, err) {
				app.errorLog(data, err);
			}
		);
	},
	updateWindowHeight: function (currentHeight, frame) {
		frame.height(currentHeight);
	},

	registerEvents: function () {
		const thisInstance = this;
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
		this.registerSetReadRecord(detailViewContainer);
		thisInstance.registerEventForPicklistDependencySetup(thisInstance.getForm());
		thisInstance.getForm().validationEngine(app.validationEngineOptionsForRecord);
		thisInstance.loadWidgets();
		this.registerBasicEvents();
		this.registerEventForTotalRecordsCount();
	}
});
