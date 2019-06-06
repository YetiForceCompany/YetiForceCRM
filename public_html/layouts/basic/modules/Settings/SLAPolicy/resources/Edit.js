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
		 * Registeer events
		 */
		registerEvents() {
			this.container = $('#EditView');
			this.registerSubmitEvent();
			this.container.validationEngine(app.validationEngineOptions);
			this.sourceModuleName = this.container.find('input[name="sourceModuleName"]').val();
			this.conditionBuilder = new Vtiger_ConditionBuilder_Js(
				this.container.find('.js-condition-builder'),
				this.sourceModuleName
			);
			this.conditionBuilder.registerEvents();
		}
	}
);
