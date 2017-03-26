/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

jQuery.Class("Calendar_ActivityStateModal_Js", {}, {
	registerActivityState: function () {
		var thisInstance = this;
		jQuery('#activityStateModal button:not(.close)').on('click', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			currentTarget.closest('.modal').addClass('hide');

			if (thisInstance.saveResultInstance == undefined) {
				thisInstance.saveResultInstance = new SaveResult();
			}

			if (currentTarget.data('type') == '1') {
				thisInstance.updateActivityState(currentTarget);
			}
			if (currentTarget.hasClass('showQuickCreate')) {
				var progressIndicatorElement = jQuery.progressIndicator({
					'position': 'html',
					'blockInfo': {
						'enabled': true
					}
				});
				var moduleName = 'Calendar';
				var url = 'index.php?module=Calendar&view=QuickCreateAjax&addRelation=true&sourceModule=Calendar&sourceRecord=' + currentTarget.data('id');
				var params = {};
				params.noCache = true;
				var subject = currentTarget.closest('.modalEditStatus').find('.modalSummaryValues .fieldVal').data('subject');
				var headerInstance = Vtiger_Header_Js.getInstance();
				headerInstance.getQuickCreateForm(url, moduleName, params).then(function (data) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					if (currentTarget.data('type') == '0' && typeof subject != 'undefined' && subject.length > 0) {
						data = jQuery(data);
						var element = data.find('[name="subject"]');
						if (element.length) {
							element.val(subject);
						}
					}
					headerInstance.handleQuickCreateData(data, {callbackFunction: function (data) {
							if (data && data.success && data.result.followup.value == currentTarget.data('id')) {
								thisInstance.updateActivityState(currentTarget);
							}
							var formData2 = {};
							formData2.record = currentTarget.data('id');
							formData2.module = 'Calendar';
							formData2.view = 'quick_edit';
							formData2['activitystatus'] = currentTarget.data('state');
							thisInstance.saveResultInstance.checkData(formData2);
						}});
				});
			}
		});
	},
	updateActivityState: function (currentTarget) {
		var thisInstance = this;
		var params = {
			module: 'Calendar',
			action: "ActivityStateAjax",
			record: currentTarget.data('id'),
			state: currentTarget.data('state')
		};
		app.hideModalWindow();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		AppConnector.request(params).then(
				function (data) {
					if (data.success) {
						var viewName = app.getViewName();
						if (viewName === 'Detail') {
							var widget = jQuery('.activityWidgetContainer .widgetContentBlock');
							var thisInstance = Vtiger_Detail_Js.getInstance();
							if (widget.length) {
								thisInstance.loadWidget(widget);
							} else {
								var recentActivitiesTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentActivitiesTabLabel);
								if (recentActivitiesTab) {
									recentActivitiesTab.trigger('click');
								}
								if (app.getModuleName() == 'Calendar') {
									recentActivitiesTab = ((!thisInstance.getSelectedTab().length || thisInstance.getSelectedTab().data('linkKey') == thisInstance.detailViewDetailsTabLabel) ? thisInstance.getTabContainer().find('[data-link-key="' + thisInstance.detailViewDetailsTabLabel + '"]:not(.hide)') : jQuery('<div></div>'));
									jQuery('.showModal.closeCalendarRekord').addClass('hide');
									recentActivitiesTab.trigger('click');
								}
							}
						}
						if (viewName == 'List') {
							var listinstance = new Vtiger_List_Js();
							listinstance.getListViewRecords();
						}
						if (viewName == 'DashBoard') {
							(new Vtiger_DashBoard_Js()).getContainer().find('a[name="drefresh"]').trigger('click');
						}
						if (app.getModuleName() == 'Calendar' && viewName == 'Calendar') {
							(Calendar_CalendarView_Js.getInstanceByView()).loadCalendarData();
						}
						//updates the Calendar Reminder popup's status
						Vtiger_Index_Js.requestReminder();
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
					} else {
						return false;
					}
				}
		);
	},
	registerEvents: function () {
		this.registerActivityState();
	}

});

jQuery(document).ready(function (e) {
	var instance = new Calendar_ActivityStateModal_Js();
	instance.registerEvents();
});
