/**
 * InRelation SlaPolicy
 *
 * @description InRelation scripts for SlaPolicy module
 * @license     YetiForce Public License 5.0
 * @author      Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
		 * Get template table
		 * @param {Array} rows
		 *
		 * @returns {String} HTML
		 */
		getTemplateTableHtml(rows) {
			let somethingChecked = false;
			rows.forEach((row) => {
				if (row.checked) {
					somethingChecked = true;
					return false;
				}
			});
			if (!somethingChecked && typeof rows[0] !== 'undefined') {
				rows[0].checked = true;
			}
			return `<div class="col-12"><table class="table js-sla-policy-template-table">
		<thead>
			<tr>
				<th></th>
				<th>${app.vtranslate('JS_POLICY_NAME')}</th>
				<th>${app.vtranslate('JS_OPERATIONAL_HOURS')}</th>
				<th>${app.vtranslate('JS_REACTION_TIME')}</th>
				<th>${app.vtranslate('JS_IDLE_TIME')}</th>
				<th>${app.vtranslate('JS_RESOLVE_TIME')}</th>
			</tr>
		</thead>
		<tbody>
		${rows
			.map((row) => {
				return `<tr>
				<td><input type="radio" name="policy_id" value="${row.id}"${row.checked ? 'checked="checked"' : ''}></td>
				<td>${row.name}</td>
				<td>${row.operational_hours}</td>
				<td>${row.reaction_time}</td>
				<td>${row.idle_time}</td>
				<td>${row.resolve_time}</td>
			</tr>`;
			})
			.join('')}
		</tbody>
		</table>
		</div>`;
		},

		/**
		 * Load predefined sla policy templates
		 */
		loadTemplates() {
			const progress = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request({
				module: 'ServiceContracts',
				action: 'PolicyTemplatesAjax',
				targetModule: this.targetModule,
				record: Number($('#recordId').val())
			}).done((data) => {
				progress.progressIndicator({ mode: 'hide' });
				if (data.success) {
					this.container.find('.js-sla-policy-template--container').html(this.getTemplateTableHtml(data.result));
				}
			});
		},

		/**
		 * On policy type change event handler
		 */
		onPolicyTypeChange() {
			this.policyType = Number(this.container.find('[name="policy_type"]:checked').val());
			if (this.policyType === 1) {
				this.hideAll().showTemplateSettings().loadTemplates();
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
					$.each(this.container.find('.js-custom-row'), (index, rowElem) => {
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
				const index = this.container.find('.js-custom-row').length;
				const row = $(`<div class="card js-custom-row shadow-sm mb-2" data-id="0" data-record-id="` + addPolicyBtn.data('record-id') + `" data-js="container">
			<div class="card-body">
				<div class="d-flex">
					<div class="d-block" style="flex-grow:1">
						<div class="row no-gutters">
							<div class="col-5 pr-2">
								<label>${app.vtranslate('JS_BUSINESS_HOURS')}</label>
								<select class="select2" name="business_hours[${index}][]" multiple data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]">
								${this.businessHours
									.map((businessHours) => {
										return `<option value="${businessHours.id}">${businessHours.name}</option>`;
									})
									.join('')}
								</select>
							</div>
							<div class="col-2 pr-2">
								<label>${app.vtranslate('JS_REACTION_TIME')}</label>
								<div class="input-group time">
									<input type="hidden" name="reaction_time[${index}]" class="c-time-period" value="1:d">
								</div>
							</div>
							<div class="col-2 pr-2">
								<label>${app.vtranslate('JS_IDLE_TIME')}</label>
								<div class="input-group time">
									<input type="hidden" name="idle_time[${index}]" class="c-time-period" value="1:d">
								</div>
							</div>
							<div class="col-2 pr-2">
								<label>${app.vtranslate('JS_RESOLVE_TIME')}</label>
								<div class="input-group time">
									<input type="hidden" name="resolve_time[${index}]" class="c-time-period" value="1:d">
								</div>
							</div>
						</div>
						<div class="row mt-2">
							<div class="js-conditions-col col">
								<input type="hidden" name="rowid[${index}]" value="0" class="js-custom-row-id" />
								<input type="hidden" name="conditions[${index}]" class="js-conditions-value" value="{}" data-js="container">
								${this.container.find('.js-conditions-template').html()}
							</div>
						</div>
					</div>
					<div class="d-inline-flex text-right border-left" style="flex-grow:0">
						<div class="d-inline-block align-center" style="margin:auto 0;">
							<a href class="btn btn-danger ml-4 js-delete-row-action"><span class="fas fa-trash-alt"></span></a>
						</div>
					</div>
				</div>
			</div>
		</div>`
				);
				App.Fields.TimePeriod.register(row);
				this.registerDelBtnClick(row);
				App.Fields.Picklist.showSelect2ElementView(row.find('.select2'));
				this.registerConditionBuilder(
					row.find('.js-condition-builder').eq(0),
					this.container.find('.js-conditions-col').length
				);
				this.container.find('.js-custom-conditions').append(row);
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
				}).done((data) => {
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
			this.businessHours = JSON.parse(this.container.find('.js-all-business-hours').val());
			this.conditionBuilders = [];
			this.conditionsBuildersContainers = [];
			this.container.off('submit').on('submit', this.onSubmit.bind(this));
			this.container.find('.js-sla-policy-type-radio').on('click', (e) => this.onPolicyTypeChange());
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
			app.event.on('DetailView.Tab.AfterLoad', (event, data, instance) => {
				if (instance.getForm().find('.js-sla-policy').length) {
					instance.initSlaPolicy();
				}
			});
		}
	}
);
