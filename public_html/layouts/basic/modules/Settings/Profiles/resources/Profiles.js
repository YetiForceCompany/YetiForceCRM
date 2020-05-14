/*+*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
'use strict';

var Settings_Profiles_Js = {
	initEditView: function () {
		function toggleEditViewTableRow(e) {
			var target = jQuery(e.currentTarget);
			var container = jQuery('[data-togglecontent="' + target.data('togglehandler') + '"]');
			var closestTrElement = container.closest('tr');

			if (target.find('.fas').hasClass('fa-chevron-down')) {
				closestTrElement.removeClass('d-none');
				container.slideDown('slow');
				target.find('.fas').removeClass('fa-chevron-down').addClass('fa-chevron-up');
			} else {
				container.slideUp('slow', function () {
					closestTrElement.addClass('d-none');
				});
				target.find('.fa-chevron-up').removeClass('fa-chevron-up').addClass('fa-chevron-down');
			}
		}

		function handleChangeOfPermissionRange(e, ui) {
			var target = jQuery(ui.handle);
			if (!target.hasClass('mini-slider-control')) {
				target = target.closest('.mini-slider-control');
			}
			var input = jQuery('[data-range-input="' + target.data('range') + '"]');
			input.val(ui.value);
			target.attr('data-value', ui.value);
		}

		function handleModuleSelectionState(e) {
			var target = jQuery(e.currentTarget);
			var tabid = target.data('value');

			var parent = target.closest('tr');
			if (target.prop('checked')) {
				jQuery('[data-action-state]', parent).prop('checked', true);
				jQuery('[data-handlerfor]', parent).removeAttr('disabled');
			} else {
				jQuery('[data-action-state]', parent).prop('checked', false);

				// Pull-up fields / tools details in disabled state.
				jQuery('[data-handlerfor]', parent).attr('disabled', 'disabled');
				jQuery('[data-togglecontent="' + tabid + '-fields"]').hide();
				jQuery('[data-togglecontent="' + tabid + '-tools"]').hide();
			}
		}

		function handleActionSelectionState(e) {
			var target = jQuery(e.currentTarget);
			var parent = target.closest('tr');
			var checked = target.prop('checked') ? true : false;
			let action = target.data('action-state');
			if (action === 'EditView' || action === 'Delete' || action === 'CreateView') {
				if (checked) {
					jQuery('[data-action-state="DetailView"]', parent).prop('checked', true);
					jQuery('[data-module-state]', parent).prop('checked', true);
					jQuery('[data-handlerfor]', parent).removeAttr('disabled');
				}
			} else if (action === 'DetailView') {
				if (!checked) {
					jQuery('[data-action-state]', parent).prop('checked', false);
					jQuery('[data-module-state]', parent).prop('checked', false).trigger('change');
				} else {
					jQuery('[data-module-state]', parent).prop('checked', true);
					jQuery('[data-handlerfor]', parent).removeAttr('disabled');
				}
			}
		}

		function selectAllModulesViewAndToolPriviliges(e) {
			var target = jQuery(e.currentTarget);
			var checked = target.prop('checked') ? true : false;
			if (checked) {
				jQuery('#mainAction4CheckBox').prop('checked', true);
				jQuery('#mainModulesCheckBox').prop('checked', true);
				jQuery('.modulesCheckBox').prop('checked', true);
				jQuery('.action4CheckBox').prop('checked', true);
				jQuery('[data-handlerfor]').removeAttr('disabled');
			}
		}

		jQuery('[data-module-state]').on('change', handleModuleSelectionState);
		jQuery('[data-action-state]').on('change', handleActionSelectionState);
		jQuery('#mainAction1CheckBox,#mainAction2CheckBox, #mainAction7CheckBox').on(
			'change',
			selectAllModulesViewAndToolPriviliges
		);

		jQuery('[data-togglehandler]').on('click', toggleEditViewTableRow);
		jQuery('[data-range]').each(function (index, item) {
			item = jQuery(item);
			var value = item.data('value');
			item.slider({
				min: 0,
				max: 2,
				value: value,
				disabled: item.data('locked'),
				slide: handleChangeOfPermissionRange
			});
		});

		jQuery('[data-range]').find('a').css('filter', '');
	},

	registerSelectAllModulesEvent: function () {
		var moduleCheckBoxes = jQuery('.modulesCheckBox');
		var viewAction = jQuery('#mainAction4CheckBox');
		var editAction = jQuery('#mainAction1CheckBox');
		var deleteAction = jQuery('#mainAction2CheckBox');
		var createAction = jQuery('#mainAction7CheckBox');
		var mainModulesCheckBox = jQuery('#mainModulesCheckBox');
		mainModulesCheckBox.on('change', function (e) {
			var mainCheckBox = jQuery(e.currentTarget);
			if (mainCheckBox.is(':checked')) {
				moduleCheckBoxes.prop('checked', true);
				viewAction.prop('checked', true);
				editAction.show().prop('checked', true);
				deleteAction.show().prop('checked', true);
				createAction.show().prop('checked', true);
				moduleCheckBoxes.trigger('change');
			} else {
				moduleCheckBoxes.prop('checked', false);
				moduleCheckBoxes.trigger('change');
				viewAction.prop('checked', false);
				editAction.prop('checked', false);
				deleteAction.prop('checked', false);
				createAction.prop('checked', false);
			}
		});

		moduleCheckBoxes.on('change', function () {
			Settings_Profiles_Js.checkSelectAll(moduleCheckBoxes, mainModulesCheckBox);
			Settings_Profiles_Js.checkSelectAll(jQuery('.action4CheckBox'), viewAction);
			Settings_Profiles_Js.checkSelectAll(jQuery('.action1CheckBox'), editAction);
			Settings_Profiles_Js.checkSelectAll(jQuery('.action2CheckBox'), deleteAction);
			Settings_Profiles_Js.checkSelectAll(jQuery('.action7CheckBox'), createAction);
		});
	},

	registerSelectAllViewActionsEvent: function () {
		var viewActionCheckBoxes = jQuery('.action4CheckBox');
		var mainViewActionCheckBox = jQuery('#mainAction4CheckBox');
		var modulesMainCheckBox = jQuery('#mainModulesCheckBox');

		mainViewActionCheckBox.on('change', function (e) {
			var mainCheckBox = jQuery(e.currentTarget);
			if (mainCheckBox.is(':checked')) {
				modulesMainCheckBox.prop('checked', true);
				modulesMainCheckBox.trigger('change');
			} else {
				modulesMainCheckBox.prop('checked', false);
				modulesMainCheckBox.trigger('change');
			}
		});

		viewActionCheckBoxes.on('change', function () {
			Settings_Profiles_Js.checkSelectAll(jQuery('.modulesCheckBox'), modulesMainCheckBox);
			Settings_Profiles_Js.checkSelectAll(viewActionCheckBoxes, mainViewActionCheckBox);
		});
	},

	registerSelectAllEditActionsEvent: function () {
		var modulesMainCheckBox = jQuery('#mainModulesCheckBox');
		var mainViewActionCheckBox = jQuery('#mainAction4CheckBox');
		var editActionCheckBoxes = jQuery('.action1CheckBox');
		var mainEditActionCheckBox = jQuery('#mainAction1CheckBox');
		mainEditActionCheckBox.on('change', function (e) {
			var mainCheckBox = jQuery(e.currentTarget);
			if (mainCheckBox.is(':checked')) {
				editActionCheckBoxes.prop('checked', true);
			} else {
				editActionCheckBoxes.prop('checked', false);
			}
		});
		editActionCheckBoxes.on('change', function () {
			Settings_Profiles_Js.checkSelectAll(jQuery('.modulesCheckBox'), modulesMainCheckBox);
			Settings_Profiles_Js.checkSelectAll(jQuery('.action4CheckBox'), mainViewActionCheckBox);
			Settings_Profiles_Js.checkSelectAll(editActionCheckBoxes, mainEditActionCheckBox);
		});
	},

	registerSelectAllDeleteActionsEvent: function () {
		var modulesMainCheckBox = jQuery('#mainModulesCheckBox');
		var mainViewActionCheckBox = jQuery('#mainAction4CheckBox');
		var deleteActionCheckBoxes = jQuery('.action2CheckBox');
		var mainDeleteActionCheckBox = jQuery('#mainAction2CheckBox');
		mainDeleteActionCheckBox.on('change', function (e) {
			var mainCheckBox = jQuery(e.currentTarget);
			if (mainCheckBox.is(':checked')) {
				deleteActionCheckBoxes.prop('checked', true);
			} else {
				deleteActionCheckBoxes.prop('checked', false);
			}
		});
		deleteActionCheckBoxes.on('change', function () {
			Settings_Profiles_Js.checkSelectAll(jQuery('.modulesCheckBox'), modulesMainCheckBox);
			Settings_Profiles_Js.checkSelectAll(jQuery('.action4CheckBox'), mainViewActionCheckBox);
			Settings_Profiles_Js.checkSelectAll(deleteActionCheckBoxes, mainDeleteActionCheckBox);
		});
	},

	registerSelectAllCreateActionsEvent: function () {
		var modulesMainCheckBox = jQuery('#mainModulesCheckBox');
		var mainViewActionCheckBox = jQuery('#mainAction4CheckBox');
		var createActionCheckBoxes = jQuery('.action7CheckBox');
		var mainCreateActionCheckBox = jQuery('#mainAction7CheckBox');
		mainCreateActionCheckBox.on('change', function (e) {
			var mainCheckBox = jQuery(e.currentTarget);
			if (mainCheckBox.is(':checked')) {
				createActionCheckBoxes.prop('checked', true);
			} else {
				createActionCheckBoxes.prop('checked', false);
			}
		});
		createActionCheckBoxes.on('change', function () {
			Settings_Profiles_Js.checkSelectAll(jQuery('.modulesCheckBox'), modulesMainCheckBox);
			Settings_Profiles_Js.checkSelectAll(jQuery('.action4CheckBox'), mainViewActionCheckBox);
			Settings_Profiles_Js.checkSelectAll(createActionCheckBoxes, mainCreateActionCheckBox);
		});
	},

	checkSelectAll: function (checkBoxElement, mainCheckBoxElement) {
		var state = true;
		if (typeof checkBoxElement === 'undefined' || typeof mainCheckBoxElement === 'undefined') {
			return false;
		}
		checkBoxElement.each(function (index, element) {
			if (jQuery(element).is(':checked')) {
				state = true;
			} else {
				state = false;
				return false;
			}
		});
		if (state == true) {
			mainCheckBoxElement.prop('checked', true);
		} else {
			mainCheckBoxElement.prop('checked', false);
		}
	},

	performSelectAllActionsOnLoad: function () {
		if (jQuery('[data-module-unchecked]').length > 0) {
			jQuery('#mainModulesCheckBox').prop('checked', false);
		}
		if (jQuery('[data-action4-unchecked]').length <= 0) {
			jQuery('#mainAction4CheckBox').prop('checked', true);
		}
		if (jQuery('[data-action7-unchecked]').length <= 0) {
			jQuery('#mainAction7CheckBox').prop('checked', true);
		}
		if (jQuery('[data-action1-unchecked]').length <= 0) {
			jQuery('#mainAction1CheckBox').prop('checked', true);
		}
		if (jQuery('[data-action2-unchecked]').length > 0) {
			jQuery('#mainAction2CheckBox').prop('checked', false);
		}
	},

	registerSubmitEvent: function () {
		var thisInstance = this;
		var form = jQuery('[name="EditProfile"]');
		form.on('submit', function (e) {
			let button = form.find('button[type="submit"]'),
				progressIndicatorInstance = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
			button.attr('disabled', true);
			if (form.data('submit') === 'true' && form.data('performCheck') === 'true') {
				return true;
			} else {
				if (form.data('jqv').InvalidFields.length <= 0) {
					var formData = form.serializeFormData();
					thisInstance
						.checkDuplicateName({
							profileName: formData.profilename,
							profileId: formData.record
						})
						.done(function (data) {
							form.data('submit', 'true');
							form.data('performCheck', 'true');
							form.submit();
						})
						.fail(function (data, err) {
							progressIndicatorInstance.progressIndicator({ mode: 'hide' });
							button.attr('disabled', false);
							var params = {};
							params['text'] = data['message'];
							params['type'] = 'error';
							Settings_Vtiger_Index_Js.showMessage(params);
							return false;
						});
				} else {
					progressIndicatorInstance.progressIndicator({ mode: 'hide' });
					button.attr('disabled', false);
					//If validation fails, form should submit again
					form.removeData('submit');
					app.formAlignmentAfterValidation(form);
				}
				e.preventDefault();
			}
		});
	},

	/*
	 * Function to check Duplication of Profile Name
	 * returns boolean true or false
	 */

	checkDuplicateName: function (details) {
		var profileName = details.profileName;
		var recordId = details.profileId;
		var aDeferred = jQuery.Deferred();

		var params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			action: 'EditAjax',
			mode: 'checkDuplicate',
			profilename: profileName,
			record: recordId
		};

		AppConnector.request(params)
			.done(function (data) {
				var response = data['result'];
				var result = response['success'];
				if (result == true) {
					aDeferred.reject(response);
				} else {
					aDeferred.resolve(response);
				}
			})
			.fail(function (error, err) {
				aDeferred.reject(error, err);
			});
		return aDeferred.promise();
	},

	registerGlobalPermissionActionsEvent: function () {
		var editAllAction = jQuery('[name="editall"]').filter(':checkbox');
		var viewAllAction = jQuery('[name="viewall"]').filter(':checkbox');

		if (editAllAction.is(':checked')) {
			viewAllAction.attr('readonly', 'readonly');
		}

		viewAllAction.on('change', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			if (currentTarget.attr('readonly') == 'readonly') {
				var status = jQuery(e.currentTarget).is(':checked');
				if (!status) {
					jQuery(e.currentTarget).prop('checked', true);
				} else {
					jQuery(e.currentTarget).prop('checked', false);
				}
				e.preventDefault();
			}
		});

		editAllAction.on('change', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			if (currentTarget.is(':checked')) {
				viewAllAction.prop('checked', 'checked');
				viewAllAction.attr('readonly', 'readonly');
			} else {
				viewAllAction.removeAttr('readonly');
			}
		});
	},

	registerEvents: function () {
		Settings_Profiles_Js.initEditView();
		Settings_Profiles_Js.registerSelectAllModulesEvent();
		Settings_Profiles_Js.registerSelectAllViewActionsEvent();
		Settings_Profiles_Js.registerSelectAllCreateActionsEvent();
		Settings_Profiles_Js.registerSelectAllEditActionsEvent();
		Settings_Profiles_Js.registerSelectAllDeleteActionsEvent();
		Settings_Profiles_Js.performSelectAllActionsOnLoad();
		Settings_Profiles_Js.registerSubmitEvent();
		Settings_Profiles_Js.registerGlobalPermissionActionsEvent();
	}
};
jQuery(document).ready(function () {
	Settings_Profiles_Js.registerEvents();
});
