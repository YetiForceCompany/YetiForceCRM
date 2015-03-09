/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
var Settings_UserColors_Js = {
	initEvants: function() {
		$('.UserColors .updateColor').click(Settings_UserColors_Js.updateColor);
		$('.UserColors #update_event').click(Settings_UserColors_Js.updateEvent);
	},
	updateColor: function(e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		var editColorModal = jQuery('.UserColors .editColorContainer');
		var clonedContainer = editColorModal.clone(true, true);
		var metod = target.data('metod');
		
		var callBackFunction = function(data) {
			data.find('.editColorContainer').removeClass('hide');
			var selectedColor = data.find('.selectedColor');
			selectedColor.val( closestTrElement.data('color') );
			//register color picker
			var params = {
				flat : true,
				color : closestTrElement.data('color'),
				onChange : function(hsb, hex, rgb) {
					selectedColor.val('#'+hex);
				}
			};
			if(typeof customParams != 'undefined'){
				params = jQuery.extend(params,customParams);
			}
			data.find('.calendarColorPicker').ColorPicker(params);
			
			//save the user calendar with color
			data.find('[name="saveButton"]').click(function(e) {
				var progress = $.progressIndicator({
					'message' : app.vtranslate('Update labels'),
					'blockInfo' : {
						'enabled' : true
					}
				});
				Settings_UserColors_Js.registerSaveEvent(metod,{
					'color': selectedColor.val(),
					'id':closestTrElement.data('id'),
				});
				closestTrElement.find('.calendarColor').css('background',selectedColor.val());
				closestTrElement.data('color', selectedColor.val());
				progress.progressIndicator({'mode': 'hide'});
			});
		}
		app.showModalWindow(clonedContainer,function(data) {
			if(typeof callBackFunction == 'function') {
				callBackFunction(data);
			}
		}, {'width':'1000px'});
	},
	
	updateEvent: function(e) {
		var progress = $.progressIndicator({
			'message' : app.vtranslate('Update labels'),
			'blockInfo' : {
				'enabled' : true
			}
		});
		var target = $(e.currentTarget);
		var metod = target.data('metod');
		if(target.prop('checked')){
			value = 1;
		}else
			value = 0;
		params = {};
		params.color = value;
		params.id = target.attr('id');
		params = jQuery.extend({}, params);
		Settings_UserColors_Js.registerSaveEvent(metod,params);
		progress.progressIndicator({'mode': 'hide'});
	},
	
	registerSaveEvent: function(mode, data) {
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
			},
			function(data, err) {}
        );
	},
	
	registerEvents : function() {
		Settings_UserColors_Js.initEvants();
	}
}
$(document).ready(function(){
	Settings_UserColors_Js.registerEvents();
})