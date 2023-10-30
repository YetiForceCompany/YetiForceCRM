/**
 * InRelation SlaPolicy
 *
 * @description InRelation scripts for SlaPolicy module
 * @license     YetiForce Public License 6.5
 * @author      Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author      Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
'use strict';

Vtiger_Detail_Js(
	'ServiceContracts_Detail_Js',
	{},
	{
		/**
		 * Hide all settings
		 *
		 * @return  {self}
		 */
		hideAll() {
			this.container.find('.js-sla-policy-template, .js-sla-policy-custom').addClass('d-none');
			return this;
		},

		/**
		 * Show template settings
		 *
		 * @return  {self}
		 */
		showTemplateSettings() {
			this.container.find('.js-sla-policy-template').removeClass('d-none');
			return this;
		},

		/**
		 * Show custom settings
		 *
		 * @return  {self}
		 */
		showCustomSettings() {
			this.container.find('.js-sla-policy-custom').removeClass('d-none');
			return this;
		},
		/**
		 * Get default params
		 * @returns {Object}
		 */
		getDefaultParam() {
			return {
				module: 'ServiceContracts',
				view: 'PolicyTemplatesAjax',
				targetModule: this.targetModule,
				record: Number($('#recordId').val())
			};
		},

		/**
		 * Load predefined sla policy templates
		 * @param {Object} param
		 * @returns
		 */
		loadTemplates(param) {
			const progress = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			return new Promise((resolve, _reject) => {
				AppConnector.request(param)
					.done((data) => {
						progress.progressIndicator({ mode: 'hide' });
						resolve(data);
					})
					.fail((e, t) => {
						progress.progressIndicator({ mode: 'hide' });
						app.errorLog(e, t);
						app.showNotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
					});
			});
		},

		/**
		 * On policy type change event handler
		 */
		onPolicyTypeChange() {
			this.policyType = Number(this.container.find('[name="policy_type"]:checked').val());
			if (this.policyType === 1) {
				this.hideAll()
					.showTemplateSettings()
					.loadTemplates({ mode: 'slaPolicyTemplate', ...this.getDefaultParam() })
					.then((data) => {
						this.container.find('.js-sla-policy-template--container').html(data);
					});
			} else if (this.policyType === 2) {
				this.hideAll().showCustomSettings();
			} else {
				this.hideAll();
			}
		},

		/**
		 * On submit event handler
		 *
		 * @param {Event} ev
		 */
		onSubmit(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			this.container.validationEngine(app.validationEngineOptions);
			const policyType = Number(this.container.find('[name="policy_type"]:checked').val());
			const policyId = Number(this.container.find('[name="policy_id"]:checked').val());
			if (policyType === 2 && !this.container.validationEngine('validate')) {
				return;
			}
			if (policyType === 2 && !this.container.find('.js-custom-row').length) {
				app.showNotify({
					text: app.vtranslate('JS_NO_ITEM_SELECTED'),
					type: 'notice',
					animation: 'show'
				});
				return;
			}
			if (policyType === 1 && isNaN(policyId)) {
				app.showNotify({
					text: app.vtranslate('JS_NO_ITEM_SELECTED'),
					type: 'notice',
					animation: 'show'
				});
				return;
			}
			const progress = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			const params = this.container.serializeFormData();
			params.module = 'ServiceContracts';
			params.action = 'PolicySaveAjax';
			params.targetModule = this.targetModule;
			params.record = $('#recordId').val();
			params.policyType = policyType;
			params.policyId = policyId;
			AppConnector.request({ data: params }).done((data) => {
				progress.progressIndicator({ mode: 'hide' });
				if (Array.isArray(data.result)) {
					data.result.forEach((row, index) => {
						const rowElem = this.container.find('.js-custom-row').eq(index);
						rowElem.data('id', row.id);
						rowElem.find('.js-custom-row-id').val(row.id);
					});
				} else {
					$.each(this.container.find('.js-custom-row'), (_index, rowElem) => {
						rowElem = $(rowElem);
						rowElem.data('id', 0);
						rowElem.find('.js-custom-row-id').val(0);
					});
				}
				app.showNotify({
					text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
					type: 'success',
					animation: 'show'
				});
			});
		},

		/**
		 * Register add record button click
		 */
		registerAddRecordBtnClick() {
			let addPolicyBtn = this.container.find('.js-sla-policy-add-record-btn');
			addPolicyBtn.on('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				this.loadTemplates({
					mode: 'slaPolicyCustom',
					index: this.container.find('.js-custom-row').length,
					...this.getDefaultParam()
				}).then((data) => {
					let html = $(data);
					App.Fields.TimePeriod.register(html);
					this.registerDelBtnClick(html);
					App.Fields.Picklist.showSelect2ElementView(html.find('.select2'));
					this.registerConditionBuilder(
						html.find('.js-condition-builder').eq(0),
						this.container.find('.js-conditions-col').length
					);
					this.container.find('.js-custom-conditions').append(html);
				});
			});
		},

		/**
		 * Register delete button click
		 *
		 * @param {jQuery} container
		 */
		registerDelBtnClick(container) {
			container.find('.js-delete-row-action').on('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				const row = $(e.target).closest('.js-custom-row');
				const rowId = Number(row.data('id'));
				if (!rowId) {
					row.remove();
					return;
				}
				const progress = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request({
					module: 'ServiceContracts',
					action: 'PolicyDeleteAjax',
					targetModule: this.targetModule,
					record: row.data('record-id'),
					rowId: rowId
				}).done(() => {
					progress.progressIndicator({ mode: 'hide' });
					$(e.target).closest('.card').remove();
					app.showNotify({
						text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
						type: 'success',
						animation: 'show'
					});
				});
			});
		},

		/**
		 * On condition change event
		 *
		 * @param   {Vtiger_ConditionBuilder_Js}  instance
		 */
		onConditionsChange(instance) {
			const index = this.conditionBuilders.indexOf(instance);
			this.conditionsBuildersContainers[index]
				.parent()
				.find('.js-conditions-value')
				.val(JSON.stringify(instance.getConditions()));
		},

		/**
		 * Register condition builder
		 *
		 * @param {jQuery} container
		 * @param {Number} index
		 */
		registerConditionBuilder(container, index) {
			this.conditionBuilders[index] = new Vtiger_ConditionBuilder_Js(
				container,
				this.targetModule,
				this.onConditionsChange.bind(this)
			);
			this.conditionBuilders[index].registerEvents();
			this.conditionsBuildersContainers[index] = container;
		},

		/**
		 * Init sla policy events
		 */
		initSlaPolicy() {
			this.container = this.getForm();
			this.policyType = Number(this.container.find('[name="policy_type"]:checked').val());
			this.targetModule = this.container.find('[name="target"]').val();
			this.conditionBuilders = [];
			this.conditionsBuildersContainers = [];
			this.container.off('submit').on('submit', this.onSubmit.bind(this));
			this.container.find('.js-sla-policy-type-radio').on('click', () => this.onPolicyTypeChange());
			this.onPolicyTypeChange();
			App.Fields.TimePeriod.register(this.container);
			this.registerAddRecordBtnClick();
			this.registerDelBtnClick(this.container);
			$.each(this.container.find('.js-custom-conditions .js-condition-builder'), (index, col) => {
				this.registerConditionBuilder($(col), index);
			});
		},

		registerEvents: function () {
			this._super();
			let detailViewForm = this.getForm();
			if (detailViewForm.find('.js-sla-policy').length) {
				this.initSlaPolicy();
			}
			app.event.on('DetailView.Tab.AfterLoad', (_event, _data, instance) => {
				if (instance.getForm().find('.js-sla-policy').length) {
					instance.initSlaPolicy();
				}
			});
		}
	}
);
