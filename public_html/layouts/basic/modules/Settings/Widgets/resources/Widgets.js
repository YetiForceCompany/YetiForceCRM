/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Widgets_Index_Js',
	{},
	{
		getTabId: function () {
			return $(".WidgetsManage [name='tabid']").val();
		},
		getType: function () {
			return $(".form-modalAddWidget [name='type']").val();
		},
		createStep2: function (type) {
			var thisInstance = this;
			var tabId = thisInstance.getTabId();
			var progressIndicatorElement = jQuery.progressIndicator({ position: 'html' });
			app.showModalWindow(
				null,
				'index.php?parent=Settings&module=Widgets&view=Widget&mode=createStep2&type=' + type + '&tabId=' + tabId,
				function (wizardContainer) {
					app.showPopoverElementView(wizardContainer.find('.js-help-info'));
					if (type === 'RelatedModule' || type === 'RelatedModuleChart' || type === 'Documents') {
						thisInstance.loadFilters(wizardContainer);
						thisInstance.relatedModuleFields(wizardContainer);
						thisInstance.customView(wizardContainer);
						wizardContainer.find("select[name='relation_id']").on('change', function () {
							thisInstance.changeRelatedModule(wizardContainer);
							thisInstance.relatedModuleFields(wizardContainer);
							thisInstance.customView(wizardContainer);
						});
					}
					thisInstance.registerSort(wizardContainer);
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
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
								thisInstance
									.registerSaveEvent('saveWidget', {
										data: form.serializeFormData(),
										tabid: tabId
									})
									.done((_) => {
										thisInstance.reloadWidgets();
										app.hideModalWindow();
									});
							}
						}
					});
				}
			);
		},
		loadWidgets: function () {
			var thisInstance = this;
			var blocks = jQuery('.blocksSortable');
			blocks.sortable({
				revert: true,
				connectWith: '.blocksSortable',
				tolerance: 'pointer',
				cursor: 'move',
				placeholder: 'state-highlight',
				stop: function (event, ui) {
					thisInstance.updateSequence();
				}
			});
		},
		updateSequence: function () {
			var thisInstance = this;
			var params = {};
			$('.blockSortable').each(function (index) {
				params[$(this).data('id')] = {
					index: index,
					column: $(this).closest('.blocksSortable').data('column')
				};
			});
			var progress = $.progressIndicator({
				message: app.vtranslate('Saving changes'),
				blockInfo: {
					enabled: true
				}
			});
			thisInstance.registerSaveEvent('updateSequence', {
				data: params,
				tabid: $("input[name='tabid']").val()
			});
			progress.progressIndicator({ mode: 'hide' });
		},
		reloadWidgets: function () {
			var thisInstance = this;
			var Indicator = jQuery.progressIndicator({
				message: app.vtranslate('Loading data'),
				position: 'html',
				blockInfo: {
					enabled: true
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
				Indicator.progressIndicator({ mode: 'hide' });
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
			})
				.done((data) => {
					aDeferred.resolve(data);
					app.showNotify({
						text: data['result']['message'],
						type: 'success'
					});
				})
				.fail((textStatus, errorThrown) => {
					aDeferred.reject(textStatus, errorThrown);
				});
			return aDeferred.promise();
		},
		loadFilters: function (contener) {
			let types = ['filter', 'checkbox', 'switchHeader'];
			let selected = contener.find("select[name='relation_id'] option:selected");
			let relatedModuleInput = contener.find("input[name='relatedmodule']");
			let relatedModule = relatedModuleInput.val();
			if (selected.length) {
				relatedModule = selected.data('relatedmodule');
				relatedModuleInput.val(selected.data('relatedmodule')).data('module-name', selected.data('module-name'));
			}
			for (let i in types) {
				let filters = app.getMainParams(types[i] + 'All', true);
				let filterField = contener.find("select[name='" + types[i] + "']");
				let filterSelected = contener.find('input#' + types[i] + '_selected').val();
				filterField.empty();
				filterField.append($('<option/>', { value: '-', text: app.vtranslate('None') }));
				if (filters[relatedModule] !== undefined) {
					filterField.closest('.form-group').removeClass('d-none');
					$.each(filters[relatedModule], function (index, value) {
						if (typeof value === 'object') {
							value = value.label;
						}
						let option = { value: index, text: value };
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
			const relatedModule = parseInt(container.find("input[name='relatedmodule']").val());
			const relatedfields = container.find("select[name='relatedfields'],select[name='groupField']");
			relatedfields.find('optgroup').each(function (index, optgroup) {
				optgroup = $(optgroup);
				if (relatedModule !== optgroup.data('module')) {
					optgroup.addClass('d-none');
					optgroup.prop('disabled', 'disabled');
				} else {
					optgroup.removeClass('d-none');
					optgroup.prop('disabled', false);
				}
				optgroup.find('option').each(function (index, option) {
					option = $(option);
					if (relatedModule !== option.data('module')) {
						option.addClass('d-none').removeAttr('selected');
						option.prop('disabled', 'disabled');
					} else {
						option.removeClass('d-none');
						option.prop('disabled', false);
					}
				});
			});
			relatedfields.trigger('change:select2');
		},

		changeRelatedModule(wizardContainer) {
			this.loadFilters(wizardContainer.find('.form-modalAddWidget'));
		},

		customView(container) {
			const relatedModule = parseInt(container.find("input[name='relatedmodule']").val());
			let customViews = app.getMainParams('customView', true);
			let customView = container.find("select[name='customView']");
			let customViewValues = container.find('.js-custom-view').val();
			if (customViewValues) {
				customViewValues = JSON.parse(customViewValues);
			} else {
				customViewValues = [];
			}
			customView.empty();
			if (customViews[relatedModule] !== undefined) {
				$.each(customViews[relatedModule], function (index, value) {
					let option = { value: index, text: value };
					if (customViewValues.includes(index)) {
						option.selected = 'selected';
					}
					customView.append($('<option/>', option));
				});
			}
			customView.trigger('change:select2');
		},

		modalFormEdit(wizardContainer) {
			const thisInstance = this;
			$('#massEditHeader.modal-title').text(app.vtranslate('JS_EDIT_WIDGET'));
			app.showPopoverElementView(wizardContainer.find('.js-help-info'));
			let type = thisInstance.getType();
			if (type == 'RelatedModule' || type === 'RelatedModuleChart' || type === 'Documents') {
				thisInstance.loadFilters(wizardContainer);
				thisInstance.relatedModuleFields(wizardContainer);
				thisInstance.customView(wizardContainer);
				wizardContainer.find("select[name='relation_id']").on('change', function () {
					thisInstance.changeRelatedModule(wizardContainer);
					thisInstance.relatedModuleFields(wizardContainer);
					thisInstance.customView(wizardContainer);
				});
			}
			this.registerSort(wizardContainer);
			const form = $('form', wizardContainer);
			form.validationEngine(app.validationEngineOptions);
			form.on('submit', (e) => {
				e.preventDefault();
				if (form.validationEngine('validate')) {
					const progress = $.progressIndicator({
						message: app.vtranslate('Loading data'),
						blockInfo: {
							enabled: true
						}
					});
					thisInstance
						.registerSaveEvent('saveWidget', {
							data: form.serializeFormData(),
							tabid: $("input[name='tabid']").val()
						})
						.done(() => {
							thisInstance.reloadWidgets();
							app.hideModalWindow();
							progress.progressIndicator({ mode: 'hide' });
						});
				}
			});
		},

		registerSort: function (container) {
			container.find("select[name='relation_id']").on('change', (e) => {
				container.find('#orderBy').val('[]');
			});
			container.find('.js-sort-modal').on('click', (e) => {
				let relatedModule = container.find("input[name='relatedmodule']").data('module-name');
				let url = e.currentTarget.dataset.url;
				app.showModalWindow(
					null,
					url + '&module=' + relatedModule,
					function (wizardContainer) {
						wizardContainer.find('.js-modal__save').on('click', (el) => {
							el.preventDefault();
							let sortData = {};
							wizardContainer.find('.js-sort-container_element:not(.js-base-element)').each(function () {
								let orderBy = $(this).find('.js-orderBy').val();
								if (orderBy) {
									sortData[orderBy] = $(this).find('.js-sort-order').val();
								}
							});
							container.find('#orderBy').val(JSON.stringify(sortData));
						});
					},
					{ modalId: e.currentTarget.dataset.modalid }
				);
			});
		},

		registerEvents: function (container) {
			var thisInstance = this;
			this.loadWidgets();
			if (typeof container === 'undefined') {
				container = jQuery('.WidgetsManage');
			}
			App.Fields.Picklist.showSelect2ElementView(container.find('.select2'));
			container.find('select.js-module__list').on('change', function (e) {
				var target = $(e.currentTarget);
				$("input[name='tabid']").val(target.val());
				thisInstance.reloadWidgets();
			});
			container.find('.js-widget__add').on('click', function (e) {
				var progressIndicatorElement = jQuery.progressIndicator({ position: 'html' });
				var module = $('.WidgetsManage select.js-module__list').val();
				app.showModalWindow(
					null,
					'index.php?parent=Settings&module=Widgets&view=Widget&mod=' + module,
					function (wizardContainer) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						var form = jQuery('form', wizardContainer);
						form.on('submit', function (e) {
							e.preventDefault();
							var type = form.find('[name="type"]').val();
							thisInstance.createStep2(type);
						});
					}
				);
			});
			container.find('.js-widget__edit').on('click', (e) => {
				app.showModalWindow({
					url:
						'index.php?parent=Settings&module=Widgets&view=Widget&mode=edit&id=' +
						$(e.currentTarget).closest('.blockSortable').data('id'),
					cb: (wizardContainer) => {
						this.modalFormEdit(wizardContainer);
					}
				});
			});
			container.find('.js-widget__remove').on('click', function (e) {
				var target = $(e.currentTarget);
				var blockSortable = target.closest('.blockSortable');
				thisInstance.registerSaveEvent('removeWidget', {
					wid: blockSortable.data('id')
				});
				thisInstance.reloadWidgets();
			});
		}
	}
);
