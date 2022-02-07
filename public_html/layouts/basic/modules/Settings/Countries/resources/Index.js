/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Settings_Countries_Index_Js',
	{},
	{
		registerSortableEvent: function () {
			var thisInstance = this;
			var tbody = $('tbody', $('.listViewEntriesTable'));
			tbody.sortable({
				helper: function (e, ui) {
					//while dragging helper elements td element will take width as contents width
					//so we are explicity saying that it has to be same width so that element will not
					//look like distrubed
					ui.children().each(function (index, element) {
						element = $(element);
						element.width(element.width());
					});
					return ui;
				},
				containment: tbody,
				revert: true,
				update: function (e, ui) {
					thisInstance.registerSequenceListOnServer();
				}
			});
		},
		registerStatus: function (content) {
			content.find('button.status').on('click', function () {
				var element = $(this);
				var id = element.closest('tr').data('id');
				var status = element.data('status') ? 0 : 1;
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					action: 'SaveAjax',
					mode: 'updateStatus',
					id: id,
					status: status
				}).done(function (data) {
					if (data.success && data.result) {
						element.data('status', status);
						element.toggleClass('btn-success').toggleClass('btn-danger');
						Vtiger_Helper_Js.showMessage({
							title: app.vtranslate('JS_COUNTRY_SETTING'),
							text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
							type: 'success'
						});
					}
				});
			});
		},
		registerPhone: function (content) {
			content.find('button.phone').on('click', function () {
				var element = $(this);
				var id = element.closest('tr').data('id');
				var phone = element.data('phone') ? 0 : 1;
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					action: 'SaveAjax',
					mode: 'updatePhone',
					id: id,
					phone: phone
				}).done(function (data) {
					if (data.success && data.result) {
						element.data('phone', phone);
						element.toggleClass('btn-success').toggleClass('btn-danger');
						Vtiger_Helper_Js.showMessage({
							title: app.vtranslate('JS_COUNTRY_SETTING'),
							text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
							type: 'success'
						});
					}
				});
			});
		},
		registerUitype: function (content) {
			content.find('button.uitype').on('click', function () {
				var element = $(this);
				var id = element.closest('tr').data('id');
				var uitype = element.data('uitype') ? 0 : 1;
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					action: 'SaveAjax',
					mode: 'updateUitype',
					id: id,
					uitype: uitype
				}).done(function (data) {
					if (data.success && data.result) {
						element.data('uitype', uitype);
						element.toggleClass('btn-success').toggleClass('btn-danger');
						Vtiger_Helper_Js.showMessage({
							title: app.vtranslate('JS_COUNTRY_SETTING'),
							text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
							type: 'success'
						});
					}
				});
			});
		},
		allStatuses: 1,
		registerAllStatuses: function (content) {
			var thisInstance = this;
			content.find('button.all-statuses').on('click', function () {
				var status = thisInstance.allStatuses ? 0 : 1;
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					action: 'SaveAjax',
					mode: 'updateAllStatuses',
					status: status
				}).done(function (data) {
					if (data.success && data.result) {
						var elements = content.find('.status');
						if (status) {
							elements.removeClass('btn-success').addClass('btn-danger');
							elements.data('status', 1);
						} else {
							elements.removeClass('btn-danger').addClass('btn-success');
							elements.data('status', 0);
						}
						Vtiger_Helper_Js.showMessage({
							title: app.vtranslate('JS_COUNTRY_SETTING'),
							text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
							type: 'success'
						});
					}
					if (data.success) {
						thisInstance.allStatuses = status;
					}
				});
			});
		},
		registerRowToTop: function (content) {
			var thisInstance = this;
			content.find('button.to-top').on('click', function () {
				var row = $(this).closest('tr');
				$(this).closest('table.listViewEntriesTable tbody').prepend(row);
				thisInstance.registerSequenceListOnServer();
			});
		},
		registerRowToBottom: function (content) {
			var thisInstance = this;
			content.find('button.to-bottom').on('click', function () {
				var row = $(this).closest('tr');
				$(this).closest('table.listViewEntriesTable tbody').append(row);
				thisInstance.registerSequenceListOnServer();
			});
		},
		registerSequenceListOnServer: function () {
			var sequenceList = {};
			$('tbody tr').each(function (i) {
				sequenceList[++i] = $(this).data('id');
			});
			AppConnector.request({
				sequencesList: JSON.stringify(sequenceList),
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SaveAjax',
				mode: 'updateSequence'
			}).done(function (data) {
				Vtiger_Helper_Js.showMessage({
					title: app.vtranslate('JS_COUNTRY_SETTING'),
					text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
					type: 'success'
				});
			});
		},
		registerChangeGettingDefaultCountry: function (container) {
			container.find('.js-update-get-default-phone-country').on('change', function () {
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					action: 'SaveAjax',
					mode: 'updateGetDefaultCountry',
					value: $(this).val()
				}).done(function (data) {
					Vtiger_Helper_Js.showMessage({
						text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
						type: 'success'
					});
				});
			});
		},
		registerEvents: function () {
			let content = $('.contentsDiv');

			this.registerSortableEvent();
			this.registerStatus(content);
			this.registerPhone(content);
			this.registerUitype(content);
			this.registerAllStatuses(content);
			this.registerRowToTop(content);
			this.registerRowToBottom(content);
			this.registerChangeGettingDefaultCountry(content);
		}
	}
);
