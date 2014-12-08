var Settings_Index_Js = {
	initEvants: function() {
		$('.CustomViewList .delete').click(Settings_Index_Js.Delete);
		$('.CustomViewList .updateField').click(Settings_Index_Js.updateField);
		$('.CustomViewList .update').click(Settings_Index_Js.update);
	},
	update: function(e) {
		var target = $(e.currentTarget);
		var editUrl = target.data('editurl');
		Vtiger_CustomView_Js.loadFilterView(editUrl);
	},
	updateField: function(e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		var progress = $.progressIndicator({
			'message' : app.vtranslate('Update labels'),
			'blockInfo' : {
				'enabled' : true
			}
		});
		Settings_Index_Js.registerSaveEvent('UpdateField',{
			'cvid':closestTrElement.data('cvid'),
			'mod':closestTrElement.data('mod'),
			'name':target.attr('name'),
			'checked':target.prop('checked'),
		});
		var params = {};
		params['module'] = 'CustomView';
		params['view'] = 'Index';
		params['parent'] = 'Settings';
		AppConnector.request(params).then(
			function(data) {
				jQuery('div.contentsDiv').html( data );
				Settings_Index_Js.initEvants();
				progress.progressIndicator({'mode': 'hide'});
			}
		);
	},
	Delete: function(e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		var progress = $.progressIndicator({
			'message' : app.vtranslate('Update labels'),
			'blockInfo' : {
				'enabled' : true
			}
		});
		Settings_Index_Js.registerSaveEvent('Delete',{
			'cvid':closestTrElement.data('cvid'),
		});
		var params = {};
		params['module'] = 'CustomView';
		params['view'] = 'Index';
		params['parent'] = 'Settings';
		AppConnector.request(params).then(
			function(data) {
				jQuery('div.contentsDiv').html( data );
				Settings_Index_Js.initEvants();
				progress.progressIndicator({'mode': 'hide'});
			}
		);
	},
	registerSaveEvent: function(mode, data) {
		var resp = '';
		var params = {}
		params.data = {
			module: app.getModuleName(), 
			parent: app.getParentModuleName(), 
			action: 'SaveAjax', 
			mode: mode,
			params: data
		}
		params.async = false;
		params.dataType = 'json';
        AppConnector.request(params).then(
			function(data) {
				var response = data['result'];
				var params = {
					text: response['message'],
					animation: 'show',
					type: 'success'
				};
				Vtiger_Helper_Js.showPnotify(params);
				resp = response['success'];
			},
			function(data, err) {

			}
        );
	},

	registerEvents : function() {
		Settings_Index_Js.initEvants();
	}
	
}
$(document).ready(function(){
	Settings_Index_Js.registerEvents();
})