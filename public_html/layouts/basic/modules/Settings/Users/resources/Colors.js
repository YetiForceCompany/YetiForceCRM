/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
var Colors_Js = {
	initEvants: function () {
		$('.UserColors .updateColor').click(Colors_Js.updateColor);
		$('.UserColors .generateColor').click(Colors_Js.generateColor);
		$('.UserColors .activeColor').click(Colors_Js.activeColor);
	},
	updateColor: function (e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		var closestTableElement = target.closest('table');
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
				Colors_Js.registerSaveEvent(metod, {
					'color': selectedColor.val(),
					'id': closestTrElement.data('id'),
					'table': closestTrElement.data('table'),
					'field': closestTableElement.data('fieldname'),
				});
				closestTrElement.find('.calendarColor').css('background', selectedColor.val());
				closestTrElement.data('color', selectedColor.val());
				progress.progressIndicator({'mode': 'hide'});
				app.hideModalWindow();
			});
		}
		app.showModalWindow(clonedContainer, function (data) {
			if (typeof callBackFunction == 'function') {
				callBackFunction(data);
			}
		}, {'width': '1000px'});
	},
	generateColor: function (e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		var closestTableElement = target.closest('table');
		var metod = target.data('metod');

		var params = {
			module: app.getModuleName(),
			//	parent: app.getParentModuleName(), 
			action: 'SaveAjax',
			mode: 'generateColor',
			params: {id: closestTrElement.data('id'),
				table: closestTrElement.data('table'),
				field: closestTableElement.data('fieldname'),
				mode: metod,
			}
		}
		AppConnector.request(params).then(
				function (data) {
					var response = data['result'];
					var params = {
						text: response['message'],
						animation: 'show',
						type: 'success'
					};
					Vtiger_Helper_Js.showPnotify(params);
					closestTrElement.find('.calendarColor').css('background', response.color);
					closestTrElement.data('color', response.color);
				},
				function (data, err) {
				}
		);
	},
	registerSaveEvent: function (mode, data) {
		var params = {}
		params.data = {
			module: app.getModuleName(),
			//	parent: app.getParentModuleName(), 
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
					Vtiger_Helper_Js.showPnotify(params);
					return response;
				},
				function (data, err) {
				}
		);
	},
	activeColor: function (e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		var params = {}
		params.data = {
			module: app.getModuleName(),
			action: 'SaveAjax',
			mode: 'activeColor',
			params: {
				'status': target.is(':checked'),
				'color': closestTrElement.data('color'),
				'id': closestTrElement.data('id')
			}
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
					Vtiger_Helper_Js.showPnotify(params);
					if (closestTrElement.data('color') == '') {
						closestTrElement.find('.calendarColor').css('background', response['color']);
						closestTrElement.data('color', response['color']);
					}
				}
		);
	},
	registerEvents: function () {
		Colors_Js.initEvants();
	}
}
$(document).ready(function () {
	Colors_Js.registerEvents();
})
