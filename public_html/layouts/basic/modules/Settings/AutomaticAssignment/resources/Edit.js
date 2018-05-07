/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
jQuery.Class('Settings_AutomaticAssignment_Edit_Js', {}, {
	container: false,
	advanceFilterInstance: false,
	getContainer: function () {
		if (this.container == false) {
			this.container = jQuery('div.contentsDiv');
		}
		return this.container;
	},
	registerBasicEvents: function (container) {
		var thisInstance = this;

		var advanceFilterContainer = container.find('.filterContainer');
		if (advanceFilterContainer.length) {
			this.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(advanceFilterContainer);
		}
		var form = container.find('form');
		if (form.length) {
			form.validationEngine(app.validationEngineOptions);
			form.find("[data-inputmask]").inputmask();
		}
		container.find('.select2noactive').each(function (index, domElement) {
			var select = $(domElement);
			if (!select.data('select2')) {
				App.Fields.Picklist.showSelect2ElementView(select, {placeholder: app.vtranslate('JS_SELECT_AN_OPTION')});
			}
		});
		var table = app.registerDataTables(container.find('.dataTable'));
		if (table) {
			table.$('.changeRoleType').on('click', function (e) {
				e.stopPropagation();
				e.preventDefault();
				var element = jQuery(e.currentTarget);
				var dataElement = element.closest('tr');
				app.saveAjax('changeRoleType', dataElement.data('value'), {'record': app.getMainParams('record')}).then(function (data) {
					thisInstance.refreshTab();
				});
			});
			table.$('.delete').on('click', function (e) {
				e.stopPropagation();
				e.preventDefault();
				var element = jQuery(e.currentTarget);
				var dataElement = element.closest('tr');
				var params = {
					record: app.getMainParams('record'),
					value: dataElement.data('value'),
					name: dataElement.data('name')
				};
				app.saveAjax('deleteElement', null, params).then(function (data) {
					thisInstance.refreshTab();
				});
			});
		}
		container.find('.fieldContainer').on('click', function (e) {
			e.stopPropagation();
			e.preventDefault();
		});

		container.find('.saveValue').on('click', function (e) {
			var button = jQuery(e.currentTarget);
			var fieldContainer = button.closest('.fieldContainer');
			var baseFieldName = fieldContainer.data('dbname');
			if (baseFieldName === 'conditions') {
				var advfilterlist = thisInstance.advanceFilterInstance.getValues();
				var value = JSON.stringify(advfilterlist);
			} else {
				var fieldName = fieldContainer.data('name');
				var fieldElement = fieldContainer.find('[name="' + fieldName + '"]');
				if (fieldElement.validationEngine('validate')) {
					return false;
				}
				var value = fieldElement.val();
			}

			var params = [];
			params[baseFieldName] = value;
			app.saveAjax('save', jQuery.extend({}, params), {'record': app.getMainParams('record')}).then(function (respons) {
				thisInstance.refreshTab();
			});
		});
		container.find('.js-switch').on('change', (e) => {
			const currentTarget = $(e.currentTarget),
				state = currentTarget.val();
			let params = [];
			if (currentTarget.hasClass('noField')) {
				if (state === '1') {
					currentTarget.closest('form').find('.fieldToShowHide').removeClass('d-none');
					return false;
				} else if (state === '0'){
					currentTarget.closest('form').find('.fieldToShowHide').addClass('d-none');
				}
			}
			params[currentTarget.attr('name')] = Number(state)
			app.saveAjax('save', jQuery.extend({}, params), {'record': app.getMainParams('record')}).then(function (respons) {
				thisInstance.refreshTab();
			});
		});
	},
	refreshTab: function () {
		var thisInstance = this;
		Settings_Vtiger_Index_Js.showMessage({text: app.vtranslate('JS_SAVE_SUCCESS')});
		var tabContainer = this.getContainer().find('.tab-pane.active');
		if (tabContainer.hasClass('noRefresh')) {
			return false;
		}
		AppConnector.request(tabContainer.data('url')).then(
			function (data) {
				tabContainer.html(data);
				thisInstance.registerBasicEvents(tabContainer);
			},
			function (textStatus, errorThrown) {
				app.errorLog(textStatus, errorThrown);
			}
		);
	},
	registerEvents: function () {
		this.registerBasicEvents(this.getContainer());
	}
})
