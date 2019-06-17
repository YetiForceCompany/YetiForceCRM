/**
 * InRelation SlaPolicy
 *
 * @package     InRelation
 *
 * @description InRelation scripts for SlaPolicy module
 * @license     YetiForce Public License 3.0
 * @author      Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class SlaPolicy_InRelation_Js {
	/**
	 * Constructor
	 *
	 * @param   {jQuery}  container
	 */
	constructor(container) {
		this.container = container;
		this.policyType = Number(this.container.find('[name="policy_type"]:checked').val());
		this.targetModule = this.container.find('[name="target"]').val();
		this.businessHours = JSON.parse(this.container.find('.js-all-business-hours').val());
		this.registerEvents();
	}

	/**
	 * Hide all settings
	 *
	 * @return  {self}
	 */
	hideAll() {
		this.container.find('.js-sla-policy-template, .js-sla-policy-custom').addClass('d-none');
		return this;
	}

	/**
	 * Show template settings
	 *
	 * @return  {self}
	 */
	showTemplateSettings() {
		this.container.find('.js-sla-policy-template').removeClass('d-none');
		return this;
	}

	/**
	 * Show custom settings
	 *
	 * @return  {self}
	 */
	showCustomSettings() {
		this.container.find('.js-sla-policy-custom').removeClass('d-none');
		return this;
	}

	/**
	 * Get template table
	 * @param {Array} rows
	 *
	 * @returns {String} HTML
	 */
	getTemplateTableHtml(rows) {
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
			.map(row => {
				return `<tr>
				<td><input type="radio" name="policy_id" value="${row.id}"></td>
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
	}

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
			module: 'SlaPolicy',
			action: 'TemplatesAjax',
			targetModule: this.targetModule
		}).done(data => {
			progress.progressIndicator({ mode: 'hide' });
			if (data.success) {
				this.container.find('.js-sla-policy-template--container').html(this.getTemplateTableHtml(data.result));
			}
		});
	}

	/**
	 * On policy type change event handler
	 */
	onPolicyTypeChange() {
		this.policyType = Number(this.container.find('[name="policy_type"]:checked').val());
		if (this.policyType === 1) {
			this.hideAll()
				.showTemplateSettings()
				.loadTemplates();
		} else if (this.policyType === 2) {
			this.hideAll().showCustomSettings();
		} else {
			this.hideAll();
		}
	}

	/**
	 * On submit event handler
	 *
	 * @param {Event} ev
	 */
	onSubmit(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		const progress = jQuery.progressIndicator({
			position: 'html',
			blockInfo: {
				enabled: true
			}
		});
		AppConnector.request({
			module: 'SlaPolicy',
			action: 'SaveAjax',
			targetModule: this.targetModule,
			recordId: $('#recordId').val(),
			policyType: this.container.find('[name="policy_type"]:checked').val(),
			policyId: this.container.find('[name="policy_id"]').val()
		}).done(data => {
			progress.progressIndicator({ mode: 'hide' });
		});
	}

	/**
	 * Get hash
	 *
	 * @param   {Number}  length
	 *
	 * @return  {String}
	 */
	getHash(length) {
		var result = '';
		var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		var charactersLength = characters.length;
		for (var i = 0; i < length; i++) {
			result += characters.charAt(Math.floor(Math.random() * charactersLength));
		}
		return result;
	}

	/**
	 * Register add record button click
	 */
	registerAddRecordBtnClick() {
		this.container.find('.js-sla-policy-add-record-btn').on('click', e => {
			e.preventDefault();
			e.stopPropagation();
			const row = $(`<tr data-id="0" data-hash="${this.getHash(8)}">
			<td>${Vtiger_ConditionBuilder_Js.getDisplayValue(null)}</td>
			<td>
				<select class="select2" name="business_hours[]" multiple>
				${this.businessHours
					.map(businessHours => {
						return `<option value="${businessHours.id}">${businessHours.name}</option>`;
					})
					.join('')}
				</select>
			</td>
			<td>
			<div class="js-reaction-time-container">
					<label>${app.vtranslate('JS_REACTION_TIME')}</label>
					<div class="input-group time">
						<input type="hidden" name="reaction_time[]" class="c-time-period" value="0">
					</div>
				</div>
				<div class="js-idle-time-container">
					<label>${app.vtranslate('JS_IDLE_TIME')}</label>
					<div class="input-group time">
						<input type="hidden" name="idle_time[]" class="c-time-period" value="0">
					</div>
				</div>
				<div class="js-resolve-time-container">
					<label>${app.vtranslate('JS_RESOLVE_TIME')}</label>
					<div class="input-group time">
						<input type="hidden" name="resolve_time[]" class="c-time-period" value="0">
					</div>
				</div>
			</td>
			<td>
					<a href class="btn btn-danger js-delete-row-action"><span class="fas fa-trash-alt"></span></a>
					<a href class="btn btn-primary ml-2 js-edit-row-action"><span class="fas fa-edit"></span></a>
			</td>
			</tr>`);
			App.Fields.TimePeriod.register(row);
			this.registerDelBtnClick(row);
			App.Fields.Picklist.showSelect2ElementView(row.find('.select2'));
			this.container.find('.js-custom-conditions-table tbody').append(row);
		});
	}

	/**
	 * Register delete button click
	 *
	 * @param {jQuery} container
	 */
	registerDelBtnClick(container) {
		container.find('.js-delete-row-action').on('click', e => {
			e.preventDefault();
			e.stopPropagation();
			const tr = $(e.target).closest('tr');
			const rowId = Number(tr.data('id'));
			if (!rowId) {
				tr.remove();
				return;
			}
			const progress = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request({
				module: 'SlaPolicy',
				action: 'Delete',
				targetModule: this.targetModule,
				record: rowId,
				hash: tr.data('hash')
			}).done(data => {
				progress.progressIndicator({ mode: 'hide' });
				$(e.target)
					.closest('tr')
					.remove();
			});
		});
	}

	registerDisplayConditions() {
		const conditionsElem = this.container.find('.js-conditions');
		$.each(conditionsElem, (index, elem) => {
			elem = $(elem);
			const conditions = elem.data('conditions') ? elem.data('conditions') : '{}';
			elem.html(Vtiger_ConditionBuilder_Js.getDisplayValue(JSON.parse(conditions)));
		});
	}

	/**
	 * Register events
	 */
	registerEvents() {
		this.container.off('submit').on('submit', this.onSubmit.bind(this));
		this.container.find('.js-sla-policy-type-radio').on('click', e => this.onPolicyTypeChange());
		this.onPolicyTypeChange();
		App.Fields.TimePeriod.register(this.container);
		this.registerAddRecordBtnClick();
		this.registerDelBtnClick(this.container);
		this.registerDisplayConditions();
	}
}
$(document).ready(jQuery => {
	new SlaPolicy_InRelation_Js($('#detailView'));
});
app.event.on('DetailView.Tab.AfterLoad', (event, data, instance) => {
	if (instance.detailViewForm.find('.js-sla-policy').length) {
		new SlaPolicy_InRelation_Js(instance.detailViewForm);
	}
});
