/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Sharing_Access_Js',
	{},
	{
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
			var moduleName = parentElement.data('moduleName');
			var trElementForRuleList = jQuery(
				'<tr class="' +
					this.getCustomRuleContainerClassName(moduleName) +
					'"><td class="js-custom-rule-container" data-js="container" colspan="6"></td></tr>'
			);
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
			var params = {};
			params['for_module'] = forModule;
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['view'] = 'IndexAjax';
			params['mode'] = 'showRules';
			AppConnector.request(params)
				.done(function (data) {
					aDeferred.resolve(data);
				})
				.fail(function (error) {
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
			if (typeof data === 'undefined') {
				data = {};
			}
			AppConnector.request(data)
				.done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					aDeferred.resolve(data);
				})
				.fail(function (error, errorThrown) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					aDeferred.reject(error);
				});
			return aDeferred.promise();
		},

		/*
		 * function to Save the Custom Rule
		 */
		saveCustomRule: function (form, e) {
			const thisInstance = this;
			let data = form.serializeFormData();
			if (typeof data === 'undefined') {
				data = {};
			}
			let progressIndicatorElement = jQuery.progressIndicator({
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
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
				let response = data.result;
				if (response && response.success) {
					app.hideModalWindow();
					let moduleName = $('[name="for_module"]', form).val();
					thisInstance.loadCustomRulesList(moduleName);
					app.showNotify({
						type: 'success',
						text: response.message
					});
				} else {
					app.showNotify({
						type: 'error',
						text: 'JS_ERROR'
					});
				}
			});
		},

		/*
		 * function to load the CustomRules List for the module after save the custom rule
		 */
		loadCustomRulesList: function (moduleName) {
			var thisInstance = this;
			var contentTable = this.getContentTable();

			thisInstance.getCustomRules(moduleName).done(function (data) {
				var customRuleListContainer = jQuery(
					'.' + thisInstance.getCustomRuleContainerClassName(moduleName),
					contentTable
				);
				customRuleListContainer.find('td.js-custom-rule-container').html(data);
			});
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
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
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
			let deleteUrl = deleteElement.data('url');
			let currentRow = deleteElement.closest('.js-custom-rule-entries');
			app.showConfirmModal({
				title: app.vtranslate('LBL_DELETE_CONFIRMATION'),
				confirmedCallback: () => {
					AppConnector.request(deleteUrl).done(function (data) {
						let response = data.result;
						if (response && response.success) {
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
							app.showNotify({
								type: 'error',
								text: 'JS_ERROR'
							});
						}
					});
				}
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
			let container = this.getContentContainer();
			let contentTable = this.getContentTable();
			let modulesList = JSON.parse(container.find('.dependentModules').val());

			$.each(modulesList, function (moduleName, dependentList) {
				var dependentPrivilege = contentTable
					.find('[data-module-name="' + moduleName + '"]')
					.find('[data-action-state="Private"]');
				dependentPrivilege.on('change', function (e) {
					var currentTarget = $(e.currentTarget);
					if (currentTarget.is(':checked')) {
						app.showNotify({
							text: app.vtranslate('JS_DEPENDENT_PRIVILEGES_SHOULD_CHANGE'),
							hide: false
						});
						$.each(dependentList, function (index, module) {
							contentTable
								.find('[data-module-name="' + module + '"]')
								.find('[data-action-state="Private"]')
								.attr('checked', 'checked');
						});
					}
				});
			});
		},

		registerEvents: function () {
			const thisInstance = this;
			let contentTable = thisInstance.getContentTable(),
				contentContainer = thisInstance.getContentContainer();
			thisInstance.registerSharingAccessEdit();
			thisInstance.registerDependentModulesPrivilegesChange();
			contentTable.on('click', 'td.triggerCustomSharingAccess', function (e) {
				let element = $(e.currentTarget),
					trElement = element.closest('tr'),
					moduleName = trElement.data('moduleName'),
					customRuleListContainer = $('.' + thisInstance.getCustomRuleContainerClassName(moduleName), contentTable);
				if (customRuleListContainer.length > 0) {
					if (app.isHidden(customRuleListContainer)) {
						customRuleListContainer.show();
						$('.ruleListContainer', customRuleListContainer).slideDown('slow');
						trElement.addClass('collapseRow');
						element.find('button.arrowDown').addClass('d-none');
						element.find('button.arrowUp').removeClass('d-none').show();
					} else {
						$('.ruleListContainer', customRuleListContainer).slideUp('slow', function (e) {
							customRuleListContainer.css('display', 'none');
						});
						element.find('button.arrowUp').addClass('d-none');
						element.find('button.arrowDown').removeClass('d-none').show();
						trElement.removeClass('collapseRow');
					}
					return;
				}
				let progressIndicatorElement = $.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				thisInstance.getCustomRules(moduleName).done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					thisInstance.showCustomRulesNextToElement(trElement, data);
					element.find('button.arrowDown').addClass('d-none');
					element.find('button.arrowUp').removeClass('d-none').show();
				});
			});
			contentTable.on('click', '.js-add-custom-rule', function (e) {
				let button = $(e.currentTarget);
				thisInstance.editCustomRule(button.data('url'));
			});
			contentTable.on('click', '.js-edit', function (e) {
				let editElement = $(e.currentTarget),
					editUrl = editElement.data('url');
				thisInstance.editCustomRule(editUrl);
			});
			contentTable.on('click', '.js-delete', function (e) {
				let deleteElement = $(e.currentTarget);
				thisInstance.deleteCustomRule(deleteElement);
			});
			contentContainer.on('submit', '#js-edit-sharing-access', function (e) {
				e.preventDefault();
				let form = $(e.currentTarget),
					data = form.serializeFormData();
				thisInstance.save(data).done(function (data) {
					contentContainer.find('button:submit').addClass('d-none');
					thisInstance.registerSharingAccessEdit();
					app.showNotify({
						text: app.vtranslate('JS_NEW_SHARING_RULES_APPLIED_SUCCESSFULLY'),
						type: 'success'
					});
				});
			});
		}
	}
);

jQuery(function () {
	let settingSharingAcessInstance = new Settings_Sharing_Access_Js();
	settingSharingAcessInstance.registerEvents();
});
