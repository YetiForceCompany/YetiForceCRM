/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

jQuery.Class('Settings_WidgetsManagement_Js', {
}, {
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
		var thisInstance = this;
		var authorization = new Array();
		continer = jQuery('#moduleBlocks');
		continer.find('.editFieldsTable').each(function () {
			authorization.push(jQuery(this).data('code'));
		});
		return authorization;
	},
	getAllFieldsInBlock: function (continer) {
		var thisInstance = this;
		var fields = new Array();
		continer.find('.blockFieldsList .editFieldsWidget').each(function () {
			fields.push(jQuery(this).data('linkid').toString());
		});
		return fields;
	},
	getCurrentDashboardId: function () {
		return $('.selectDashboard li.active').data('id');
	},
	registerAddedDashboard: function () {
		var thisInstance = this;
		$('.addDashboard').on('click', function () {
			var data = {
				url: 'index.php?parent=Settings&module=' + app.getModuleName() + '&view=DashboardType',
				sendByAjaxCb: function () {
					var contentsDiv = $('.contentsDiv');
					thisInstance.getModuleLayoutEditor('Home').then(
							function (data) {
								contentsDiv.html(data);
								thisInstance.registerEvents();
							}
					);
				},
			};
			app.showModalWindow(data);
		});
	},
	registerSelectDashboard: function () {
		var thisInstance = this;
		$('.selectDashboard li').on('click', function (e) {
			var currentTarget = $(e.currentTarget);
			var dashboardId = currentTarget.data('id');
			var contentsDiv = $('.contentsDiv');
			thisInstance.getModuleLayoutEditor('Home', dashboardId).then(
					function (data) {
						contentsDiv.html(data);
						thisInstance.registerEvents();
					}
			);
		});
	},
	registerDashboardAction: function () {
		var thisInstance = this;
		$('.editDashboard').on('click', function (e) {
			var currentTarget = $(e.currentTarget);
			e.stopPropagation();
			var data = {
				url: 'index.php?parent=Settings&module=' + app.getModuleName() + '&view=DashboardType&dashboardId=' + currentTarget.closest('li').data('id'),
				sendByAjaxCb: function () {
					var contentsDiv = $('.contentsDiv');
					thisInstance.getModuleLayoutEditor('Home', currentTarget.closest('li').data('id')).then(
							function (data) {
								contentsDiv.html(data);
								thisInstance.registerEvents();
							}
					);
				},
			};
			app.showModalWindow(data);
		});
		$('.deleteDashboard').on('click', function (e) {
			var currentTarget = $(e.currentTarget);
			e.stopPropagation();
			var params = {
				parent: 'Settings',
				module: app.getModuleName(),
				action: 'Dashboard',
				mode: 'delete',
				dashboardId: currentTarget.closest('li').data('id')
			};
			AppConnector.request(params).then(function () {
				var contentsDiv = $('.contentsDiv');
				thisInstance.getModuleLayoutEditor('Home', 1).then(
						function (data) {
							contentsDiv.html(data);
							thisInstance.registerEvents();
						}
				);
			});
		});
	},
	/**
	 * Function to register click event for add custom block button
	 */
	registerAddBlockDashBoard: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutDashBoards');
		contents.find('.addBlockDashBoard').click(function (e) {
			var addBlockContainer = contents.find('.addBlockDashBoardModal').clone(true, true);
			var inUseAuthorization = thisInstance.getAuthorization();
			addBlockContainer.find('select.authorized option').each(function () {
				if (jQuery.inArray(jQuery(this).val(), inUseAuthorization) != -1)
					jQuery(this).remove();
			});

			var callBackFunction = function (data) {
				//register all select2 Elements
				app.changeSelectElementView(data.find('select'));

				var form = data.find('.addBlockDashBoardForm');
				var params = app.validationEngineOptions;
				var block = form.find('[name="authorized"]');
				form.validationEngine(params);
				form.submit(function (e) {
					if (form.validationEngine('validate')) {
						var paramsForm = form.serializeFormData();
						paramsForm['action'] = 'addBlock';
						var paramsBlock = [];
						paramsBlock['authorized'] = block.val();
						paramsBlock['label'] = block.find(':selected').text();
						thisInstance.save(paramsForm, 'save').then(
								function (data) {
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
								}
						);
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

		var thisInstance = this;
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

		AppConnector.request(params).then(
				function (data) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					aDeferred.resolve(data);
				},
				function (error) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					aDeferred.reject(error);
				}
		);
		return aDeferred.promise();
	},
	displayNewCustomBlock: function (result) {
		var thisInstance = this;
		var contents = jQuery('#layoutDashBoards');
		var newBlockCloneCopy = contents.find('.newCustomBlockCopy').clone(true, true);
		newBlockCloneCopy.data('block-id', result['id']).find('.blockLabel span').append(jQuery('<strong>' + result['label'] + '</strong>'));
		newBlockCloneCopy.find('.addCustomField').removeClass('hide').show();
		newBlockCloneCopy.find('.specialWidget').data('block-id', result['id']);
		contents.find('#moduleBlocks').append(newBlockCloneCopy.removeClass('newCustomBlockCopy hide').addClass('editFieldsTable block_' + result['id']).data('code', result['authorized']));
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
		contents.find('.addCustomField').click(function (e) {
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
				addFieldContainer.find('.widgetFilter').removeClass('hide').find('select').removeAttr('disabled').show();
				var restrictFilter = thisInstance.restrictFilter[name];
				if (restrictFilter) {
					for (var i in restrictFilter) {
						addFieldContainer.find('.widgetFilter select option[value="' + restrictFilter[i] + '"]').remove();
					}
				}
			}
			if (jQuery.inArray(name, thisInstance.widgetWithFilterDate) != -1) {
				addFieldContainer.find('.widgetFilterDate').removeClass('hide').find('select').removeAttr('disabled').show();
			}

			var callBackFunction = function (data) {
				//register all select2 Elements
				app.showSelect2ElementView(data.find('select'));
				data.find('select.widgets').on('change', function () {
					data.find('.widgetFilter').remove();
					data.find('.widgetFilterDate').remove();
					var elementsToFilter = contents.find('.createFieldModal .widgetFilter').clone(true, true);
					var elementsToFilterDate = contents.find('.createFieldModal .widgetFilterDate').clone(true, true);

					data.find('.modal-body').append(elementsToFilter);
					data.find('.modal-body').append(elementsToFilterDate);
					var name = jQuery(this).find(':selected').data('name');
					if (jQuery.inArray(name, thisInstance.widgetWithFilterUsers) != -1) {
						elementsToFilter.removeClass('hide').find('select').prop('disabled', false);
						var restrictFilter = thisInstance.restrictFilter[name];
						if (restrictFilter) {
							for (var i in restrictFilter) {
								addFieldContainer.find('.widgetFilter select option[value="' + restrictFilter[i] + '"]').remove();
							}
						}
						app.showSelect2ElementView(elementsToFilter.find('select'));
					} else {
						elementsToFilter.addClass('hide').find('select').prop('disabled', true);
					}
					if (jQuery.inArray(name, thisInstance.widgetWithFilterDate) != -1) {
						elementsToFilterDate.removeClass('hide').find('select').prop('disabled', false);
						app.showSelect2ElementView(elementsToFilterDate.find('select'));
					} else {
						elementsToFilterDate.addClass('hide').find('select').prop('disabled', true);
					}
				});

				var form = data.find('.createCustomFieldForm');
				form.attr('id', 'createFieldForm');
				var widgets = form.find('[name="widgets"]');
				var params = app.validationEngineOptions;
				params.onValidationComplete = function (form, valid) {
					if (valid) {
						if (widgets.val()) {
							var saveButton = form.find(':submit');
							saveButton.attr('disabled', 'disabled');
							var field = form.find('[name="widgets"]');

							paramsForm = form.serializeFormData();
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
							if (paramsForm['default_owner'] && typeof paramsForm['owners_all'] == 'undefined') {
								var result = app.vtranslate('JS_FIELD_EMPTY');
								form.find('select[name="owners_all"]').prev('div').validationEngine('showPrompt', result, 'error', 'bottomLeft', true);
								saveButton.removeAttr('disabled');
								e.preventDefault();
								return false;
							}
							thisInstance.save(paramsForm, 'save').then(
									function (data) {
										var result = data['result'];
										var params = {};
										if (data['success']) {
											app.hideModalWindow();
											paramsForm['id'] = result['id']
											paramsForm['status'] = result['status']
											params['text'] = app.vtranslate('JS_WIDGET_ADDED');
											Settings_Vtiger_Index_Js.showMessage(params);
											thisInstance.showCustomField(paramsForm);
										} else {
											var message = data['error']['message'];
											if (data['error']['code'] != 513) {
												var errorField = form.find('[name="fieldName"]');
											} else {
												var errorField = form.find('[name="fieldLabel"]');
											}
											errorField.validationEngine('showPrompt', message, 'error', 'topLeft', true);
											saveButton.removeAttr('disabled');
										}
									}
							);
						} else {
							var result = app.vtranslate('JS_FIELD_EMPTY');
							widgets.prev('div').validationEngine('showPrompt', result, 'error', 'topLeft', true);
							e.preventDefault();
							return;
						}
					}
					//To prevent form submit
					return false;
				}
				form.validationEngine(params);
			}
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
		var fieldContainer = fieldCopy.find('div.marginLeftZero.border1px');
		fieldContainer.addClass('opacity editFieldsWidget').attr('data-field-id', result['id']).attr('data-block-id', result['blockid']).attr('data-linkid', result['linkid']);
		fieldContainer.find('.deleteCustomField, .saveFieldDetails').attr('data-field-id', result['id']);
		if (result['title']) {
			fieldContainer.find('.fieldLabel').html(result['title']);
		} else {
			fieldContainer.find('.fieldLabel').html(result['label']);
		}
		if (!result['status'])
			fieldContainer.find('input[name="limit"]').closest('div.limit').remove();
		if (typeof result['default_owner'] != 'undefined')
			fieldContainer.find('.widgetFilterAll').removeClass('hide').show();

		var block = relatedBlock.find('.blockFieldsList');
		var sortable1 = block.find('ul[name=sortable1]');
		var length1 = sortable1.children().length;
		var sortable2 = block.find('ul[name=sortable2]');
		var length2 = sortable2.children().length;

		// Deciding where to add the new field
		if (length1 > length2) {
			sortable2.append(fieldCopy.removeClass('hide newCustomFieldCopy'));
		} else {
			sortable1.append(fieldCopy.removeClass('hide newCustomFieldCopy'));
		}
		var form = fieldCopy.find('form.fieldDetailsForm');
		thisInstance.setFieldDetails(result, form);
	},
	/**
	 * Function to set the field info for edit field actions
	 */
	setFieldDetails: function (result, form) {
		var thisInstance = this;
		//add field label to the field details
		form.find('.modal-header').html(jQuery('<strong>' + result['label'] + '</strong><div class="pull-right"><a href="javascript:void(0)" class="cancel">X</a></div>'));

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
	registerEditFieldDetailsClick: function () {
		var thisInstance = this;
		contents = jQuery('#layoutDashBoards');
		contents.find('.editFieldDetails').click(function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var fieldRow = currentTarget.closest('div.editFieldsWidget');
			fieldRow.removeClass('opacity');
			var basicDropDown = fieldRow.find('.basicFieldOperations');
			var dropDownContainer = currentTarget.closest('.btn-group');
			dropDownContainer.find('.dropdown-menu').remove();
			var dropDown = basicDropDown.clone().removeClass('basicFieldOperations hide').addClass('dropdown-menu');
			dropDownContainer.append(dropDown);
			var dropDownMenu = dropDownContainer.find('.dropdown-menu');
			var params = app.getvalidationEngineOptions(true);
			params.binded = false;
			params.onValidationComplete = function (form, valid) {
				if (valid) {
					if (form == undefined) {
						return true;
					}
					var paramsForm = form.serializeFormData();
					if (form.find('[name="isdefault"]').prop("checked"))
						paramsForm['isdefault'] = 1;
					if (form.find('[name="cache"]').prop("checked"))
						paramsForm['cache'] = 1;
					var id = form.find('.saveFieldDetails').data('field-id');
					paramsForm['action'] = 'saveDetails';
					paramsForm['id'] = id;
					if (paramsForm['default_owner'] && typeof paramsForm['owners_all'] == 'undefined') {
						var params = {};
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
			};
			dropDownMenu.find('form').validationEngine(params);
			//handled registration of selectize for select element
			var selectElements = basicDropDown.find('select[name="owners_all"]');
			if (selectElements.length > 0) {
				var users = dropDownMenu.find('select[name="owners_all"]');
				app.showSelectizeElementView(users);
			}
			selectElements = basicDropDown.find('select[name="default_date"]');
			if (selectElements.length > 0) {
				var users = dropDownMenu.find('select[name="default_date"]');
				app.showSelect2ElementView(users);
			}

			thisInstance.avoidDropDownClick(dropDownContainer);

			dropDownMenu.on('change', ':checkbox', function (e) {
				var currentTarget = jQuery(e.currentTarget);
				if (currentTarget.attr('readonly') == 'readonly') {
					var status = jQuery(e.currentTarget).is(':checked');
					if (!status) {
						jQuery(e.currentTarget).attr('checked', 'checked')
					} else {
						jQuery(e.currentTarget).removeAttr('checked');
					}
					e.preventDefault();
				}
			});

			//added for drop down position change
			var offset = currentTarget.offset(),
					height = currentTarget.outerHeight(),
					dropHeight = dropDown.outerHeight(),
					viewportBottom = $(window).scrollTop() + document.documentElement.clientHeight,
					dropTop = offset.top + height,
					enoughRoomBelow = dropTop + dropHeight <= viewportBottom;
			if (!enoughRoomBelow) {
				dropDown.addClass('bottom-up');
			} else {
				dropDown.removeClass('bottom-up');
			}

			var callbackFunction = function () {
				fieldRow.addClass('opacity');
				dropDown.remove();
			}
			thisInstance.addClickOutSideEvent(dropDown, callbackFunction);

			jQuery('.cancel').click(function () {
				callbackFunction();
			});
		});
	},
	/**
	 * Function to register the click event for save button after edit field details
	 */
	registerSaveFieldDetailsEvent: function (form) {
		var thisInstance = this;
		var submitButtton = form.find('.saveFieldDetails');
		var fieldId = submitButtton.data('field-id');
		var block = submitButtton.closest('.editFieldsTable');
		var blockId = block.data('block-id');
		//close the drop down
		submitButtton.closest('.btn-group').removeClass('open');
		//adding class opacity to fieldRow - to give opacity to the actions of the fields
		var fieldRow = submitButtton.closest('.editFieldsWidget');
		fieldRow.addClass('opacity');
		var dropDownMenu = form.closest('.dropdown-menu');
		app.destroySelectizeElement(form)
		form.find('select').each(function () {
			var selectedvalue = jQuery(this).val();
			jQuery(this).find('option').removeAttr('selected');
			if (typeof (jQuery(this).attr('multiple')) == 'undefined') {
				var encodedSelectedValue = selectedvalue.replace(/"/g, '\\"');
				jQuery(this).find('[value="' + encodedSelectedValue + '"]').attr('selected', 'selected');
			} else {
				for (var i = 0; i < selectedvalue.length; i++) {
					var encodedSelectedValue = selectedvalue[i].replace(/"/g, '\\"');
					jQuery(this).find('[value="' + encodedSelectedValue + '"]').attr('selected', 'selected');
				}
			}
		});
		var basicContents = form.closest('.editFieldsWidget').find('.basicFieldOperations');
		basicContents.html(form);
		dropDownMenu.remove();
	},
	/**
	 * Function to register click event for drop-downs in fields list 
	 */
	avoidDropDownClick: function (dropDownContainer) {
		dropDownContainer.find('.dropdown-menu').click(function (e) {
			e.stopPropagation();
		});
	},
	registerSpecialWidget: function () {
		var thisInstance = this;
		var container = jQuery('#layoutDashBoards');
		container.find('.addNotebook').click(function (e) {
			thisInstance.addNoteBookWidget(this, jQuery(this).data('url'));
		});
		container.find('.addCharts').click(function (e) {
			thisInstance.addChartWidget($(e.currentTarget));
		});
		container.find('.addMiniList').click(function (e) {
			thisInstance.addMiniListWidget(this, jQuery(this).data('url'));
		});
		container.find('.addChartFilter').click(function (e) {
			thisInstance.addChartFilterWidget(this, jQuery(this).data('url'));
		});
		container.find('.addRss').click(function (e) {
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
					newRow.removeClass('hide');
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
						thisInstance.save(paramsForm, 'save').then(
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
		var fieldTypeToGroup = ['currency', 'double', 'percentage', 'integer'];
		app.showModalWindow(null, "index.php?module=Home&view=ChartFilter&step=step1", function (wizardContainer) {
			var form = jQuery('form', wizardContainer);
			var chartType = jQuery('select[name="chartType"]', wizardContainer);
			var moduleNameSelectDOM = jQuery('select[name="module"]', wizardContainer);
			var filteridSelectDOM = jQuery('select[name="filterid"]', wizardContainer);
			var fieldsSelectDOM = jQuery('select[name="groupField"]', wizardContainer);
			var sectorContainer = form.find('.sectorContainer');
			app.showSelect2ElementView(sectorContainer.find('[name="sectorField"]'), {
				tags: true,
				tokenSeparators: [',', ' ']
			});
			var moduleNameSelect2 = app.showSelect2ElementView(moduleNameSelectDOM, {
				placeholder: app.vtranslate('JS_SELECT_MODULE')
			});
			var filteridSelect2 = app.showSelect2ElementView(filteridSelectDOM, {
				placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION')
			});
			var fieldsSelect2 = app.showSelect2ElementView(fieldsSelectDOM, {
				placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION'),
				closeOnSelect: true,
				maximumSelectionLength: 6
			});
			var footer = jQuery('.modal-footer', wizardContainer);

			filteridSelectDOM.closest('tr').hide();
			fieldsSelectDOM.closest('tr').hide();
			footer.hide();
			chartType.on('change', function (e) {
				var currentTarget = $(e.currentTarget);
				var value = currentTarget.val();
				if (value == 'Barchat' || value == 'Horizontal') {
					form.find('.isColorContainer').removeClass('hide');
				} else {
					form.find('.isColorContainer').addClass('hide');
				}
			});
			moduleNameSelect2.change(function () {
				if (!moduleNameSelect2.val())
					return;
				footer.hide();
				fieldsSelectDOM.closest('tr').hide();
				AppConnector.request({
					module: 'Home',
					view: 'ChartFilter',
					step: 'step2',
					selectedModule: moduleNameSelect2.val()
				}).then(function (res) {
					filteridSelectDOM.empty().html(res).trigger('change');
					filteridSelect2.closest('tr').show();
				})
			});
			filteridSelect2.change(function () {
				if (!filteridSelect2.val())
					return;

				AppConnector.request({
					module: 'Home',
					view: 'ChartFilter',
					step: 'step3',
					selectedModule: moduleNameSelect2.val(),
					filterid: filteridSelect2.val()
				}).then(function (res) {
					fieldsSelectDOM.empty().html(res).trigger('change');
					fieldsSelect2.closest('tr').show();
					fieldsSelect2.data('select2').$selection.find('.select2-search__field').parent().css('width', '100%');
				});
			});
			fieldsSelect2.change(function () {
				if (!fieldsSelect2.val()) {
					footer.hide();
				} else {
					var fieldType = fieldsSelect2.find(':selected').data('fieldType');
					if (chartType.val() == 'Funnel' && fieldTypeToGroup.indexOf(fieldType) != -1) {
						sectorContainer.removeClass('hide');
					} else {
						sectorContainer.addClass('hide');
					}
					footer.show();
				}
			});

			form.submit(function (e) {
				e.preventDefault();
				var selectedModule = moduleNameSelect2.val();
				var selectedModuleLabel = moduleNameSelect2.find(':selected').text();
				var selectedFilterId = filteridSelect2.val();
				var selectedFilterLabel = filteridSelect2.find(':selected').text();
				var fieldLabel = fieldsSelect2.find(':selected').text();
				var isColorValue = 0;
				var isColor = form.find('.isColor');
				if (!isColor.hasClass('hide') && isColor.is(':checked')) {
					isColorValue = 1;
				}
				var data = {
					module: selectedModule,
					groupField: fieldsSelect2.val(),
					chartType: chartType.val(),
					color: isColorValue,
					sector: sectorContainer.find('[name="sectorField"]').val()
				};
				finializeAddChart(selectedModuleLabel, selectedFilterId, selectedFilterLabel, fieldLabel, data, form);
			});
		});

		function finializeAddChart(moduleNameLabel, filterid, filterLabel, fieldLabel, data, form) {

			var paramsForm = {};
			paramsForm['data'] = JSON.stringify(data);
			paramsForm['action'] = 'addWidget';
			paramsForm['blockid'] = element.data('block-id');
			paramsForm['linkid'] = element.data('linkid');
			paramsForm['label'] = moduleNameLabel + ' - ' + filterLabel + ' - ' + fieldLabel;
			paramsForm['name'] = 'ChartFilter';
			paramsForm['filterid'] = filterid;
			paramsForm['title'] = form.find('[name="widgetTitle"]').val();
			paramsForm['isdefault'] = 0;
			paramsForm['cache'] = 0;
			paramsForm['height'] = 4;
			paramsForm['width'] = 4;
			paramsForm['owners_all'] = ["mine", "all", "users", "groups"];
			paramsForm['default_owner'] = 'mine';

			thisInstance.save(paramsForm, 'save').then(
					function (data) {
						var result = data['result'];
						var params = {};
						if (data['success']) {
							app.hideModalWindow();
							paramsForm['id'] = result['id'];
							paramsForm['status'] = result['status'];
							params['text'] = app.vtranslate('JS_WIDGET_ADDED');
							Settings_Vtiger_Index_Js.showMessage(params);
							thisInstance.showCustomField(paramsForm);
						} else {
							var message = data['error']['message'];
							if (data['error']['code'] != 513) {
								var errorField = form.find('[name="fieldName"]');
							} else {
								var errorField = form.find('[name="fieldLabel"]');
							}
							errorField.validationEngine('showPrompt', message, 'error', 'topLeft', true);
						}
					}
			);
		}
	},
	addChartWidget: function (element) {
		app.showModalWindow(null, "index.php?parent=Settings&module=WidgetsManagement&view=AddChart", function (wizardContainer) {
			wizardContainer.find('[name="blockid"]').val(element.data('block-id'));
			wizardContainer.find('[name="linkId"]').val(element.data('linkid'));
		});
	},
	addNoteBookWidget: function (element, url) {
		var thisInstance = this;
		element = jQuery(element);

		app.showModalWindow(null, "index.php?module=Home&view=AddNotePad", function (wizardContainer) {
			var form = jQuery('form', wizardContainer);
			var params = app.validationEngineOptions;
			params.onValidationComplete = function (form, valid) {
				if (valid) {
					//To prevent multiple click on save
					jQuery("[name='saveButton']", wizardContainer).attr('disabled', 'disabled');
					var notePadName = form.find('[name="notePadName"]').val();
					var notePadContent = form.find('[name="notePadContent"]').val();
					var isDefault = 0;
					var linkId = element.data('linkid');
					var blockId = element.data('block-id');
					var noteBookParams = {
						'module': 'Vtiger',
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
					AppConnector.request(noteBookParams).then(
							function (data) {
								if (data.result.success) {
									var widgetId = data.result.widgetId;
									app.hideModalWindow();
									noteBookParams['id'] = widgetId;
									noteBookParams['label'] = notePadName;
									params['text'] = app.vtranslate('JS_WIDGET_ADDED');
									Settings_Vtiger_Index_Js.showMessage(params);
									thisInstance.showCustomField(noteBookParams);
								}
							})
				}
				return false;
			}
			form.validationEngine(params);
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

			var moduleNameSelect2 = app.showSelect2ElementView(moduleNameSelectDOM, {
				placeholder: app.vtranslate('JS_SELECT_MODULE')
			});
			var filteridSelect2 = app.showSelect2ElementView(filteridSelectDOM, {
				placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION')
			});
			var fieldsSelect2 = app.showSelect2ElementView(fieldsSelectDOM, {
				placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION'),
				closeOnSelect: true,
				maximumSelectionLength: 6
			});
			var filterFieldsSelect2 = app.showSelect2ElementView(filterFieldsSelectDOM, {
				placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION')
			});
			var footer = jQuery('.modal-footer', wizardContainer);

			filteridSelectDOM.closest('tr').hide();
			fieldsSelectDOM.closest('tr').hide();
			filterFieldsSelectDOM.closest('tr').hide();
			footer.hide();

			moduleNameSelect2.change(function () {
				if (!moduleNameSelect2.val())
					return;

				AppConnector.request({
					module: 'Home',
					view: 'MiniListWizard',
					step: 'step2',
					selectedModule: moduleNameSelect2.val()
				}).then(function (res) {
					filteridSelectDOM.empty().html(res).trigger('change');
					filteridSelect2.closest('tr').show();
					fieldsSelectDOM.closest('tr').hide();
					filterFieldsSelectDOM.closest('tr').hide();
				})
			});
			filteridSelect2.change(function () {
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
				}).then(function (res) {
					var res = jQuery(res);
					fieldsSelectDOM.empty().html(res.find('select[name="fields"]').html()).trigger('change');
					filterFieldsSelectDOM.empty().html(res.find('select[name="filter_fields"]').html()).trigger('change');
					fieldsSelect2.closest('tr').show();
					filterFieldsSelect2.closest('tr').show();
					fieldsSelect2.data('select2').$selection.find('.select2-search__field').parent().css('width', '100%');
					filterFieldsSelect2.data('select2').$selection.find('.select2-search__field').parent().css('width', '100%');
				});
			});
			fieldsSelect2.change(function () {
				if (!fieldsSelect2.val()) {
					footer.hide();
				} else {
					footer.show();
				}
			});
			form.submit(function (e) {
				e.preventDefault();
				var selectedModule = moduleNameSelect2.val();
				var selectedModuleLabel = moduleNameSelect2.find(':selected').text();
				var selectedFilterId = filteridSelect2.val();
				var selectedFilterLabel = filteridSelect2.find(':selected').text();
				var selectedFields = [];
				fieldsSelect2.select2('data').map(function (obj) {
					selectedFields.push(obj.id);
				});
				var data = {
					module: selectedModule
				}
				if (typeof selectedFields != 'object') {
					selectedFields = [selectedFields];
				}
				data['fields'] = selectedFields;
				data['filterFields'] = filterFieldsSelect2.val();
				var paramsForm = {
					data: JSON.stringify(data),
					action: 'addWidget',
					blockid: element.data('block-id'),
					title: form.find('[name="widgetTitle"]').val(),
					linkid: element.data('linkid'),
					label: selectedModuleLabel + ' - ' + selectedFilterLabel,
					name: 'Mini List',
					filterid: selectedFilterId,
					isdefault: 0,
					cache: 0,
					height: 4,
					width: 4,
					owners_all: ["mine", "all", "users", "groups"],
					default_owner: 'mine'
				};
				thisInstance.save(paramsForm, 'save').then(function (data) {
					var result = data['result'];
					var params = {};
					if (data['success']) {
						app.hideModalWindow();
						paramsForm['id'] = result['id'];
						paramsForm['status'] = result['status'];
						params['text'] = app.vtranslate('JS_WIDGET_ADDED');
						Settings_Vtiger_Index_Js.showMessage(params);
						thisInstance.showCustomField(paramsForm);
					} else {
						var message = data['error']['message'];
						if (data['error']['code'] != 513) {
							var errorField = form.find('[name="fieldName"]');
						} else {
							var errorField = form.find('[name="fieldLabel"]');
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
		if (typeof contents == 'undefined') {
			contents = jQuery('#layoutDashBoards');
		}
		contents.find('a.deleteCustomField').click(function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var fieldId = currentTarget.data('field-id');
			var paramsForm = {}
			paramsForm['action'] = 'removeWidget';
			paramsForm['id'] = fieldId;
			var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
			Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
					function (e) {
						thisInstance.save(paramsForm, 'delete').then(
								function (data) {
									var field = currentTarget.closest('div.editFieldsWidget');
									var blockId = field.data('block-id');
									field.parent().fadeOut('slow').remove();
									var block = jQuery('#block_' + blockId);
									thisInstance.reArrangeBlockFields(block);
									var params = {};
									params['text'] = app.vtranslate('JS_CUSTOM_FIELD_DELETED');
									Settings_Vtiger_Index_Js.showMessage(params);
								}, function (error, err) {

						}
						);
					},
					function (error, err) {

					}
			);
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
		var table = contents.find('.editFieldsTable');
		contents.on('click', '.deleteCustomBlock', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var table = currentTarget.closest('div.editFieldsTable');
			var blockId = table.data('block-id');
			var paramsFrom = {};
			paramsFrom['blockid'] = blockId;
			paramsFrom['action'] = 'removeBlock';
			var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
			Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
					function (e) {
						thisInstance.save(paramsFrom, 'delete').then(
								function (data) {
									thisInstance.removeDeletedBlock(blockId, 'delete');
									var params = {};
									params['text'] = app.vtranslate('JS_CUSTOM_BLOCK_DELETED');
									Settings_Vtiger_Index_Js.showMessage(params);
								}, function (error, err) {

						}
						);
					},
					function (error, err) {

					}
			);
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
	registerModulesChangeEvent: function () {
		var thisInstance = this;
		var container = jQuery('#widgetsManagementEditorContainer');
		var contentsDiv = container.closest('.contentsDiv');

		app.changeSelectElementView(container.find('[name="widgetsManagementEditorModules"]'));

		container.on('change', '[name="widgetsManagementEditorModules"]', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var selectedModule = currentTarget.val();
			thisInstance.getModuleLayoutEditor(selectedModule, thisInstance.getCurrentDashboardId()).then(
					function (data) {
						contentsDiv.html(data);
						thisInstance.registerEvents();
					}
			);
		});

	},
	/**
	 * Function to get the respective module layout editor through pjax
	 */
	getModuleLayoutEditor: function (selectedModule, selectedDashboard) {
		var thisInstance = this;
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
		AppConnector.requestPjax(params).then(
				function (data) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					aDeferred.resolve(data);
				},
				function (error) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					aDeferred.reject();
				}
		);
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
