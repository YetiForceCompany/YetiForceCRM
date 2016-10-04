/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery.Class('Settings_Users_SwitchUsers_Js', {}, {
	registerAdd: function (content) {
		var thisInstance = this;
		content.find('.addItem').click(function (e) {
			var data = [];
			content.find('.switchUsersTable tbody tr').each(function (index) {
				data.push({
					user: $(this).find('.sufrom').val(),
				});
			});
			var id = parseInt(content.find('#suCount').val()) + 1;
			var target = $(e.currentTarget);
			var cloneItem = content.find('.cloneItem tbody').clone(true, true);
			var suFrom = cloneItem.find('.sufrom option')
			suFrom.each(function (index, option) {			
				$.each( data, function( key, selectedUser ){
					if($(option).val() == selectedUser.user){
						cloneItem.find(option).remove()
					}
				});
			});
			cloneItem.find('tr').attr('data-id', id).addClass('row' + id);
			content.find('.switchUsersTable tbody').append(cloneItem.html());
			content.find('#suCount').val(id);
			thisInstance.registerDelete(content.find('tr.row' + id));
			app.showSelect2ElementView(content.find('tr.row' + id).find('select'));
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
			content.find('.switchUsersTable tbody tr').each(function (index) {
				if($(this).find('.suto :selected').length > 0){
					data.push({
						user: $(this).find('.sufrom').val(),
						access: $(this).find('.suto').val(),
					});
				}
			});
			app.saveAjax('saveSwitchUsers', data).then(function (data) {
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
