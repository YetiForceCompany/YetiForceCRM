/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery.Class("Settings_MarketingProcesses_Index_Js", {}, {
	registerChangeVal: function (content) {
		var thisInstance = this;
		content.find('.configField').change(function (e) {
			var target = $(e.currentTarget);
			var params = {};
			params['type'] = target.data('type');
			params['param'] = target.attr('name');
			if (target.attr('type') == 'checkbox') {
				params['val'] = this.checked;
			} else {
				params['val'] = target.val() != null ? target.val() : '';
			}
			app.saveAjax('updateConfig', params).then(function (data) {
				Settings_Vtiger_Index_Js.showMessage({type: 'success', text: data.result.message});
			});
		});
	},
	registerSaveMapping: function (content) {
		var thisInstance = this;
		content.find('button.saveMapping').on('click', function () {
			var mapping = [];
			jQuery('#convertLeadMapping tr.listViewEntries:not(.hide)').each(function () {
				var leadName = jQuery(this).find('select.leadsFields').val();
				var accountName = jQuery(this).find('select.accountsFields').val();
				var relations = [];
				relations[leadName] = accountName;
				relations = jQuery.extend({}, relations);
				if (thisInstance.uniqueReduce(mapping, relations)) {
					mapping.push(relations);
				}
			});
			if (mapping.length) {
				jQuery('[name="mapping"]').val(JSON.stringify(mapping)).change();
			} else {
				Settings_Vtiger_Index_Js.showMessage({type: 'info', text: app.vtranslate('JS_NO_CONDITIONS')});
			}
		});
	},
	uniqueReduce: function (mapping, relations) {
		for (var key in mapping) {
			if (JSON.stringify(mapping[key]) === JSON.stringify(relations)) {
				return false;
			}
		}
		return true;
	},
	registerEventToDeleteMapping: function (content) {
		var thisInstance = this;
		content.find('.deleteMapping').on('click', function (e) {
			var element = jQuery(e.currentTarget);
			var trContainer = element.closest('tr');
			trContainer.remove();
		});
	},
	registerMapping: function (content) {
		var thisInstance = this;
		content.find('[name="create_always"]').on('change', function (e) {
			var mappingTable = jQuery('.mappingTable');
			if (this.checked) {
				mappingTable.removeClass('hide');
			} else {
				mappingTable.addClass('hide');
			}
		});
	},
	registerEvents: function () {
		var content = $('#supportProcessesContainer');
		this.registerChangeVal(content);
		this.registerSaveMapping(content);
		this.registerMapping(content);
		this.registerEventToDeleteMapping(content);
		var instance = new Settings_LeadMapping_Js();
		instance.registerEventForAddingNewMapping();
	}
});

