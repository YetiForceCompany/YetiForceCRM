/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 *************************************************************************************/
'use strict';

jQuery.Class(
	'Vtiger_Detail_Js',
	{
		detailInstance: false,
		getInstance: function () {
			if (Vtiger_Detail_Js.detailInstance == false) {
				let moduleClassName = app.getModuleName() + '_' + app.getViewName() + '_Js',
					instance;
				if (typeof window[moduleClassName] !== 'undefined') {
					instance = new window[moduleClassName]();
				} else {
					instance = new Vtiger_Detail_Js();
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
			let detailInstance = Vtiger_Detail_Js.getInstance();
			let selectedIds = [];
			selectedIds.push(detailInstance.getRecordId());
			let postData = {
				selected_ids: JSON.stringify(selectedIds)
			};
			let actionParams = {
				type: 'POST',
				url: detailActionUrl,
				dataType: 'html',
				data: postData
			};

			AppConnector.request(actionParams)
				.done(function (data) {
					if (data) {
						app.showModalWindow(data, { 'text-align': 'left' });
						if (typeof callBackFunction == 'function') {
							callBackFunction(data);
						}
					}
				})
				.fail(function (error, err) {});
		},
		/**
		 * Function to trigger SMS quick view actions
		 */
		triggerSMSmodal: () => {
			App.Components.QuickCreate.createRecord('SMSNotifier', { noCache: true });
		},
		triggerTransferOwnership: function (massActionUrl) {
			let thisInstance = this;
			thisInstance.getRelatedModulesContainer = false;
			let actionParams = {
				type: 'POST',
				url: massActionUrl,
				dataType: 'html',
				data: {}
			};
			AppConnector.request(actionParams).done(function (data) {
				if (data) {
					let callback = function (data) {
						let params = { ...app.validationEngineOptions };
						params.onValidationComplete = function (form, valid) {
							if (valid) {
								if (form.attr('name') == 'changeOwner') {
									thisInstance.transferOwnershipSave(form);
								}
							}
							return false;
						};
						jQuery('#changeOwner').validationEngine(params);
					};
					app.showModalWindow(data, function (data) {
						let selectElement = thisInstance.getRelatedModuleContainer();
						App.Fields.Picklist.changeSelectElementView(selectElement, 'select2');
						if (typeof callback == 'function') {
							callback(data);
						}
					});
				}
			});
		},
		transferOwnershipSave: function (form) {
			let transferOwner = jQuery('#transferOwnerId').val();
			let relatedModules = jQuery('#related_modules').val();
			let recordId = jQuery('#recordId').val();
			let params = {
				module: app.getModuleName(),
				action: 'TransferOwnership',
				record: recordId,
				transferOwnerId: transferOwner,
				related_modules: relatedModules
			};
			AppConnector.request(params).done(function (data) {
				if (data.success) {
					app.hideModalWindow();
					let params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('JS_RECORDS_TRANSFERRED_SUCCESSFULLY'),
						type: 'info'
					};
					let oldValue = jQuery('.assigned_user_id').val();
					let element = jQuery('.assigned_user_id ');

					element.find('option[value="' + oldValue + '"]').removeAttr('selected');
					element.find('option[value="' + transferOwner + '"]').attr('selected', 'selected');
					element.trigger('liszt:updated');
					let fieldName = element.find('option[value="' + transferOwner + '"]').data('picklistvalue');
					element
						.closest('.row-fluid')
						.find('.value')
						.html(
							'<a href="index.php?module=Users&amp;parent=Settings&amp;view=Detail&amp;record=' +
								transferOwner +
								'">' +
								fieldName +
								'</a>'
						);

					app.showNotify(params);
				}
			});
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
			let detailInstance = Vtiger_Detail_Js.getInstance();
			let params = {};
			if (jQuery('[name="currentPageNum"]').length > 0) {
				params.page = jQuery('[name="currentPageNum"]').val();
			}
			detailInstance.loadRelatedList(params);
		},
		runRecordChanger: function (id) {
			AppConnector.request({
				module: app.getModuleName(),
				record: app.getRecordId(),
				action: 'Save',
				mode: 'recordChanger',
				id: id
			})
				.done(function () {
					window.location.reload();
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					app.showNotify({
						type: 'error',
						text: textStatus
					});
				});
		},
		showWorkflowTriggerView: function (instance) {
			$(instance).popover('hide');
			const detailInstance = Vtiger_Detail_Js.getInstance(),
				callback = function (data) {
					let treeInstance = data.find('#treeWorkflowContents');
					treeInstance.jstree({
						core: {
							data: JSON.parse(data.find('.js-tree-workflow-data').val()),
							themes: {
								name: 'proton',
								responsive: true,
								icons: false
							}
						},
						checkbox: {
							three_state: false
						},
						plugins: ['search', 'category']
					});
					data.find('[type="submit"]').on('click', function () {
						let tasks = {};
						let selected = treeInstance.jstree('getCategory', true);
						$.each(selected, function (index, treeElement) {
							if (treeElement.attr === 'record') {
								tasks[treeElement.record_id] = [];
							}
						});
						$.each(selected, function (index, treeElement) {
							if (tasks[treeElement.parent] !== undefined && treeElement.attr === 'task') {
								tasks[treeElement.parent].push(treeElement.record_id);
							}
						});
						if (Object.keys(tasks).length === 0) {
							app.showNotify({
								title: app.vtranslate('JS_INFORMATION'),
								text: app.vtranslate('JS_NOT_SELECTED_WORKFLOW_TRIGGER'),
								type: 'error'
							});
						} else {
							app.showNotify({
								title: app.vtranslate('JS_MESSAGE'),
								text: app.vtranslate('JS_STARTED_PERFORM_WORKFLOW'),
								type: 'info'
							});
							AppConnector.request({
								module: app.getModuleName(),
								action: 'Workflow',
								mode: 'execute',
								user: data.find('[name="user"]').val(),
								record: detailInstance.getRecordId(),
								tasks: JSON.stringify(tasks)
							})
								.done(function () {
									app.showNotify({
										title: app.vtranslate('JS_MESSAGE'),
										text: app.vtranslate('JS_COMPLETED_PERFORM_WORKFLOW'),
										type: 'success'
									});
									app.hideModalWindow();
									detailInstance.loadWidgets();
								})
								.fail(function () {
									app.showNotify({
										title: app.vtranslate('JS_ERROR'),
										text: app.vtranslate('JS_ERROR_DURING_TRIGGER_OF_WORKFLOW'),
										type: 'error'
									});
									app.hideModalWindow();
								});
						}
					});
				};
			AppConnector.request({
				module: app.getModuleName(),
				view: 'WorkflowTrigger',
				record: detailInstance.getRecordId()
			}).done(function (data) {
				if (data) {
					app.showModalWindow(data, '', callback);
				}
			});
		}
	},
	{
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
		//constructor
		init: function () {},
		loadWidgetsEvents: function () {
			const thisInstance = this;
			app.event.on('DetailView.Widget.AfterLoad', function (e, widgetContent, relatedModuleName, instance) {
				if (relatedModuleName === 'Calendar') {
					thisInstance.reloadWidgetActivitesStats(widgetContent.closest('.activityWidgetContainer'));
				}
				if (relatedModuleName === 'ModComments') {
					thisInstance.registerCommentEventsInDetail(widgetContent.closest('.updatesWidgetContainer'));
				}
				if (widgetContent.find('[name="relatedModule"]').length) {
					thisInstance.registerShowSummary(widgetContent);
				}
				if (relatedModuleName === 'OSSMailView') {
					Vtiger_Index_Js.registerMailButtons(widgetContent);
					widgetContent.find('.showMailModal').on('click', function (e) {
						e.preventDefault();
						let progressIndicatorElement = jQuery.progressIndicator();
						app.showModalWindow('', $(e.currentTarget).data('url') + '&noloadlibs=1', function (data) {
							Vtiger_Index_Js.registerMailButtons(data);
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
						});
					});
				}
				thisInstance.registerEmailEvents(widgetContent);
				if (relatedModuleName === 'DetailView') {
					thisInstance.registerBlockStatusCheckOnLoad();
				}
				thisInstance.registerCollapsiblePanels(widgetContent.closest('.js-detail-widget'));
			});
		},
		loadWidgets: function () {
			let container = this.getForm();
			let widgetList = jQuery('[class^="widgetContainer_"]');
			let length = widgetList.length;
			widgetList.each((index, widget) => {
				widget = $(widget);
				if (widget.is(':visible')) {
					this.loadWidget(widget);
				}
				if (length === index + 1) {
					container.validationEngine('detach');
					container.validationEngine(app.validationEngineOptionsForRecord);
				}
			});
			this.registerRelatedModulesRecordCount();
		},
		loadWidget: function (widgetContainer, params) {
			const thisInstance = this,
				contentContainer = $('.js-detail-widget-content', widgetContainer);
			let relatedModuleName;
			this.registerFilterForAddingModuleRelatedRecordFromSummaryWidget(widgetContainer);
			if (widgetContainer.find('[name="relatedModule"]').length) {
				relatedModuleName = widgetContainer.find('[name="relatedModule"]').val();
			} else {
				relatedModuleName = widgetContainer.data('name');
			}
			if (params === undefined) {
				let urlParams = widgetContainer.data('url');
				if (urlParams == undefined) {
					return;
				}
				let queryParameters = urlParams.split('&'),
					keyValueMap = {},
					index;
				for (index = 0; index < queryParameters.length; index++) {
					let queryParamComponents = queryParameters[index].split('=');
					keyValueMap[queryParamComponents[0]] = queryParamComponents[1];
				}
				params = keyValueMap;
			}
			let aDeferred = $.Deferred();
			contentContainer.progressIndicator({});
			AppConnector.request({
				type: 'POST',
				async: false,
				dataType: 'html',
				data: params
			})
				.done(function (data) {
					contentContainer.progressIndicator({ mode: 'hide' });
					contentContainer.html(data);
					App.Fields.Picklist.showSelect2ElementView(widgetContainer.find('.select2'));
					app.registerModal(contentContainer);
					App.Components.DropFile.register(contentContainer);
					if (relatedModuleName) {
						let relatedController = Vtiger_RelatedList_Js.getInstanceByUrl(
							widgetContainer.data('url'),
							thisInstance.getSelectedTab()
						);
						relatedController.setRelatedContainer(contentContainer);
						relatedController.registerRelatedEvents();
						thisInstance.widgetRelatedRecordView(widgetContainer, true);
						let chart = contentContainer.find('[name="typeChart"]');
						if (chart.length && typeof window['Vtiger_Widget_Js'] !== 'undefined') {
							let widgetInstance = Vtiger_Widget_Js.getInstance(contentContainer, chart.val());
							widgetInstance.init(contentContainer);
							widgetInstance.loadChart();
						}
					}
					app.event.trigger('DetailView.Widget.AfterLoad', contentContainer, relatedModuleName, thisInstance);
					aDeferred.resolve(params);
				})
				.fail(function () {
					contentContainer.progressIndicator({ mode: 'hide' });
					aDeferred.reject();
				});
			return aDeferred.promise();
		},

		/**
		 * Adding relationships in the products and services widget.
		 */
		registerWidgetProductAndServices: function () {
			let thisInstance = this;
			this.getForm().on('click', '.js-widget-products-services', (e) => {
				let currentTarget = $(e.currentTarget);
				let params = {
					module: app.getModuleName(),
					action: 'RelationAjax',
					mode: 'updateRelation',
					recordsToAdd: [],
					src_record: app.getRecordId(),
					related_module: currentTarget.closest('.js-detail-widget-header').find('[name="relatedModule"]').val()
				};
				let url = currentTarget.data('url');
				app.showRecordsList(url, (_, instance) => {
					instance.setSelectEvent((data) => {
						for (let i in data) {
							params.recordsToAdd.push(i);
						}
						AppConnector.request(params).done(function () {
							thisInstance.reloadTabContent();
						});
					});
				});
			});
		},

		widgetRelatedRecordView: function (container, load) {
			let cacheKey = this.getRecordId() + '_' + container.data('id');
			let relatedRecordCacheID = app.moduleCacheGet(cacheKey);
			if (relatedRecordCacheID !== null) {
				let newActive = container.find(".js-carousel-item[data-id = '" + relatedRecordCacheID + "']");
				if (newActive.length) {
					container.find('.js-carousel-item.active').removeClass('active');
					container.find(".js-carousel-item[data-id = '" + relatedRecordCacheID + "']").addClass('active');
				}
			}
			let controlBox = container.find('.control-widget');
			let prev = controlBox.find('.prev');
			let next = controlBox.find('.next');
			let active = container.find('.js-carousel-item.active');
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
					let active = container.find('.js-carousel-item.active');
					active.removeClass('active');
					let nextElement = active.next();
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
					let active = container.find('.js-carousel-item.active');
					active.removeClass('active');
					let prevElement = active.prev();
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

		loadContents: function (url, data) {
			let thisInstance = this;
			let aDeferred = jQuery.Deferred();

			let detailContentsHolder = this.getContentHolder();
			let params = url;
			if (typeof data !== 'undefined') {
				params = {};
				params.url = url;
				params.data = data;
			}
			AppConnector.requestPjax(params).done(function (responseData) {
				detailContentsHolder.html(responseData);
				responseData = detailContentsHolder.html();
				thisInstance.registerBlockStatusCheckOnLoad();
				//Make select box more usability
				App.Fields.Picklist.changeSelectElementView(detailContentsHolder);
				//Attach date picker event to date fields
				App.Fields.Date.register(detailContentsHolder);
				thisInstance.getForm().validationEngine();
				app.event.trigger('DetailView.LoadContents.AfterLoad', responseData);
				aDeferred.resolve(responseData);
			});
			return aDeferred.promise();
		},
		getUpdateFieldsArray: function () {
			return this.updatedFields;
		},
		/**
		 * Function to return related tab.
		 * @return : jQuery Object.
		 */
		getTabByLabel: function (tabLabel) {
			let tabs = this.getTabs();
			let targetTab = false;
			tabs.each(function (index, element) {
				let tab = jQuery(element);
				let labelKey = tab.data('labelKey');
				if (labelKey == tabLabel) {
					targetTab = tab;
					return false;
				}
			});
			return targetTab;
		},
		getTabByModule: function (moduleName, relationId = '') {
			let tabs = this.getTabs();
			let targetTab = false;
			tabs.each(function (index, element) {
				let tab = jQuery(element);
				if (
					tab.data('reference') == moduleName &&
					(!relationId || (relationId && relationId == tab.data('relation-id')))
				) {
					targetTab = tab;
					return false;
				}
			});
			return targetTab;
		},
		selectModuleTab: function () {
			let relatedTabContainer = this.getTabContainer();
			let moduleTab = relatedTabContainer.find('li.module-tab');
			this.deSelectAllrelatedTabs();
			this.markTabAsSelected(moduleTab);
		},
		deSelectAllrelatedTabs: function () {
			this.getTabs().removeClass('active');
		},
		markTabAsSelected: function (tabElement) {
			tabElement.addClass('active');
			$(
				'.related .dropdown [data-reference="' +
					tabElement.data('reference') +
					'"][data-relation-id="' +
					tabElement.data('relation-id') +
					'"]'
			).addClass('active');
		},
		reloadTabContent: function () {
			this.getSelectedTab().trigger('click');
		},
		getSelectedTab: function () {
			let tabContainer = this.getTabContainer();
			return tabContainer.find('.js-detail-tab.active:not(.d-none)');
		},
		getTabContainer: function () {
			return jQuery('div.related');
		},
		getTabs: function () {
			let topTabs = this.getTabContainer().find('li.baseLink:not(.d-none)');
			let dropdownMenuTabs = this.getTabContainer().find('li:not(.baseLink)');
			dropdownMenuTabs.each(function (n, e) {
				let currentTarget = jQuery(this);
				let iteration = currentTarget.data('iteration');
				let className = currentTarget.hasClass('mainNav') ? 'mainNav' : 'relatedNav';
				if (
					iteration != undefined &&
					topTabs.filter('.' + className + '[data-iteration="' + iteration + '"]').length < 1
				) {
					topTabs.push(currentTarget.get(0));
				}
			});
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
			return app.getRecordId();
		},
		getRelatedModuleName: function () {
			if (jQuery('.relatedModuleName', this.getContentHolder()).length == 1) {
				return jQuery('.relatedModuleName', this.getContentHolder()).val();
			}
		},
		getRelatedListCurrentPageNum: function () {
			return jQuery('input[name="currentPageNum"]', this.getContentHolder()).val();
		},

		/**
		 * function to hide button action.
		 */
		hideButtonAction: function () {
			$('.js-hb__container').removeClass('u-hidden-block__opened');
		},

		/**
		 * function to get the Comment thread for the given parent.
		 * params: Url to get the Comment thread
		 */
		getCommentThread: function (url) {
			let aDeferred = jQuery.Deferred();
			AppConnector.request(url)
				.done(function (data) {
					aDeferred.resolve(data);
				})
				.fail(function (error, err) {});
			return aDeferred.promise();
		},
		/**
		 * Function to save comment
		 */
		saveCommentAjax: function (
			element,
			commentMode,
			commentContentValue,
			editCommentReason,
			commentId,
			parentCommentId,
			aDeferred
		) {
			let thisInstance = this;
			let progressIndicatorElement = jQuery.progressIndicator({});
			let commentInfoBlock = element.closest('.js-comment-single');
			let relatedTo = commentInfoBlock.find('.related_to').val();
			if (!relatedTo) {
				relatedTo = thisInstance.getRecordId();
			}
			let postData = {
				action: 'SaveAjax',
				commentcontent: commentContentValue,
				related_to: relatedTo,
				module: 'ModComments'
			};
			if (commentMode == 'edit') {
				postData['fromView'] = 'QuickEdit';
				postData['record'] = commentId;
				postData['reasontoedit'] = editCommentReason;
				postData['parent_comments'] = parentCommentId;
			} else if (commentMode == 'add') {
				postData['parent_comments'] = commentId;
			}
			AppConnector.request(postData)
				.done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					if (commentMode == 'add') {
						thisInstance.addRelationBetweenRecords(
							'ModComments',
							data.result._recordId,
							thisInstance.getTabByLabel(thisInstance.detailViewRecentCommentsTabLabel),
							{ relationId: null }
						);
					}
					app.event.trigger('DetailView.SaveComment.AfterAjax', commentInfoBlock, postData, data);
					aDeferred.resolve(data);
				})
				.fail(function (textStatus, errorThrown) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					element.removeAttr('disabled');
					aDeferred.reject(textStatus, errorThrown);
				});
		},
		saveComment: function (e) {
			let aDeferred = jQuery.Deferred(),
				currentTarget = jQuery(e.currentTarget),
				commentMode = currentTarget.data('mode'),
				closestCommentBlock = currentTarget.closest('.js-add-comment-block'),
				commentContent = closestCommentBlock.find('.js-comment-content'),
				commentContentValue = commentContent.html(),
				errorMsg,
				editCommentReason;
			if ('' === commentContentValue) {
				errorMsg = app.vtranslate('JS_LBL_COMMENT_VALUE_CANT_BE_EMPTY');
				commentContent.validationEngine('showPrompt', errorMsg, 'error', 'bottomLeft', true);
				aDeferred.reject(errorMsg);
				return aDeferred.promise();
			}
			if ('edit' === commentMode) {
				editCommentReason = closestCommentBlock.find('[name="reasonToEdit"]').val();
			}
			let element = jQuery(e.currentTarget),
				commentInfoHeader = closestCommentBlock.closest('.js-comment-details').find('.js-comment-info-header'),
				commentId = commentInfoHeader.data('commentid'),
				parentCommentId = commentInfoHeader.data('parentcommentid');
			this.saveCommentAjax(
				element,
				commentMode,
				commentContentValue,
				editCommentReason,
				commentId,
				parentCommentId,
				aDeferred
			);
			return aDeferred.promise();
		},
		/**
		 * function to return the UI of the comment.
		 * return html
		 */
		getCommentUI: function (commentId) {
			let aDeferred = jQuery.Deferred();
			let postData = {
				view: 'DetailAjax',
				module: 'ModComments',
				record: commentId
			};
			AppConnector.request(postData)
				.done(function (data) {
					aDeferred.resolve(data);
				})
				.fail(function (error, err) {});
			return aDeferred.promise();
		},
		/**
		 * function to return cloned add comment block
		 * return jQuery Obj.
		 */
		getCommentBlock: function () {
			let clonedCommentBlock = jQuery('.basicAddCommentBlock', this.getContentHolder())
				.clone(true, true)
				.removeClass('basicAddCommentBlock d-none')
				.addClass('js-add-comment-block');
			clonedCommentBlock
				.find('.commentcontenthidden')
				.removeClass('commentcontenthidden')
				.addClass('js-comment-content');
			return clonedCommentBlock;
		},
		/**
		 * function to return cloned edit comment block
		 * return jQuery Obj.
		 */
		getEditCommentBlock: function () {
			let clonedCommentBlock = jQuery('.basicEditCommentBlock', this.getContentHolder())
				.clone(true, true)
				.removeClass('basicEditCommentBlock d-none')
				.addClass('js-add-comment-block');
			clonedCommentBlock
				.find('.commentcontenthidden')
				.removeClass('commentcontenthidden')
				.addClass('js-comment-content');
			new App.Fields.Text.Completions(clonedCommentBlock.find('.js-completions'));
			return clonedCommentBlock;
		},
		/*
		 * Function to register the submit event for Send Sms
		 */
		registerSendSmsSubmitEvent: function () {
			let thisInstance = this;
			jQuery('body').on('submit', '#massSave', function (e) {
				let form = jQuery(e.currentTarget);
				let smsTextLength = form.find('#message').html().length;
				if (smsTextLength > 160) {
					let params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('LBL_SMS_MAX_CHARACTERS_ALLOWED'),
						type: 'error'
					};
					app.showNotify(params);
					return false;
				}
				let submitButton = form.find(':submit');
				submitButton.attr('disabled', 'disabled');
				thisInstance.SendSmsSave(form);
				e.preventDefault();
			});
		},
		/*
		 * Function to Save and sending the Sms and hide the modal window of send sms
		 */
		SendSmsSave: function (form) {
			let progressInstance = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			let SendSmsUrl = form.serializeFormData();
			AppConnector.request(SendSmsUrl)
				.done(function (data) {
					app.hideModalWindow();
					progressInstance.progressIndicator({
						mode: 'hide'
					});
				})
				.fail(function (error, err) {});
		},
		/**
		 * Function which will register events to update the record name in the detail view when any of
		 * the name field is changed
		 */
		registerNameAjaxEditEvent: function () {
			let thisInstance = this;
			let detailContentsHolder = thisInstance.getContentHolder();
			detailContentsHolder.on(thisInstance.fieldUpdatedEvent, '.nameField', function (e, params) {
				let form = thisInstance.getForm();
				let nameFields = form.data('nameFields');
				let recordLabel = '';
				for (let index in nameFields) {
					if (index != 0) {
						recordLabel += ' ';
					}

					let nameFieldName = nameFields[index];
					recordLabel += form.find('[name="' + nameFieldName + '"]').val();
				}
				let recordLabelElement = detailContentsHolder.closest('.contentsDiv').find('.recordLabel');
				recordLabelElement.text(recordLabel);
			});
		},
		updateHeaderNameFields: function () {
			let thisInstance = this;
			let detailContentsHolder = thisInstance.getContentHolder();
			let form = thisInstance.getForm();
			let nameFields = form.data('nameFields');
			let recordLabelElement = detailContentsHolder.closest('.contentsDiv').find('.recordLabel');
			let title = '';
			for (let index in nameFields) {
				let nameFieldName = nameFields[index];
				let nameField = form.find('[name="' + nameFieldName + '"]');
				if (nameField.length > 0) {
					let recordLabel = nameField.val();
					title += recordLabel + ' ';
					recordLabelElement.find('[class="' + nameFieldName + '"]').text(recordLabel);
				}
			}
			let salutatioField = recordLabelElement.find('.salutation');
			if (salutatioField.length > 0) {
				let salutatioValue = salutatioField.text();
				title = salutatioValue + title;
			}
			recordLabelElement.attr('title', title);
		},
		registerAjaxEditEvent: function () {
			let thisInstance = this;
			let detailContentsHolder = thisInstance.getContentHolder();
			detailContentsHolder.on(thisInstance.fieldUpdatedEvent, 'input,select,textarea', function (e) {
				thisInstance.updateHeaderValues(jQuery(e.currentTarget));
			});
		},
		updateHeaderValues: function (currentElement) {
			let thisInstance = this;
			if (currentElement.hasClass('nameField')) {
				thisInstance.updateHeaderNameFields();
				return true;
			}

			let name = currentElement.attr('name');
			let updatedFields = this.getUpdateFieldsArray();
			let detailContentsHolder = thisInstance.getContentHolder();
			if (jQuery.inArray(name, updatedFields) != '-1') {
				let recordLabel = currentElement.val();
				let recordLabelElement = detailContentsHolder.closest('.contentsDiv').find('.' + name + '_label');
				recordLabelElement.text(recordLabel);
			}
		},
		/*
		 * Function to register the click event of email field
		 */
		registerEmailFieldClickEvent: function () {
			let detailContentsHolder = this.getContentHolder();
			detailContentsHolder.on('click', '.emailField', function (e) {
				e.stopPropagation();
			});
		},
		/*
		 * Function to register the click event of phone field
		 */
		registerPhoneFieldClickEvent: function () {
			let detailContentsHolder = this.getContentHolder();
			detailContentsHolder.on('click', '.phoneField', function (e) {
				e.stopPropagation();
			});
		},
		/*
		 * Function to register the click event of url field
		 */
		registerUrlFieldClickEvent: function () {
			let detailContentsHolder = this.getContentHolder();
			detailContentsHolder.on('click', '.urlField', function (e) {
				e.stopPropagation();
			});
		},
		/**
		 * Function to register event for related list row click
		 */
		registerRelatedRowClickEvent: function () {
			let detailContentsHolder = this.getContentHolder();
			detailContentsHolder.on('click', '.listViewEntries', function (e) {
				let targetElement = jQuery(e.target, jQuery(e.currentTarget));
				if (targetElement.is('td:first-child') && targetElement.children('input[type="checkbox"]').length > 0) return;
				if (jQuery(e.target).is('input[type="checkbox"]')) return;
				let elem = jQuery(e.currentTarget);
				let recordUrl = elem.data('recordurl');
				if (typeof recordUrl !== 'undefined') {
					window.location.href = recordUrl;
				}
			});
		},
		loadRelatedList: function (params) {
			let aDeferred = jQuery.Deferred();
			if (params == undefined) {
				params = {};
			}
			let relatedListInstance = Vtiger_RelatedList_Js.getInstance(
				this.getRecordId(),
				app.getModuleName(),
				this.getSelectedTab(),
				this.getRelatedModuleName()
			);
			relatedListInstance
				.loadRelatedList(params)
				.done(function (data) {
					aDeferred.resolve(data);
				})
				.fail(function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				});
			return aDeferred.promise();
		},
		/**
		 * Function to register Event for Sorting
		 */
		registerEventForRelatedList: function () {
			const self = this;
			let detailContentsHolder = this.getContentHolder();
			let relatedModuleName = self.getRelatedModuleName();
			if (relatedModuleName) {
				let relatedController = Vtiger_RelatedList_Js.getInstance(
					self.getRecordId(),
					app.getModuleName(),
					self.getSelectedTab(),
					relatedModuleName
				);
				relatedController.setRelatedContainer(detailContentsHolder);
				relatedController.registerRelatedEvents();
			}
			detailContentsHolder.find('.detailViewBlockLink').each(function (n, block) {
				self.reloadDetailViewBlock($(block), false);
			});
			detailContentsHolder.find('.detailViewBlockLink .blockHeader').on('click', function (e) {
				const target = $(e.target);
				if (
					target.is('input') ||
					target.is('button') ||
					target.parents().is('button') ||
					target.hasClass('js-stop-propagation') ||
					target.parents().hasClass('js-stop-propagation')
				) {
					return false;
				}
				self.reloadDetailViewBlock($(this).closest('.js-toggle-panel'));
			});
		},
		/**
		 * Function to reload detail view block
		 * @param {$} block - Jquery container.
		 */
		reloadDetailViewBlock: function (block, progressIndicator = true) {
			const self = this;
			const blockContent = block.find('.blockContent');
			const isEmpty = blockContent.is(':empty');
			let url = block.data('url');
			if (blockContent.is(':visible') && url) {
				if (progressIndicator) {
					blockContent.progressIndicator();
				}
				AppConnector.request(url).done(function (response) {
					blockContent.html(response);
					const relatedController = Vtiger_RelatedList_Js.getInstanceByUrl(url, self.getSelectedTab());
					relatedController.setRelatedContainer(blockContent);
					if (isEmpty) {
						relatedController.registerRelatedEvents();
					} else {
						relatedController.registerPostLoadEvents();
						relatedController.registerListEvents();
					}
				});
			}
		},
		registerBlockStatusCheckOnLoad: function () {
			let blocks = this.getContentHolder().find('.js-toggle-panel');
			let module = app.getModuleName();
			blocks.each(function (index, block) {
				let currentBlock = jQuery(block);
				let dynamicAttr = currentBlock.attr('data-dynamic');
				if (typeof dynamicAttr !== typeof undefined && dynamicAttr !== false) {
					let headerAnimationElement = currentBlock.find('.js-block-toggle').not('.d-none');
					let bodyContents = currentBlock.closest('.js-toggle-panel').find('.blockContent');
					let blockId = headerAnimationElement.data('id');
					let cacheKey = module + '.' + blockId;
					let value = app.cacheGet(cacheKey, null);
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
				}
			});
		},
		/**
		 * Function to handle the ajax edit for detailview and summary view fields
		 * which will expects the currentTdElement
		 */
		ajaxEditHandling: function (currentTdElement) {
			const thisInstance = this;
			let readRecord = $('.setReadRecord'),
				detailViewValue = $('.value', currentTdElement),
				editElement = $('.edit', currentTdElement),
				actionElement = $('.js-detail-quick-edit', currentTdElement),
				fieldElement = $('.fieldname', editElement);
			readRecord.prop('disabled', true);
			$(fieldElement).each(function (index, element) {
				let fieldName = $(element).val(),
					elementTarget = $(element),
					elementName =
						$.inArray(elementTarget.data('type'), [
							'taxes',
							'sharedOwner',
							'multipicklist',
							'multiListFields',
							'multiDomain',
							'mailScannerFields',
							'mailScannerActions'
						]) != -1
							? fieldName + '[]'
							: fieldName;
				let fieldElement = $('[name="' + elementName + '"]:not([type="hidden"])', editElement);
				if (fieldElement.attr('disabled') == 'disabled' && fieldElement.attr('type') !== 'password') {
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
				editElement
					.removeClass('d-none')
					.children()
					.filter('input[type!="hidden"]input[type!="image"],select')
					.filter(':first')
					.focus();
				let saveHandler = function (e) {
					thisInstance.registerNameAjaxEditEvent();
					let element = $(e.target);
					if ($(e.currentTarget).find('.dateTimePickerField').length) {
						if (element.closest('.drp-calendar').length || element.hasClass('drp-calendar')) {
							return;
						}
					}
					if (
						element.closest('.fieldValue').is(currentTdElement) ||
						element.closest('.pnotify-modal').length ||
						element.hasClass('select2-selection__choice__remove') ||
						element.closest('.select2-container--open').length ||
						element.parents('.clockpicker-popover').length
					) {
						return;
					}
					currentTdElement.removeAttr('tabindex');
					currentTdElement.removeClass('is-edit-active');
					let previousValue = elementTarget.data('prevValue'),
						editElement = elementTarget.closest('.edit'),
						ajaxEditNewValue =
							editElement.find('[name="' + elementName + '"]').length > 0
								? editElement.find('[name="' + elementName + '"]').val()
								: editElement.find('[name="' + fieldName + '"]').val(),
						fieldInfo = Vtiger_Field_Js.getInstance(fieldElement.data('fieldinfo')),
						dateTimeField = [],
						dateTime = false;
					if (editElement.find('[data-fieldinfo]').length == 2) {
						editElement.find('[data-fieldinfo]').each(function () {
							let field = {
								name: $(this).attr('name'),
								type: $(this).data('fieldinfo').type
							};
							if (field['type'] == 'datetime') {
								dateTime = true;
							}
							dateTimeField.push(field);
						});
					}
					if (fieldElement.is('input:checkbox')) {
						if (fieldElement.is(':checked')) {
							ajaxEditNewValue = '1';
						} else {
							ajaxEditNewValue = '0';
						}
						fieldElement = fieldElement.filter('[type="checkbox"]');
					}
					if (fieldElement.validationEngine('validate')) {
						if (fieldElement.attr('data-inputmask')) {
							fieldElement.inputmask();
						}
						return;
					}
					function toStr(v) {
						return v === undefined || v === null ? '' : v + '';
					}
					fieldElement.validationEngine('hide');
					if (toStr(previousValue) === toStr(ajaxEditNewValue)) {
						editElement.addClass('d-none');
						detailViewValue.removeClass('d-none');
						actionElement.removeClass('d-none');
						readRecord.prop('disabled', false);
						editElement.off('clickoutside');
					} else {
						let preFieldSaveEvent = jQuery.Event(thisInstance.fieldPreSave);
						fieldElement.trigger(preFieldSaveEvent, {
							fieldValue: ajaxEditNewValue,
							recordId: thisInstance.getRecordId()
						});
						if (preFieldSaveEvent.isDefaultPrevented()) {
							readRecord.prop('disabled', false);
							return;
						}
						editElement.addClass('d-none');
						Vtiger_Edit_Js.saveAjax(
							thisInstance.getCustomFieldNameValueMap({
								field: fieldName,
								value: ajaxEditNewValue
							})
						)
							.done(function (response) {
								editElement.off('clickoutside');
								readRecord.prop('disabled', false);
								detailViewValue.removeClass('d-none');
								actionElement.removeClass('d-none');
								if (!response.success) {
									return;
								}
								const postSaveRecordDetails = response.result;
								let displayValue = postSaveRecordDetails[fieldName].display_value,
									prevDisplayValue = postSaveRecordDetails[fieldName].prev_display_value;
								if (dateTimeField.length && dateTime) {
									displayValue =
										postSaveRecordDetails[dateTimeField[0].name].display_value +
										' ' +
										postSaveRecordDetails[dateTimeField[1].name].display_value;
								}
								detailViewValue.html(displayValue);
								app.showNotify({
									title: app.vtranslate('JS_SAVE_NOTIFY_OK'),
									text:
										'<b>' +
										fieldInfo.data.label +
										'</b><br>' +
										'<b>' +
										app.vtranslate('JS_SAVED_FROM') +
										'</b>: ' +
										prevDisplayValue +
										'<br> ' +
										'<b>' +
										app.vtranslate('JS_SAVED_TO') +
										'</b>: ' +
										displayValue,
									type: 'info',
									textTrusted: true
								});
								if (postSaveRecordDetails['_isViewable'] === false) {
									let urlObject = app.convertUrlToObject(window.location.href);
									if (window !== window.parent) {
										window.parent.location.href = 'index.php?module=' + urlObject['module'] + '&view=ListPreview';
									} else {
										window.location.href = 'index.php?module=' + urlObject['module'] + '&view=List';
									}
								} else if (
									postSaveRecordDetails['_isEditable'] === false ||
									postSaveRecordDetails['_reload'] === true
								) {
									$.progressIndicator({
										position: 'html',
										blockInfo: {
											enabled: true
										}
									});
									if (window !== window.parent) {
										window.location.href = window.location.href.replace('view=Detail', 'view=DetailPreview');
									} else {
										window.location.reload();
									}
								}
								fieldElement.trigger(thisInstance.fieldUpdatedEvent, {
									old: previousValue,
									new: ajaxEditNewValue
								});
								ajaxEditNewValue = ajaxEditNewValue === undefined ? '' : ajaxEditNewValue; //data cannot be undefined
								elementTarget.data('prevValue', ajaxEditNewValue);
								fieldElement.data('selectedValue', ajaxEditNewValue);
								if (thisInstance.targetPicklistChange) {
									if ($('.js-widget-general-info', thisInstance.getForm()).length > 0) {
										thisInstance.targetPicklist.find('.js-detail-quick-edit').trigger('click');
									} else {
										thisInstance.targetPicklist.trigger('click');
									}
									thisInstance.targetPicklistChange = false;
									thisInstance.targetPicklist = false;
								}
								let selectedTabElement = thisInstance.getSelectedTab();
								if (selectedTabElement.data('linkKey') == thisInstance.detailViewSummaryTabLabel) {
									let detailContentsHolder = thisInstance.getContentHolder();
									thisInstance.reloadTabContent();
									thisInstance.registerSummaryViewContainerEvents(detailContentsHolder);
									thisInstance.registerEventForRelatedList();
								}
								thisInstance.updateRecordsPDFTemplateBtn(thisInstance.getForm());
							})
							.fail(function (jqXHR, textStatus, errorThrown) {
								editElement.addClass('d-none');
								detailViewValue.removeClass('d-none');
								actionElement.removeClass('d-none');
								editElement.off('clickoutside');
								readRecord.prop('disabled', false);
								app.showNotify({
									type: 'error',
									title: app.vtranslate('JS_SAVE_NOTIFY_FAIL'),
									text: textStatus
								});
							});
					}
				};
				editElement.on('clickoutside', saveHandler);
			});
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
			let thisInstance = this;

			/*
			 * Register click event for add button in Related Activities widget
			 */
			jQuery('.createActivity').on('click', function (e) {
				let referenceModuleName = 'Calendar';
				let recordId = thisInstance.getRecordId();
				let module = app.getModuleName();
				let element = jQuery(e.currentTarget);

				let customParams = {};
				customParams['sourceModule'] = module;
				customParams['sourceRecord'] = recordId;
				let fullFormUrl = element.data('url');
				let preQuickCreateSave = function (data) {
					thisInstance.addElementsToQuickCreateForCreatingRelation(data, customParams);
					let taskGoToFullFormButton = data.find('[class^="CalendarQuikcCreateContents"]').find('.js-full-editlink');
					let eventsGoToFullFormButton = data.find('[class^="EventsQuikcCreateContents"]').find('.js-full-editlink');
					let taskFullFormUrl = taskGoToFullFormButton.data('url') + '&' + fullFormUrl;
					let eventsFullFormUrl = eventsGoToFullFormButton.data('url') + '&' + fullFormUrl;
					taskGoToFullFormButton.data('url', taskFullFormUrl);
					eventsGoToFullFormButton.data('url', eventsFullFormUrl);
				};
				let callbackFunction = function () {
					thisInstance.getFiltersDataAndLoad(e);
					thisInstance.loadWidget($('.widgetContentBlock[data-type="Updates"]'));
				};
				let QuickCreateParams = {};
				QuickCreateParams['callbackPostShown'] = preQuickCreateSave;
				QuickCreateParams['callbackFunction'] = callbackFunction;
				QuickCreateParams['data'] = Object.assign({}, customParams);
				QuickCreateParams['noCache'] = false;
				App.Components.QuickCreate.createRecord(referenceModuleName, QuickCreateParams);
			});
		},
		/**
		 * Function to add module related record from summary widget
		 */
		registerFilterForAddingModuleRelatedRecordFromSummaryWidget: function (container) {
			let thisInstance = this;
			container
				.find('.createRecordFromFilter')
				.off()
				.on('click', function (e) {
					let currentElement = jQuery(e.currentTarget);
					let summaryWidgetContainer = currentElement.closest('.js-detail-widget');
					let referenceModuleName = summaryWidgetContainer.data('moduleName');
					let quickcreateUrl = currentElement.data('url');
					let quickCreateParams = {};
					let autoCompleteFields = currentElement.data('acf');
					let moduleName = currentElement.closest('.js-detail-widget-header').find('[name="relatedModule"]').val();
					let relatedParams = {};
					let postQuickCreateSave = function (data) {
						thisInstance.postSummaryWidgetAddRecord(data, currentElement);
						if (referenceModuleName == 'ProjectTask') {
							thisInstance.loadModuleSummary();
						}
					};
					if (typeof autoCompleteFields !== 'undefined') {
						$.each(autoCompleteFields, function (index, value) {
							relatedParams[index] = value;
						});
					}
					if (Object.keys(relatedParams).length > 0) {
						quickCreateParams['data'] = relatedParams;
					}
					quickCreateParams['noCache'] = true;
					quickCreateParams['callbackFunction'] = postQuickCreateSave;
					let progress = jQuery.progressIndicator({
						blockInfo: {
							enabled: true
						}
					});
					let quickCreate;
					if (window !== window.parent) {
						quickCreate = window.parent.App.Components.QuickCreate;
					} else {
						quickCreate = App.Components.QuickCreate;
					}
					quickCreate.getForm(quickcreateUrl, moduleName, quickCreateParams).done(function (data) {
						quickCreate.showModal(data, quickCreateParams, currentElement);
						progress.progressIndicator({ mode: 'hide' });
					});
				});
			container
				.find('button.selectRelation')
				.off('click')
				.on('click', function (e) {
					let summaryWidgetContainer = jQuery(e.currentTarget).closest('.js-detail-widget');
					let referenceModuleName = summaryWidgetContainer.data('moduleName');
					let restrictionsField = $(this).data('rf');
					let params = {
						module: referenceModuleName,
						src_module: app.getModuleName(),
						src_record: thisInstance.getRecordId(),
						multi_select: true,
						relationId: summaryWidgetContainer.data('relationId')
					};
					if (restrictionsField && Object.keys(restrictionsField).length > 0) {
						params['search_key'] = restrictionsField.key;
						params['search_value'] = restrictionsField.name;
					}
					app.showRecordsList(params, (_modal, instance) => {
						instance.setSelectEvent((responseData) => {
							thisInstance
								.addRelationBetweenRecords(referenceModuleName, Object.keys(responseData), null, {
									relationId: params.relationId
								})
								.done(function () {
									thisInstance.loadWidget(summaryWidgetContainer.find('.widgetContentBlock'));
								});
						});
					});
				});
		},
		registerAddingInventoryRecords: function () {
			jQuery('.createInventoryRecordFromFilter').on('click', function (e) {
				let currentElement = jQuery(e.currentTarget);
				let createUrl = currentElement.data('url');
				let autoCompleteFields = currentElement.data('acf');
				let addidtionalParams = '';
				if (typeof autoCompleteFields !== 'undefined') {
					$.each(autoCompleteFields, function (index, value) {
						addidtionalParams = '&' + index + '=' + value;
						createUrl = createUrl.concat(addidtionalParams);
					});
				}
				window.location.href = createUrl;
			});
		},
		registerEmailEvent: function () {
			this.getContentHolder()
				.find('.resetRelationsEmail')
				.on('click', function (e) {
					app.showConfirmModal({
						title: app.vtranslate('JS_EMAIL_RESET_RELATIONS_CONFIRMATION'),
						confirmedCallback: () => {
							AppConnector.request({
								module: 'OSSMailView',
								action: 'Relation',
								moduleName: app.getModuleName(),
								record: app.getRecordId()
							}).done(function (d) {
								Vtiger_Helper_Js.showMessage({ text: d.result });
							});
						}
					});
				});
		},
		getFiltersDataAndLoad: function (e, params) {
			let data = this.getFiltersData(e, params);
			this.loadWidget(data['container'], data['params']);
		},
		getFiltersData: function (e, params) {
			let currentElement;
			if (e.currentTarget) {
				currentElement = jQuery(e.currentTarget);
			} else {
				currentElement = e;
			}
			let summaryWidgetContainer = currentElement.closest('.js-detail-widget');
			let widget = summaryWidgetContainer.find('.widgetContentBlock');
			let url = '&' + widget.data('url');
			let urlParams = {};
			url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
				urlParams[key] = value;
			});
			let urlNewParams = [];
			summaryWidgetContainer.find('.js-switch, .js-filter_field').each(function (n, item) {
				let value = '';
				let element = jQuery(item);
				let name = element.data('urlparams');
				if (element.attr('type') == 'radio') {
					if (element.prop('checked')) {
						value = typeof element.data('on-val') !== 'undefined' ? element.data('on-val') : element.data('off-val');
						let additionalParams = element.data('params');
						if (typeof additionalParams !== typeof undefined && additionalParams !== false) {
							$.each(additionalParams, function (paramName, paramValue) {
								if (paramName in urlNewParams) {
									urlNewParams[paramName].push(paramValue);
								} else {
									urlNewParams[paramName] = paramValue;
								}
							});
						}
					}
				} else {
					let selectedFilter = element.find('option:selected').val();
					let fieldlable = element.data('fieldlable');
					let filter = element.data('filter');
					if (element.data('return') === 'value') {
						value = selectedFilter;
					} else {
						if (selectedFilter != fieldlable) {
							value = [[filter, 'e', selectedFilter]];
						} else {
							return;
						}
					}
				}
				if (name && value) {
					if (element.data('return') === 'value') {
						urlNewParams[name] = value;
					} else {
						if (name in urlNewParams) {
							urlNewParams[name].push(value);
						} else {
							urlNewParams[name] = [value];
						}
					}
				}
			});
			if (params != undefined) {
				$.extend(urlNewParams, params);
			}
			return { container: $(widget), params: $.extend(urlParams, urlNewParams) };
		},
		registerChangeFilterForWidget: function () {
			let thisInstance = this;
			jQuery('.js-switch').on('change', function (e, state) {
				$(e.currentTarget).closest('.js-switch__btn').addClass('active').siblings().removeClass('active');
				thisInstance.getFiltersDataAndLoad(e);
			});
			jQuery('.js-filter_field').on('select2:select', function (e, state) {
				thisInstance.getFiltersDataAndLoad(e);
			});
		},
		/**
		 * Function to register all the events related to summary view widgets
		 */
		registerSummaryViewContainerEvents: function (summaryViewContainer) {
			let thisInstance = this;
			this.registerEventForActivityWidget();
			this.registerChangeFilterForWidget();
			this.registerAddingInventoryRecords();
			this.registerEmailEvent();
			/**
			 * Function to handle the ajax edit for summary view fields
			 */
			summaryViewContainer.off('click').on('click', '.row .js-detail-quick-edit', function (e) {
				let currentTarget = jQuery(e.currentTarget);
				currentTarget.addClass('d-none');
				let currentTdElement = currentTarget.closest('.fieldValue');
				thisInstance.ajaxEditHandling(currentTdElement);
			});
			/**
			 * Function to handle actions after ajax save in summary view
			 */
			summaryViewContainer.on(thisInstance.fieldUpdatedEvent, '.js-widget-general-info', function () {
				let updatesWidget = summaryViewContainer.find("[data-type='Updates']"),
					params;
				if (updatesWidget.length) {
					params = thisInstance.getFiltersData(updatesWidget);
					updatesWidget.find('.btnChangesReviewedOn').parent().remove();
					thisInstance.loadWidget(updatesWidget, params['params']);
				}
			});

			summaryViewContainer.on('click', '.editDefaultStatus', function (e) {
				let currentTarget = jQuery(e.currentTarget);
				currentTarget.popover('hide');
				let url = currentTarget.data('url');
				if (url) {
					if (currentTarget.hasClass('showEdit')) {
						let quickCreate = App.Components.QuickCreate;
						if (window !== window.parent) {
							quickCreate = window.parent.App.Components.QuickCreate;
						}
						quickCreate.getForm(url, 'Calendar', { noCache: true }).done((data) => {
							quickCreate.showModal(
								data,
								{
									callbackFunction: () => {
										let widget = currentTarget.closest('.widgetContentBlock');
										if (widget.length) {
											thisInstance.loadWidget(widget);
											let updatesWidget = thisInstance.getContentHolder().find("[data-type='Updates']");
											if (updatesWidget.length > 0) {
												thisInstance.loadWidget(updatesWidget);
											}
										} else {
											thisInstance.loadRelatedList();
										}
										thisInstance.registerRelatedModulesRecordCount();
									}
								},
								currentTarget
							);
						});
					} else {
						app.showModalWindow(null, url);
					}
				}
			});

			/*
			 * Register the event to edit Description for related activities
			 */
			summaryViewContainer.on('click', '.editDescription', function (e) {
				let currentTarget = jQuery(e.currentTarget),
					currentDiv = currentTarget.closest('.activityDescription'),
					editElement = currentDiv.find('.edit'),
					detailViewElement = currentDiv.find('.value'),
					descriptionText = currentDiv.find('.js-description-text'),
					descriptionEmpty = currentDiv.find('.js-no-description'),
					saveButton = currentDiv.find('.js-save-description'),
					closeButton = currentDiv.find('.js-close-description'),
					activityButtonContainer = currentDiv.find('.js-activity-buttons__container'),
					fieldnameElement = jQuery('.fieldname', editElement),
					fieldName = fieldnameElement.val(),
					fieldElement = jQuery('[name="' + fieldName + '"]', editElement),
					callbackFunction = () => {
						let previousValue = fieldnameElement.data('prevValue'),
							ajaxEditNewValue = fieldElement.val(),
							ajaxEditNewLable = fieldElement.val(),
							activityDiv = currentDiv.closest('.activityEntries'),
							activityId = activityDiv.find('.activityId').val(),
							moduleName = activityDiv.find('.activityModule').val(),
							activityType = activityDiv.find('.activityType').val();
						if (previousValue == ajaxEditNewValue) {
							closeDescription();
						} else {
							currentDiv.progressIndicator();
							editElement.add(activityButtonContainer).addClass('d-none');
							return new Promise(function (resolve, reject) {
								resolve(fieldElement.validationEngine('validate'));
							}).then((errorExists) => {
								//If validation fails
								if (errorExists) {
									Vtiger_Helper_Js.addClickOutSideEvent(currentDiv, callbackFunction);
									return;
								} else {
									ajaxEditNewValue = fieldElement.val(); //update editor value after conversion
									AppConnector.request({
										action: 'SaveAjax',
										record: activityId,
										field: fieldName,
										value: ajaxEditNewValue,
										module: moduleName,
										activitytype: activityType
									}).done(() => {
										currentDiv.progressIndicator({ mode: 'hide' });
										detailViewElement.removeClass('d-none');
										currentTarget.show();
										descriptionText.html(ajaxEditNewLable);
										fieldnameElement.data('prevValue', ajaxEditNewValue);
										if (ajaxEditNewValue === '') {
											descriptionEmpty.removeClass('d-none');
										} else {
											descriptionEmpty.addClass('d-none');
										}
									});
								}
							});
						}
					},
					closeDescription = function () {
						fieldElement.val(fieldnameElement.data('prevValue'));
						editElement.add(activityButtonContainer).addClass('d-none');
						detailViewElement.removeClass('d-none');
						currentTarget.show();
					};
				App.Fields.Text.Editor.register(currentDiv, { toolbar: 'Min' });
				currentTarget.hide();
				detailViewElement.addClass('d-none');
				activityButtonContainer.removeClass('d-none');
				editElement.removeClass('d-none').show();
				saveButton.off('click').one('click', callbackFunction);
				closeButton.off('click').one('click', closeDescription);
			});

			/*
			 * Register click event for add button in Related widgets
			 * to add record from widget
			 */

			$('.changeDetailViewMode').on('click', function (e) {
				thisInstance
					.getTabs()
					.filter('[data-link-key="' + thisInstance.detailViewDetailsTabLabel + '"]:not(.d-none)')
					.trigger('click');
			});
			this.registerFastEditingFields();
		},
		addRelationBetweenRecords: function (relatedModule, relatedModuleRecordId, selectedTabElement, params = {}, url) {
			let aDeferred = jQuery.Deferred();
			let thisInstance = this;
			let relatedController;
			if (selectedTabElement == undefined) {
				selectedTabElement = thisInstance.getSelectedTab();
			}
			if (url) {
				relatedController = Vtiger_RelatedList_Js.getInstanceByUrl(url, selectedTabElement);
			} else {
				relatedController = Vtiger_RelatedList_Js.getInstance(
					thisInstance.getRecordId(),
					app.getModuleName(),
					selectedTabElement,
					relatedModule
				);
			}
			relatedController
				.addRelations(relatedModuleRecordId, params)
				.done(function (data) {
					let summaryViewContainer = thisInstance.getContentHolder();
					let updatesWidget = summaryViewContainer.find("[data-type='Updates']");
					if (updatesWidget.length > 0) {
						let params = thisInstance.getFiltersData(updatesWidget);
						updatesWidget.find('.btnChangesReviewedOn').parent().remove();
						thisInstance.loadWidget(updatesWidget, params['params']);
					}
					aDeferred.resolve(data);
				})
				.fail(function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				});
			return aDeferred.promise();
		},
		/**
		 * Function to handle Post actions after adding record from
		 * summary view widget
		 */
		postSummaryWidgetAddRecord: function (data, currentElement) {
			let summaryWidgetContainer = currentElement.closest('.js-detail-widget');
			let widgetContainer = summaryWidgetContainer.find('[class^="widgetContainer_"]');

			this.loadWidget(widgetContainer);
			let updatesWidget = this.getContentHolder().find("[data-type='Updates']");
			if (updatesWidget.length > 0) {
				let params = this.getFiltersData(updatesWidget);
				updatesWidget.find('.btnChangesReviewedOn').parent().remove();
				this.loadWidget(updatesWidget, params['params']);
			}
		},
		registerChangeEventForModulesList: function () {
			jQuery('#tagSearchModulesList').on('change', function (e) {
				let modulesSelectElement = jQuery(e.currentTarget);
				if (modulesSelectElement.val() == 'all') {
					jQuery('[name="tagSearchModuleResults"]').removeClass('d-none');
				} else {
					jQuery('[name="tagSearchModuleResults"]').removeClass('d-none');
					let selectedOptionValue = modulesSelectElement.val();
					jQuery('[name="tagSearchModuleResults"]')
						.filter(':not(#' + selectedOptionValue + ')')
						.addClass('d-none');
				}
			});
		},
		registerEventForRelatedTabClick: function () {
			let thisInstance = this;
			let detailContentsHolder = thisInstance.getContentHolder();
			let detailContainer = detailContentsHolder.closest('div.detailViewInfo');

			jQuery('.related', detailContainer).on('click', 'li:not(.spaceRelatedList)', function (e, urlAttributes) {
				let tabElement = jQuery(e.currentTarget);
				if (!tabElement.hasClass('dropdown')) {
					let element = jQuery('<div></div>');
					element.progressIndicator({
						position: 'html',
						blockInfo: {
							enabled: true,
							elementToBlock: detailContainer
						}
					});
					let url = tabElement.data('url');
					if (typeof urlAttributes !== 'undefined') {
						let callBack = urlAttributes.callback;
						delete urlAttributes.callback;
					}
					thisInstance
						.loadContents(url, urlAttributes)
						.done(function (data) {
							thisInstance.deSelectAllrelatedTabs();
							thisInstance.markTabAsSelected(tabElement);
							Vtiger_Helper_Js.showHorizontalTopScrollBar();
							element.progressIndicator({ mode: 'hide' });
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
							app.event.trigger('DetailView.Tab.AfterLoad', data, thisInstance);
						})
						.fail(function () {
							element.progressIndicator({ mode: 'hide' });
						});
				}
			});
		},
		/**
		 * Function to get child comments
		 */
		getChildComments: function (commentId) {
			let aDeferred = jQuery.Deferred();
			let url =
				'module=' +
				app.getModuleName() +
				'&view=Detail&record=' +
				this.getRecordId() +
				'&mode=showChildComments&commentid=' +
				commentId;
			let dataObj = this.getCommentThread(url);
			dataObj.done(function (data) {
				aDeferred.resolve(data);
			});
			return aDeferred.promise();
		},
		/**
		 * Function to get parent comment
		 * @param {number} commentId
		 * @returns {string}
		 */
		getParentComments(commentId) {
			let aDeferred = $.Deferred(),
				url =
					'module=' +
					app.getModuleName() +
					'&view=Detail&record=' +
					this.getRecordId() +
					'&mode=showParentComments&commentid=' +
					commentId;
			this.getCommentThread(url).done(function (data) {
				aDeferred.resolve(data);
			});
			return aDeferred.promise();
		},
		/**
		 * Function to show total records count in listview on hover
		 * of pageNumber text
		 */
		registerEventForTotalRecordsCount: function () {
			let thisInstance = this;
			let detailContentsHolder = this.getContentHolder();
			detailContentsHolder.on('click', '.totalNumberOfRecords', function (e) {
				let element = jQuery(e.currentTarget);
				let totalNumberOfRecords = jQuery('#totalCount').val();
				element.addClass('d-none');
				element.parent().progressIndicator({});
				if (totalNumberOfRecords == '') {
					let selectedTabElement = thisInstance.getSelectedTab();
					let relatedModuleName = thisInstance.getRelatedModuleName();
					let relatedController = Vtiger_RelatedList_Js.getInstance(
						thisInstance.getRecordId(),
						app.getModuleName(),
						selectedTabElement,
						relatedModuleName
					);
					relatedController.getRelatedPageCount().done(function () {
						thisInstance.showPagingInfo();
					});
				} else {
					thisInstance.showPagingInfo();
				}
				element.parent().progressIndicator({ mode: 'hide' });
			});
		},
		showPagingInfo: function () {
			let totalNumberOfRecords = jQuery('#totalCount').val();
			let pageNumberElement = jQuery('.pageNumbersText');
			let pageRange = pageNumberElement.text();
			let newPagingInfo = pageRange + ' (' + totalNumberOfRecords + ')';
			let listViewEntriesCount = parseInt(jQuery('#noOfEntries').val());
			if (listViewEntriesCount != 0) {
				jQuery('.pageNumbersText').html(newPagingInfo);
			} else {
				jQuery('.pageNumbersText').html('');
			}
		},
		getCustomFieldNameValueMap: function (fieldNameValueMap) {
			return fieldNameValueMap;
		},
		registerSetReadRecord: function (detailContentsHolder) {
			let thisInstance = this;
			detailContentsHolder.on('click', '.setReadRecord', function (e) {
				let currentElement = jQuery(e.currentTarget);
				currentElement.closest('.btn-group').addClass('d-none');
				jQuery('#Accounts_detailView_fieldValue_was_read').find('.value').text(app.vtranslate('LBL_YES'));
				let params = {
					module: app.getModuleName(),
					action: 'SaveAjax',
					record: thisInstance.getRecordId(),
					field: 'was_read',
					value: 'on'
				};
				AppConnector.request(params).done(function (data) {
					let params = {
						text: app.vtranslate('JS_SET_READ_RECORD'),
						title: app.vtranslate('System'),
						type: 'info'
					};
					app.showNotify(params);
					let relatedTabKey = jQuery('.related li.active');
					if (
						relatedTabKey.data('linkKey') == thisInstance.detailViewSummaryTabLabel ||
						relatedTabKey.data('linkKey') == thisInstance.detailViewDetailsTabLabel
					) {
						thisInstance.reloadTabContent();
					}
				});
			});
		},
		registerFastEditingFields: function () {
			let thisInstance = this;
			let fastEditingFiels = jQuery('.summaryWidgetFastEditing select');
			fastEditingFiels.on('change', function (e) {
				let fieldElement = jQuery(e.currentTarget);
				let fieldContainer = fieldElement.closest('.editField');
				let progressIndicatorElement = jQuery.progressIndicator({
					message: app.vtranslate('JS_SAVE_LOADER_INFO'),
					position: 'summaryWidgetFastEditing',
					blockInfo: {
						enabled: true
					}
				});
				let fieldName = fieldContainer.data('fieldname');
				fieldName = fieldName.replace('q_', '');
				let fieldValue = fieldElement.val();
				let errorExists = fieldElement.validationEngine('validate');
				if (errorExists) {
					fieldContainer.progressIndicator({ mode: 'hide' });
					return;
				}
				let preFieldSaveEvent = jQuery.Event(thisInstance.fieldPreSave);
				fieldElement.trigger(preFieldSaveEvent, {
					fieldValue: fieldValue,
					recordId: thisInstance.getRecordId()
				});
				Vtiger_Edit_Js.saveAjax(
					thisInstance.getCustomFieldNameValueMap({
						field: fieldName,
						value: fieldValue
					}),
					false
				).always(() => {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					app.showNotify({
						title: app.vtranslate('JS_SAVE_NOTIFY_OK'),
						type: 'success'
					});
					thisInstance.reloadTabContent();
				});
			});
		},
		registerHelpInfo: function (form) {
			if (!form) {
				form = this.getForm();
			}
			app.showPopoverElementView(form.find('.js-help-info'));
		},
		/**
		 * Register related modules record cound
		 * @param {jQuery} tabContainer
		 */
		registerRelatedModulesRecordCount(tabContainer) {
			const moreList = $('.related .nav .dropdown-menu');
			let relationContainer = tabContainer;
			if (!relationContainer || typeof relationContainer.length === 'undefined') {
				relationContainer = $(
					'.related .nav > .relatedNav, .related .nav > .mainNav, .detailViewBlockLink, .related .nav .dropdown-menu > .relatedNav'
				);
			}
			relationContainer.each((n, item) => {
				item = $(item);
				let relationId = item.data('relationId'),
					relatedModule = item.data('reference');
				if (item.data('count') === 1) {
					AppConnector.request({
						module: app.getModuleName(),
						action: 'RelationAjax',
						record: app.getRecordId(),
						relatedModule: relatedModule,
						mode: 'getRelatedListPageCount',
						relationId: relationId,
						tab_label: item.data('label-key')
					}).done((response) => {
						if (response.success) {
							if (response.result.numberOfRecords === 0) {
								response.result.numberOfRecords = '';
							}
							item.find('.count').text(response.result.numberOfRecords);
							moreList
								.find('[data-reference="${relatedModule}"][data-relation-id="${relationId}"] .count')
								.text(response.result.numberOfRecords);
						}
					});
				}
			});
		},
		/**
		 * Function to display a new comments
		 */
		addComment: function (currentTarget, data) {
			const self = this;
			let mode = currentTarget.data('mode'),
				closestAddCommentBlock = currentTarget.closest('.js-add-comment-block'),
				commentTextAreaElement = closestAddCommentBlock.find('.js-comment-content'),
				commentInfoBlock = currentTarget.closest('.js-comment-single');
			commentTextAreaElement.html('');
			if (mode == 'add') {
				let commentHtml = self.getCommentUI(data['result']['_recordId']);
				commentHtml.done(function (data) {
					let commentBlock = closestAddCommentBlock.closest('.js-comment-details'),
						detailContentsHolder = self.getContentHolder(),
						noCommentsMsgContainer = $('.js-noCommentsMsgContainer', detailContentsHolder);
					noCommentsMsgContainer.remove();
					if (commentBlock.length > 0) {
						closestAddCommentBlock.remove();
						let childComments = commentBlock.find('ul');
						if (childComments.length <= 0) {
							let currentChildCommentsCount = commentInfoBlock
									.find('.js-view-thread-block')
									.data('data-child-comments-count'),
								newChildCommentCount = currentChildCommentsCount + 1;
							commentInfoBlock.find('.js-child-comments-count').text(newChildCommentCount);
							let parentCommentId = commentInfoBlock.find('.js-comment-info-header').data('commentid');
							self.getChildComments(parentCommentId).done(function (responsedata) {
								$(responsedata).appendTo(commentBlock);
								commentInfoBlock.find('.js-view-thread-block').hide();
								commentInfoBlock.find('.hideThreadBlock').show();
							});
						} else {
							$('<li class="js-comment-details commentDetails">' + data + '</li>').appendTo(
								commentBlock.find('.js-comments-body')
							);
						}
					} else {
						$('<li class="js-comment-details commentDetails">' + data + '</li>').prependTo(
							closestAddCommentBlock.closest('.contents').find('.commentsList')
						);
					}
					commentInfoBlock.find('.js-comment-container').show();
					app.event.trigger('DetailView.SaveComment.AfterLoad', commentInfoBlock, data);
				});
			} else if (mode == 'edit') {
				let modifiedTime = commentInfoBlock.find('.js-comment-modified-time'),
					commentInfoContent = commentInfoBlock.find('.js-comment-info'),
					commentEditStatus = commentInfoBlock.find('.js-edited-status'),
					commentReason = commentInfoBlock.find('.js-edit-reason-span');
				commentInfoContent.html(data['result']['commentcontent']['display_value']);
				commentReason.html(data['result']['reasontoedit']['display_value']);
				modifiedTime.html(data['result']['modifiedtime']['formatToViewDate']);
				modifiedTime.attr('title', data['result']['modifiedtime']['formatToDay']);
				if (commentEditStatus.hasClass('d-none')) {
					commentEditStatus.removeClass('d-none');
				}
				if (data['result']['reasontoedit']['display_value'] != '') {
					commentInfoBlock.find('.js-edit-reason').removeClass('d-none');
				}
				commentInfoContent.show();
				commentInfoBlock.find('.js-comment-container').show();
				closestAddCommentBlock.remove();
				app.event.trigger('DetailView.SaveComment.AfterUpdate', commentInfoBlock, data);
			}
		},
		/**
		 * Register all comment events
		 * @param {jQuery} detailContentsHolder
		 */
		registerCommentEvents(detailContentsHolder) {
			const self = this;
			detailContentsHolder.on('click', '.js-close-comment-block', function (e) {
				let commentInfoBlock = $(e.currentTarget.closest('.js-comment-single'));
				commentInfoBlock.find('.js-comment-container').show();
				commentInfoBlock.find('.js-comment-info').show();
				commentInfoBlock.find('.js-add-comment-block').remove();
			});
			detailContentsHolder.on('click', '.js-reply-comment', function (e) {
				let commentInfoBlock = $(e.currentTarget).closest('.js-comment-single');
				commentInfoBlock.find('.js-add-comment-block').remove();
				self.hideButtonAction();
				commentInfoBlock.find('.js-comment-info').show();
				self.getCommentBlock().appendTo(commentInfoBlock).show();
			});
			detailContentsHolder.on('click', '.js-edit-comment', function (e) {
				let commentInfoBlock = $(e.currentTarget).closest('.js-comment-single');
				commentInfoBlock.find('.js-add-comment-block').remove();
				self.hideButtonAction();
				let commentInfoContent = commentInfoBlock.find('.js-comment-info'),
					editCommentBlock = self.getEditCommentBlock();
				editCommentBlock.find('.js-comment-content').html(commentInfoContent.html());
				editCommentBlock.find('.js-reason-to-edit').html(commentInfoBlock.find('.js-edit-reason-span').text());
				commentInfoContent.hide();
				commentInfoBlock.find('.js-comment-container').hide();
				editCommentBlock.appendTo(commentInfoBlock).show();
			});
			detailContentsHolder.on('click', '.js-detail-view-save-comment', function (e) {
				let element = $(e.currentTarget);
				if (!element.is(':disabled')) {
					self
						.saveComment(e)
						.done(function () {
							self.registerRelatedModulesRecordCount();
							self.loadWidget(detailContentsHolder.find("[data-type='Comments']")).done(function () {
								element.removeAttr('disabled');
							});
						})
						.fail(function (error, err) {
							element.removeAttr('disabled');
							app.errorLog(error, err);
						});
				}
			});
			detailContentsHolder.on('click', '.js-save-comment', function (e) {
				let element = $(e.currentTarget);
				if (!element.is(':disabled')) {
					self
						.saveComment(e)
						.done(function (data) {
							self.registerRelatedModulesRecordCount(self.getTabByLabel(self.detailViewRecentCommentsTabLabel));
							self.addComment(element, data);
							element.removeAttr('disabled');
						})
						.fail(function (error, err) {
							element.removeAttr('disabled');
							app.errorLog(error, err);
						});
				}
			});
			detailContentsHolder.on('click', '.js-more-recent-comments ', function () {
				self.getTabByLabel(self.detailViewRecentCommentsTabLabel).trigger('click');
			});
			detailContentsHolder.find('.js-detail-hierarchy-comments').on('change', function (e) {
				let recentCommentsTab = self.getTabByLabel(self.detailViewRecentCommentsTabLabel),
					url = recentCommentsTab.data('url'),
					regex = /&hierarchy=+([\w,]+)/;
				url = url.replace(regex, '');
				let hierarchy = [];
				detailContentsHolder.find('.js-detail-hierarchy-comments:checked').each(function () {
					hierarchy.push($(this).val());
				});
				if (hierarchy.length !== 0) {
					url += '&hierarchy=' + hierarchy.join(',');
				}
				recentCommentsTab.data('url', url);
				recentCommentsTab.trigger('click');
			});
			detailContentsHolder.on('keypress', '.js-comment-search', function (e) {
				if (13 === e.which) {
					self.submitSearchForm(detailContentsHolder);
				}
			});
			detailContentsHolder.on('click', '.js-search-icon', function (e) {
				self.submitSearchForm(detailContentsHolder);
			});
		},
		/**
		 * Submit search comment form
		 * @param {jQuery} detailContentsHolder
		 */
		submitSearchForm(detailContentsHolder) {
			let searchTextDom = detailContentsHolder.find('.js-comment-search'),
				widgetContainer = searchTextDom.closest('[data-name="ModComments"]'),
				progressIndicatorElement = $.progressIndicator();
			if (searchTextDom.data('container') === 'widget' && !searchTextDom.val()) {
				let request = widgetContainer.data('url');
				AppConnector.request(request).done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					detailContentsHolder.find('.js-comments-container').html(data);
				});
			} else {
				let hierarchy = [],
					limit = '',
					isWidget = false;
				if (searchTextDom.data('container') === 'widget') {
					(limit = widgetContainer.data('limit')), (isWidget = true);
					widgetContainer.find('.js-hierarchy-comments:checked').each(function () {
						hierarchy.push($(this).val());
					});
				} else {
					detailContentsHolder.find('.js-detail-hierarchy-comments:checked').each(function () {
						hierarchy.push($(this).val());
					});
				}
				AppConnector.request({
					module: app.getModuleName(),
					view: 'Detail',
					mode: 'showSearchComments',
					hierarchy: hierarchy.join(','),
					limit: limit,
					record: app.getRecordId(),
					search_key: searchTextDom.val(),
					is_widget: isWidget
				}).done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					if (!searchTextDom.val()) {
						detailContentsHolder.html(data);
					} else {
						detailContentsHolder.find('.js-comments-body').html(data);
					}
				});
			}
		},
		/**
		 * Register hierarchy comments buttons
		 * @param {jQuery} widgetContainer
		 */
		registerCommentEventsInDetail(widgetContainer) {
			new App.Fields.Text.Completions($('.js-completions').eq(0));
			widgetContainer.on('change', '.js-hierarchy-comments', function (e) {
				let hierarchy = [];
				widgetContainer.find('.js-hierarchy-comments').each(function () {
					if ($(this).is(':checked')) {
						hierarchy.push($(this).val());
					}
				});
				if (!hierarchy.length) {
					widgetContainer.find('.js-detail-widget-content').html('');
					return false;
				}
				let progressIndicatorElement = $.progressIndicator();
				AppConnector.request({
					module: app.getModuleName(),
					view: 'Detail',
					mode: 'showRecentComments',
					hierarchy: hierarchy.join(','),
					record: app.getRecordId(),
					limit: widgetContainer.find('.widgetContentBlock').data('limit')
				}).done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					let widgetDataContainer = widgetContainer.find('.js-detail-widget-content');
					widgetDataContainer.html(data);
					App.Fields.Picklist.showSelect2ElementView(widgetDataContainer.find('.select2'));
				});
			});
		},
		registerMailPreviewWidget: function (container) {
			const self = this;
			container.on('click', '.showMailBody', (e) => {
				let row = $(e.currentTarget).closest('.js-mail-row'),
					mailBody = row.find('.mailBody'),
					mailTeaser = row.find('.mailTeaser');
				mailBody.toggleClass('d-none');
				mailTeaser.toggleClass('d-none');
			});
			container.find('[name="mail-type"]').on('change', function (e) {
				self.loadMailPreviewWidget(container);
			});
			container.find('[name="mailFilter"]').on('change', function (e) {
				self.loadMailPreviewWidget(container);
			});
			container.on('click', '.showMailsModal', (e) => {
				let url = $(e.currentTarget).data('url');
				let type = container.find('[name="mail-type"]');
				let typeValue = '';
				if (type.length > 0) {
					typeValue = type.val();
				} else {
					typeValue = 'All';
				}
				url += '&type=' + typeValue;
				if (container.find('[name="mailFilter"]').length > 0) {
					url += '&mailFilter=' + container.find('[name="mailFilter"]').val();
				}
				let progressIndicatorElement = jQuery.progressIndicator();
				app.showModalWindow('', url, (data) => {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					self.registerMailPreviewWidget(data);
					Vtiger_Index_Js.registerMailButtons(data);
					data.find('.expandAllMails').click();
				});
			});
			container.find('.expandAllMails').on('click', function (e) {
				container.find('.mailBody').removeClass('d-none');
				container.find('.mailTeaser').addClass('d-none');
				container.find('.showMailBody .js-toggle-icon').removeClass('fa-caret-down').addClass('fa-caret-up');
			});
			container.find('.collapseAllMails').on('click', function (e) {
				container.find('.mailBody').addClass('d-none');
				container.find('.mailTeaser').removeClass('d-none');
				container.find('.showMailBody .js-toggle-icon').removeClass('fa-caret-up').addClass('fa-caret-down');
			});
			container
				.find('.showMailModal')
				.off('click')
				.on('click', function (e) {
					e.preventDefault();
					let progressIndicatorElement = jQuery.progressIndicator();
					app.showModalWindow('', $(e.currentTarget).data('url') + '&noloadlibs=1', function (data) {
						Vtiger_Index_Js.registerMailButtons(data);
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
					});
				});
		},
		loadMailPreviewWidget: function (widgetContent) {
			let thisInstance = this;
			let widgetDataContainer = widgetContent.find('.js-detail-widget-content');
			let recordId = $('#recordId').val();
			let progress = widgetDataContainer.progressIndicator();
			let params = {};
			params['module'] = 'OSSMailView';
			params['view'] = 'Widget';
			params['smodule'] = $('#module').val();
			params['srecord'] = recordId;
			params['mode'] = 'showEmailsList';
			params['type'] = $('[name="mail-type"]').val();
			params['mailFilter'] = $('[name="mailFilter"]').val();
			AppConnector.request(params).done(function (data) {
				widgetDataContainer.html(data);
				app.event.trigger('DetailView.Widget.AfterLoad', widgetDataContainer, params['module'], thisInstance);
				progress.progressIndicator({ mode: 'hide' });
			});
		},
		registerEmailEvents: function (detailContentsHolder) {
			Vtiger_Index_Js.registerMailButtons(detailContentsHolder);
		},
		registerMapsEvents: function (container) {
			if (container.find('#coordinates').length) {
				let mapView = new OpenStreetMap_Map_Js();
				mapView.registerDetailView(container);
			}
		},
		registerShowSummary: function (container) {
			container.on('click', '.showSummaryRelRecord', function (e) {
				let currentTarget = $(e.currentTarget);
				let id = currentTarget.data('id');
				let summaryView = container.find('.summaryRelRecordView' + id);
				container.find('.listViewEntriesTable').css('display', 'none');
				summaryView.show();
			});
			container.on('click', '.hideSummaryRelRecordView', function (e) {
				let summaryView = container.find('.summaryRelRecordView');
				container.find('.listViewEntriesTable').css('display', 'table');
				summaryView.hide();
			});
		},
		/**
		 * Show confirmation on event click
		 * @param {jQuery} element
		 * @param {string} picklistName
		 */
		showProgressConfirmation(element, picklistName) {
			const picklistValue = $(element).data('picklistValue');
			app.showConfirmModal({
				title: $(element).data('picklistLabel'),
				text: app.vtranslate('JS_CHANGE_VALUE_CONFIRMATION'),
				confirmedCallback: () => {
					Vtiger_Edit_Js.saveAjax({
						value: picklistValue,
						field: picklistName
					})
						.done((response) => {
							if (!response || response.success !== false) {
								window.location.reload();
							}
						})
						.fail(function (error, err) {
							app.errorLog(error, err);
						});
				}
			});
		},
		/**
		 * Change status from progress
		 */
		registerProgress() {
			const self = this;
			$('.js-header-progress-bar').each((index, element) => {
				let picklistName = $(element).data('picklistName');
				$(element)
					.find('.js-access')
					.on('click', (e) => {
						self.showProgressConfirmation(e.currentTarget, picklistName);
					});
			});
		},
		loadChat() {
			let chatVue = $('#ChatRecordRoomVue', this.detailViewContentHolder);
			if (chatVue.length) {
				let chatContainer = this.detailViewContentHolder.find('.js-chat-container');
				const padding = 10;
				chatContainer.height(
					$(document).height() - chatContainer.offset().top - $('.js-footer').outerHeight() - padding
				);
				window.ChatRecordRoomVueComponent.mount({
					el: '#ChatRecordRoomVue'
				});
			}
		},
		registerChat() {
			if (window.ChatRecordRoomVueComponent !== undefined) {
				this.loadChat();
				app.event.on('DetailView.Tab.AfterLoad', (e, data, instance) => {
					instance.detailViewContentHolder.ready(() => {
						this.loadChat();
					});
				});
			}
		},
		registerBasicEvents: function () {
			let thisInstance = this;
			let detailContentsHolder = thisInstance.getContentHolder();
			let selectedTabElement = thisInstance.getSelectedTab();
			//register all the events for summary view container

			if (this.getSelectedTab().data('labelKey') === 'ModComments') {
				new App.Fields.Text.Completions(detailContentsHolder.find('.js-completions'));
			}
			app.registerBlockAnimationEvent(this.getForm());
			thisInstance.registerSummaryViewContainerEvents(detailContentsHolder);
			thisInstance.registerCommentEvents(detailContentsHolder);
			thisInstance.registerEmailEvents(detailContentsHolder);
			thisInstance.registerMapsEvents(detailContentsHolder);
			thisInstance.registerSubProducts(detailContentsHolder);
			thisInstance.registerCollapsiblePanels(detailContentsHolder);
			App.Fields.Date.register(detailContentsHolder);
			App.Fields.DateTime.register(detailContentsHolder);
			App.Fields.MultiImage.register(detailContentsHolder);
			App.Fields.Password.register(detailContentsHolder);
			App.Fields.MultiAttachment.register(detailContentsHolder);
			//Attach time picker event to time fields
			app.registerEventForClockPicker();
			this.registerHelpInfo(detailContentsHolder);
			App.Fields.Picklist.showSelect2ElementView(detailContentsHolder.find('select.select2'));
			App.Fields.Text.Editor.register(detailContentsHolder, { toolbar: 'Min' });
			detailContentsHolder.on('click', '#detailViewNextRecordButton', function (e) {
				let url = selectedTabElement.data('url');
				let currentPageNum = thisInstance.getRelatedListCurrentPageNum();
				let requestedPage = parseInt(currentPageNum) + 1;
				let nextPageUrl = url + '&page=' + requestedPage;
				thisInstance.loadContents(nextPageUrl);
			});
			detailContentsHolder.on('click', '#detailViewPreviousRecordButton', function (e) {
				let url = selectedTabElement.data('url');
				let currentPageNum = thisInstance.getRelatedListCurrentPageNum();
				let requestedPage = parseInt(currentPageNum) - 1;
				let nextPageUrl = url + '&page=' + requestedPage;
				thisInstance.loadContents(nextPageUrl);
			});
			detailContentsHolder.on('click', '.js-detail-quick-edit', function (e) {
				thisInstance.ajaxEditHandling(jQuery(e.currentTarget).closest('.fieldValue'));
			});
			detailContentsHolder.on('click', 'div.recordDetails span.squeezedWell', function (e) {
				let currentElement = jQuery(e.currentTarget);
				let relatedLabel = currentElement.data('reference');
				jQuery('.detailViewInfo .related .nav > li[data-reference="' + relatedLabel + '"]').trigger('click');
			});
			detailContentsHolder.on('click', '.relatedPopup', function (e) {
				let editViewObj = new Vtiger_Edit_Js();
				editViewObj.showRecordsList(e);
				return false;
			});
			detailContentsHolder.on('click', '.viewThread', function (e) {
				thisInstance.hideButtonAction();
				let currentTarget = jQuery(e.currentTarget),
					currentTargetParent = currentTarget.parent(),
					commentActionsBlock = currentTarget.closest('.js-comment-actions'),
					currentCommentBlock = currentTarget.closest('.js-comment-details'),
					ulElements = currentCommentBlock.find('ul');
				if (ulElements.length > 0) {
					ulElements.show();
					commentActionsBlock.find('.hideThreadBlock').show();
					currentTargetParent.hide();
					return;
				}
				let commentId = currentTarget.closest('.js-comment-div').find('.js-comment-info-header').data('commentid');
				thisInstance.getChildComments(commentId).done(function (data) {
					jQuery(data).appendTo(jQuery(e.currentTarget).closest('.js-comment-details'));
					commentActionsBlock.find('.hideThreadBlock').show();
					currentTargetParent.hide();
				});
			});
			detailContentsHolder.on('click', '.js-view-parent-thread', function (e) {
				let currentTarget = jQuery(e.currentTarget),
					currentTargetParent = currentTarget.parent(),
					commentId = currentTarget.closest('.js-comment-div').find('.js-comment-info-header').data('commentid');
				thisInstance.getParentComments(commentId).done(function (data) {
					$(e.currentTarget.closest('.js-comment-details')).html(data);
					currentTarget.closest('.js-comment-actions').find('.hideThreadBlock').show();
					currentTargetParent.hide();
				});
			});
			detailContentsHolder.on('click', '.hideThread', function (e) {
				let currentTarget = jQuery(e.currentTarget);
				let currentTargetParent = currentTarget.parent();
				let commentActionsBlock = currentTarget.closest('.js-comment-actions');
				let currentCommentBlock = currentTarget.closest('.js-comment-details');
				currentCommentBlock.find('ul').hide();
				currentTargetParent.hide();
				commentActionsBlock.find('.js-view-thread-block').show();
			});
			detailContentsHolder.on('click', '.detailViewThread', function (e) {
				let recentCommentsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentCommentsTabLabel);
				let commentId = jQuery(e.currentTarget)
					.closest('.js-comment-single')
					.find('.js-comment-info-header')
					.data('commentid');
				let commentLoad = function (data) {
					window.location.href = window.location.href + '#' + commentId;
				};
				recentCommentsTab.trigger('click', { commentid: commentId, callback: commentLoad });
			});
			detailContentsHolder.on('click', '.moreRecentRecords', function (e) {
				e.preventDefault();
				let recentCommentsTab = thisInstance.getTabByModule($(this).data('label-key'), $(this).data('relation-id'));
				if (recentCommentsTab.length) {
					recentCommentsTab.trigger('click');
				} else {
					let currentTarget = $(e.currentTarget),
						container = currentTarget.closest("[class^='widgetContainer_']");
					if (container.length) {
						let page = container.find('[name="page"]:last').val(),
							url = container.data('url');
						currentTarget.prop('disabled', true);
						url = url.replace('&page=1', '&page=' + ++page);
						AppConnector.request(url).done(function (data) {
							let dataObj = $(data),
								containerTable = container.find('.js-detail-widget-content table');
							currentTarget.prop('disabled', false).addClass('d-none');
							container.find('[name="page"]:last').val(dataObj.find('[name="page"]').val());
							if (containerTable.length) {
								containerTable.append(dataObj.find('tbody tr'));
								if (dataObj.find('.moreRecentRecords').length) {
									currentTarget.removeClass('d-none');
								}
							} else {
								container.find('.js-detail-widget-content').append(dataObj);
							}
						});
					}
				}
			});
			detailContentsHolder.on('change', '.relatedHistoryTypes', function (e) {
				let widgetContent = jQuery(this).closest('.widgetContentBlock').find('.widgetContent'),
					progressIndicatorElement = jQuery.progressIndicator({
						position: 'html',
						blockInfo: {
							enabled: true,
							elementToBlock: widgetContent
						}
					});
				AppConnector.request({
					module: app.getModuleName(),
					view: 'Detail',
					record: app.getRecordId(),
					mode: 'showRecentRelation',
					page: 1,
					limit: widgetContent.find('.js-relatedHistoryPageLimit').val(),
					type: $(e.currentTarget).val()
				}).done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					widgetContent.find('#relatedHistoryCurrentPage').remove();
					widgetContent.find('#moreRelatedUpdates').remove();
					widgetContent.html(data);
					Vtiger_Index_Js.registerMailButtons(widgetContent);
				});
			});
			detailContentsHolder.on('click', '.moreProductsService', function () {
				jQuery('.related .mainNav[data-reference="ProductsAndServices"]:not(.d-none)').trigger('click');
			});
			detailContentsHolder.on('click', '.moreRelatedUpdates', function () {
				let widgetContainer = jQuery(this).closest('.widgetContentBlock');
				let widgetContent = widgetContainer.find('.widgetContent');
				let progressIndicatorElement = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true,
						elementToBlock: widgetContent
					}
				});
				let currentPage = widgetContent.find('#relatedHistoryCurrentPage').val();
				let nextPage = parseInt(currentPage) + 1;
				let types = widgetContainer.find('.relatedHistoryTypes').val();
				let pageLimit = widgetContent.find('#relatedHistoryPageLimit').val();
				AppConnector.request({
					module: app.getModuleName(),
					view: 'Detail',
					record: app.getRecordId(),
					mode: 'showRecentRelation',
					page: nextPage,
					limit: pageLimit,
					type: types
				}).done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					widgetContent.find('#relatedHistoryCurrentPage').remove();
					widgetContent.find('#moreRelatedUpdates').remove();
					widgetContent.find('#relatedUpdates').append(data);
				});
			});
			detailContentsHolder.on('click', '.moreRecentUpdates', function (e) {
				const container = $(e.currentTarget).closest('.recentActivitiesContainer');
				let newChange = container.find('#newChange').val(),
					nextPage = parseInt(container.find('#updatesCurrentPage').val()) + 1,
					url;
				if (container.closest('.js-detail-widget').length) {
					let data = thisInstance.getFiltersData(
						e,
						{
							page: nextPage,
							tab_label: 'LBL_UPDATES',
							newChange: newChange
						},
						container.find('#updates')
					);
					url = data['params'];
				} else {
					url = thisInstance.getTabByLabel(thisInstance.detailViewRecentUpdatesTabLabel).data('url');
					url = url.replace('&page=1', '&page=' + nextPage) + '&skipHeader=true&newChange=' + newChange;
					if (url.indexOf('&whereCondition') === -1) {
						let switchBtn = jQuery('.active .js-switch--recentActivities');
						url +=
							'&whereCondition=' +
							(typeof switchBtn.data('on-val') === 'undefined' ? switchBtn.data('off-val') : switchBtn.data('on-val'));
					}
				}
				AppConnector.request(url).done(function (data) {
					let dataContainer = jQuery(data);
					container.find('#newChange').val(dataContainer.find('#newChange').val());
					container.find('#updatesCurrentPage').val(dataContainer.find('#updatesCurrentPage').val());
					container.find('.js-more-link').html(dataContainer.find('.js-more-link').html());
					container.find('#updates ul').append(dataContainer.find('#updates ul').html());
					app.event.trigger('DetailView.UpdatesWidget.AddMore', data, thisInstance);
				});
			});
			detailContentsHolder.on('click', '.btnChangesReviewedOn', function (e) {
				let progressInstance = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				let url = 'index.php?module=ModTracker&action=ChangesReviewedOn&record=' + app.getRecordId();
				AppConnector.request(url).done(function (data) {
					progressInstance.progressIndicator({ mode: 'hide' });
					jQuery(e.currentTarget).parent().remove();
					thisInstance.getTabByLabel(thisInstance.detailViewRecentUpdatesTabLabel).find('.count.badge').text('');
					if (selectedTabElement.data('labelKey') == thisInstance.detailViewRecentUpdatesTabLabel) {
						thisInstance.reloadTabContent();
					} else if (selectedTabElement.data('linkKey') == thisInstance.detailViewSummaryTabLabel) {
						let updatesWidget = detailContentsHolder.find("[data-type='Updates']");
						if (updatesWidget.length > 0) {
							let params = thisInstance.getFiltersData(updatesWidget);
							thisInstance.loadWidget(updatesWidget, params['params']);
						}
					}
				});
			});
			detailContentsHolder.on('click', '.moreRecentDocuments', function () {
				let recentDocumentsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentDocumentsTabLabel);
				recentDocumentsTab.trigger('click');
			});
			detailContentsHolder.on('click', '.moreRecentActivities', function (e) {
				let currentTarget = $(e.currentTarget);
				currentTarget.prop('disabled', true);
				let container = currentTarget.closest('.activityWidgetContainer');
				let page = container.find('.currentPage').val();
				let records = container.find('.countActivities').val();
				let data = thisInstance.getFiltersData(e, { page: ++page });
				AppConnector.request({
					type: 'POST',
					async: false,
					dataType: 'html',
					data: data['params']
				}).done(function (data) {
					currentTarget.prop('disabled', false);
					currentTarget.addClass('d-none');
					container.find('.currentPage').remove();
					container.find('.countActivities').remove();
					container.find('.js-detail-widget-content').append(data);
					let newRecords = container.find('.countActivities').val();
					container.find('.countActivities').val(parseInt(newRecords) + parseInt(records));
					thisInstance.reloadWidgetActivitesStats(container);
				});
			});
			detailContentsHolder.on('click', '.widgetFullscreen', function (e) {
				let currentTarget = $(e.currentTarget);
				let widgetContentBlock = currentTarget.closest('.widgetContentBlock');
				let url = widgetContentBlock.data('url');
				url = url.replace('&view=Detail&', '&view=WidgetFullscreen&');
				let progressIndicatorElement = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				app.showModalWindow(null, 'index.php?' + url, function (modal) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				});
			});
			thisInstance.registerEventForRelatedList();
			thisInstance.registerMailPreviewWidget(detailContentsHolder.find('.widgetContentBlock[data-type="EmailList"]'));
			thisInstance.registerMailPreviewWidget(
				detailContentsHolder.find('.widgetContentBlock[data-type="HistoryRelation"]')
			);
			detailContentsHolder
				.find('.js-switch--recentActivities')
				.off()
				.on('change', function (e) {
					const currentTarget = jQuery(e.currentTarget),
						tabElement = thisInstance.getTabByLabel(thisInstance.detailViewRecentUpdatesTabLabel),
						variableName = currentTarget.data('urlparams'),
						valueOn = $(this).data('on-val'),
						valueOff = $(this).data('off-val');
					let url = tabElement.data('url');
					url = url.replace('&' + variableName + '=' + valueOn, '').replace('&' + variableName + '=' + valueOff, '');
					if (typeof currentTarget.data('on-val') !== 'undefined') {
						url += '&' + variableName + '=' + valueOn;
					} else if (typeof currentTarget.data('off-val') !== 'undefined') {
						url += '&' + variableName + '=' + valueOff;
					}
					tabElement.data('url', url);
					tabElement.trigger('click');
				});
			app.registerIframeEvents(detailContentsHolder);
		},
		reloadWidgetActivitesStats: function (container) {
			let countElement = container.find('.countActivities');
			let totalElement = container.find('.totaltActivities');
			let switchBtn = container.find('.active .js-switch');
			if (!switchBtn.length) {
				switchBtn = container.find('.js-switch.previousMark');
			} else {
				container.find('.js-switch').removeClass('previousMark');
				switchBtn.addClass('previousMark');
			}
			container.find('.js-switch').toggleClass('previousMark');
			if (!countElement.length || !totalElement.length || totalElement.val() === '') {
				return false;
			}
			let stats = ' (' + countElement.val() + '/' + totalElement.val() + ')';
			let switchBtnParent = switchBtn.parent();
			let text = switchBtn.data('basic-text') + stats;
			switchBtnParent.removeTextNode();
			switchBtnParent.append(text);
		},
		refreshCommentContainer: function (commentId) {
			let thisInstance = this;
			let commentContainer = $('.commentsBody');
			let params = {
				module: app.getModuleName(),
				view: 'Detail',
				record: thisInstance.getRecordId(),
				mode: 'showThreadComments',
				commentid: commentId
			};
			let progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true,
					elementToBlock: commentContainer
				}
			});
			AppConnector.request(params).done(function (data) {
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
				commentContainer.html(data);
			});
		},
		updateRecordsPDFTemplateBtn: function (form) {
			const thisInstance = this;
			let btnToolbar = $('.js-btn-toolbar .js-pdf');
			if (btnToolbar.length) {
				AppConnector.request({
					data: {
						module: app.getModuleName(),
						action: 'PDF',
						mode: 'hasValidTemplate',
						record: app.getRecordId(),
						view: app.getViewName()
					},
					dataType: 'json'
				})
					.done(function (data) {
						if (data['result'].valid === false) {
							btnToolbar.addClass('d-none');
						} else {
							btnToolbar.removeClass('d-none');
						}
					})
					.fail(function (data, err) {
						app.errorLog(data, err);
					});
			}
		},
		updateWindowHeight: function (currentHeight, frame) {
			frame.height(currentHeight);
		},
		loadSubProducts: function (parentRow) {
			const thisInstance = this;
			let recordId = parentRow.data('product-id'),
				subProrductParams = {
					module: 'Products',
					action: 'SubProducts',
					record: recordId
				};
			AppConnector.request(subProrductParams).done(function (data) {
				let responseData = data.result;
				thisInstance.addSubProducts(parentRow, responseData);
			});
		},
		addSubProducts: function (parentRow, responseData) {
			let subProductsContainer = $('.js-subproducts-container ul', parentRow);
			for (let id in responseData) {
				let productText = $('<li>').text(responseData[id]);
				subProductsContainer.append(productText);
			}
		},
		registerSubProducts: function (container) {
			const thisInstance = this;
			container.find('.inventoryItems .js-inventory-row').each(function (index) {
				thisInstance.loadSubProducts($(this), false);
			});
		},
		registerCollapsiblePanels(detailViewContainer) {
			const panels = detailViewContainer.find('.js-detail-widget-collapse');
			const storageName = `yf-${app.getModuleName()}-detail-widgets`;
			if (Quasar.plugins.LocalStorage.has(storageName)) {
				this.setPanels({ panels, storageName });
			} else {
				panels.collapse('show');
				let panelsStorage = {};
				panels.each((i, item) => {
					panelsStorage[item.dataset.storageId] = 'shown';
				});
				Quasar.plugins.LocalStorage.set(storageName, panelsStorage);
			}
			panels.on('hidden.bs.collapse shown.bs.collapse', (e) => {
				this.updatePanelsStorage({ id: e.target.dataset.storageKey, type: e.type, storageName });
			});
			panels.on('hide.bs.collapse show.bs.collapse', function (e) {
				$(e.currentTarget).siblings('.js-detail-widget-header').toggleClass('collapsed');
			});
		},
		setPanels({ panels, storageName }) {
			const panelsStorage = Quasar.plugins.LocalStorage.getItem(storageName);
			panels.each((i, item) => {
				if (
					panelsStorage[item.dataset.storageKey] === 'shown' ||
					undefined === panelsStorage[item.dataset.storageKey]
				) {
					$(item).collapse('show');
					$(item).siblings('.js-detail-widget-header').toggleClass('collapsed');
				}
			});
		},
		updatePanelsStorage({ id, type, storageName }) {
			const panelsStorage = Quasar.plugins.LocalStorage.getItem(storageName);
			panelsStorage[id] = type;
			Quasar.plugins.LocalStorage.set(storageName, panelsStorage);
		},
		registerSendPdfFromPdfViewer: function (container) {
			container.find('.js-email-pdf').on('click', function (e) {
				let selectedPdfTemplate = $(e.currentTarget).closest('.js-detail-widget').find('.js-pdf-viewer-template').val();
				let url = $(this).attr('data-url');
				if (url && selectedPdfTemplate && selectedPdfTemplate > 0) {
					window.open(url + selectedPdfTemplate, '_blank');
				}
			});
		},
		/**
		 * Register keyboard shortcuts events
		 * @param {jQuery} container
		 */
		registerKeyboardShortcutsEvent: function (container) {
			document.addEventListener('keydown', (event) => {
				if (event.shiftKey && event.ctrlKey && event.code === 'KeyD') {
					container.find('.js-duplicate-btn').trigger('click');
				}
				if (event.shiftKey && event.ctrlKey && event.code === 'KeyE' && container.find('.js-edit-btn').length) {
					container.find('.js-edit-btn').trigger('click');
				}
				if (event.shiftKey && event.ctrlKey && event.code === 'KeyW' && container.find('.js-edit-btn').length) {
					App.Components.QuickEdit.showModal({
						module: app.getModuleName(),
						record: app.getRecordId(),
						removeFromUrl: 'step'
					});
				}
			});
		},
		registerEvents: function () {
			this.registerSendSmsSubmitEvent();
			this.registerAjaxEditEvent();
			this.registerRelatedRowClickEvent();
			this.registerBlockStatusCheckOnLoad();
			this.registerEmailFieldClickEvent();
			this.registerPhoneFieldClickEvent();
			this.registerEventForRelatedTabClick();
			Vtiger_Helper_Js.showHorizontalTopScrollBar();
			this.registerUrlFieldClickEvent();
			let detailViewContainer = jQuery('div.detailViewContainer');
			if (detailViewContainer.length <= 0) {
				// Not detail view page
				return;
			}
			this.registerWidgetProductAndServices();
			this.registerSetReadRecord(detailViewContainer);
			this.getForm().validationEngine(app.validationEngineOptionsForRecord);
			this.loadWidgetsEvents();
			this.loadWidgets();
			this.registerBasicEvents();
			this.registerEventForTotalRecordsCount();
			this.registerProgress();
			this.registerChat(detailViewContainer);
			this.registerSendPdfFromPdfViewer(detailViewContainer);
			this.registerKeyboardShortcutsEvent(detailViewContainer);
			App.Components.ActivityNotifier.register(detailViewContainer);
		}
	}
);
