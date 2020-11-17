/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Vtiger_Detail_Js(
	'Project_Detail_Js',
	{},
	{
		/**
		 * Function to register event for create related record
		 * in summary view widgets
		 */
		registerSummaryViewContainerEvents: function (summaryViewContainer) {
			this._super(summaryViewContainer);
		},
		/**
		 * Function to load module summary of Projects
		 */
		loadModuleSummary: function () {
			var summaryParams = {};
			summaryParams['module'] = app.getModuleName();
			summaryParams['view'] = 'Detail';
			summaryParams['mode'] = 'showModuleSummaryView';
			summaryParams['record'] = jQuery('#recordId').val();
			AppConnector.request(summaryParams).done(function (data) {
				jQuery('.js-widget-general-info').html(data);
			});
		},
		/**
		 * Load gantt component
		 */
		loadGantt() {
			let ganttContainer = $('.c-gantt', this.detailViewContentHolder);
			if (ganttContainer.length) {
				let gantt = new Project_Gantt_Js(this.detailViewContentHolder);
				gantt.registerEvents();
			}
		},
		/**
		 * Load gantt component when needed
		 */
		registerGantt() {
			this.loadGantt();
			app.event.on('DetailView.Tab.AfterLoad', (e, data, instance) => {
				instance.detailViewContentHolder.ready(() => {
					this.loadGantt();
				});
			});
		},
		/**
		 * Function to get response from hierarchy
		 * @param {array} params
		 * @returns {jQuery}
		 */
		getHierarchyResponseData: function (params) {
			let thisInstance = this,
				aDeferred = $.Deferred();
			if (!$.isEmptyObject(thisInstance.hierarchyResponseCache)) {
				aDeferred.resolve(thisInstance.hierarchyResponseCache);
			} else {
				AppConnector.request(params).then(function (data) {
					thisInstance.hierarchyResponseCache = data;
					aDeferred.resolve(thisInstance.hierarchyResponseCache);
				});
			}
			return aDeferred.promise();
		},
		/**
		 * Function to display the hierarchy response data
		 * @param {array} data
		 */
		displayHierarchyResponseData: function (data) {
			let callbackFunction = function () {
				app.showScrollBar($('#hierarchyScroll'), {
					height: '300px',
					railVisible: true,
					size: '6px'
				});
			};
			app.showModalWindow(data, function (modalContainer) {
				App.Components.Scrollbar.xy($('#hierarchyScroll', modalContainer));
				if (typeof callbackFunction == 'function' && $('#hierarchyScroll', modalContainer).height() > 300) {
					callbackFunction();
				}
			});
		},
		/**
		 * Registers read count of hierarchy if it is possible
		 */
		registerHierarchyRecordCount: function () {
			let hierarchyButton = $('.js-detail-hierarchy'),
				params = {
					module: app.getModuleName(),
					action: 'RelationAjax',
					record: app.getRecordId(),
					mode: 'getHierarchyCount'
				};
			if (hierarchyButton.length) {
				AppConnector.request(params).then(function (response) {
					if (response.success) {
						$('.hierarchy .badge').html(response.result);
					}
				});
			}
		},
		/**
		 * Shows hierarchy
		 */
		registerShowHierarchy: function () {
			let thisInstance = this,
				hierarchyButton = $('.detailViewTitle'),
				params = {
					module: app.getModuleName(),
					view: 'Hierarchy',
					record: app.getRecordId()
				};
			hierarchyButton.on('click', '.js-detail-hierarchy', function () {
				let progressIndicatorElement = $.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				thisInstance.getHierarchyResponseData(params).then(function (data) {
					thisInstance.displayHierarchyResponseData(data);
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				});
			});
		},
		/**
		 * Register events
		 */
		registerEvents: function () {
			var detailContentsHolder = this.getContentHolder();
			var thisInstance = this;
			this._super();
			detailContentsHolder.on('click', '.moreRecentTickets', function () {
				var recentTicketsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentTicketsTabLabel);
				recentTicketsTab.trigger('click');
			});
			this.registerGantt();
			this.registerHierarchyRecordCount();
			this.registerShowHierarchy();
		}
	}
);
