/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery.Class('Settings_Widgets_Index_Js', {
}, {
	getTabId: function () {
		return $(".WidgetsManage [name='tabid']").val();
	},
	getType: function () {
		return $(".form-modalAddWidget [name='type']").val();
	},
	createStep2: function (type) {
		var thisInstance = this;
		var tabId = thisInstance.getTabId();
		var progressIndicatorElement = jQuery.progressIndicator({'position': 'html'});
		app.showModalWindow(null, "index.php?parent=Settings&module=Widgets&view=Widget&mode=createStep2&type=" + type + "&tabId=" + tabId, function (wizardContainer) {
			app.showPopoverElementView(wizardContainer.find('.HelpInfoPopover'));
			app.showBtnSwitch(wizardContainer.find('.switchBtn'));
			if (type == 'RelatedModule') {
				thisInstance.loadFilters(wizardContainer);
				wizardContainer.find("select[name='relatedmodule']").change(function () {
					thisInstance.changeRelatedModule();
					;
				});
			}
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			var form = jQuery('form', wizardContainer);
			form.submit(function (e) {
				e.preventDefault();
				var save = true;
				if (form && form.hasClass('validateForm') && form.data('jqv').InvalidFields.length > 0) {
					app.formAlignmentAfterValidation(form);
					save = false;
				}
				if (save) {
					var formData = form.serializeFormData();
					thisInstance.registerSaveEvent('saveWidget', {
						'data': formData,
						'tabid': tabId,
					});
					thisInstance.reloadWidgets();
					app.hideModalWindow();
				}
			});

		});

	},
	loadWidgets: function () {
		var thisInstance = this;
		var blocks = jQuery('.blocksSortable');
		blocks.sortable({
			'revert': true,
			'connectWith': ".blocksSortable",
			'tolerance': 'pointer',
			'cursor': 'move',
			'placeholder': "state-highlight",
			'stop': function (event, ui) {
				thisInstance.updateSequence();
			}
		});

	},
	updateSequence: function () {
		var thisInstance = this;
		var params = {};
		$(".blockSortable").each(function (index) {
			params[$(this).data('id')] = {'index': index, 'column': $(this).closest('.blocksSortable').data('column')};
		});
		var progress = $.progressIndicator({
			'message': app.vtranslate('Saving changes'),
			'blockInfo': {
				'enabled': true
			}
		});
		thisInstance.registerSaveEvent('updateSequence', {
			'data': params,
			'tabid': $("input[name='tabid']").val(),
		});
		progress.progressIndicator({'mode': 'hide'});
	},
	reloadWidgets: function () {
		var thisInstance = this;
		var Indicator = jQuery.progressIndicator({
			'message': app.vtranslate('Loading data'),
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		var params = {};
		params['module'] = 'Widgets';
		params['view'] = 'Index';
		params['parent'] = 'Settings';
		params['source'] = $("input[name='tabid']").val();
		AppConnector.request(params).then(
				function (data) {
					var container = jQuery('div.contentsDiv').html(data);
					thisInstance.registerEvents(container);
					Indicator.progressIndicator({'mode': 'hide'});
				}
		);
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
		if (mode == 'saveWidget') {
			params.async = false;
		} else {
			params.async = true;
		}
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
	loadFilters: function (contener) {
		var types = ['filter', 'checkbox', 'switchHeader'];
		var relatedmodule = contener.find("select[name='relatedmodule'] option:selected").val();
		for (var i in types) {
			var filters = app.getMainParams(types[i] + 'All', true);
			var filterField = contener.find("select[name='" + types[i] + "']");
			var filterSelected = contener.find('input#' + types[i] + '_selected').val();
			filterField.empty();
			filterField.append($('<option/>', {value: '-', text: app.vtranslate('None')}));
			if (filters[relatedmodule] !== undefined) {
				filterField.closest('.form-group').removeClass('hide');
				$.each(filters[relatedmodule], function (index, value) {
					if (typeof value === 'object') {
						value = value.label;
					}
					var option = {value: index, text: value}
					if (filterSelected == index) {
						option.selected = 'selected';
					}
					filterField.append($('<option/>', option));
				});
				app.showSelect2ElementView(filterField);
			} else {
				filterField.closest('.form-group').addClass('hide');
			}
		}
	},
	changeRelatedModule: function (e) {
		var thisInstance = this;
		var form = jQuery('.form-modalAddWidget');
		thisInstance.loadFilters(form);
	},
	registerEvents: function (container) {
		var thisInstance = this;
		this.loadWidgets();
		if (typeof container == 'undefined') {
			container = jQuery('.WidgetsManage');
		}
		app.showSelect2ElementView(container.find('.select2'));
		$(".WidgetsManage select[name='ModulesList']").change(function (e) {
			var target = $(e.currentTarget);
			$("input[name='tabid']").val(target.val());
			thisInstance.reloadWidgets();
		});
		$('.WidgetsManage .addWidget').click(function (e) {
			var progressIndicatorElement = jQuery.progressIndicator({'position': 'html'});
			var module = $(".WidgetsManage select[name='ModulesList']").val();
			app.showModalWindow(null, "index.php?parent=Settings&module=Widgets&view=Widget&mod=" + module, function (wizardContainer) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				var form = jQuery('form', wizardContainer);
				form.submit(function (e) {
					e.preventDefault();
					var type = form.find('[name="type"]').val();
					thisInstance.createStep2(type);
				});

			});
		});
		$('.WidgetsManage .editWidget').click(function (e) {
			var target = $(e.currentTarget);
			var blockSortable = target.closest('.blockSortable');
			app.showModalWindow(null, "index.php?parent=Settings&module=Widgets&view=Widget&mode=edit&id=" + blockSortable.data('id'), function (wizardContainer) {
				jQuery('#massEditHeader.modal-title').text(app.vtranslate('JS_EDIT_WIDGET'));
				app.showBtnSwitch(wizardContainer.find('.switchBtn'));
				app.showPopoverElementView(wizardContainer.find('.HelpInfoPopover'));
				if (thisInstance.getType() == 'RelatedModule') {
					thisInstance.loadFilters(wizardContainer);
					wizardContainer.find("select[name='relatedmodule']").change(function () {
						thisInstance.changeRelatedModule();
					});
				}
				var form = jQuery('form', wizardContainer);
				form.submit(function (e) {
					e.preventDefault();
					var progress = $.progressIndicator({
						'message': app.vtranslate('Loading data'),
						'blockInfo': {
							'enabled': true
						}
					});
					var FormData = form.serializeFormData();
					thisInstance.registerSaveEvent('saveWidget', {
						'data': FormData,
						'tabid': $("input[name='tabid']").val(),
					});
					thisInstance.reloadWidgets();
					app.hideModalWindow();
					progress.progressIndicator({'mode': 'hide'});
				});
			});
		});
		$('.WidgetsManage .removeWidget').click(function (e) {
			var target = $(e.currentTarget);
			var blockSortable = target.closest('.blockSortable');
			thisInstance.registerSaveEvent('removeWidget', {
				'wid': blockSortable.data('id'),
			});
			thisInstance.reloadWidgets();
		});
	}
});
