/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_MarketingProcesses_Index_Js',
	{},
	{
		registerChangeVal: function (content) {
			content.find('.configField').on('change', function (e) {
				var target = $(e.currentTarget);
				var val;
				if (target.attr('type') === 'checkbox') {
					val = this.checked;
				} else {
					val = target.val() != null ? target.val() : '';
				}
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					action: 'SaveAjax',
					mode: 'updateConfig',
					type: target.data('type'),
					param: target.attr('name'),
					value: val
				}).done(function (data) {
					Settings_Vtiger_Index_Js.showMessage({ type: 'success', text: data.result.message });
				});
			});
		},
		registerSaveMapping: function (content) {
			var thisInstance = this;
			content.find('button.saveMapping').on('click', function () {
				var mapping = [];
				jQuery('#convertLeadMapping tr.listViewEntries:not(.d-none)').each(function () {
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
					Settings_Vtiger_Index_Js.showMessage({
						type: 'info',
						text: app.vtranslate('JS_NO_CONDITIONS')
					});
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
			content.find('.deleteMapping').on('click', function (e) {
				var element = jQuery(e.currentTarget);
				var trContainer = element.closest('tr');
				trContainer.remove();
			});
		},
		registerMapping: function (content) {
			content.find('[name="create_always"]').on('change', function (e) {
				var mappingTable = jQuery('.mappingTable');
				if (this.checked) {
					mappingTable.removeClass('d-none');
				} else {
					mappingTable.addClass('d-none');
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
	}
);
