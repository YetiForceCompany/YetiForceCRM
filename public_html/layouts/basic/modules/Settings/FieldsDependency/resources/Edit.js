/**
 * Fields dependency edit view js class
 *
 * @package     Settings.Edit
 *
 * @description Fields dependency edit view scripts
 * @license     YetiForce Public License 5.0
 * @author      Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
'use strict';

$.Class(
	'Settings_FieldsDependency_Edit_Js',
	{},
	{
		/**
		 * Register submit event
		 */
		registerSubmitEvent() {
			this.container.off('submit').on('submit', (e) => {
				if ($(e.currentTarget).validationEngine('validate')) {
					this.container.find('input[name="conditions"]').val(JSON.stringify(this.conditionBuilder.getConditions()));
					return true;
				}
				e.preventDefault();
				e.stopPropagation();
				return false;
			});
		},

		/**
		 * Load condition builder
		 */
		loadConditionBuilderView() {
			this.conditionBuilder = new Vtiger_ConditionBuilder_Js(
				this.container.find('.js-condition-builder'),
				this.sourceModule
			);
			this.conditionBuilder.registerEvents();
		},
		/**
		 * Register source module change
		 */
		registerSourceModuleChange() {
			let select = this.container.find('#inputSourceModule');
			let blocks = this.container.find('.js-dynamic-blocks');
			select.on('change', (e) => {
				this.sourceModule = select.find('option:selected').data('module');
				blocks.html('');
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					view: 'Edit',
					mode: 'dynamic',
					selectedModule: this.sourceModule
				}).done((data) => {
					blocks.html(data);
					App.Fields.Picklist.changeSelectElementView(blocks);
					this.loadConditionBuilderView();
				});
			});
		},
		registerAddDependField() {
			this.container.find('.js-dependence-add-field').on('click', (e) => {
				let progress = $.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				this.sourceModule = select.find('option:selected').data('module');
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					view: 'FieldDependence',
					mode: 'dependencyRow',
					selectedModule: this.sourceModule
				}).done((data) => {
					progress.progressIndicator({ mode: 'hide' });
					blocks.html(data);
					App.Fields.Picklist.changeSelectElementView(blocks);
					this.loadConditionBuilderView();
				});
			});
		},
		/**
		 * Register events
		 */
		registerEvents() {
			this.container = $('#EditView');
			this.sourceModule = this.container.find('#inputSourceModule').data('module');
			this.record = this.container.find('input[name="record"]').val();
			this.registerSourceModuleChange();
			this.loadConditionBuilderView();
			this.registerSubmitEvent();
			this.registerAddDependField();
			this.container.validationEngine(app.validationEngineOptions);
		}
	}
);
