/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js('Settings_AutomaticAssignment_List_Js', {
	changeRecordState: function (recordId, state) {
		var aDeferred = jQuery.Deferred();
		var message = app.vtranslate('JS_STATE_CONFIRMATION');
		Vtiger_Helper_Js.showConfirmationBox({'message': message}).done(function (e) {
			app.saveAjax('save', {active: state}, {record: recordId}).done(function (respons) {
				var listInstance = Settings_AutomaticAssignment_List_Js.getInstance();
				listInstance.getListViewRecords();
			});
		}).fail(function (error, err) {
			app.errorLog(error, err);
		});
		return aDeferred.promise();
	},
}, {
	container: false,
	registerFilterChangeEvent: function () {
		var thisInstance = this;
		jQuery('#moduleFilter').on('change', function (e) {
			jQuery('#pageNumber').val("1");
			jQuery('#pageToJump').val('1');
			jQuery('#orderBy').val('');
			jQuery("#sortOrder").val('');
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				sourceModule: jQuery(e.currentTarget).val()
			}
			//Make the select all count as empty
			jQuery('#recordsCount').val('');
			//Make total number of pages as empty
			jQuery('#totalPageCount').text("");
			thisInstance.getListViewRecords(params).done(function (data) {
				thisInstance.updatePagination();
			});
		});
	},
	getContainer: function () {
		if (this.container == false) {
			this.container = jQuery('div.contentsDiv');
		}
		return this.container;
	},
	registerModalEvents: function (data, url) {
		var form = data.find('form');
		var submitButton = data.find('.submitButton');
		data.find('[name="tabid"]').on('change', function (e) {
			var progress = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			var element = jQuery(e.currentTarget);
			var getFieldsUrl = url + '&tabid=' + element.val();
			AppConnector.request(getFieldsUrl).done(function (fields) {
					data.find('.fieldList').html(fields);
					App.Fields.Picklist.showSelect2ElementView(data.find('.fieldList select'));
					submitButton.removeClass('d-none');
					progress.progressIndicator({'mode': 'hide'});
				}).fail(function (textStatus, errorThrown) {
					progress.progressIndicator({'mode': 'hide'});
					app.errorLog(textStatus, errorThrown);
				}
			);
		})
		form.on('submit', function (e) {
			e.preventDefault();
			var params = form.serializeFormData();
			app.saveAjax('save', params).done(function (response) {
				var id = response.result;
				if (id) {
					var url = form.attr('action');
					url = url + '&record=' + id;
					window.location.href = url;
				}
			});
		});
	},
	registerEvents: function () {
		var thisInstance = this;
		this._super();
		this.registerFilterChangeEvent();
		this.getContainer().find('.addRecord').on('click', function (e) {
			var button = jQuery(e.currentTarget);
			var url = button.data('url');
			app.showModalWindow(null, url, function (data) {
				thisInstance.registerModalEvents(data, url);
			});
		});
	}
})
