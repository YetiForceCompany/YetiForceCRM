/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
var Settings_Index_Js = {
	updatedBlockFieldsList: [],
	initEvants: function () {
		$('.SearchFieldsEdit .fieldname').change(Settings_Index_Js.save);
		$('.SearchFieldsEdit .searchcolumn').change(Settings_Index_Js.save);
		$('.SearchFieldsEdit .updateLabels').click(Settings_Index_Js.updateLabels);
		$('.SearchFieldsEdit .turn_off').click(Settings_Index_Js.replacement);
	},
	replacement: function (e) {
		var thisInstance = this;
		var target = $(e.currentTarget);

		if (parseInt(target.val())) {
			target.val(0).html(app.vtranslate('JS_TURN_ON'));
			target.removeClass("btn-success").addClass("btn-danger");
		} else {
			target.val(1).html(app.vtranslate('JS_TURN_OFF'));
			target.removeClass("btn-danger").addClass("btn-success");
		}
		Settings_Index_Js.save(e);
	},
	updateLabels: function (e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		if(!closestTrElement.hasClass('ui-sortable-handle')){
			closestTrElement = closestTrElement.prev();
		}
		var progress = $.progressIndicator({
			'message': app.vtranslate('Update labels'),
			'blockInfo': {
				'enabled': true
			}
		});

		Settings_Index_Js.registerSaveEvent('UpdateLabels', {
			'tabid': closestTrElement.data('tabid'),
		});
		progress.progressIndicator({'mode': 'hide'});
	},
	save: function (e) {
		var target = $(e.currentTarget);
		var name = target.attr("name");
		var value = target.val();
		target.trigger("chosen:updated")
		if(value.length == 1){
			app.getChosenElementFromSelect(target).find('.search-choice-close').remove();
		}
		var closestTrElement = target.closest('tr');
		var progress = $.progressIndicator({
			'message': app.vtranslate('Saving changes'),
			'blockInfo': {
				'enabled': true
			}
		});
		Settings_Index_Js.registerSaveEvent('Save', {
			'name': name,
			'value': value,
			'tabid': closestTrElement.data('tabid'),
		});		
		progress.progressIndicator({'mode': 'hide'});
	},
	registerSaveEvent: function (mode, data) {
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
						animation: 'show',
						type: 'success'
					};
					Vtiger_Helper_Js.showPnotify(params);
					resp = response['success'];
				},
				function (data, err) {

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
		saveButton.removeClass('hide');
	},
	/**
	 * Function which will hide the saveModuleSequence button
	 */
	hideSaveModuleSequenceButton: function () {
		var layout = jQuery('.SearchFieldsEdit');
		var saveButton = layout.find('.saveModuleSequence');
		saveButton.addClass('hide');
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
		params['mode'] = 'SaveSequenceNumber';
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
	checkCountItems: function (element) {
		element.each(function (e) {
			var value = jQuery(this).val();
			if (value && value.length == 1) {
				app.getChosenElementFromSelect(jQuery(this)).find('.search-choice-close').remove();
			}
		})
	},
	registerEvents: function () {
		Settings_Index_Js.initEvants();
		this.makeFieldsListSortable();
		this.registerModuleSequenceSaveClick();
		this.checkCountItems($('.SearchFieldsEdit .fieldname'));
		this.checkCountItems($('.SearchFieldsEdit .searchcolumn'));
	}
}
$(document).ready(function () {
	Settings_Index_Js.registerEvents();
})
