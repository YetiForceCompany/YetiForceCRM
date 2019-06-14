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
		this.policyType = this.container.find('[name="policy_type"]:checked').val();
		this.targetModule = this.container.find('[name="target"]').val();
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
	 *
	 * @param {Event} e
	 */
	onPolicyTypeChange(e) {
		this.policyType = this.container.find('[name="policy_type"]:checked').val();
		if (this.policyType === 'template') {
			this.hideAll()
				.showTemplateSettings()
				.loadTemplates();
		} else if (this.policyType === 'custom') {
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
	 * Register events
	 */
	registerEvents() {
		this.container.off('submit').on('submit', this.onSubmit.bind(this));
		this.container.find('.js-sla-policy-type-radio').on('click', e => this.onPolicyTypeChange(e));
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
