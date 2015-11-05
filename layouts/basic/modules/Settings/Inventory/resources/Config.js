/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

jQuery.Class("Settings_Inventory_Config_Js", {}, {
	
	registerChangeCheckbox: function (content) {
		var thisInstance = this;
		content.find('input[type="checkbox"]').on('change', function (e) {
			var target = $(e.currentTarget);
			var value = 0;
			if(target.is(':checked')){
				value = 1;
			}
			var params = {};
			params['param'] = {
				'value': value,
				'param': target.attr('name')
			};
			params['view'] = app.getViewName();
			app.saveAjax('saveConfig', params).then(function (data) {
				Settings_Vtiger_Index_Js.showMessage({type: 'success', text: app.vtranslate('JS_SAVE_CHANGES')});
				if (value) {
					target.parent().removeClass('btn-default').addClass('btn-success').find('.glyphicon').removeClass('glyphicon-unchecked').addClass('glyphicon-check');
					target.next().html('&nbsp;&nbsp;' + app.vtranslate('JS_YES'));
				} else {
					target.parent().removeClass('btn-success').addClass('btn-default').find('.glyphicon').removeClass('glyphicon-check').addClass('glyphicon-unchecked');
					target.next().html('&nbsp;&nbsp;' + app.vtranslate('JS_NO'));
					;
				}
			});
		});
	},
	registerChangeVal: function (content) {
		var thisInstance = this;
		content.find('select').change(function (e) {
			var target = $(e.currentTarget);
			var params = {};
			var value = '';
			if(target.attr('multiple') && target.val() != null){
				value = target.val().join();
			}else if(target.val() != null){
				value = target.val();
			}
			params['param'] = {
				'value': value,
				'param': target.attr('name')
			};
			params['view'] = app.getViewName();
			app.saveAjax('saveConfig', params).then(function (data) {
				Settings_Vtiger_Index_Js.showMessage({type: 'success', text: app.vtranslate('JS_SAVE_CHANGES')});
			});
		});
	},
	
	registerEvents: function () {
		var content = jQuery('#inventoryConfig');
		this.registerChangeVal(content);
		this.registerChangeCheckbox(content);
	}

});

jQuery(document).ready(function (e) {
	var instance = new Settings_Inventory_Config_Js();
	instance.registerEvents();
})
