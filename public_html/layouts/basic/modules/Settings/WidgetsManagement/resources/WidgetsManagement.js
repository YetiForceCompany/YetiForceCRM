/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class('Settings_WidgetsManagement_Js', {}, {
	widgetWithFilterUsers: [],
	setWidgetWithFilterUsers: function () {
		var thisInstance = this;
		var element = jQuery('[name="filter_users"]').val();
		if (element)
			thisInstance.widgetWithFilterUsers = JSON.parse(element);
		else
			thisInstance.widgetWithFilterUsers = [];
	},
	widgetWithFilterDate: [],
	setWidgetWithFilterDate: function () {
		var thisInstance = this;
		var element = jQuery('[name="filter_date"]').val();
		if (element)
			thisInstance.widgetWithFilterDate = JSON.parse(element);
		else
			thisInstance.widgetWithFilterDate = [];
	},
	restrictFilter: [],
	setRestrictFilter: function () {
		var thisInstance = this;
		var element = jQuery('[name="filter_restrict"]').val();
		if (element)
			thisInstance.restrictFilter = JSON.parse(element);
		else
			thisInstance.restrictFilter = [];
	},
	/**
	 * Function to create the array of block roles list
	 */
	getAuthorization: function () {
		let authorization = [],
			container = jQuery('#moduleBlocks');
		container.find('.editFieldsTable').each(function () {
			authorization.push(jQuery(this).data('code'));
		});
		return authorization;
	},
	getAllFieldsInBlock: function (continer) {
		var fields = [];
		continer.find('.blockFieldsList .editFieldsWidget').each(function () {
			fields.push(jQuery(this).data('linkid').toString());
		});
		return fields;
	},
	getCurrentDashboardId() {
		return $('.selectDashboard li a.active').parent().data('id');
	},
	registerAddedDashboard() {
		const thisInstance = this;
		$('.addDashboard').on('click', () => {
			app.showModalWindow({
				url: 'index.php?parent=Settings&module=' + app.getModuleName() + '&view=DashboardType',
				sendByAjaxCb: () => {
					let contentsDiv = $('.contentsDiv');
					thisInstance.getModuleLayoutEditor('Home').done((data) => {
						thisInstance.updateContentsDiv(contentsDiv, data);
					});
				},
			});
		});
	},
	registerSelectDashboard() {
		const thisInstance = this;
		$('.selectDashboard li').on('click', (e) => {
			let contentsDiv = $('.contentsDiv');
			thisInstance.getModuleLayoutEditor($('#selectedModuleName').val(), $(e.currentTarget).data('id')).done((data) => {
				thisInstance.updateContentsDiv(contentsDiv, data);
			});
		});
	},
	registerDashboardAction() {
		const thisInstance = this;
		$('.editDashboard').on('click', (e) => {
			let currentTarget = $(e.currentTarget);
			e.stopPropagation();
			app.showModalWindow({
				url: 'index.php?parent=Settings&module=' + app.getModuleName() + '&view=DashboardType&dashboardId=' + currentTarget.closest('li').data('id'),
				sendByAjaxCb: () => {
					let contentsDiv = $('.contentsDiv');
					thisInstance.getModuleLayoutEditor('Home', currentTarget.closest('li').data('id')).done((data) => {
						thisInstance.updateContentsDiv(contentsDiv, data);
					});
				},
			});
		});
		$('.deleteDashboard').on('click', (e) => {
			let currentTarget = $(e.currentTarget);
			e.stopPropagation();
			AppConnector.request({
				parent: 'Settings',
				module: app.getModuleName(),
				action: 'Dashboard',
				mode: 'delete',
				dashboardId: currentTarget.closest('li').data('id')
			}).done(() => {
				let contentsDiv = $('.contentsDiv');
				thisInstance.getModuleLayoutEditor('Home', 1).done((data) => {
					thisInstance.updateContentsDiv(contentsDiv, data);
				});
			});
		});
	},
	/**
	 * Function to register click event for add custom block button
	 */
	registerAddBlockDashBoard: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutDashBoards');
		contents.find('.addBlockDashBoard').on('click', function (e) {
			var addBlockContainer = contents.find('.addBlockDashBoardModal').clone(true, true);
			var inUseAuthorization = thisInstance.getAuthorization();
			addBlockContainer.find('select.authorized option').each(function () {
				if (jQuery.inArray(jQuery(this).val(), inUseAuthorization) != -1)
					jQuery(this).remove();
			});

			var callBackFunction = function (data) {
				//register all select2 Elements
				App.Fields.Picklist.changeSelectElementView(data.find('select'));
				var form = data.find('.addBlockDashBoardForm');
				var block = form.find('[name="authorized"]');
				form.validationEngine(app.validationEngineOptions);
				form.on('submit', function (e) {
					if (form.validationEngine('validate')) {
						var paramsForm = form.serializeFormData();
						paramsForm['action'] = 'addBlock';
						var paramsBlock = {
							authorized: block.val(),
							label: block.find(':selected').text()
						};
						thisInstance.save(paramsForm, 'save').done(function (data) {
							var params = {};
							var response = data.result;
							if (response['success']) {
								paramsBlock['id'] = response['id'];
								thisInstance.displayNewCustomBlock(paramsBlock);
								app.hideModalWindow();
								params['text'] = app.vtranslate('JS_BLOCK_ADDED');
							} else {
								params['text'] = response['message'];
								params['type'] = 'error';
							}
							Settings_Vtiger_Index_Js.showMessage(params);
							window.location.reload();
						});
					}
					e.preventDefault();
				})
			}
			app.showModalWindow(addBlockContainer, function (data) {
				if (typeof callBackFunction == 'function') {
					callBackFunction(data);
				}
			}, {'width': '1000px'});
		});
	},
	save: function (form, mode) {
		var aDeferred = jQuery.Deferred();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});

		var params = {};
		params['form'] = form;
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['sourceModule'] = jQuery('#selectedModuleName').val();
		params['action'] = 'SaveAjax';
		params['mode'] = mode;

		AppConnector.request(params).done(function (data) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			aDeferred.resolve(data);
		}).fail(function (error) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			aDeferred.reject(error);
		});
		return aDeferred.promise();
	},
	displayNewCustomBlock: function (result) {
		var contents = jQuery('#layoutDashBoards');
		var newBlockCloneCopy = contents.find('.newCustomBlockCopy').clone(true, true);
		newBlockCloneCopy.data('block-id', result['id']).find('.blockLabel span').append(jQuery('<strong>' + result['label'] + '</strong>'));
		newBlockCloneCopy.find('.addCustomField').removeClass('d-none').show();
		newBlockCloneCopy.find('.specialWidget').data('block-id', result['id']);
		contents.find('#moduleBlocks').append(newBlockCloneCopy.removeClass('newCustomBlockCopy d-none').addClass('editFieldsTable block_' + result['id']).data('code', result['authorized']));
	},
	/*
	 * Function to add clickoutside event on the element - By using outside events plugin
	 * @params element---On which element you want to apply the click outside event
	 * @params callbackFunction---This function will contain the actions triggered after clickoutside event
	 */
	addClickOutSideEvent: function (element, callbackFunction) {
		element.one('clickoutside', callbackFunction);
	},
	registerAddCustomFieldEvent: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutDashBoards');
		contents.find('.addCustomField').on('click', function (e) {
			var continer = jQuery(e.currentTarget).closest('.editFieldsTable');
			var blockId = continer.data('block-id');
			var addFieldContainer = contents.find('.createFieldModal').clone(true, true);
			var allFieldsInBlock = thisInstance.getAllFieldsInBlock(continer);
			var selectWidgets = addFieldContainer.find('select.widgets');
			selectWidgets.find('option').each(function () {
				if (jQuery.inArray(jQuery(this).val(), allFieldsInBlock) != -1) {
					jQuery(this).remove();
				}
			});
			var name = selectWidgets.find(':first-child').data('name');
			if (jQuery.inArray(name, thisInstance.widgetWithFilterUsers) != -1) {
				addFieldContainer.find('.widgetFilter').removeClass('d-none').find('select').removeAttr('disabled').show();
				var restrictFilter = thisInstance.restrictFilter[name];
				if (restrictFilter) {
					for (var i in restrictFilter) {
						addFieldContainer.find('.widgetFilter select option[value="' + restrictFilter[i] + '"]').remove();
					}
				}
			}
			if (jQuery.inArray(name, thisInstance.widgetWithFilterDate) != -1) {
				addFieldContainer.find('.widgetFilterDate').removeClass('d-none').find('select').removeAttr('disabled').show();
			}

			var callBackFunction = function (data) {
				//register all select2 Elements
				App.Fields.Picklist.showSelect2ElementView(data.find('select'));
				data.find('select.widgets').on('change', function () {
					data.find('.widgetFilter').remove();
					data.find('.widgetFilterDate').remove();
					var elementsToFilter = contents.find('.createFieldModal .widgetFilter').clone(true, true);
					var elementsToFilterDate = contents.find('.createFieldModal .widgetFilterDate').clone(true, true);

					data.find('.modal-body').append(elementsToFilter);
					data.find('.modal-body').append(elementsToFilterDate);
					var name = jQuery(this).find(':selected').data('name');
					if (jQuery.inArray(name, thisInstance.widgetWithFilterUsers) != -1) {
						elementsToFilter.removeClass('d-none').find('select').prop('disabled', false);
						var restrictFilter = thisInstance.restrictFilter[name];
						if (restrictFilter) {
							for (var i in restrictFilter) {
								addFieldContainer.find('.widgetFilter select option[value="' + restrictFilter[i] + '"]').remove();
							}
						}
						App.Fields.Picklist.showSelect2ElementView(elementsToFilter.find('select'));
					} else {
						elementsToFilter.addClass('d-none').find('select').prop('disabled', true);
					}
					if (jQuery.inArray(name, thisInstance.widgetWithFilterDate) != -1) {
						elementsToFilterDate.removeClass('d-none').find('select').prop('disabled', false);
						App.Fields.Picklist.showSelect2ElementView(elementsToFilterDate.find('select'));
					} else {
						elementsToFilterDate.addClass('d-none').find('select').prop('disabled', true);
					}
				});

				var form = data.find('.createCustomFieldForm');
				form.attr('id', 'createFieldForm');
				var widgets = form.find('[name="widgets"]');
				form.validationEngine($.extend(true, {
					onValidationComplete: function (form, valid) {
						if (valid) {
							if (widgets.val()) {
								let saveButton = form.find(':submit'),
									field = form.find('[name="widgets"]');
								saveButton.attr('disabled', 'disabled');

								let paramsForm = form.serializeFormData();
								paramsForm['action'] = 'addWidget';
								paramsForm['blockid'] = blockId;
								paramsForm['linkid'] = field.val();
								paramsForm['label'] = field.find(':selected').text();
								paramsForm['name'] = field.find(':selected').data('name');
								paramsForm['height'] = form.find('[name="height"]').val();
								paramsForm['width'] = form.find('[name="width"]').val();
								if (form.find('[name="isdefault"]').prop("checked"))
									paramsForm['isdefault'] = 1;
								if (form.find('[name="cache"]').prop("checked"))
									paramsForm['cache'] = 1;
								if (paramsForm['default_owner'] && typeof paramsForm['owners_all'] === "undefined") {
									form.find('select[name="owners_all"]')
										.prev('div')
										.validationEngine('showPrompt', app.vtranslate('JS_FIELD_EMPTY'), 'error', 'bottomLeft', true);
									saveButton.removeAttr('disabled');
									e.preventDefault();
									return false;
								}
								thisInstance.save(paramsForm, 'save').done(function (data) {
									let result = data['result'],
										params = {};
									if (data['success']) {
										app.hideModalWindow();
										paramsForm['id'] = result['id']
										paramsForm['status'] = result['status']
										params['text'] = app.vtranslate('JS_WIDGET_ADDED');
										Settings_Vtiger_Index_Js.showMessage(params);
										thisInstance.showCustomField(paramsForm);
									} else {
										let message = data['error']['message'],
											errorField;
										if (data['error']['code'] != 513) {
											errorField = form.find('[name="fieldName"]');
										} else {
											errorField = form.find('[name="fieldLabel"]');
										}
										errorField.validationEngine('showPrompt', message, 'error', 'topLeft', true);
										saveButton.removeAttr('disabled');
									}
								});
							} else {
								widgets.prev('div').validationEngine('showPrompt', app.vtranslate('JS_FIELD_EMPTY'), 'error', 'topLeft', true);
								e.preventDefault();
								return;
							}
						}
						//To prevent form submit
						return false;
					}
				}, app.validationEngineOptions));
			};
			app.showModalWindow(addFieldContainer, function (data) {
				if (typeof callBackFunction == 'function') {
					callBackFunction(data);
				}
			}, {'width': '1000px'});
		});
	},
	/**
	 * Function to add new custom field ui to the list
	 */
	showCustomField: function (result) {
		var thisInstance = this;
		var contents = jQuery('#layoutDashBoards');
		var relatedBlock = contents.find('.block_' + result['blockid']);
		var fieldCopy = contents.find('.newCustomFieldCopy').clone(true, true);
		var fieldContainer = fieldCopy.find('.js-custom-field');
		fieldContainer.addClass('opacity editFieldsWidget').attr('data-field-id', result['id']).attr('data-block-id', result['blockid']).attr('data-linkid', result['linkid']);
		fieldContainer.find('.deleteCustomField, .saveFieldDetails').attr('data-field-id', result['id']);
		if (result['title']) {
			fieldContainer.find('.fieldLabel').html(result['title']);
		} else {
			fieldContainer.find('.fieldLabel').html(result['label']);
		}
		if (!result['status'])
			fieldContainer.find('input[name="limit"]').closest('div.limit').remove();
		if (typeof result['default_owner'] !== "undefined")
			fieldContainer.find('.widgetFilterAll').removeClass('d-none').show();

		var block = relatedBlock.find('.blockFieldsList');
		var sortable1 = block.find('ul[name=sortable1]');
		var length1 = sortable1.children().length;
		var sortable2 = block.find('ul[name=sortable2]');
		var length2 = sortable2.children().length;

		// Deciding where to add the new field
		if (length1 > length2) {
			sortable2.append(fieldCopy.removeClass('d-none newCustomFieldCopy'));
		} else {
			sortable1.append(fieldCopy.removeClass('d-none newCustomFieldCopy'));
		}
		var form = fieldCopy.find('form.fieldDetailsForm');
		thisInstance.setFieldDetails(result, form);
	},
	/**
	 * Function to set the field info for edit field actions
	 */
	setFieldDetails: function (result, form) {
		form.find('.modal-header').html($('<h5 class="modal-title">' + result['label'] + '</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'));
		if (result['isdefault']) {
			form.find('[name="isdefault"]').filter(':checkbox').attr('checked', true);
		}
		if (result['cache']) {
			form.find('[name="cache"]').filter(':checkbox').attr('checked', true);
		}
		if (result['width']) {
			form.find('select[name="width"]').find('option').removeAttr('selected');
			form.find('select[name="width"]').find('option[value="' + result['width'] + '"]').attr('selected', 'selected');
		}
		if (result['height']) {
			form.find('select[name="height"]').find('option').removeAttr('selected');
			form.find('select[name="height"]').find('option[value="' + result['height'] + '"]').attr('selected', 'selected');
		}
		if (result['default_owner']) {
			form.find('select[name="default_owner"]').find('option').removeAttr('selected');
			form.find('select[name="default_owner"]').find('option[value="' + result['default_owner'] + '"]').attr('selected', 'selected');
		}
		if (result['owners_all']) {
			form.find('select[name="owners_all"]').find('option').removeAttr('selected');
			var selectedvalue = result['owners_all'];
			if (typeof (selectedvalue) != 'string') {
				for (var i = 0; i < selectedvalue.length; i++) {
					var encodedSelectedValue = selectedvalue[i].replace(/"/g, '\\"');
					form.find('select[name="owners_all"]').find('option[value="' + encodedSelectedValue + '"]').attr('selected', 'selected');
				}
			} else {
				form.find('select[name="owners_all"]').find('option[value="' + selectedvalue + '"]').attr('selected', 'selected');
			}
		}
	},
	registerEditFieldDetailsClick: function (contents = null) {
		const thisInstance = this;
		if (!contents) {
			contents = jQuery('#layoutDashBoards');
		}
		contents.find('.editFieldDetails').on('click', function (e) {
			const currentTarget = $(e.currentTarget);
			const fieldRow = currentTarget.closest('div.editFieldsWidget');
			fieldRow.removeClass('opacity');
			const basicDropDown = fieldRow.find('.basicFieldOperations');
			const dropDownContainer = currentTarget.closest('.btn-group');
			dropDownContainer.find('.dropdown-menu').remove();
			const dropDown = basicDropDown.clone().removeClass('basicFieldOperations d-none').addClass('dropdown-menu p-0');
			dropDownContainer.append(dropDown);
			const dropDownMenu = dropDownContainer.find('.dropdown-menu');
			dropDownContainer.dropdown('dispose').dropdown('toggle');
			dropDownMenu.find('form').validationEngine($.extend(true, {
				binded: false,
				onValidationComplete: function (form, valid) {
					if (valid) {
						if (form === undefined) {
							return true;
						}
						let paramsForm = form.serializeFormData();
						if (form.find('[name="isdefault"]').prop("checked"))
							paramsForm['isdefault'] = 1;
						if (form.find('[name="cache"]').prop("checked"))
							paramsForm['cache'] = 1;
						let id = form.find('.saveFieldDetails').data('field-id');
						paramsForm['action'] = 'saveDetails';
						paramsForm['id'] = id;
						if (typeof paramsForm['customMultiFilter'] !== "undefined" && !$.isArray(paramsForm['customMultiFilter'])) {
							paramsForm['customMultiFilter'] = [paramsForm['customMultiFilter']];
						}
						if (paramsForm['default_owner'] && typeof paramsForm['owners_all'] === "undefined") {
							let params = {};
							params['type'] = 'error';
							params['text'] = app.vtranslate('JS_FILTERS_AVAILABLE') + ': ' + app.vtranslate('JS_FIELD_EMPTY');
							Settings_Vtiger_Index_Js.showMessage(params);
							e.preventDefault();
							return false;
						}
						thisInstance.save(paramsForm, 'save');
						thisInstance.registerSaveFieldDetailsEvent(form);
					}
					return false;
				}
			}, app.getvalidationEngineOptions(true)));
			let selectElements = basicDropDown.find('select[name="owners_all"]');
			if (selectElements.length > 0) {
				App.Fields.Picklist.showSelect2ElementView(dropDownMenu.find('select[name="owners_all"]'));
			}
			selectElements = basicDropDown.find('select[name="default_date"]');
			if (selectElements.length > 0) {
				App.Fields.Picklist.showSelect2ElementView(dropDownMenu.find('select[name="default_date"]'));
			}
			selectElements = basicDropDown.find('select[name="customMultiFilter"]');
			if (selectElements.length > 0) {
				App.Fields.Picklist.showSelect2ElementView(dropDownMenu.find('select[name="customMultiFilter"]'));
			}
			selectElements = basicDropDown.find('select[name="defaultFilter"]');
			if (selectElements.length > 0) {
				App.Fields.Picklist.showSelect2ElementView(dropDownMenu.find('select[name="defaultFilter"]'));
			}
			thisInstance.avoidDropDownClick(dropDownContainer);
			dropDownMenu.on('change', ':checkbox', function (e) {
				let currentTarget = jQuery(e.currentTarget);
				if (currentTarget.attr('readonly') === 'readonly') {
					let status = jQuery(e.currentTarget).is(':checked');
					if (!status) {
						$(e.currentTarget).attr('checked', 'checked')
					} else {
						$(e.currentTarget).removeAttr('checked');
					}
					e.preventDefault();
				}
			});
			const callbackFunction = function () {
				fieldRow.addClass('opacity');
				dropDown.remove();
			};
			thisInstance.addClickOutSideEvent(dropDown, callbackFunction);
			$('.cancel,.close').on('click', callbackFunction);
		});
	},
	/**
	 * Function to register the click event for save button after edit field details
	 */
	registerSaveFieldDetailsEvent: function (form) {
		let submitButtton = form.find('.saveFieldDetails'),
			fieldRow = submitButtton.closest('.editFieldsWidget'),
			dropDownMenu = form.closest('.dropdown-menu');
		//close the drop down
		submitButtton.closest('.btn-group').removeClass('open');
		//adding class opacity to fieldRow - to give opacity to the actions of the fields
		fieldRow.addClass('opacity');
		form.find('select').each(function () {
			let selectedValue = $(this).val(),
				encodedSelectedValue;
			$(this).find('option').removeAttr('selected');
			if (typeof ($(this).attr('multiple')) === "undefined") {
				encodedSelectedValue = selectedValue.replace(/"/g, '\\"');
				$(this).find('[value="' + encodedSelectedValue + '"]').attr('selected', 'selected');
			} else {
				for (let i = 0; i < selectedValue.length; i++) {
					encodedSelectedValue = selectedValue[i].replace(/"/g, '\\"');
					$(this).find('[value="' + encodedSelectedValue + '"]').attr('selected', 'selected');
				}
			}
		});
		form.closest('.editFieldsWidget').find('.basicFieldOperations').html(form);
		dropDownMenu.remove();
	},
	/**
	 * Function to register click event for drop-downs in fields list
	 */
	avoidDropDownClick: function (dropDownContainer) {
		dropDownContainer.find('.dropdown-menu').on('click', function (e) {
			e.stopPropagation();
		});
	},
	registerSpecialWidget: function () {
		var thisInstance = this;
		var container = jQuery('#layoutDashBoards');
		container.find('.addNotebook').on('click', function (e) {
			thisInstance.addNoteBookWidget(this, jQuery(this).data('url'));
		});
		container.find('.addMiniList').on('click', function (e) {
			thisInstance.addMiniListWidget(this, jQuery(this).data('url'));
		});
		container.find('.addChartFilter').on('click', function (e) {
			thisInstance.addChartFilterWidget(this, jQuery(this).data('url'));
		});
		container.find('.addRss').on('click', function (e) {
			thisInstance.addRssWidget($(e.currentTarget), jQuery(this).data('url'));
		});
	},
	addRssWidget: function (element, url) {
		var thisInstance = this;
		var objectToShowModal = {
			url: 'index.php?module=' + app.getModuleName() + '&parent=' + app.getParentModuleName() + '&view=AddRss',
			cb: function (container) {
				container.find('.removeChannel').on('click', function (e) {
					var currentTarget = $(e.currentTarget);
					var row = currentTarget.closest('.form-group');
					row.remove();
				});
				container.find('.addChannel').on('click', function (e) {
					var newRow = container.find('.newChannel').clone();
					var formContainer = container.find('.formContainer');
					formContainer.append(newRow);
					newRow.removeClass('d-none');
					newRow.removeClass('newChannel');
					newRow.find('input').removeAttr('disabled');
					newRow.find('.removeChannel').on('click', function (e) {
						var currentTarget = $(e.currentTarget);
						var row = currentTarget.closest('.form-group');
						row.remove();
					});
				});
				container.find('[name="blockid"]').val(element.data('blockId'));
				container.find('[name="linkid"]').val(element.data('linkid'));
				var form = container.find('form');
				var submit = form.find('[type="submit"]');
				submit.on('click', function (e) {
					var channels = [];
					if (form.validationEngine('validate')) {
						form.find('.channelRss:not(:disabled)').each(function () {
							channels.push(jQuery(this).val());
						})
						var paramsForm = form.serializeFormData();
						paramsForm.data = JSON.stringify({channels: channels});
						thisInstance.save(paramsForm, 'save').done(
							function (data) {
								paramsForm.label = paramsForm.title;
								thisInstance.saveAfterInfo(data, paramsForm)
							}
						);
					}
					e.preventDefault();
				})
			},
		};
		app.showModalWindow(objectToShowModal);
	},
	saveAfterInfo: function (data, paramsForm) {
		var thisInstance = this;
		var result = data['result'];
		var params = {};
		if (data['success']) {
			app.hideModalWindow();
			paramsForm['id'] = result['id'];
			paramsForm['status'] = result['status'];
			params['text'] = app.vtranslate('JS_WIDGET_ADDED');
			Settings_Vtiger_Index_Js.showMessage(params);
			thisInstance.showCustomField(paramsForm);
		}
	},
	addChartFilterWidget: function (element) {
		var thisInstance = this;
		element = jQuery(element);
		app.showModalWindow(null, "index.php?module=Home&view=ChartFilter&step=step1", function (wizardContainer) {
			var form = jQuery('form', wizardContainer);
			var chartType = jQuery('select[name="chartType"]', wizardContainer);
			var moduleNameSelectDOM = jQuery('select[name="module"]', wizardContainer);
			var moduleNameSelect2 = App.Fields.Picklist.showSelect2ElementView(moduleNameSelectDOM, {
				placeholder: app.vtranslate('JS_SELECT_MODULE')
			});
			var step1 = jQuery('.step1', wizardContainer);
			var step2 = jQuery('.step2', wizardContainer);
			var step3 = jQuery('.step3', wizardContainer);
			var footer = jQuery('.modal-footer', wizardContainer);
			step2.remove();
			step3.remove();
			footer.hide();
			chartType.on('change', function (e) {
				var currentTarget = $(e.currentTarget);
				var value = currentTarget.val();
				if (value == 'Barchat' || value == 'Horizontal') {
					form.find('.isColorContainer').removeClass('d-none');
				} else {
					form.find('.isColorContainer').addClass('d-none');
				}
				if (wizardContainer.find('#widgetStep').val() == 4) {
					wizardContainer.find('.step3 .groupField').trigger('change');
				}
			});
			moduleNameSelect2.on('change', function () {
				if (!moduleNameSelect2.val())
					return;
				footer.hide();
				wizardContainer.find('.step2').remove();
				wizardContainer.find('.step3').remove();
				AppConnector.request({
					module: 'Home',
					view: 'ChartFilter',
					step: 'step2',
					chartType: chartType.val(),
					selectedModule: moduleNameSelect2.val()
				}).done(function (step2Response) {
					step1.after(step2Response);
					wizardContainer.find('#widgetStep').val(2);
					const step2 = wizardContainer.find('.step2');
					footer.hide();
					const filtersIdElement = step2.find('.filtersId');
					const valueTypeElement = step2.find('.valueType');
					App.Fields.Picklist.showSelect2ElementView(filtersIdElement);
					App.Fields.Picklist.showSelect2ElementView(valueTypeElement);
					step2.find('.filtersId, .valueType').on('change', function () {
						wizardContainer.find('.step3').remove();
						wizardContainer.find('.step4').remove();
						AppConnector.request({
							module: 'Home',
							view: 'ChartFilter',
							step: 'step3',
							selectedModule: moduleNameSelect2.val(),
							chartType: chartType.val(),
							filtersId: filtersIdElement.val(),
							valueType: valueTypeElement.val(),
						}).done(function (step3Response) {
							step2.last().after(step3Response);
							wizardContainer.find('#widgetStep').val(3);
							var step3 = wizardContainer.find('.step3');
							App.Fields.Picklist.showSelect2ElementView(step3.find('select'));
							footer.hide();
							step3.find('.groupField').on('change', function () {
								wizardContainer.find('.step4').remove();
								var groupField = $(this);
								if (!groupField.val())
									return;
								footer.show();
								AppConnector.request({
									module: 'Home',
									view: 'ChartFilter',
									step: 'step4',
									selectedModule: moduleNameSelect2.val(),
									filtersId: filtersIdElement.val(),
									groupField: groupField.val(),
									chartType: chartType.val()
								}).done(function (step4Response) {
									step3.last().after(step4Response);
									wizardContainer.find('#widgetStep').val(4);
									var step4 = wizardContainer.find('.step4');
									App.Fields.Picklist.showSelect2ElementView(step4.find('select'));
								});
							});
						});
					});
				});
			});
			form.on('submit', function (e) {
				e.preventDefault();
				const selectedModule = moduleNameSelect2.val();
				const selectedModuleLabel = moduleNameSelect2.find(':selected').text();
				const selectedFilterId = form.find('.filtersId').val();
				const selectedFieldLabel = form.find('.groupField').find(':selected').text();
				const data = {
					module: selectedModule,
					groupField: form.find('.groupField').val(),
					chartType: chartType.val(),
				};
				form.find('.saveParam').each(function (index, element) {
					element = $(element);
					if (!(element.is('input') && element.prop('type') === 'checkbox' && !element.prop('checked'))) {
						data[element.attr('name')] = element.val();
					}
				});
				finializeAddChart(selectedModuleLabel, selectedFilterId, null, selectedFieldLabel, data, form);
			});
		});

		function finializeAddChart(moduleNameLabel, filterid, filterLabel, fieldLabel, data, form) {
			let paramsForm = {};
			paramsForm['data'] = JSON.stringify(data);
			paramsForm['action'] = 'addWidget';
			paramsForm['blockid'] = element.data('block-id');
			paramsForm['linkid'] = element.data('linkid');
			paramsForm['label'] = moduleNameLabel;
			if (typeof filterLabel !== 'undefined' && filterLabel !== null && filterLabel !== '') {
				paramsForm['label'] += ' - ' + filterLabel;
			}
			if (typeof fieldLabel !== 'undefined' && fieldLabel !== null && fieldLabel !== '') {
				paramsForm['label'] += ' - ' + fieldLabel;
			}
			paramsForm['name'] = 'ChartFilter';
			paramsForm['filterid'] = filterid;
			paramsForm['title'] = form.find('[name="widgetTitle"]').val();
			paramsForm['isdefault'] = 0;
			paramsForm['cache'] = 0;
			paramsForm['height'] = 4;
			paramsForm['width'] = 4;
			paramsForm['owners_all'] = ["mine", "all", "users", "groups"];
			paramsForm['default_owner'] = 'mine';

			thisInstance.save(paramsForm, 'save').done(function (data) {
				let result = data['result'],
					params = {};
				if (data['success']) {
					app.hideModalWindow();
					paramsForm['id'] = result['id'];
					paramsForm['status'] = result['status'];
					params['text'] = app.vtranslate('JS_WIDGET_ADDED');
					Settings_Vtiger_Index_Js.showMessage(params);
					thisInstance.showCustomField(paramsForm);
				} else {
					let message = data['error']['message'],
						errorField;
					if (data['error']['code'] != 513) {
						errorField = form.find('[name="fieldName"]');
					} else {
						errorField = form.find('[name="fieldLabel"]');
					}
					errorField.validationEngine('showPrompt', message, 'error', 'topLeft', true);
				}
			});
		}
	},
	addNoteBookWidget: function (element, url) {
		var thisInstance = this;
		element = jQuery(element);
		app.showModalWindow(null, "index.php?module=Home&view=AddNotePad", function (wizardContainer) {
			var form = jQuery('form', wizardContainer);
			form.validationEngine($.extend(true, {
				onValidationComplete: function (form, valid) {
					if (valid) {
						//To prevent multiple click on save
						jQuery("[name='saveButton']", wizardContainer).attr('disabled', 'disabled');
						var notePadName = form.find('[name="notePadName"]').val();
						var notePadContent = form.find('[name="notePadContent"]').val();
						var isDefault = 0;
						var linkId = element.data('linkid');
						var blockId = element.data('block-id');
						var noteBookParams = {
							'module': jQuery('#selectedModuleName').val(),
							'action': 'NoteBook',
							'mode': 'noteBookCreate',
							'notePadName': notePadName,
							'notePadContent': notePadContent,
							'blockid': blockId,
							'linkId': linkId,
							'isdefault': isDefault,
							'width': 4,
							'height': 3
						}
						AppConnector.request(noteBookParams).done(function (data) {
							if (data.result.success) {
								var widgetId = data.result.widgetId;
								app.hideModalWindow();
								noteBookParams['id'] = widgetId;
								noteBookParams['label'] = notePadName;
								Settings_Vtiger_Index_Js.showMessage({text: app.vtranslate('JS_WIDGET_ADDED')});
								thisInstance.showCustomField(noteBookParams);
							}
						})
					}
					return false;
				}
			}, app.validationEngineOptions));
		});
	},
	addMiniListWidget: function (element, url) {
		// 1. Show popup window for selection (module, filter, fields)
		// 2. Compute the dynamic mini-list widget url
		// 3. Add widget with URL to the page.
		var thisInstance = this;
		element = jQuery(element);

		app.showModalWindow(null, "index.php?module=Home&view=MiniListWizard&step=step1", function (wizardContainer) {
			var form = jQuery('form', wizardContainer);

			var moduleNameSelectDOM = jQuery('select[name="module"]', wizardContainer);
			var filteridSelectDOM = jQuery('select[name="filterid"]', wizardContainer);
			var fieldsSelectDOM = jQuery('select[name="fields"]', wizardContainer);
			var filterFieldsSelectDOM = jQuery('select[name="filter_fields"]', wizardContainer);

			var moduleNameSelect2 = App.Fields.Picklist.showSelect2ElementView(moduleNameSelectDOM, {
				placeholder: app.vtranslate('JS_SELECT_MODULE')
			});
			var filteridSelect2 = App.Fields.Picklist.showSelect2ElementView(filteridSelectDOM, {
				placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION')
			});
			var fieldsSelect2 = App.Fields.Picklist.showSelect2ElementView(fieldsSelectDOM, {
				placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION'),
				closeOnSelect: true,
				maximumSelectionLength: 6
			});
			var filterFieldsSelect2 = App.Fields.Picklist.showSelect2ElementView(filterFieldsSelectDOM, {
				placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION')
			});
			var footer = jQuery('.modal-footer', wizardContainer);

			filteridSelectDOM.closest('tr').hide();
			fieldsSelectDOM.closest('tr').hide();
			filterFieldsSelectDOM.closest('tr').hide();
			footer.hide();

			moduleNameSelect2.on('change', function () {
				if (!moduleNameSelect2.val())
					return;

				AppConnector.request({
					module: 'Home',
					view: 'MiniListWizard',
					step: 'step2',
					selectedModule: moduleNameSelect2.val()
				}).done(function (res) {
					filteridSelectDOM.empty().html(res).trigger('change');
					filteridSelect2.closest('tr').show();
					fieldsSelectDOM.closest('tr').hide();
					filterFieldsSelectDOM.closest('tr').hide();
				});
			});
			filteridSelect2.on('change', function () {
				if (!filteridSelect2.val())
					return;
				footer.hide();
				fieldsSelectDOM.closest('tr').hide();
				filterFieldsSelectDOM.closest('tr').hide();
				AppConnector.request({
					module: 'Home',
					view: 'MiniListWizard',
					step: 'step3',
					selectedModule: moduleNameSelect2.val(),
					filterid: filteridSelect2.val()
				}).done(function (res) {
					res = $(res);
					fieldsSelectDOM.empty().html(res.find('select[name="fields"]').html()).trigger('change');
					filterFieldsSelectDOM.empty().html(res.find('select[name="filter_fields"]').html()).trigger('change');
					fieldsSelect2.closest('tr').show();
					filterFieldsSelect2.closest('tr').show();
					fieldsSelect2.data('select2').$selection.find('.select2-search__field').parent().css('width', '100%');
					filterFieldsSelect2.data('select2').$selection.find('.select2-search__field').parent().css('width', '100%');
				});
			});
			fieldsSelect2.on('change', function () {
				if (!fieldsSelect2.val()) {
					footer.hide();
				} else {
					footer.show();
				}
			});
			form.on('submit', function (e) {
				e.preventDefault();
				let selectedFields = [];
				fieldsSelect2.select2('data').map(function (obj) {
					selectedFields.push(obj.id);
				});
				let data = {
					module: moduleNameSelect2.val()
				};
				data['fields'] = selectedFields;
				data['filterFields'] = filterFieldsSelect2.val();
				let paramsForm = {
					data: JSON.stringify(data),
					action: 'addWidget',
					blockid: element.data('block-id'),
					title: form.find('[name="widgetTitle"]').val(),
					linkid: element.data('linkid'),
					label: moduleNameSelect2.find(':selected').text() + ' - ' + filteridSelect2.find(':selected').text(),
					name: 'Mini List',
					filterid: filteridSelect2.val(),
					isdefault: 0,
					cache: 0,
					height: 4,
					width: 4,
					owners_all: ["mine", "all", "users", "groups"],
					default_owner: 'mine'
				};
				thisInstance.save(paramsForm, 'save').done(function (data) {
					let result = data['result'],
						params = {};
					if (data['success']) {
						app.hideModalWindow();
						paramsForm['id'] = result['id'];
						paramsForm['status'] = result['status'];
						params['text'] = app.vtranslate('JS_WIDGET_ADDED');
						Settings_Vtiger_Index_Js.showMessage(params);
						thisInstance.showCustomField(paramsForm);
					} else {
						let message = data['error']['message'],
							errorField;
						if (data['error']['code'] !== 513) {
							errorField = form.find('[name="fieldName"]');
						} else {
							errorField = form.find('[name="fieldLabel"]');
						}
						errorField.validationEngine('showPrompt', message, 'error', 'topLeft', true);
					}
				});
			});
		});
	},
	/**
	 * Function to register the click event for delete custom field
	 */
	registerDeleteCustomFieldEvent: function (contents) {
		var thisInstance = this;
		if (typeof contents === "undefined") {
			contents = jQuery('#layoutDashBoards');
		}
		contents.find('a.deleteCustomField').on('click', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var fieldId = currentTarget.data('field-id');
			var paramsForm = {}
			paramsForm['action'] = 'removeWidget';
			paramsForm['id'] = fieldId;
			var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
			Vtiger_Helper_Js.showConfirmationBox({'message': message}).done(function (e) {
				thisInstance.save(paramsForm, 'delete').done(function (data) {
					var field = currentTarget.closest('div.editFieldsWidget');
					var blockId = field.data('block-id');
					field.parent().fadeOut('slow').remove();
					var block = jQuery('#block_' + blockId);
					thisInstance.reArrangeBlockFields(block);
					var params = {};
					params['text'] = app.vtranslate('JS_CUSTOM_FIELD_DELETED');
					Settings_Vtiger_Index_Js.showMessage(params);
				});
			});
		});
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
		} else if (leftSideContainer.children().length > rightSideContainer.children().length + 1) {	//greater than 1
			var lastElementInLeftContainer = leftSideContainer.children(':last');
			rightSideContainer.append(lastElementInLeftContainer);
		}
	},
	/**
	 * Function to register the click event for delete custom block
	 */
	registerDeleteCustomBlockEvent: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutDashBoards');
		contents.on('click', '.js-delete-custom-block-btn', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var table = currentTarget.closest('div.editFieldsTable');
			var blockId = table.data('block-id');
			var paramsFrom = {};
			paramsFrom['blockid'] = blockId;
			paramsFrom['action'] = 'removeBlock';
			var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
			Vtiger_Helper_Js.showConfirmationBox({'message': message}).done(function (e) {
				thisInstance.save(paramsFrom, 'delete').done(function (data) {
					thisInstance.removeDeletedBlock(blockId, 'delete');
					var params = {};
					params['text'] = app.vtranslate('JS_CUSTOM_BLOCK_DELETED');
					Settings_Vtiger_Index_Js.showMessage(params);
				});
			});
		});
	},
	/**
	 * Function to remove the deleted custom block from the ui
	 */
	removeDeletedBlock: function (blockId) {
		var contents = jQuery('#layoutDashBoards');
		var deletedTable = contents.find('.block_' + blockId);
		deletedTable.fadeOut('slow').remove();
	},
	/**
	 * Function to register the change event for layout editor modules list
	 */
	registerModulesChangeEvent() {
		const thisInstance = this;
		let container = $('#widgetsManagementEditorContainer');
		let contentsDiv = container.closest('.contentsDiv');
		App.Fields.Picklist.changeSelectElementView(container.find('[name="widgetsManagementEditorModules"]'));
		container.on('change', '[name="widgetsManagementEditorModules"]', (e) => {
			thisInstance.getModuleLayoutEditor($(e.currentTarget).val(), thisInstance.getCurrentDashboardId()).done((data) => {
				thisInstance.updateContentsDiv(contentsDiv, data);
			});
		});
	},
	/**
	 * Update contents div and register events.
	 * @param {jQuery} contentsDiv
	 * @param {HTMLElement} data
	 */
	updateContentsDiv(contentsDiv, data) {
		contentsDiv.html(data);
		App.Fields.Picklist.showSelect2ElementView(contentsDiv.find('.select2'));
		this.registerEvents();
	},
	/**
	 * Function to get the respective module layout editor through pjax
	 */
	getModuleLayoutEditor: function (selectedModule, selectedDashboard) {
		var aDeferred = jQuery.Deferred();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});

		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['view'] = 'Configuration';
		params['sourceModule'] = selectedModule;
		params['dashboardId'] = selectedDashboard;
		AppConnector.requestPjax(params).done(function (data) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			aDeferred.resolve(data);
		}).fail(function (error) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			aDeferred.reject();
		});
		return aDeferred.promise();
	},
	/**
	 * register events for layout editor
	 */
	registerEvents: function () {
		var thisInstance = this;

		thisInstance.registerAddBlockDashBoard();
		thisInstance.registerAddCustomFieldEvent();
		thisInstance.registerEditFieldDetailsClick();
		thisInstance.registerSpecialWidget();
		thisInstance.registerDeleteCustomFieldEvent();
		thisInstance.registerDeleteCustomBlockEvent();
		thisInstance.registerModulesChangeEvent();
		thisInstance.setWidgetWithFilterUsers();
		thisInstance.setRestrictFilter();
		thisInstance.setWidgetWithFilterDate();
		thisInstance.registerAddedDashboard();
		thisInstance.registerSelectDashboard();
		thisInstance.registerDashboardAction();
	}

});

jQuery(document).ready(function () {
	var instance = new Settings_WidgetsManagement_Js();
	instance.registerEvents();
})

