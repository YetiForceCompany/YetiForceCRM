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
	'Contacts_Detail_Js',
	{},
	{
		hierarchyResponseCache: {},
		/**
		 * Function to register recordpresave event
		 */
		registerRecordPreSaveEvent: function (form) {
			var primaryEmailField = jQuery('[name="email"]');
			if (typeof form === 'undefined') {
				form = this.getForm();
			}

			form.on(this.fieldPreSave, '[name="portal"]', function (e, data) {
				var portalField = jQuery(e.currentTarget);

				var primaryEmailValue = primaryEmailField.val();
				var isAlertAlreadyShown = jQuery('.ui-pnotify').length;

				if (portalField.is(':checked')) {
					if (primaryEmailField.length == 0) {
						if (isAlertAlreadyShown <= 0) {
							app.showNotify({
								text: app.vtranslate('JS_PRIMARY_EMAIL_FIELD_DOES_NOT_EXISTS'),
								type: 'error'
							});
						}
						e.preventDefault();
					}
					if (primaryEmailValue == '') {
						if (isAlertAlreadyShown <= 0) {
							app.showNotify({
								text: app.vtranslate('JS_PLEASE_ENTER_PRIMARY_EMAIL_VALUE_TO_ENABLE_PORTAL_USER'),
								type: 'info'
							});
						}
						e.preventDefault();
					}
				}
			});
		},

		/**
		 * Function to get response from hierarchy
		 * @param {array} params
		 * @returns {Promise}
		 */
		getHierarchyResponseData: function (params) {
			let thisInstance = this,
				aDeferred = jQuery.Deferred();

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
		 * function to display the hierarchy response data
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
		 * Registers read count of hierarchy if it is possoble
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
		 * Function which will register all the events
		 */
		registerEvents: function () {
			var form = this.getForm();
			this._super();
			this.registerHierarchyRecordCount();
			this.registerShowHierarchy();
			this.registerRecordPreSaveEvent(form);
		}
	}
);
