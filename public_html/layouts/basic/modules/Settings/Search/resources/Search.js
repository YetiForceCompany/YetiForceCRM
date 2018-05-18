/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
var Settings_Index_Js = {
	updatedBlockFieldsList: [],
	initEvants: function () {
		$('.SearchFieldsEdit .fieldname').on('change', Settings_Index_Js.save);
		$('.SearchFieldsEdit .searchcolumn').on('change', Settings_Index_Js.save);
		$('.SearchFieldsEdit .updateLabels').on('click', Settings_Index_Js.updateLabels);
		$('.SearchFieldsEdit .editLabels').on('click', Settings_Index_Js.editLabels);
		$('.SearchFieldsEdit .turn_off').on('click', Settings_Index_Js.replacement);
	},
	replacement: function (e) {
		var target = $(e.currentTarget);
		if (parseInt(target.val())) {
			target.val(0).html('<span class="fas fa-power-off u-mr-5px"></span>' +app.vtranslate('JS_TURN_ON'));
		} else {
			target.val(1).html('<span class="fas fa-power-off u-mr-5px"></span>' +app.vtranslate('JS_TURN_OFF'));
		}
		target.toggleClass("btn-success btn-danger");
		Settings_Index_Js.save(e);
	},
	updateLabels: function (e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		if (!closestTrElement.hasClass('ui-sortable-handle')) {
			closestTrElement = closestTrElement.prev();
		}
		Settings_Index_Js.registerSaveEvent('updateLabels', {
			'tabid': closestTrElement.data('tabid'),
		});
	},
	editLabels: function (e) {
		var target = $(e.currentTarget);
		var tabId = target.data('tabid');
		var closestTrElement = target.closest('tr');
		jQuery('.elementLabels' + tabId).addClass('d-none');
		var e = jQuery('.elementEdit' + tabId).removeClass('d-none');
		Settings_Index_Js.registerSelectElement(e.find('select'));
	},
	save: function (e) {
		var target = $(e.currentTarget);
		Settings_Index_Js.registerSaveEvent('save', {
			name: target.attr('name'),
			value: target.val(),
			tabid: target.data('tabid'),
		});
	},
	registerSaveEvent: function (mode, data) {
		var progress = $.progressIndicator({
			'message': app.vtranslate('Saving changes'),
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
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
			function (data) {
				var response = data['result'];
				var params = {
					text: response['message'],
					type: 'success'
				};
				Vtiger_Helper_Js.showPnotify(params);
				resp = response['success'];
				progress.progressIndicator({'mode': 'hide'});
			},
			function (data, err) {
				app.errorLog(data, err);
				progress.progressIndicator({'mode': 'hide'});
			}
		);
	},
	/**
	 * Function to regiser the event to make the modules sortable
	 */
	makeFieldsListSortable: function () {
		var thisInstance = this;
		var contents = jQuery('.SearchFieldsEdit').find('.contents');
		var table = contents.find('table');

		table.each(function () {
			jQuery(this).find('tbody').sortable({
				'containment': '#modulesEntity',
				'revert': true,
				'tolerance': 'pointer',
				'cursor': 'move',
				'helper': function (e, ui) {
					//while dragging helper elements td element will take width as contents width
					//so we are explicitly saying that it has to be same width so that element will not
					//look like disturbed
					ui.children().each(function (index, element) {
						element = jQuery(element);
						element.width(element.width());
					})
					return ui;
				},
				'update': function (e, ui) {
					thisInstance.showSaveModuleSequenceButton();
				}
			});
		});
	},
	/**
	 * Function to show the save button of moduleSequence
	 */
	showSaveModuleSequenceButton: function () {
		var thisInstance = this;
		var layout = jQuery('.SearchFieldsEdit');
		var saveButton = layout.find('.saveModuleSequence');
		thisInstance.updatedBlockFieldsList = [];
		saveButton.removeClass('d-none');
	},
	/**
	 * Function which will hide the saveModuleSequence button
	 */
	hideSaveModuleSequenceButton: function () {
		var layout = jQuery('.SearchFieldsEdit');
		var saveButton = layout.find('.saveModuleSequence');
		saveButton.addClass('d-none');
	},
	/**
	 * Function will save the field sequences
	 */
	updateModulesSequence: function () {
		var thisInstance = this;
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'SaveAjax';
		params['mode'] = 'saveSequenceNumber';
		params['updatedFields'] = thisInstance.updatedBlockFieldsList;

		AppConnector.request(params).then(
			function (data) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				var params = {};
				params['text'] = app.vtranslate('JS_MODULES_SEQUENCE_UPDATED');
				Settings_Vtiger_Index_Js.showMessage(params);
			},
			function (error) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
			}
		);
	},
	/**
	 * Function to create the list of updated modules and their sequences
	 */
	createUpdatedSequenceModulesList: function () {
		var thisInstance = this;
		var contents = jQuery('.SearchFieldsEdit').find('.contents');

		var updatedModules = contents.find('tbody');
		var editModules = updatedModules.find('tr');
		var expectedModuleSequence = 1;
		editModules.each(function (i, domElement) {
			var moduleEle = jQuery(domElement);
			var moduleId = moduleEle.data('tabid');
			thisInstance.updatedBlockFieldsList.push({'tabid': moduleId, 'sequence': expectedModuleSequence});
			expectedModuleSequence = expectedModuleSequence + 1;
		});
	},
	/**
	 * Function to register click event for save button of modules sequence
	 */
	registerModuleSequenceSaveClick: function () {
		var thisInstance = this;
		var layout = jQuery('.SearchFieldsEdit');
		layout.on('click', '.saveModuleSequence', function () {
			thisInstance.hideSaveModuleSequenceButton();
			thisInstance.createUpdatedSequenceModulesList();
			thisInstance.updateModulesSequence();
		});
	},
	registerSelectElement: function (element) {
		var value = element.val();
		if (!element.hasClass('selectized')) {
			element.selectize({
				plugins: ['drag_drop', 'remove_button'],
				onChange: function (value) {
					if (value.length > 1) {
						jQuery(this.$control[0]).find('.remove').removeClass('d-none');
					} else {
						jQuery(this.$control[0]).find('.remove').addClass('d-none');
					}
				},
				onInitialize: function () {
					if (this.items.length > 1) {
						jQuery(this.$control[0]).find('.remove').removeClass('d-none');
					} else {
						jQuery(this.$control[0]).find('.remove').addClass('d-none');
					}
				},
			})
		}
	},
	registerEvents: function () {
		Settings_Index_Js.initEvants();
		this.makeFieldsListSortable();
		this.registerModuleSequenceSaveClick();
	}
}
$(document).ready(function () {
	Settings_Index_Js.registerEvents();
})
