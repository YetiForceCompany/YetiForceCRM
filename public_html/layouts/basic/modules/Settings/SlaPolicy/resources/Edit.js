/**
 * SlaPolicy Edit Js class
 *
 * @package     Edit
 *
 * @description SlaPolicy Edit View scripts
 * @license     YetiForce Public License 5.0
 * @author      Rafal Pospiech <r.pospiech@yetiforce.com>
 */
'use strict';

$.Class(
	'Settings_SlaPolicy_Edit_Js',
	{},
	{
		/**
		 * Register submit event
		 */
		registerSubmitEvent() {
			this.container.off('submit').on('submit', (e) => {
				if ($(e.currentTarget).validationEngine('validate')) {
					$('input[name="conditions"]').val(JSON.stringify(this.conditionBuilder.getConditions()));
					return true;
				}
				e.preventDefault();
				e.stopPropagation();
				return false;
			});
		},

		/**
		 * Load condition builder
		 *
		 * @param   {String}  sourceModuleName
		 */
		loadConditionBuilderView(sourceModuleName) {
			this.conditionBuilderView = this.container.find('.js-condition-builder-view').eq(0);
			let progress = $.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request({
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				view: 'Conditions',
				record: sourceModuleName === this.sourceModuleName ? this.record : 0,
				sourceModuleName: sourceModuleName
			}).done((data) => {
				progress.progressIndicator({ mode: 'hide' });
				this.conditionBuilderView.html(data);
				this.conditionBuilder = new Vtiger_ConditionBuilder_Js(
					this.conditionBuilderView.find('.js-condition-builder'),
					sourceModuleName
				);
				this.conditionBuilder.registerEvents();
			});
		},
		/**
		 * Register source module change
		 */
		registerSourceModuleChange() {
			this.sourceModuleSelect = this.container.find('select[name="source_module"]');
			this.sourceModuleSelect.on('change', (e) => {
				this.sourceModuleName = this.sourceModuleSelect.val();
				this.loadConditionBuilderView(this.sourceModuleName);
			});
		},

		/**
		 * Render business hours
		 *
		 * @param {Array} rows
		 * @returns {String} html
		 */
		renderBusinessHours(rows) {
			let html = `<table class="table table-sm js-business-hours-table" data-js="container">`;
			html += `<thead>
				<tr>
					<th colspan="6" class="text-center">${app.vtranslate('JS_BUSINESS_HOURS')}</th>
				</tr>
				<tr>
					<th></th>
					<th>${app.vtranslate('JS_BUSINESS_HOURS_NAME')}</th>
					<th>${app.vtranslate('JS_BUSINESS_HOURS_DAYS')}</th>
					<th>${app.vtranslate('JS_BUSINESS_HOURS_FROM')}</th>
					<th>${app.vtranslate('JS_BUSINESS_HOURS_TO')}</th>
				</tr>
			</thead><tbody>`;
			rows.forEach((row) => {
				html += `<tr class="js-business-hours-row" data-id="${row.id}" data-js="click">
					<td class="js-business-hours-table-id"><input type="checkbox" class="checkbox js-business-hours-checkbox"${
						this.businessHours.indexOf(Number(row.id)) !== -1 ? ' checked="checked"' : ''
					}></td>
					<td class="js-business-hours-table-name">${row.name}</td>
					<td class="js-business-hours-table-days">${row.working_days}${
					row.holidays ? ', ' + app.vtranslate('JS_HOLIDAYS') : ''
				}</td>
					<td class="js-business-hours-table-from">${row.working_hours_from}</td>
					<td class="js-business-hours-table-to">${row.working_hours_to}</td>
				</tr>`;
			});
			return html + '</tbody></table>';
		},

		/**
		 * Update business hours value inside hidden form input
		 */
		updateBusinessHoursValue() {
			this.container.find('[name="business_hours"]').val(this.businessHours.join(','));
		},

		/**
		 * Remove business hours
		 *
		 * @param {Number} businessHoursId
		 */
		removeBusinessHours(businessHoursId = 0) {
			if (this.businessHours.indexOf(businessHoursId) !== -1) {
				this.businessHours = this.businessHours.filter((id) => id !== businessHoursId);
			}
			this.updateBusinessHoursValue();
		},

		/**
		 * Add business hours
		 *
		 * @param {Number} businessHoursId
		 */
		addBusinessHours(businessHoursId) {
			if (this.businessHours.indexOf(businessHoursId) === -1) {
				this.businessHours.push(businessHoursId);
			}
			this.updateBusinessHoursValue();
		},

		/**
		 * On business hours row click event handler
		 */
		onBusinessHoursRowClick(e) {
			e.stopPropagation();
			const target = $(e.target);
			const isCheckbox = target.hasClass('js-business-hours-checkbox');
			const row = target.closest('tr');
			const id = Number(row.data('id'));
			const checkbox = row.find('input[type="checkbox"]');
			if (checkbox.prop('checked')) {
				if (!isCheckbox) {
					checkbox.prop('checked', false);
					this.removeBusinessHours(id);
				} else {
					this.addBusinessHours(id);
				}
			} else {
				if (!isCheckbox) {
					checkbox.prop('checked', true);
					this.addBusinessHours(id);
				} else {
					this.removeBusinessHours(id);
				}
			}
		},

		/**
		 * Register business hours table events
		 */
		registerBusinessHoursTableEvents() {
			const table = this.container.find('.js-business-hours-table').eq(0);
			table.find('.js-business-hours-row').on('click', this.onBusinessHoursRowClick.bind(this));
		},

		/**
		 * Change operational hours view
		 */
		changeOperationalHoursView() {
			if (this.operationalHours === 1) {
				let progress = $.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request({
					data: {
						module: 'BusinessHours',
						parent: 'Settings',
						view: 'List'
					},
					dataType: 'json'
				}).done((data) => {
					if (!data.success) {
						return;
					}
					const rows = JSON.parse(data.result);
					this.container.find('.js-business-hours-container-content').html(this.renderBusinessHours(rows));
					this.registerBusinessHoursTableEvents();
					this.container.find('.js-business-hours-container').removeClass('d-none');
					progress.progressIndicator({ mode: 'hide' });
				});
			} else {
				this.container.find('.js-business-hours-container').addClass('d-none');
			}
		},

		/**
		 * Register operational hours change
		 */
		registerOperationalHoursChange() {
			const operationalHoursElem = this.container.find('[name="operational_hours"]').eq(0);
			operationalHoursElem.on('change', (e) => {
				this.operationalHours = Number(operationalHoursElem.val());
				this.changeOperationalHoursView();
			});
		},

		/**
		 * Register events
		 */
		registerEvents() {
			this.container = $('#EditView');
			this.sourceModuleName = this.container.find('[name="source_module"]').val();
			this.operationalHours = Number(this.container.find('[name="operational_hours"]').val());
			this.businessHours = [];
			const hoursFromInput = this.container.find('[name="business_hours"]').val();
			if (hoursFromInput) {
				this.businessHours = hoursFromInput.split(',').map((id) => Number(id));
			}
			this.record = this.container.find('input[name="record"]').val();
			this.registerSourceModuleChange();
			this.loadConditionBuilderView(this.sourceModuleName);
			App.Fields.TimePeriod.register(this.container);
			this.changeOperationalHoursView();
			this.registerOperationalHoursChange();
			this.registerSubmitEvent();
			this.container.validationEngine(app.validationEngineOptions);
		}
	}
);
