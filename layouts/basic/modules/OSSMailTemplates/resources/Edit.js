/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
Vtiger_Edit_Js("OSSMailTemplates_Edit_Js", {
}, {
	registerToZeroClipboard: function () {
		jQuery('.copy-button').each(function () {
			var clip = jQuery(this).data('prefix');
			clip = new ZeroClipboard(
					$(this), {
				moviePath: 'libraries/jquery/ZeroClipboard/ZeroClipboard.swf'
			});
			clip.on('dataRequested', function (client, args) {
				var selectName = jQuery(this).data('select'),
						fieldPrefix = jQuery(this).data('prefix');
				var txt = '';
				var selectValue = jQuery('[name="' + selectName + '"]').val();
				txt = '#' + fieldPrefix + '#' + selectValue + '#' + fieldPrefix + 'End#';
				client.setText(txt);
			});
			clip.on('complete', function (client, args) {
				// notification about copy to clipboard
				var params = {
					text: app.vtranslate('JS_NOTIF_PASS_COPIED'),
					animation: 'show',
					title: app.vtranslate('JS_NOTIF_PASS_TITLE'),
					type: 'success'
				};
				Vtiger_Helper_Js.showPnotify(params);
			});
		});
	},
	loadSpecialFunctionList: function (selectedModule) {
		params = {};
		params.data = {
			module: 'OSSMailTemplates',
			action: 'GetListSpecialFunction',
			tpl_module: selectedModule
		}
		AppConnector.request(params).then(
				function (data) {
					if (data.success) {
						var listSpecialFunctionSelect = jQuery('[name="oss_special_function_list"]'),
								tabField = data.result;
						listSpecialFunctionSelect.find('option').remove();
						jQuery(tabField).each(function (index, item) {
							jQuery(listSpecialFunctionSelect).append('<option value="' + item.filename + '">' + item.label + '</option>');
						});
						listSpecialFunctionSelect.trigger('chosen:updated');
					}
				}
		);
	},
	loadFieldList: function (selectedModule) {
		params = {};
		params.data = {
			module: 'OSSMailTemplates',
			action: 'GetListField',
			tpl_module: selectedModule
		}
		AppConnector.request(params).then(
				function (data) {
					if (data.success) {
						var fieldListSelect = jQuery('[name="oss_fields_list"]'),
								tabField = data.result;
						fieldListSelect.find('option').remove();
						fieldListSelect.find('optgroup').remove();
						for (var id in tabField) {
							var oprgroup = '<optgroup label="' + tabField[id]['blockLabel'] + '">';
							for (var item in tabField[id]) {
								if (item != 'blockLabel') {
									oprgroup += '<option value="' + tabField[id][item]['id'] + '">' + tabField[id][item]['label'] + '</option>';
								}
							}
							oprgroup += '</optgroup>';
							jQuery(fieldListSelect).append(oprgroup);
						}
						fieldListSelect.trigger('chosen:updated');
					}
				}
		);
	},
	loadRelatedFieldList: function () {
		var selectedModule = jQuery('[name="oss_module_list"]').val(),
				params = {};
		params.data = {
			module: 'OSSMailTemplates',
			action: 'GetListRelatedField',
			tpl_module: selectedModule
		}

		//params.async = false;
		AppConnector.request(params).then(
				function (data) {
					if (data.success) {
						var fieldRelatedListSelect = jQuery('[name="oss_related_fields_list"]');
						var tabField = data.result;
						fieldRelatedListSelect.find('optgroup').remove();
						fieldRelatedListSelect.find('option').remove();
						jQuery(tabField).each(function (index, item) {
							for (var tab in item) {
								var html = '<optgroup label="' + tab + '">';
								for (var itemTab in item[tab]) {
									for (var content in item[tab][itemTab]) {
										if (undefined != item[tab][itemTab][content]['id'])
											html += '<option value="' + item[tab][itemTab][content]['id'] + '">' + item[tab][itemTab][content]['label'] + '</option>';
									}
								}
								html += '</optgroup>';
								jQuery(fieldRelatedListSelect).append(html);
							}
						});
						fieldRelatedListSelect.trigger('chosen:updated');
					}
				}
		);
	},
	setFieldList: function () {
		var thisInstance = this;
		var selectedModule = 'Accounts';
		var oss_module_list = jQuery('[name="oss_module_list"]');
		selectedModule = oss_module_list.val();
		oss_module_list.on("change", function () {
			selectedModule = oss_module_list.val();
			thisInstance.loadFieldList(selectedModule);
		});
		thisInstance.loadFieldList(selectedModule);
	},
	setSpecialFunctionList: function () {
		var thisInstance = this;
		var selectedModule = 'Accounts';
		var oss_module_list = jQuery('[name="oss_module_list"]');
		selectedModule = oss_module_list.val();
		oss_module_list.on("change", function () {
			selectedModule = oss_module_list.val();
			thisInstance.loadSpecialFunctionList(selectedModule);
		});
		thisInstance.loadSpecialFunctionList(selectedModule);
	},
	setRelatedFieldList: function () {
		var thisInstance = this;
		var oss_module_list = jQuery('[name="oss_module_list"]');
		oss_module_list.on("change", function () {
			thisInstance.loadRelatedFieldList();
		});
		thisInstance.loadRelatedFieldList();
	},
	showHideSpecialField: function () {
		var thisInstance = this;
		var type = jQuery('[name="ossmailtemplates_type"]');
		var fieldsList = jQuery('[name="oss_fields_list"]');
		var fieldsButtons = fieldsList.closest('.fieldValue').find('button');
		var relatedFieldsList = jQuery('[name="oss_related_fields_list"]');
		var relatedFieldsListButtons = relatedFieldsList.closest('.fieldValue').find('button');
		type.on("change", function () {
			var value = jQuery(this).val();
			if (value == 'PLL_MODULE') {
				fieldsList.prop('disabled', true).trigger('chosen:updated');
				fieldsButtons.addClass('hide');
				relatedFieldsList.prop('disabled', true).trigger('chosen:updated');
				relatedFieldsListButtons.addClass('hide');
			} else {
				fieldsList.prop('disabled', false).trigger('chosen:updated');
				fieldsButtons.removeClass('hide');
				relatedFieldsList.prop('disabled', false).trigger('chosen:updated');
				relatedFieldsListButtons.removeClass('hide');
			}
		});
	},
	registerBasicEvents: function (container) {
		this._super(container);
		this.setFieldList();
		this.setRelatedFieldList();
		this.setSpecialFunctionList();
		this.showHideSpecialField();
		this.registerToZeroClipboard();
	}
})
