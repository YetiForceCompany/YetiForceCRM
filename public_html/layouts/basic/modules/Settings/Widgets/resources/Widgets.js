/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class('Settings_Widgets_Index_Js', {}, {
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
			app.showPopoverElementView(wizardContainer.find('.js-help-info'));
			if (type === 'RelatedModule') {
				thisInstance.loadFilters(wizardContainer);
				thisInstance.relatedModuleFields(wizardContainer);
				wizardContainer.find("select[name='relatedmodule']").on('change', function () {
					thisInstance.changeRelatedModule();
					thisInstance.relatedModuleFields(wizardContainer);
				});
			}
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			var form = jQuery('form', wizardContainer);
			form.validationEngine(app.validationEngineOptions);
			form.on('submit', function (e) {
				e.preventDefault();
				if (form.validationEngine('validate')) {
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
		AppConnector.request(params).done(function (data) {
			var container = jQuery('div.contentsDiv').html(data);
			thisInstance.registerEvents(container);
			Indicator.progressIndicator({'mode': 'hide'});
		});
	},
	registerSaveEvent(mode, data) {
		let aDeferred = $.Deferred();
		AppConnector.request({
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			action: 'SaveAjax',
			mode: mode,
			params: data,
			async: mode !== 'saveWidget',
			dataType: 'json'
		}).done((data) => {
			aDeferred.resolve(data);
			Vtiger_Helper_Js.showPnotify({
				text: data['result']['message'],
				type: 'success'
			});
		}).fail((textStatus, errorThrown) => {
			aDeferred.reject(textStatus, errorThrown);
		});
		return aDeferred.promise();
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
				filterField.closest('.form-group').removeClass('d-none');
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
				App.Fields.Picklist.showSelect2ElementView(filterField);
			} else {
				filterField.closest('.form-group').addClass('d-none');
			}
		}
	},
	relatedModuleFields: function (container) {
		const relatedModule = parseInt(container.find("select[name='relatedmodule']").val());
		const relatedfields = container.find("select[name='relatedfields']");
		relatedfields.find('optgroup').each(function (index, optgroup) {
			optgroup = $(optgroup);
			if (relatedModule !== optgroup.data('module')) {
				optgroup.addClass("d-none");
				optgroup.prop("disabled", "disabled");
			} else {
				optgroup.removeClass('d-none');
				optgroup.prop("disabled", false);
			}
			optgroup.find('option').each(function (index, option) {
				option = $(option);
				if (relatedModule !== option.data('module')) {
					option.addClass("d-none");
					option.prop("disabled", "disabled");
				} else {
					option.removeClass('d-none');
					option.prop('disabled', false);
				}
			});
		});
		relatedfields.trigger('change:select2');
	},

	changeRelatedModule: function (e) {
		var thisInstance = this;
		var form = jQuery('.form-modalAddWidget');
		thisInstance.loadFilters(form);
	},

	modalFormEdit(wizardContainer) {
		const thisInstance = this;
		$('#massEditHeader.modal-title').text(app.vtranslate('JS_EDIT_WIDGET'));
		app.showPopoverElementView(wizardContainer.find('.js-help-info'));
		if (thisInstance.getType() == 'RelatedModule') {
			thisInstance.loadFilters(wizardContainer);
			thisInstance.relatedModuleFields(wizardContainer);
			wizardContainer.find("select[name='relatedmodule']").on('change', function () {
				thisInstance.changeRelatedModule();
				thisInstance.relatedModuleFields(wizardContainer);
			});
		}
		const form = $('form', wizardContainer);
		form.validationEngine(app.validationEngineOptions);
		form.on('submit', (e) => {
			e.preventDefault();
			if (form.validationEngine('validate')) {
				const progress = $.progressIndicator({
					'message': app.vtranslate('Loading data'),
					'blockInfo': {
						'enabled': true
					}
				});
				thisInstance.registerSaveEvent('saveWidget', {
					'data': form.serializeFormData(),
					'tabid': $("input[name='tabid']").val(),
				}).done(() => {
					thisInstance.reloadWidgets();
					app.hideModalWindow();
					progress.progressIndicator({'mode': 'hide'});
				});
			}
		});
	},

	registerEvents: function (container) {
		var thisInstance = this;
		this.loadWidgets();
		if (typeof container === "undefined") {
			container = jQuery('.WidgetsManage');
		}
		App.Fields.Picklist.showSelect2ElementView(container.find('.select2'));
		container.find("select.js-module__list").on('change', function (e) {
			var target = $(e.currentTarget);
			$("input[name='tabid']").val(target.val());
			thisInstance.reloadWidgets();
		});
		container.find('.js-widget__add').on('click', function (e) {
			var progressIndicatorElement = jQuery.progressIndicator({'position': 'html'});
			var module = $(".WidgetsManage select.js-module__list").val();
			app.showModalWindow(null, "index.php?parent=Settings&module=Widgets&view=Widget&mod=" + module, function (wizardContainer) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				var form = jQuery('form', wizardContainer);
				form.on('submit', function (e) {
					e.preventDefault();
					var type = form.find('[name="type"]').val();
					thisInstance.createStep2(type);
				});

			});
		});
		container.find('.js-widget__edit').on('click', (e) => {
			app.showModalWindow({
				url: "index.php?parent=Settings&module=Widgets&view=Widget&mode=edit&id=" + $(e.currentTarget).closest('.blockSortable').data('id'),
				cb: (wizardContainer) => {
					this.modalFormEdit(wizardContainer);
				},
			});
		});
		container.find('.js-widget__remove').on('click', function (e) {
			var target = $(e.currentTarget);
			var blockSortable = target.closest('.blockSortable');
			thisInstance.registerSaveEvent('removeWidget', {
				'wid': blockSortable.data('id'),
			});
			thisInstance.reloadWidgets();
		});
	}
});
