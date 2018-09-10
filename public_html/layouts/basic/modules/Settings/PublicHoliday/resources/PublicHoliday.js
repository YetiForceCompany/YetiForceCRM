/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class('Settings_PublicHoliday_Js', {}, {

	/**
	 * Function that deletes holiday from list
	 */
	registerDeleteHoliday: function (element) {
		const thisInstance = this;
		element.find('.deleteHoliday').each(function () {
			jQuery(this).on('click', function () {
				thisInstance.deleteHoliday(jQuery(this).data('holiday-id'));
			});
		});
	},
	/**
	 * Delete chosen holiday date
	 */
	deleteHoliday: function (holidayId) {
		const thisInstance = this,
			progressIndicatorElement = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
		AppConnector.request({
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			action: "Holiday",
			mode: "delete",
			id: holidayId
		}).done(function (data) {
			AppConnector.request({
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				view: 'Configuration',
				async: false
			}).done(function (data) {
				jQuery('.contentsDiv').html(data);
				thisInstance.registerEvents();
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
			});
			Settings_Vtiger_Index_Js.showMessage({text: data.result.message});
		}).fail(function () {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
		});
	},
	/**
	 * Function to register click event for add custom block button
	 */
	registerAddDate: function () {
		const thisInstance = this,
			contents = jQuery('#layoutDashBoards');
		contents.find('.addDateWindow').on('click', function (e) {
			let addBlockContainer = contents.find('.addDateWindowModal').clone(true, true),
				translate = app.vtranslate('JS_ADD_NEW_HOLIDAY');
			addBlockContainer.find('.modal-title').text(translate);
			let callBackFunction = function (data) {
				data.find('.addDateWindowModal').removeClass('d-none').show();
				let form = data.find('.addDateWindowForm');
				jQuery('[name="holidayId"]').val('');
				jQuery(document).find('div.blockOverlay').on('click', function () {
					let progressIndicatorElement = jQuery.progressIndicator({
						'position': 'html',
						'blockInfo': {
							'enabled': true
						}
					});
					AppConnector.request({
						module: app.getModuleName(),
						view: 'Configuration',
						parent: app.getParentModuleName()
					}).done(function (data) {
						jQuery('.contentsDiv').html(data);
						thisInstance.registerEvents();
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
					});
				});

				jQuery('.cancelLink').on('click', function () {
					let progressIndicatorElement = jQuery.progressIndicator({
						'position': 'html',
						'blockInfo': {
							'enabled': true
						}
					});
					AppConnector.request({
						module: app.getModuleName(),
						view: 'Configuration',
						parent: app.getParentModuleName()
					}).done(function (data) {
						jQuery('.contentsDiv').html(data);
						thisInstance.registerEvents();
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
					});
				});

				form.on('submit', function (e) {
					e.preventDefault();
					let progressIndicatorElement = jQuery.progressIndicator({
						'position': 'html',
						'blockInfo': {
							'enabled': true
						}
					});
					thisInstance.saveNewDate(form).done(function (data) {
						if (data['success']) {
							Settings_Vtiger_Index_Js.showMessage({text: data['result']['message']});
							AppConnector.request({
								module: app.getModuleName(),
								view: 'Configuration',
								parent: app.getParentModuleName()
							}).done(function (data) {
								jQuery('.contentsDiv').html(data);
								thisInstance.registerEvents();
								progressIndicatorElement.progressIndicator({'mode': 'hide'});
							});
						} else {
							progressIndicatorElement.progressIndicator({'mode': 'hide'});
							Settings_Vtiger_Index_Js.showMessage({
								text: data['result']['message'],
								type: 'error'
							});
						}
					});
					app.hideModalWindow();
					return true;
				})
			};
			app.showModalWindow(addBlockContainer, function (data) {
				if (typeof callBackFunction == 'function') {
					callBackFunction(data);
				}
			}, {'width': '1000px'});
		});
	},

	/**
	 * Function to register click event for add custom block button
	 */
	registerEditDate: function () {
		const thisInstance = this,
			contents = jQuery('#layoutDashBoards');
		contents.find('.editHoliday').on('click', function (e) {
			let addBlockContainer = contents.find('.addDateWindowModal').clone(true, true),
				dateElement = jQuery(this).closest('.holidayElement');
			addBlockContainer.find('[name="holidayId"]').val(dateElement.data('holiday-id'));
			addBlockContainer.find('[name="holidayDate"]').val(dateElement.data('holiday-date'));
			addBlockContainer.find('[name="holidayName"]').val(dateElement.data('holiday-name'));
			addBlockContainer.find('[name="holidayType"]').val(dateElement.data('holiday-type'));
			let translate = app.vtranslate('JS_EDIT_HOLIDAY');
			addBlockContainer.find('.modal-title').text(translate);

			let callBackFunction = function (data) {
				data.find('.addDateWindowModal').removeClass('d-none').show();
				let form = data.find('.addDateWindowForm');
				jQuery('[name="saveButton"]').on('click', function () {
					let progressIndicatorElement = jQuery.progressIndicator({
						'position': 'html',
						'blockInfo': {
							'enabled': true
						}
					});
					thisInstance.saveNewDate(form).done(function (data) {
						if (data['success']) {
							Settings_Vtiger_Index_Js.showMessage({text: data['result']['message']});
							AppConnector.request({
								module: app.getModuleName(),
								view: 'Configuration',
								parent: app.getParentModuleName()
							}).done(function (data) {
								jQuery('.contentsDiv').html(data);
								thisInstance.registerEvents();
								progressIndicatorElement.progressIndicator({'mode': 'hide'});
							});
						} else {
							progressIndicatorElement.progressIndicator({'mode': 'hide'});
							Settings_Vtiger_Index_Js.showMessage({
								text: data['result']['message'],
								type: 'error'
							});
						}
					});
					app.hideModalWindow();
					return true;
				});

				jQuery(document).find('div.blockOverlay').on('click', function () {
					let progressIndicatorElement = jQuery.progressIndicator({
						'position': 'html',
						'blockInfo': {
							'enabled': true
						}
					});
					AppConnector.request({
						module: app.getModuleName(),
						view: 'Configuration',
						parent: app.getParentModuleName()
					}).done(function (data) {
						jQuery('.contentsDiv').html(data);
						thisInstance.registerEvents();
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
					});
				});

				jQuery('.cancelLink').on('click', function () {
					let progressIndicatorElement = jQuery.progressIndicator({
						'position': 'html',
						'blockInfo': {
							'enabled': true
						}
					});
					AppConnector.request({
						module: app.getModuleName(),
						view: 'Configuration',
						parent: app.getParentModuleName()
					}).done(function (data) {
						jQuery('.contentsDiv').html(data);
						thisInstance.registerEvents();
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
					});
				});

				form.on('submit', function (e) {
					e.preventDefault();
				});
			};
			app.showModalWindow(addBlockContainer, function (data) {
				if (typeof callBackFunction == 'function') {
					callBackFunction(data);
				}
			}, {'width': '1000px'});
		});
	},

	/**
	 * Function to save the new custom block details
	 */
	saveNewDate: function (form) {
		const thisInstance = this,
			params = form.serializeFormData();
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'Holiday';
		params['mode'] = 'save';

		if (params['holidayName'] == '' || params['holidayDate'] == '') {
			Settings_Vtiger_Index_Js.showMessage({
				text: app.vtranslate('JS_FILL_FORM_ERROR'),
				type: 'error'
			});
			let progressIndicatorElement = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			AppConnector.request({
				module: app.getModuleName(),
				view: 'Configuration',
				parent: app.getParentModuleName()
			}).done(function (data) {
				jQuery('.contentsDiv').html(data);
				thisInstance.registerEvents();
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
			});
		} else {
			let aDeferred = jQuery.Deferred();
			AppConnector.request(params).done(function (data) {
				aDeferred.resolve(data);
			}).fail(function (error) {
				aDeferred.reject(error);
			});
			return aDeferred.promise();
		}

		return true;
	},

	registerChangeDate: function () {
		const thisInstance = this,
			dateFilter = jQuery('.dateFilter');
		App.Fields.Date.registerRange(dateFilter, {ranges: false});
		dateFilter.on('apply.daterangepicker', function (ev, picker) {
			let format = jQuery(ev.currentTarget).data('dateFormat').toUpperCase();
			$(this).val(picker.startDate.format(format) + ',' + picker.endDate.format(format));
			let progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request({
				module: app.getModuleName(),
				view: 'Configuration',
				parent: app.getParentModuleName(),
				date: '["' + picker.startDate.format(format) + '","' + picker.endDate.format(format) + '"]'
			}).done(function (data) {
				jQuery('.contentsDiv').html(data);
				thisInstance.registerEvents();
				progressIndicatorElement.progressIndicator({mode: 'hide'});
			});
		});
	},

	/**
	 * register events for layout editor
	 */
	registerEvents: function () {
		this.registerDeleteHoliday(jQuery('#moduleBlocks'));
		this.registerAddDate();
		this.registerEditDate();
		this.registerChangeDate();
	}
});

jQuery(document).ready(function () {
	let instance = new Settings_PublicHoliday_Js();
	instance.registerEvents();
});
