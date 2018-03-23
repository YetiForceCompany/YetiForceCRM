/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
jQuery.Class('Settings_Users_Locks_Js', {}, {
	registerAdd: function (content) {
		var thisInstance = this;
		content.find('.addItem').click(function (e) {
			var id = parseInt(content.find('#lcount').val()) + 1;
			var target = $(e.currentTarget);
			var cloneItem = content.find('.cloneItem tbody').clone(true, true);
			cloneItem.find('tr').attr('data-id', id).addClass('row' + id);
			content.find('.locksTable tbody').append(cloneItem.html());
			content.find('#lcount').val(id);
			thisInstance.registerDelete(content.find('tr.row' + id));
			App.Fields.Picklist.showSelect2ElementView(content.find('tr.row' + id).find('select'));
		});
	},
	registerDelete: function (content) {
		content.find('.delate').click(function (e) {
			var target = $(e.currentTarget);
			target.closest('tr').remove();
		});
	},
	registerSave: function (content) {
		var thisInstance = this;
		content.find('.saveItems').click(function (e) {
			var data = [];
			content.find('.locksTable tbody tr').each(function (index) {
				data.push({
					user: $(this).find('.users').val(),
					locks: $(this).find('.locks').val(),
				});
			});
			app.saveAjax('saveLocks', data).then(function (data) {
				Settings_Vtiger_Index_Js.showMessage({type: 'success', text: data.result.message});
			});
		});
	},
	registerEvents: function () {
		var content = $('.contentsDiv');
		this.registerAdd(content);
		this.registerDelete(content);
		this.registerSave(content);
	}
});
