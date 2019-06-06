/**
 * SLAPolicy Edit Js class
 *
 * @package     Edit
 *
 * @description SLAPolicy Edit View scripts
 * @license     YetiForce Public License 3.0
 * @author      Rafal Pospiech <r.pospiech@yetiforce.com>
 */
'use strict';

$.Class(
	'Settings_SLAPolicy_Edit_Js',
	{},
	{
		/**
		 * Register submit event
		 */
		registerSubmitEvent() {
			this.container.off('submit').on('submit', e => {
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
		loadConditionBuilderView(sourceModuleName = 'HelpDesk') {
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
			}).done(data => {
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
			this.sourceModuleSelect.on('change', e => {
				const sourceModuleName = this.sourceModuleSelect.val();
				this.loadConditionBuilderView(sourceModuleName);
				this.sourceModuleName = sourceModuleName;
			});
		},
		/**
		 * Registeer events
		 */
		registerEvents() {
			this.container = $('#EditView');
			this.sourceModuleName = this.container.find('[name="source_module"]').val();
			this.record = this.container.find('input[name="record"]').val();
			this.registerSourceModuleChange();
			this.loadConditionBuilderView(this.sourceModuleName);
			this.registerSubmitEvent();
			this.container.validationEngine(app.validationEngineOptions);
		}
	}
);
