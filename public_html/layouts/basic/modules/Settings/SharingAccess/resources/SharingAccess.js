/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class('Settings_Sharing_Access_Js', {}, {

	contentTable: false,
	contentsContainer: false,

	init: function () {
		this.setContentTable('.sharingAccessDetails').setContentContainer('#sharingAccessContainer');

	},

	setContentTable: function (element) {
		if (element instanceof jQuery) {
			this.contentTable = element;
			return this;
		}
		this.contentTable = jQuery(element);
		return this;
	},

	setContentContainer: function (element) {
		if (element instanceof jQuery) {
			this.contentsContainer = element;
			return this;
		}
		this.contentsContainer = jQuery(element);
		return this;
	},

	getContentTable: function () {
		return this.contentTable;
	},

	getContentContainer: function () {
		return this.contentsContainer;
	},

	getCustomRuleContainerClassName: function (parentModuleName) {
		return 'js-' + parentModuleName + '-custom-rule-list';
	},

	showCustomRulesNextToElement: function (parentElement, rulesListElement) {
		var moduleName = parentElement.data('moduleName')
		var trElementForRuleList = jQuery('<tr class="' + this.getCustomRuleContainerClassName(moduleName) + '"><td class="js-custom-rule-container" data-js="container" colspan="6"></td></tr>');
		jQuery('td', trElementForRuleList).append(rulesListElement);
		jQuery('.ruleListContainer', trElementForRuleList).css('display', 'none');
		parentElement.after(trElementForRuleList).addClass('collapseRow');
		jQuery('.ruleListContainer', trElementForRuleList).slideDown('slow');
	},

	/*
	 * function to get custom rules data based on the module
	 * @params: forModule.
	 */
	getCustomRules: function (forModule) {
		var aDeferred = jQuery.Deferred();
		var params = {}
		params['for_module'] = forModule;
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['view'] = 'IndexAjax';
		params['mode'] = 'showRules';
		AppConnector.request(params).done(function (data) {
			aDeferred.resolve(data);
		}).fail(function (error) {
			aDeferred.reject(error);
		});
		return aDeferred.promise();
	},

	save: function (data) {
		var aDeferred = jQuery.Deferred();

		var progressIndicatorElement = jQuery.progressIndicator({
			position: 'html',
			blockInfo: {
				enabled: true
			}
		});
		if (typeof data === "undefined") {
			data = {};
		}
		AppConnector.request(data).done(function (data) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			aDeferred.resolve(data);
		}).fail(function (error, errorThrown) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			aDeferred.reject(error);
		});
		return aDeferred.promise();
	},

	/*
	 * function to Save the Custom Rule
	 */
	saveCustomRule: function (form, e) {
		var thisInstance = this;
		var data = form.serializeFormData();
		if (typeof data === "undefined") {
			data = {};
		}
		var progressIndicatorElement = jQuery.progressIndicator({
			position: 'html',
			blockInfo: {
				enabled: true
			}
		});
		data.module = app.getModuleName();
		data.parent = app.getParentModuleName();
		data.action = 'IndexAjax';
		data.mode = 'saveRule';
		AppConnector.request(data).done(function (data) {
			progressIndicatorElement.progressIndicator({mode: 'hide'});
			app.hideModalWindow();
			thisInstance.displaySaveCustomRuleResponse(data);
			var moduleName = jQuery('[name="for_module"]', form).val();
			thisInstance.loadCustomRulesList(moduleName);
		});
	},

	/*
	 * function to load the CustomRules List for the module after save the custom rule
	 */
	loadCustomRulesList: function (moduleName) {
		var thisInstance = this;
		var contentTable = this.getContentTable();

		thisInstance.getCustomRules(moduleName).done(function (data) {
			var customRuleListContainer = jQuery('.' + thisInstance.getCustomRuleContainerClassName(moduleName), contentTable);
			customRuleListContainer.find('td.js-custom-rule-container').html(data);
		});
	},

	/*
	 * Function to display the SaveCustomRule response message
	 */
	displaySaveCustomRuleResponse: function (data) {
		var thisInstance = this;
		var success = data['success'];
		var params = {};
		if (success) {
			params = {
				text: app.vtranslate('JS_CUSTOM_RULE_SAVED_SUCCESSFULLY'),
				type: 'success'
			};
		} else {
			params = {
				text: app.vtranslate('JS_CUSTOM_RULE_SAVING_FAILED'),
				type: 'error'
			};
		}
		thisInstance.showNotify(params);
	},

	//This will show the notification message of SaveCustomRule using pnotify
	showNotify: function (customParams) {
		var params = {
			text: customParams.text,
			type: customParams.type,
			delay: '3000'
		};
		Vtiger_Helper_Js.showPnotify(params);
	},

	editCustomRule: function (url) {
		var thisInstance = this;
		var progressIndicatorElement = jQuery.progressIndicator({
			position: 'html',
			blockInfo: {
				enabled: true
			}
		});
		app.showModalWindow(null, url, function (modalContainer) {
			progressIndicatorElement.progressIndicator({mode: 'hide'});
			var form = modalContainer.find('.js-edit-rule-form');
			form.on('submit', function (e) {
				//To stop the submit of form
				e.preventDefault();
				var formElement = $(e.currentTarget);
				thisInstance.saveCustomRule(formElement, e);
			});
		});
	},

	/*
	 * function to delete Custom Rule from the list
	 * @params: deleteElement.
	 */
	deleteCustomRule: function (deleteElement) {
		var deleteUrl = deleteElement.data('url');
		var currentRow = deleteElement.closest('.js-custom-rule-entries');
		var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
		Vtiger_Helper_Js.showConfirmationBox({'message': message}).done(function (data) {
			AppConnector.request(deleteUrl).done(function (data) {
				if (data.success == true) {
					currentRow.fadeOut('slow');
					var customRuleTable = currentRow.closest('.js-custom-rule-table');
					//after delete the custom rule, update the sequence number of existing rules
					var nextRows = currentRow.nextAll('js-custom-rule-entries');
					if (nextRows.length > 0) {
						jQuery.each(nextRows, function (i, element) {
							var currentSequenceElement = jQuery(element).find('.js-sequence-number');
							var updatedNumber = parseInt(currentSequenceElement.text()) - 1;
							currentSequenceElement.text(updatedNumber);
						});
					}
					currentRow.remove();
					var customRuleEntries = customRuleTable.find('.js-custom-rule-entries');
					//if there are no custom rule entries, we have to hide headers also and show the empty message div
					if (customRuleEntries.length < 1) {
						customRuleTable.find('.js-custom-rule-headers').fadeOut('slow').remove();
						customRuleTable.parent().find('.js-record-details').removeClass('d-none');
						customRuleTable.addClass('d-none');
					}
				} else {
					Vtiger_Helper_Js.showPnotify(data.error.message);
				}
			});
		});
	},

	/*
	 * function to register click event for radio buttons
	 */
	registerSharingAccessEdit: function () {
		var contentContainer = this.getContentContainer();
		contentContainer.one('click', 'input:radio', function (e) {
			contentContainer.find('button:submit').removeClass('d-none');
		});
	},

	/*
	 * Function to register change event for dependent modules privileges
	 */
	registerDependentModulesPrivilegesChange: function () {
		var thisInstance = this;
		var container = thisInstance.getContentContainer();
		var contentTable = this.getContentTable();
		var modulesList = JSON.parse(container.find('.dependentModules').val());

		jQuery.each(modulesList, function (moduleName, dependentList) {
			var dependentPrivilege = contentTable.find('[data-module-name="' + moduleName + '"]').find('[data-action-state="Private"]');
			dependentPrivilege.on('change', function (e) {
				var currentTarget = jQuery(e.currentTarget);
				if (currentTarget.is(':checked')) {
					var message = app.vtranslate('JS_DEPENDENT_PRIVILEGES_SHOULD_CHANGE');
					bootbox.alert(message);
					jQuery.each(dependentList, function (index, module) {
						contentTable.find('[data-module-name="' + module + '"]').find('[data-action-state="Private"]').attr('checked', 'checked');
					})
				}
			})
		})
	},

	registerEvents: function () {
		var thisInstance = this;
		var contentTable = this.getContentTable();
		var contentContainer = this.getContentContainer();
		thisInstance.registerSharingAccessEdit();
		thisInstance.registerDependentModulesPrivilegesChange();

		contentTable.on('click', 'td.triggerCustomSharingAccess', function (e) {
			var element = jQuery(e.currentTarget);
			var trElement = element.closest('tr');
			var moduleName = trElement.data('moduleName');
			var customRuleListContainer = jQuery('.' + thisInstance.getCustomRuleContainerClassName(moduleName), contentTable);
			if (customRuleListContainer.length > 0) {
				if (app.isHidden(customRuleListContainer)) {
					customRuleListContainer.show();
					jQuery('.ruleListContainer', customRuleListContainer).slideDown('slow');
					trElement.addClass('collapseRow');
					element.find('button.arrowDown').addClass('d-none');
					element.find('button.arrowUp').removeClass('d-none').show();
				} else {
					jQuery('.ruleListContainer', customRuleListContainer).slideUp('slow', function (e) {
						customRuleListContainer.css('display', 'none');
					});
					element.find('button.arrowUp').addClass('d-none');
					element.find('button.arrowDown').removeClass('d-none').show();
					trElement.removeClass('collapseRow');
				}
				return;
			}

			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});

			thisInstance.getCustomRules(moduleName).done(function (data) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				thisInstance.showCustomRulesNextToElement(trElement, data);
				element.find('button.arrowDown').addClass('d-none');
				element.find('button.arrowUp').removeClass('d-none').show();
			});
		});
		contentTable.on('click', '.js-add-custom-rule', function (e) {
			var button = jQuery(e.currentTarget);
			thisInstance.editCustomRule(button.data('url'));
		});
		contentTable.on('click', '.js-edit', function (e) {
			var editElement = jQuery(e.currentTarget);
			var editUrl = editElement.data('url');
			thisInstance.editCustomRule(editUrl);
		});
		contentTable.on('click', '.js-delete', function (e) {
			var deleteElement = jQuery(e.currentTarget);
			thisInstance.deleteCustomRule(deleteElement);
		});
		contentContainer.on('submit', '#js-edit-sharing-access', function (e) {
			e.preventDefault();
			var form = jQuery(e.currentTarget);
			var data = form.serializeFormData();
			thisInstance.save(data).done(function (data) {
				contentContainer.find('button:submit').addClass('d-none');
				thisInstance.registerSharingAccessEdit();
				var params = {
					text: app.vtranslate('JS_NEW_SHARING_RULES_APPLIED_SUCCESSFULLY'),
					type: 'success'
				};
				thisInstance.showNotify(params);
			});
		});
	}
});

jQuery(document).ready(function () {
	var settingSharingAcessInstance = new Settings_Sharing_Access_Js();
	settingSharingAcessInstance.registerEvents();
});
