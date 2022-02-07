/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_QuickCreateEditor_Js',
	{},
	{
		updatedBlockSequence: {},

		updatedBlockFieldsList: [],

		/**
		 * Function which will enable the save button in realted tabs list
		 */
		showSaveButton: function () {
			var relatedList = jQuery('#relatedTabOrder');
			var saveButton = relatedList.find('.saveRelatedList');
			if (saveButton.attr('disabled') == 'disabled') {
				saveButton.removeAttr('disabled');
			}
		},

		/**
		 * Function to regiser the event to make the fields sortable
		 */
		makeFieldsListSortable: function () {
			var thisInstance = this;
			var contents = jQuery('#quickCreateEditorContainer').find('.contents');
			jQuery('#quickCreateEditorContainer .contents .editFieldsTable').each(function () {
				jQuery(this)
					.find('ul[name=sortable1], ul[name=sortable2]')
					.sortable({
						containment: '#moduleBlocks',
						revert: true,
						tolerance: 'pointer',
						cursor: 'move',
						connectWith: jQuery(this).find('.connectedSortable'),
						update: function (e, ui) {
							thisInstance.showSaveFieldSequenceButton();
							// rearrange the older block fields
							if (ui.sender) {
								var olderBlock = ui.sender.closest('.editFieldsTable');
								thisInstance.reArrangeBlockFields(olderBlock);
							}
						}
					});
			});
		},

		/**
		 * Function to show the save button of fieldSequence
		 */
		showSaveFieldSequenceButton: function () {
			var thisInstance = this;
			var layout = jQuery('#detailViewLayout');
			var saveButton = layout.find('.saveFieldSequence');
			thisInstance.updatedBlockFieldsList = [];
			saveButton.removeClass('visibility');
		},

		/**
		 * Function which will hide the saveFieldSequence button
		 */
		hideSaveFieldSequenceButton: function () {
			var layout = jQuery('#detailViewLayout');
			var saveButton = layout.find('.saveFieldSequence');
			saveButton.addClass('visibility');
		},

		/**
		 * Function that rearranges fields in the block when the fields are moved
		 * @param <jQuery object> block
		 */
		reArrangeBlockFields: function (block) {
			// 1.get the containers, 2.compare the length, 3.if uneven then move the last element
			var leftSideContainer = block.find('ul[name=sortable1]');
			var rightSideContainer = block.find('ul[name=sortable2]');
			if (leftSideContainer.children().length < rightSideContainer.children().length) {
				var lastElementInRightContainer = rightSideContainer.children(':last');
				leftSideContainer.append(lastElementInRightContainer);
			} else if (leftSideContainer.children().length > rightSideContainer.children().length + 1) {
				//greater than 1
				var lastElementInLeftContainer = leftSideContainer.children(':last');
				rightSideContainer.append(lastElementInLeftContainer);
			}
		},
		/**
		 * Function to create the list of updated blocks with all the fields and their sequences
		 */
		createUpdatedBlockFieldsList: function () {
			const thisInstance = this;
			const contents = $('#quickCreateEditorContainer').find('.contents');
			const updatedBlock = contents.find('.block');
			if (app.getMainParams('quickCreateLayout') === 'blocks') {
				updatedBlock.each(function (i, domElement) {
					let updatedBlock = jQuery(domElement);
					let firstBlockSortFields = updatedBlock.find('ul[name=sortable1]');
					let editFields = firstBlockSortFields.find('.editFields');
					let expectedFieldSequence = 1;
					editFields.each(function (i, domElement) {
						let fieldEle = $(domElement);
						let fieldId = fieldEle.data('fieldId');
						thisInstance.updatedBlockFieldsList.push({
							fieldid: fieldId,
							sequence: expectedFieldSequence
						});
						expectedFieldSequence = expectedFieldSequence + 2;
					});
					let secondBlockSortFields = updatedBlock.find('ul[name=sortable2]');
					let secondEditFields = secondBlockSortFields.find('.editFields');
					let sequenceValue = 2;
					secondEditFields.each(function (i, domElement) {
						let fieldEle = $(domElement);
						let fieldId = fieldEle.data('fieldId');
						thisInstance.updatedBlockFieldsList.push({
							fieldid: fieldId,
							sequence: sequenceValue
						});
						sequenceValue = sequenceValue + 2;
					});
				});
			} else {
				let firstBlockSortFields = updatedBlock.find('ul[name=sortable1]');
				let tmpArray = [];
				firstBlockSortFields.each(function (i, domElement) {
					let fieldEle = jQuery(domElement);
					let eleAmount = fieldEle.find('.editFields').length;
					tmpArray.push(eleAmount);
				});
				let editFields = firstBlockSortFields.find('.editFields');
				let expectedFieldSequence = 1;
				editFields.each(function (i, domElement) {
					let fieldEle = jQuery(domElement);
					let fieldId = fieldEle.data('fieldId');
					thisInstance.updatedBlockFieldsList.push({
						fieldid: fieldId,
						sequence: expectedFieldSequence
					});
					expectedFieldSequence = expectedFieldSequence + 2;
					if (i == tmpArray[0] - 1) {
						expectedFieldSequence = 1;
					}
				});
				let secondBlockSortFields = updatedBlock.find('ul[name=sortable2]');
				let secondEditFields = secondBlockSortFields.find('.editFields');
				let sequenceValue = 2;
				tmpArray = [];
				secondBlockSortFields.each(function (i, domElement) {
					let fieldEle = jQuery(domElement);
					let eleAmount = fieldEle.find('.editFields').length;
					tmpArray.push(eleAmount);
				});
				secondEditFields.each(function (i, domElement) {
					let fieldEle = jQuery(domElement);
					let fieldId = fieldEle.data('fieldId');
					thisInstance.updatedBlockFieldsList.push({ fieldid: fieldId, sequence: sequenceValue });
					sequenceValue = sequenceValue + 2;
					if (i == tmpArray[0] - 1) {
						sequenceValue = 2;
					}
				});
			}
		},
		/**
		 * Function to register click event for save button of fields sequence
		 */
		registerFieldSequenceSaveClick: function () {
			var thisInstance = this;
			var layout = jQuery('#detailViewLayout');
			layout.on('click', '.saveFieldSequence', function () {
				thisInstance.hideSaveFieldSequenceButton();
				thisInstance.createUpdatedBlockFieldsList();
				thisInstance.updateFieldSequence();
			});
		},

		/**
		 * Function will save the field sequences
		 */
		updateFieldSequence: function () {
			var thisInstance = this;
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['action'] = 'SaveSequenceNumber';
			params['mode'] = 'move';
			params['updatedFields'] = thisInstance.updatedBlockFieldsList;

			AppConnector.request(params)
				.done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					//window.location.reload();
					Settings_Vtiger_Index_Js.showMessage({
						text: app.vtranslate('JS_FIELD_SEQUENCE_UPDATED')
					});
				})
				.fail(function (error) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				});
		},

		/**
		 * Function to register click event for drop-downs in fields list
		 */
		avoidDropDownClick: function (dropDownContainer) {
			dropDownContainer.find('.dropdown-menu').on('click', function (e) {
				e.stopPropagation();
			});
		},

		/*
		 * Function to add clickoutside event on the element - By using outside events plugin
		 * @params element---On which element you want to apply the click outside event
		 * @params callbackFunction---This function will contain the actions triggered after clickoutside event
		 */
		addClickOutSideEvent: function (element, callbackFunction) {
			element.one('clickoutside', callbackFunction);
		},
		/**
		 * Function to register the change event for layout editor modules list
		 */
		registerModulesChangeEvent: function () {
			var thisInstance = this;
			var container = jQuery('#quickCreateEditorContainer');
			var contentsDiv = container.closest('.contentsDiv');

			App.Fields.Picklist.showSelect2ElementView(container.find('[name="quickCreateEditorModules"]'), {
				dropdownCss: { 'z-index': 0 }
			});

			container.on('change', '[name="quickCreateEditorModules"]', function (e) {
				var currentTarget = jQuery(e.currentTarget);
				var selectedModule = currentTarget.val();
				thisInstance.getModuleQuickCreateEditor(selectedModule).done(function (data) {
					contentsDiv.html(data);
					thisInstance.registerEvents();
				});
			});
		},
		/**
		 * Function to get the respective module layout editor through pjax
		 */
		getModuleQuickCreateEditor: function (selectedModule) {
			var aDeferred = jQuery.Deferred();
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});

			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['view'] = 'Index';
			params['sourceModule'] = selectedModule;

			AppConnector.requestPjax(params)
				.done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					aDeferred.resolve(data);
				})
				.fail(function (error) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					aDeferred.reject();
				});
			return aDeferred.promise();
		},

		/**
		 * register events for layout editor
		 */
		registerEvents: function () {
			var thisInstance = this;

			thisInstance.registerModulesChangeEvent();
			thisInstance.makeFieldsListSortable();
			thisInstance.registerFieldSequenceSaveClick();
		}
	}
);

jQuery(document).ready(function () {
	var instance = new Settings_QuickCreateEditor_Js();
	instance.registerEvents();
});
