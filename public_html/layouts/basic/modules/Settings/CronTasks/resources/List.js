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

Settings_Vtiger_List_Js(
	'Settings_CronTasks_List_Js',
	{
		triggerEditEvent: function (editUrl) {
			AppConnector.request(editUrl).done(function (data) {
				app.showModalWindow(data);
				jQuery('#cronJobSaveAjax').validationEngine(app.validationEngineOptions);
				var listViewInstance = Settings_CronTasks_List_Js.getInstance();
				listViewInstance.registerSaveEvent();
			});
		}
	},
	{
		getListViewRecords: function () {
			var thisInstance = this;
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				view: 'List'
			};
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request(params).done(function (data) {
				jQuery('#listViewContents').html(data);
				thisInstance.registerSortableEvent();
				progressIndicatorElement.progressIndicator({
					mode: 'hide'
				});
			});
		},

		registerSaveEvent: function () {
			var thisInstance = this;
			jQuery('#cronJobSaveAjax').on('submit', function (e) {
				var form = jQuery(e.currentTarget);
				var validationResult = form.validationEngine('validate');
				if (validationResult == true) {
					var timeFormat = jQuery('#time_format').val();
					var frequencyElement = jQuery('#frequencyValue');
					if (timeFormat == 'hours') {
						var frequencyValue = frequencyElement.val() * 60 * 60;
					} else {
						frequencyValue = frequencyElement.val() * 60;
					}
					var message = app.vtranslate('JS_VALUE_SHOULD_NOT_BE_LESS_THAN');
					var minimumFrequency = jQuery('#minimumFrequency').val();
					var minutes = app.vtranslate('JS_MINUTES');
					if (frequencyValue < minimumFrequency) {
						frequencyElement.validationEngine(
							'showPrompt',
							message + ' ' + minimumFrequency / 60 + ' ' + minutes,
							'error'
						);
						e.preventDefault();
						return;
					} else {
						jQuery('#frequency').val(frequencyValue);
					}
					var params = form.serializeFormData();
					AppConnector.request(params).done(function (data) {
						if (typeof data.result !== 'undefined' && data.result[0] == true) {
							app.hideModalWindow();
							thisInstance.getListViewRecords();
						}
					});
				}
				e.preventDefault();
			});
		},

		registerSortableEvent: function () {
			var thisInstance = this;
			var sequenceList = {};
			var tbody = jQuery('tbody', jQuery('.listViewEntriesTable'));
			tbody.sortable({
				helper: function (e, ui) {
					//while dragging helper elements td element will take width as contents width
					//so we are explicity saying that it has to be same width so that element will not
					//look like distrubed
					ui.children().each(function (index, element) {
						element = jQuery(element);
						element.width(element.width());
					});
					return ui;
				},
				containment: tbody,
				revert: true,
				update: function (e, ui) {
					jQuery('tbody tr').each(function (i) {
						sequenceList[++i] = jQuery(this).data('id');
					});
					var params = {
						sequencesList: JSON.stringify(sequenceList),
						module: app.getModuleName(),
						parent: app.getParentModuleName(),
						action: 'UpdateSequence'
					};
					AppConnector.request(params).done(function (data) {
						thisInstance.getListViewRecords();
					});
				}
			});
		},

		/**
		 * Register events
		 * @returns {undefined}
		 */
		registerEvents: function () {
			this.registerSortableEvent();
		}
	}
);
