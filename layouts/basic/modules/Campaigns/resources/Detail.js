/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/

Vtiger_Detail_Js("Campaigns_Detail_Js", {}, {
	loadRelatedList: function (params) {
		var aDeferred = jQuery.Deferred();
		if (params == undefined) {
			params = {};
		}
		var relatedListInstance = new Campaigns_RelatedList_Js(this.getRecordId(), app.getModuleName(), this.getSelectedTab(), this.getRelatedModuleName());
		this.clearSelectedRecords();
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
	/**
	 * Function to clear selected records
	 */
	clearSelectedRecords: function () {
		jQuery('[name="selectedIds"]').data('selectedIds', "");
		jQuery('[name="excludedIds"]').data('excludedIds', "");
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
			var relatedController = new Campaigns_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.nextPageHandler().then(function (data) {
				var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
				if (emailEnabledModule) {
					thisInstance.registerEmailEnabledActions();
				}
			});
		});
		detailContentsHolder.on('click', '#relatedViewPreviousPageButton', function () {
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Campaigns_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.previousPageHandler().then(function (data) {
				var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
				if (emailEnabledModule) {
					thisInstance.registerEmailEnabledActions();
				}
			});
		});
		detailContentsHolder.on('click', '#relatedListPageJump', function (e) {
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Campaigns_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.getRelatedPageCount();
		});
		detailContentsHolder.on('click', '#relatedListPageJumpDropDown > li', function (e) {
			e.stopImmediatePropagation();
		}).on('keypress', '#pageToJump', function (e) {
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Campaigns_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.pageJumpHandler(e).then(function (data) {
				var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
				if (emailEnabledModule) {
					thisInstance.registerEmailEnabledActions();
				}
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
			var relatedController = new Campaigns_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.sortHandler(element).then(function (data) {
				var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
				if (emailEnabledModule) {
					thisInstance.registerEmailEnabledActions();
				}
			});
		});

		detailContentsHolder.on('click', 'button.selectRelation', function (e) {
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Campaigns_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.showSelectRelationPopup().then(function (data) {
				var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
				if (emailEnabledModule) {
					thisInstance.registerEmailEnabledActions();
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
						var selectedTabElement = thisInstance.getSelectedTab();
						var relatedModuleName = thisInstance.getRelatedModuleName();
						var relatedController = new Campaigns_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
						relatedController.deleteRelation([relatedRecordid]).then(function (response) {
							var widget = element.closest('.widgetContentBlock');
							if (widget.length) {
								thisInstance.loadWidget(widget);
								var updatesWidget = detailContentsHolder.find("[data-type='Updates']");
								if (updatesWidget.length > 0) {
									thisInstance.loadWidget(updatesWidget);
								}
							} else {
								relatedController.loadRelatedList().then(function (data) {
									var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
									if (jQuery('#selectedIds').length) {
										var listInstance = Vtiger_List_Js.getInstance();
										var selectedIds = listInstance.readSelectedIds();
										if (selectedIds != 'all') {
											relatedRecordid = relatedRecordid.toString();
											var idIndex = jQuery.inArray(relatedRecordid, selectedIds);
											if (idIndex != -1) {
												selectedIds.splice(idIndex, 1);
												listInstance.writeSelectedIds(selectedIds);
											}
										}
									}
									if (emailEnabledModule) {
										thisInstance.registerEmailEnabledActions();
									}
								});
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
					});
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

			var relatedController = new Campaigns_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.addRelatedRecord(element).then(function (data) {
				var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
				if (emailEnabledModule) {
					thisInstance.registerEmailEnabledActions();
				}
				relatedController.registerEvents();
			});
		});
	},
	/**
	 * Function to register email enabled actions
	 */
	registerEmailEnabledActions: function () {
		var moduleName = app.getModuleName();
		var className = moduleName + "_List_Js";
		var listInstance = new window[className]();
		listInstance.registerEvents();
		listInstance.markSelectedRecords();
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
							thisInstance.registerHelpInfo();
							app.registerModal(detailContentsHolder);
							app.registerMoreContent(detailContentsHolder.find('button.moreBtn'));
							element.progressIndicator({'mode': 'hide'});
							var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
							if (emailEnabledModule) {
								var listInstance = new Campaigns_List_Js();
								listInstance.registerEvents();
								thisInstance.registerRelatedListEvents();
							}
							if (typeof callBack == 'function') {
								callBack(data);
							}
							//Summary tab is clicked
							if (tabElement.data('linkKey') == thisInstance.detailViewSummaryTabLabel) {
								thisInstance.loadWidgets();
							}
							thisInstance.registerBasicEvents();
						},
						function () {
							element.progressIndicator({'mode': 'hide'});
						}
				);
			}
		});
	},
	/**
	 * Function to register related list events
	 */
	registerRelatedListEvents: function () {
		var selectedTabElement = this.getSelectedTab();
		var relatedModuleName = this.getRelatedModuleName();
		var relatedController = new Campaigns_RelatedList_Js(this.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
		relatedController.registerEvents();
	},
	registerEvents: function () {
		this._super();
		this.registerRelatedListEvents();
		//Calling registerevents of campaigns list to handle checkboxs click of related records
		var listInstance = Vtiger_List_Js.getInstance();
		listInstance.registerEvents();
	}
});
