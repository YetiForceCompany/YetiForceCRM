/* {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Base_ChartFilter_JS',
	{},
	{
		/**
		 * Widget type name
		 */
		widgetName: 'ChartFilter',
		registerContainers() {
			this.step1 = $('.step1', this.container);
			this.step2 = $('.step2', this.container);
			this.step3 = $('.step3', this.container);
			this.step4 = $('.step4', this.container);
			this.footer = $('.js-chart-footer', this.container);
			this.form = $('form', this.container);
			this.stepNumber = $('#widgetStep', this.container);
		},
		/**
		 * Register first step elements
		 */
		registerStep1() {
			let chartType = $('select[name="chartType"]', this.container);
			let moduleElement = $('select[name="module"]', this.container);
			App.Fields.Picklist.showSelect2ElementView(moduleElement, {
				placeholder: app.vtranslate('JS_SELECT_MODULE')
			});

			this.moduleName = moduleElement.val();
			this.chartTypeValue = chartType.val();

			chartType.on('change', (e) => {
				this.chartTypeValue = e.currentTarget.value;
				moduleElement.trigger('change');
			});
			moduleElement.on('change', (e) => {
				this.moduleName = e.currentTarget.value;
				if (!this.moduleName) {
					return false;
				}
				this.footer.hide();
				this.step2.empty();
				this.step3.empty();
				this.step4.empty();
				AppConnector.request({
					module: this.sourceModuleName,
					view: this.widgetName,
					step: 'step2',
					chartType: this.chartTypeValue,
					selectedModule: this.moduleName
				}).done((data) => {
					this.registerStep2(data);
				});
			});
		},
		/**
		 * Register second step elements
		 */
		registerStep2(stepContainer) {
			this.step2.append(stepContainer);
			this.stepNumber.val(2);
			this.footer.hide();
			let filtersIdElement = this.step2.find('.filtersId');
			let valueTypeElement = this.step2.find('.valueType');
			App.Fields.Picklist.showSelect2ElementView(filtersIdElement);
			App.Fields.Picklist.showSelect2ElementView(valueTypeElement);
			this.step2.find('.filtersId, .valueType').on('change', (e) => {
				this.step3.empty();
				this.step4.empty();
				this.footer.hide();
				let filterId = filtersIdElement.val(),
					type = valueTypeElement.val();
				if (!filterId || Object.keys(filterId).length === 0 || !type || Object.keys(type).length === 0) {
					return;
				}
				AppConnector.request({
					module: this.sourceModuleName,
					view: this.widgetName,
					step: 'step3',
					selectedModule: this.moduleName,
					chartType: this.chartTypeValue,
					filtersId: filterId,
					valueType: type
				}).done((data) => {
					this.registerStep3(data);
				});
			});
		},
		/**
		 * Register third step elements
		 */
		registerStep3(stepContainer) {
			this.step3.append(stepContainer);
			this.stepNumber.val(3);
			App.Fields.Picklist.showSelect2ElementView(this.step3.find('select'));
			this.footer.hide();
			this.step3.find('.groupField').on('change', (e) => {
				this.step4.empty();
				let groupField = $(e.currentTarget);
				if (!groupField.val()) return;
				this.footer.show();
				AppConnector.request({
					module: this.sourceModuleName,
					view: this.widgetName,
					step: 'step4',
					selectedModule: this.moduleName,
					filtersId: this.step2.find('.filtersId').val(),
					groupField: groupField.val(),
					chartType: this.chartTypeValue
				}).done((data) => {
					this.registerStep4(data);
				});
			});
		},
		/**
		 * Register fourth step elements
		 */
		registerStep4(stepContainer) {
			this.step4.append(stepContainer);
			this.stepNumber.val(4);
			App.Fields.Picklist.showSelect2ElementView(this.step4.find('select'));
			this.step4.find('[name="dividingField"]').on('change', (e) => {
				let type = e.currentTarget.selectedOptions[0].dataset.fieldType;
				let selector = this.step4.find('.js-sector-container');
				if (type === 'datetime' || type === 'date') {
					selector.removeClass('d-none');
					selector.find('select').attr('disabled', false);
					App.Fields.Picklist.showSelect2ElementView(selector.find('select'));
				} else {
					selector.addClass('d-none');
					selector.find('select').attr('disabled', true);
				}
			});
			app.registerModalEvents(this.container);
		},
		/**
		 * Register submit
		 */
		registerSubmit() {
			let form = this.form;
			form.validationEngine(app.validationEngineOptions);
			form.on('submit', (e) => {
				e.preventDefault();
				if (form.data('jqv').InvalidFields.length === 0) {
					let progressIndicatorElement = $.progressIndicator({ position: 'html', blockInfo: { enabled: true } });
					let params = this.getParams();
					AppConnector.request(params)
						.done((data) => {
							if (data.success) {
								app.hideModalWindow();
								progressIndicatorElement.progressIndicator({ mode: 'hide' });
								this.postSave(data);
							}
						})
						.fail(function () {
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
						});
				} else {
					app.formAlignmentAfterValidation(form);
				}
			});
		},
		/**
		 *
		 * @param {object} response
		 */
		postSave(response) {
			let result = response['result'];
			Vtiger_Helper_Js.showMessage({ type: 'success', text: result['text'] });
			if (this.isDashboard()) {
				let linkElement = this.source.clone();
				linkElement.data('name', this.widgetName);
				linkElement.data('id', result['wid']);
				new Vtiger_DashBoard_Js().addWidget(
					linkElement,
					'index.php?module=Home&view=ShowWidget&name=ChartFilter&linkid=' +
						this.source.data('linkid') +
						'&widgetid=' +
						result['wid'] +
						'&active=0'
				);
			} else {
				window.location.reload();
			}
		},
		/**
		 * Gets params for save
		 * @returns {object}
		 */
		getParams() {
			let data = {};
			let paramsForm = this.form.serializeFormData();
			this.form.find('.saveParam').each(function (_, element) {
				if (typeof paramsForm[element.name] !== 'undefined') {
					data[element.name] = paramsForm[element.name];
				}
			});
			let filtersId = this.step2.find('.filtersId').val();
			if (Array.isArray(filtersId)) {
				filtersId = filtersId.join(',');
			}
			const formData = {
				data: JSON.stringify(data),
				blockid: this.source.data('block-id'),
				linkid: this.source.data('linkid'),
				name: this.widgetName,
				title: paramsForm['widgetTitle'],
				filterid: filtersId,
				isdefault: 0,
				height: 4,
				width: 4,
				owners_all: ['mine', 'all', 'users', 'groups'],
				default_owner: 'mine',
				dashboardId: this.getCurrentDashboard()
			};
			return {
				form: formData,
				module: this.sourceModuleName,
				sourceModule: this.sourceModuleName || app.getModuleName(),
				action: 'Widget',
				mode: 'add',
				addToUser: this.isDashboard(),
				linkid: this.source.data('linkid'),
				name: this.widgetName
			};
		},
		/**
		 * Gets dashboard ID
		 * @returns {int}
		 */
		getCurrentDashboard() {
			return $('.selectDashboard li a.active').closest('li').data('id') || 1;
		},
		/**
		 * Check if the widget is added on the dashboard
		 * @returns {boolean}
		 */
		isDashboard() {
			return $('.dashboardViewContainer').length > 0 && typeof Vtiger_DashBoard_Js !== 'undefined';
		},
		/**
		 * Register modal events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			this.container = $(modalContainer);
			let id = this.container.closest('.js-modal-container').attr('id');
			this.source = $(`[data-modalid="${id}"]`);
			this.sourceModuleName = this.source.data('module');
			this.registerContainers();
			this.registerStep1();
			this.registerSubmit();
		}
	}
);
