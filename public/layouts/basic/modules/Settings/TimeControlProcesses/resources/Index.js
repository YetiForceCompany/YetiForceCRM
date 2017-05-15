/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery.Class("Settings_TimeControlProcesses_Index_Js", {}, {
	registerChangeVal: function (content) {
		var thisInstance = this;
		content.find('input[type="checkbox"]').on('change', function (e) {
			var target = $(e.currentTarget);
			var tab = target.closest('.editViewContainer');
			var value = target.is(':checked');
			var params = {};
			params['value'] = value;
			params['type'] = tab.data('type');
			params['param'] = target.attr('name');
			app.saveAjax('', params).then(function (data) {
				Settings_Vtiger_Index_Js.showMessage({type: 'success', text: data.result.message});
				if (value) {
					target.parent().removeClass('btn-default').addClass('btn-success').find('.glyphicon').removeClass('glyphicon-unchecked').addClass('glyphicon-check');
				} else {
					target.parent().removeClass('btn-success').addClass('btn-default').find('.glyphicon').removeClass('glyphicon-check').addClass('glyphicon-unchecked');
				}
			});
		});
	},
	registerEvents: function () {
		var content = jQuery('.processesContainer');
		this.registerChangeVal(content);
	}
});
