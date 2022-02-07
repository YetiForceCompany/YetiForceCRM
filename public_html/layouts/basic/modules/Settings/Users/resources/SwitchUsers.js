/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Users_SwitchUsers_Js',
	{},
	{
		registerAdd: function (content) {
			var thisInstance = this;
			content.find('.addItem').on('click', function (e) {
				var data = [];
				content.find('.switchUsersTable tbody tr').each(function (index) {
					data.push({
						user: $(this).find('.sufrom').val()
					});
				});
				var id = parseInt(content.find('#suCount').val()) + 1;
				var cloneItem = content.find('.cloneItem tbody').clone(true, true);
				var suFrom = cloneItem.find('.sufrom option');
				suFrom.each(function (index, option) {
					$.each(data, function (key, selectedUser) {
						if ($(option).val() == selectedUser.user) {
							cloneItem.find(option).remove();
						}
					});
				});
				cloneItem
					.find('tr')
					.attr('data-id', id)
					.addClass('row' + id);
				content.find('.switchUsersTable tbody').append(cloneItem.html());
				content.find('#suCount').val(id);
				thisInstance.registerDelete(content.find('tr.row' + id));
				App.Fields.Picklist.showSelect2ElementView(content.find('tr.row' + id).find('select'));
			});
		},
		registerDelete: function (content) {
			content.find('.delate').on('click', function (e) {
				var target = $(e.currentTarget);
				target.closest('tr').remove();
			});
		},
		registerSave: function (content) {
			content.find('.saveItems').on('click', function (e) {
				var data = [];
				content.find('.switchUsersTable tbody tr').each(function (index) {
					if ($(this).find('.suto :selected').length > 0) {
						data.push({
							user: $(this).find('.sufrom').val(),
							access: $(this).find('.suto').val()
						});
					}
				});
				app.saveAjax('saveSwitchUsers', data).done(function (data) {
					Settings_Vtiger_Index_Js.showMessage({ type: 'success', text: data.result.message });
				});
			});
		},
		registerEvents: function () {
			var content = $('.contentsDiv');
			this.registerAdd(content);
			this.registerDelete(content);
			this.registerSave(content);
		}
	}
);
