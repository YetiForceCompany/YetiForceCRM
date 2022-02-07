/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_AutomaticAssignment_Edit_Js',
	{},
	{
		container: false,
		advanceFilterInstance: false,
		conditionBuilders: [],
		setContainer: function (container) {
			this.container = container;
			return this.container;
		},
		getContainer: function () {
			if (this.container == false) {
				this.container = jQuery('div.contentsDiv form');
			}
			return this.container;
		},
		registerBlockToggleEvent() {
			this.container.on('click', '.blockHeader', function (e) {
				const target = $(e.target);
				if (
					target.is('input') ||
					target.is('button') ||
					target.parents().is('button') ||
					target.hasClass('js-stop-propagation') ||
					target.parents().hasClass('js-stop-propagation')
				) {
					return false;
				}
				const blockHeader = $(e.currentTarget);
				const blockContents = blockHeader.next();
				const iconToggle = blockHeader.find('.iconToggle');
				if (blockContents.hasClass('d-none')) {
					blockContents.removeClass('d-none');
					iconToggle.removeClass(iconToggle.data('hide')).addClass(iconToggle.data('show'));
				} else {
					blockContents.addClass('d-none');
					iconToggle.removeClass(iconToggle.data('show')).addClass(iconToggle.data('hide'));
				}
			});
		},
		/**
		 * Register submit event
		 */
		registerSubmitEvent() {
			this.container.off('submit').on('submit', (e) => {
				e.preventDefault();
				this.container.find('.js-toggle-panel').find('.js-block-content').removeClass('d-none');
				if ($(e.currentTarget).validationEngine('validate')) {
					document.progressLoader = $.progressIndicator({
						message: app.vtranslate('JS_SAVE_LOADER_INFO'),
						position: 'html',
						blockInfo: {
							enabled: true
						}
					});
					for (var key in this.conditionBuilders) {
						this.container
							.find(`input[name="${key}"]`)
							.val(JSON.stringify(this.conditionBuilders[key].getConditions()));
					}

					this.preSaveValidation().done((response) => {
						if (response === true) {
							let formData = this.container.serializeFormData();
							app
								.saveAjax('save', [], formData)
								.done(function (data) {
									if (data.result && data.result.success) {
										Settings_Vtiger_Index_Js.showMessage({ text: app.vtranslate('JS_SAVE_SUCCESS') });
										window.location.href = data.result.url;
									} else {
										document.progressLoader.progressIndicator({ mode: 'hide' });
										app.showNotify({ text: app.vtranslate('JS_ERROR'), type: 'error' });
									}
								})
								.fail(function () {
									document.progressLoader.progressIndicator({ mode: 'hide' });
									app.showNotify({ text: app.vtranslate('JS_ERROR'), type: 'error' });
								});
						} else {
							document.progressLoader.progressIndicator({ mode: 'hide' });
						}
					});
				}
				e.stopPropagation();
				return false;
			});
		},
		/**
		 * PreSave validation
		 */
		preSaveValidation: function () {
			const aDeferred = $.Deferred();
			let formData = new FormData(this.container[0]);
			formData.append('mode', 'preSaveValidation');
			AppConnector.request({
				async: false,
				url: 'index.php',
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false
			})
				.done((data) => {
					let response = data.result;
					for (let i in response) {
						if (response[i].result !== true) {
							app.showNotify({
								text: response[i].message ? response[i].message : app.vtranslate('JS_ERROR'),
								type: 'error'
							});
							if (response[i].hoverField != undefined) {
								this.container.find('[name="' + response[i].hoverField + '"]').focus();
							}
						}
					}
					aDeferred.resolve(data.result.length <= 0);
				})
				.fail((textStatus, errorThrown) => {
					app.showNotify({ text: app.vtranslate('JS_ERROR'), type: 'error' });
					app.errorLog(textStatus, errorThrown);
					aDeferred.resolve(false);
				});

			return aDeferred.promise();
		},
		/**
		 * Load condition builder
		 *
		 * @param   {Integer}  sourceTabId
		 */
		loadConditionBuilderView(sourceTabId) {
			if (!sourceTabId) {
				this.container.find('.js-condition-builder-container').html('');
				return false;
			}
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
				sourceTabId: sourceTabId
			}).done((data) => {
				progress.progressIndicator({ mode: 'hide' });
				this.container.find('.js-condition-builder-container').html(data);
				this.registerConditionBuilder();
			});
		},
		registerConditionBuilder() {
			let sourceModuleName = this.sourceModuleSelect.val();
			if (sourceModuleName) {
				this.container.find('.js-condition-builder').each((_, e) => {
					let conditionBuilder = new Vtiger_ConditionBuilder_Js($(e), sourceModuleName);
					conditionBuilder.registerEvents();
					let name = $(e).closest('.js-field-container').find('.js-condition-value').attr('name');
					this.conditionBuilders[name] = conditionBuilder;
				});
			}
		},
		/**
		 * Register source module change
		 */
		registerSourceModuleChange() {
			this.sourceModuleSelect = this.container.find('select[name="tabid"]');
			this.sourceTabId = this.sourceModuleSelect.val();
			this.sourceModuleSelect.on('change', (e) => {
				let value = $(e.currentTarget).val();
				if (this.sourceTabId !== value && this.sourceTabId !== null) {
					this.sourceTabId = value;
					this.loadConditionBuilderView(this.sourceTabId);
				}
			});
		},
		registerMethodChange() {
			this.container.find('[name="method"]').on('change', (e) => {
				let countingCriteria = this.container.find('[name="record_limit_conditions"]').closest('.js-field-container');
				if ($(e.currentTarget).val() == 1) {
					countingCriteria.addClass('d-none');
				} else {
					countingCriteria.removeClass('d-none');
				}
			});
		},
		registerBasicEvents: function () {
			this.container.validationEngine(app.validationEngineOptionsForRecord);
			this.registerSubmitEvent();
			this.registerBlockToggleEvent();
			this.registerSourceModuleChange();
			this.registerConditionBuilder();
		},
		registerEvents: function () {
			this.setContainer($('.contentsDiv form'));
			this.registerBasicEvents();
			app.showPopoverElementView(this.container.find('.js-popover-tooltip'));
		}
	}
);
