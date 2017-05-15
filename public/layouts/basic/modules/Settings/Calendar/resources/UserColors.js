/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
var Settings_UserColors_Js = {
	initEvants: function () {
		$('.UserColors .updateColor').click(Settings_UserColors_Js.updateColor);
		$('.UserColors #update_event').click(Settings_UserColors_Js.updateEvent);
		$('.UserColors .generateColor').click(Settings_UserColors_Js.generateColor);
	},
	generateColor: function (e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		var params = {
			'id': closestTrElement.data('id'),
			'table': closestTrElement.data('table'),
			'field': closestTrElement.data('field'),
		}
		app.saveAjax('generateColor', params).then(function (data) {
			Settings_Vtiger_Index_Js.showMessage({type: 'success', text: data.result.message});
			closestTrElement.find('.calendarColor').css('background', data.result.color);
			closestTrElement.data('color', data.result.color);
		});
	},
	updateColor: function (e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		var editColorModal = jQuery('.UserColors .editColorContainer');
		var clonedContainer = editColorModal.clone(true, true);
		var metod = target.data('metod');

		var callBackFunction = function (data) {
			data.find('.editColorContainer').removeClass('hide').show();
			var selectedColor = data.find('.selectedColor');
			selectedColor.val(closestTrElement.data('color'));
			//register color picker
			var params = {
				flat: true,
				color: closestTrElement.data('color'),
				onChange: function (hsb, hex, rgb) {
					selectedColor.val('#' + hex);
				}
			};
			if (typeof customParams != 'undefined') {
				params = jQuery.extend(params, customParams);
			}
			data.find('.calendarColorPicker').ColorPicker(params);

			//save the user calendar with color
			data.find('[name="saveButton"]').click(function (e) {
				var progress = $.progressIndicator({
					'message': app.vtranslate('Update labels'),
					'blockInfo': {
						'enabled': true
					}
				});
				Settings_UserColors_Js.registerSaveEvent(metod, {
					'color': selectedColor.val(),
					'id': closestTrElement.data('id'),
					'table': closestTrElement.data('table'),
					'field': closestTrElement.data('field'),
				});
				closestTrElement.find('.calendarColor').css('background', selectedColor.val());
				closestTrElement.data('color', selectedColor.val());
				progress.progressIndicator({'mode': 'hide'});
			});
		}
		app.showModalWindow(clonedContainer, function (data) {
			if (typeof callBackFunction == 'function') {
				callBackFunction(data);
			}
		}, {'width': '1000px'});
	},
	updateEvent: function (e) {
		var progress = $.progressIndicator({
			'message': app.vtranslate('Update labels'),
			'blockInfo': {
				'enabled': true
			}
		});
		var target = $(e.currentTarget);
		var metod = target.data('metod');
		if (target.prop('checked')) {
			value = 1;
		} else
			value = 0;
		params = {};
		params.color = value;
		params.id = target.attr('id');
		params = jQuery.extend({}, params);
		Settings_UserColors_Js.registerSaveEvent(metod, params);
		progress.progressIndicator({'mode': 'hide'});
	},
	registerSaveEvent: function (mode, data) {
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
				function (data) {
					var response = data['result'];
					var params = {
						text: response['message'],
						animation: 'show',
						type: 'success'
					};
					app.hideModalWindow();
					Vtiger_Helper_Js.showPnotify(params);
					return response;
				},
				function (data, err) {
				}
		);
	},
	registerSaveWorkingDays: function (content) {
		var thisInstance = this;
		content.find('.workignDaysField').change(function (e) {
			var target = $(e.currentTarget);
			var params = {};
			params['type'] = target.data('type');
			params['param'] = target.attr('name');
			if (target.attr('type') == 'checkbox') {
				params['val'] = this.checked;
			} else {
				params['val'] = target.val();
			}
			app.saveAjax('updateNotWorkingDays', params).then(function (data) {
				Settings_Vtiger_Index_Js.showMessage({type: 'success', text: data.result.message});
			});
		});

	},
	registerEvents: function () {
		Settings_UserColors_Js.initEvants();
		var content = $('.workingDaysTable');
		this.registerSaveWorkingDays(content);
	}
}
$(document).ready(function () {
	Settings_UserColors_Js.registerEvents();
})
