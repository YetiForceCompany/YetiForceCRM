/* {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} */
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
		var colorPreview = $('#calendarColorPreview' + target.data('type') + target.data('id'));
		var callBackFunction = function (data) {
			data.find('.editColorContainer').removeClass('hide').show();
			var selectedColor = data.find('.selectedColor');
			selectedColor.val(colorPreview.data('color'));
			//register color picker
			var params = {
				flat: true,
				color: colorPreview.data('color'),
				onChange: function (hsb, hex, rgb) {
					selectedColor.val('#' + hex);
					colorPreview.data('color', '#' + hex);
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
					'id': target.data('id'),
					'table': closestTrElement.data('table'),
					'field': closestTableElement.data('fieldname'),
				});
				colorPreview.css('background', selectedColor.val());
				target.data('color', selectedColor.val());
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
		var closestTableElement = target.closest('table');
		var metod = target.data('metod');
		var colorPreview = $('#calendarColorPreview' + target.data('type') + target.data('id'));
		var params = {
			module: app.getModuleName(),
			action: 'SaveAjax',
			mode: 'generateColor',
			params: {id: target.data('id'),
				color: colorPreview.data('color'),
				table: target.data('table'),
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
					colorPreview.css('background', response.color);
					colorPreview.data('color', response.color);

					Vtiger_Helper_Js.showPnotify(params);
				},
				function (data, err) {
				}
		);
	},
	registerSaveEvent: function (mode, data) {
		var params = {}
		params.data = {
			module: app.getModuleName(),
			action: 'SaveAjax',
			mode: mode,
			params: data
		}
		params.async = false;
		params.dataType = 'json';
		AppConnector.request(params).done(
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
		var colorPreview = $('#calendarColorPreview' + target.data('type') + target.data('id'));
		var params = {}
		params.data = {
			module: app.getModuleName(),
			action: 'SaveAjax',
			mode: 'activeColor',
			params: {
				'status': target.is(':checked'),
				'color': colorPreview.data('color'),
				'id': target.data('id')
			}
		}
		params.async = false;
		params.dataType = 'json';
		AppConnector.request(params).done(
				function (data) {
					var response = data['result'];
					var params = {
						text: response['message'],
						animation: 'show',
						type: 'success'
					};
					Vtiger_Helper_Js.showPnotify(params);
					colorPreview.css('background', response.color);
					colorPreview.data('color', response.color);

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
