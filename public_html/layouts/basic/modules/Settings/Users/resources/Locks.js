/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Users_Locks_Js',
	{},
	{
		/**
		 * Add item
		 * @param {jQuery} content
		 */
		registerAdd: function (content) {
			var thisInstance = this;
			content.find('.js-add-item').on('click', function (e) {
				var id = parseInt(content.find('#js-lock-count').val()) + 1;
				var cloneItem = content.find('.js-clone-item tbody').clone(true, true);
				cloneItem
					.find('tr')
					.attr('data-id', id)
					.addClass('row' + id);
				content.find('.js-locks-table tbody').append(cloneItem.html());
				content.find('#js-lock-count').val(id);
				thisInstance.registerDelete(content.find('tr.row' + id));
				App.Fields.Picklist.showSelect2ElementView(content.find('tr.row' + id).find('select'));
			});
		},
		/**
		 * Register events for delete item
		 * @param {jQuery} content
		 */
		registerDelete: function (content) {
			content.find('.js-delete-item').on('click', function (e) {
				var target = $(e.currentTarget);
				target.closest('tr').remove();
			});
		},
		/**
		 * Register events for save
		 * @param {jQuery} content
		 */
		registerSave: function (content) {
			content.find('.js-save-items').on('click', function (e) {
				var data = [];
				content.find('.js-locks-table tbody tr').each(function (index) {
					data.push({
						user: $(this).find('.js-users').val(),
						locks: $(this).find('.js-locks').val()
					});
				});
				app.saveAjax('saveLocks', data).done(function (data) {
					Settings_Vtiger_Index_Js.showMessage({ type: 'success', text: data.result.message });
				});
			});
		},
		/**
		 * Main function
		 */
		registerEvents: function () {
			var content = $('.contentsDiv');
			this.registerAdd(content);
			this.registerDelete(content);
			this.registerSave(content);
		}
	}
);
