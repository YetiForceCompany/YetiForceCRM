/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
var Settings_ActivityTypes_Js = {
	initEvants: function() {
		$('.ActivityTypes .updateColor').click(Settings_ActivityTypes_Js.updateColor);
		$('.ActivityTypes .activeType').click(Settings_ActivityTypes_Js.updateActiveType);
		$('.ActivityTypes .generateColor').click(Settings_ActivityTypes_Js.generateColor);	
	},
	generateColor: function(e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');		
		var params = {
			'viewtypesid':closestTrElement.data('viewtypesid'),
		}
		app.saveAjax('generateColor', params).then(function (data) {
			Settings_Vtiger_Index_Js.showMessage({type: 'success', text: data.result.message});
			closestTrElement.find('.calendarColor').css('background',data.result.color);
			closestTrElement.data('color', data.result.color);
		});
	},
	updateActiveType: function(e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		Settings_ActivityTypes_Js.registerSaveEvent('UpdateModuleActiveType',{
			'active': target.is(':checked'),
			'viewtypesid':closestTrElement.data('viewtypesid'),
		});
	},
	updateColor: function(e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		var editColorModal = jQuery('.ActivityTypes .editColorContainer');
		var clonedContainer = editColorModal.clone(true, true);
		
		var callBackFunction = function(data) {
			data.find('.editColorContainer').removeClass('hide').show();
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
				Settings_ActivityTypes_Js.registerSaveEvent('UpdateModuleColor',{
					'color': selectedColor.val(),
					'viewtypesid':closestTrElement.data('viewtypesid'),
				});
				closestTrElement.find('.calendarColor').css('background',selectedColor.val());
				closestTrElement.data('color', selectedColor.val());
				progress.progressIndicator({'mode': 'hide'});
				app.hideModalWindow();
			});
		}
		app.showModalWindow(clonedContainer,function(data) {
			if(typeof callBackFunction == 'function') {
				callBackFunction(data);
			}
		}, {'width':'1000px'});
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
		Settings_ActivityTypes_Js.initEvants();
	}
}
$(document).ready(function(){
	Settings_ActivityTypes_Js.registerEvents();
})
