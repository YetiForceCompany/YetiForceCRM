/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Calendar_ActivityStateModal_Js',
	{},
	{
		registerActivityState() {
			const self = this;
			$('.js-activity-buttons button:not(.close)').on('click', function (e) {
				let currentTarget = $(e.currentTarget),
					viewName = app.getViewName();
				app.hideModalWindow();
				if (1 === currentTarget.data('type')) {
					self.updateActivityState(currentTarget);
				} else {
					let isReminder = currentTarget.closest('#calendar-reminder-modal').length;
					if (app.getModuleName() === 'Calendar' && viewName === 'CalendarExtended' && !isReminder) {
						let calendarInstance = new Calendar_Calendar_Js();
						calendarInstance.getCalendarSidebarData({
							module: 'Calendar',
							view: 'EventForm',
							record: currentTarget.data('id'),
							isDuplicate: true,
							addRelation: true,
							sourceModule: 'Calendar',
							sourceRecord: currentTarget.data('id')
						});
					} else if (currentTarget.hasClass('showQuickCreate') || isReminder) {
						let progressIndicatorElement = $.progressIndicator({
								position: 'html',
								blockInfo: {
									enabled: true
								}
							}),
							url =
								'index.php?module=Calendar&view=QuickCreateAjax&addRelation=true&sourceModule=Calendar&sourceRecord=' +
								currentTarget.data('id') +
								'&fillFields=all',
							params = {};
						params.noCache = true;
						App.Components.QuickCreate.getForm(url, 'Calendar', params).done(function (data) {
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
							App.Components.QuickCreate.showModal(data, {
								callbackFunction: function (data) {
									if (data && data.success && data.result.followup.value == currentTarget.data('id')) {
										self.updateActivityState(currentTarget);
									}
								}
							});
						});
					}
				}
			});
		},
		updateActivityState: function (currentTarget) {
			let params = {
				module: 'Calendar',
				action: 'ActivityStateAjax',
				record: currentTarget.data('id'),
				state: currentTarget.data('state')
			};
			app.hideModalWindow();
			let progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request(params).done(function (data) {
				if (data.success) {
					let viewName = app.getViewName();
					if (viewName === 'Detail') {
						const thisInstance = Vtiger_Detail_Js.getInstance();
						let widget = $('.activityWidgetContainer .widgetContentBlock');
						if (widget.length) {
							thisInstance.loadWidget(widget);
						} else {
							let recentActivitiesTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentActivitiesTabLabel);
							if (recentActivitiesTab) {
								recentActivitiesTab.trigger('click');
							}
							if (app.getModuleName() === 'Calendar') {
								recentActivitiesTab =
									!thisInstance.getSelectedTab().length ||
									thisInstance.getSelectedTab().data('linkKey') == thisInstance.detailViewDetailsTabLabel
										? thisInstance
												.getTabContainer()
												.find('[data-link-key="' + thisInstance.detailViewDetailsTabLabel + '"]:not(.d-none)')
										: $('<div></div>');
								$('.showModal.closeCalendarRekord').addClass('d-none');
								recentActivitiesTab.trigger('click');
							}
						}
					}
					if (viewName === 'List') {
						let listinstance = new Vtiger_List_Js();
						listinstance.getListViewRecords();
					}
					if (viewName === 'DashBoard') {
						new Vtiger_DashBoard_Js().getContainer().find('.js-widget-refresh').trigger('click');
					}
					if (app.getModuleName() === 'Calendar' && (viewName === 'Calendar' || viewName === 'CalendarExtended')) {
						const calendarInstance = new window[`Calendar_${viewName}_Js`]();
						calendarInstance.loadCalendarData();
						calendarInstance.getCalendarCreateView();
					}
					//updates the Calendar Reminder popup's status
					Vtiger_Index_Js.requestReminder();
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				} else {
					return false;
				}
			});
		},
		registerEvents: function () {
			this.registerActivityState();
		}
	}
);

jQuery(document).ready(function (e) {
	var instance = new Calendar_ActivityStateModal_Js();
	instance.registerEvents();
});
