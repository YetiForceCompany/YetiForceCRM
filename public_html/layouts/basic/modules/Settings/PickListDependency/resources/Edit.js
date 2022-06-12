/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
window.Settings_PickListDependency_Edit_Js = class {
	conditionBuilders = [];

	/**
	 * Get progress inducator element
	 * @param {jQuery} block
	 * @returns
	 */
	getProgressIndicator(block) {
		let params = { position: 'html', blockInfo: { enabled: true } };
		if (typeof block !== 'undefined') {
			params.blockInfo.elementToBlock = block;
		}
		return $.progressIndicator(params);
	}

	/**
	 * Register basic events
	 */
	registerBasicEvents() {
		this.container.on('change', 'select[name="tabid"]', (e) => {
			if (!e.currentTarget.value) {
				this.fieldsContainer.html('');
				this.graphContainer.html('');
			} else {
				this.getView({ mode: 'dependentFields', ...this.getDefaultParams() }).done((data) => {
					this.fieldsContainer.html(data);
					this.moduleField = this.fieldsContainer.find('select[name="tabid"]');
					App.Fields.Picklist.showSelect2ElementView(this.fieldsContainer.find('select'));
					this.graphContainer.html('');
				});
			}
		});

		this.fieldsContainer.on('change', 'select[name="source_field"]', () => {
			this.graphContainer.html('');
			this.validate(form).done(() => {
				this.getView({ mode: 'getDependencyGraph', ...this.getDefaultParams(), ...this.form.serializeFormData() }).done(
					(data) => {
						this.graphContainer.html(data);
						App.Fields.Picklist.showSelect2ElementView(this.graphContainer.find('select'));
						this.registerConditionBuilder();
					}
				);
			});
		});

		this.container.on('click', '.js-pd-save', () => {
			for (var key in this.conditionBuilders) {
				this.container.find(`input[name="${key}"]`).val(JSON.stringify(this.conditionBuilders[key].getConditions()));
			}
			this.validate(form).done(() => {
				this.saveAjax({ mode: 'save', ...this.form.serializeFormData() });
			});
		});
	}
	/**
	 * Validate
	 * @returns bool
	 */
	validate() {
		let aDeferred = $.Deferred();
		let result = this.form.validationEngine('validate');
		if (result) {
			this.saveAjax({
				mode: 'preSaveValidation',
				...this.getDefaultParams(),
				...this.form.serializeFormData()
			})
				.done((response) => {
					let errors = response.result;
					for (let i in errors) {
						if (errors[i].result !== true) {
							app.showNotify({
								text: errors[i].message ? errors[i].message : app.vtranslate('JS_ERROR'),
								type: 'error',
								delay: 3000,
								hide: true
							});
						}
					}
					errors.length <= 0 ? aDeferred.resolve(true) : aDeferred.reject(false);
				})
				.fail((response) => {
					aDeferred.reject(response);
				});
		} else {
			aDeferred.reject(result);
		}

		return aDeferred.promise();
	}

	/**
	 * Save data
	 * @param {Object} formData
	 */
	saveAjax(formData) {
		let progress = this.getProgressIndicator();
		let aDeferred = $.Deferred();
		app
			.saveAjax('', [], formData)
			.done((response) => {
				if (response && typeof response === 'string') {
					response = JSON.parse(response);
				}
				if (response.result && response.result.url) {
					window.location.href = response.result.url;
				}
				progress.progressIndicator({ mode: 'hide' });
				aDeferred.resolve(response);
			})
			.fail((error) => {
				app.showNotify({ text: app.vtranslate('JS_ERROR'), type: 'error' });
				progress.progressIndicator({ mode: 'hide' });
				aDeferred.reject(error);
			});

		return aDeferred.promise();
	}
	/**
	 * Get default params
	 * @returns {Object}
	 */
	getDefaultParams() {
		return {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			view: 'IndexAjax',
			tabid: this.moduleField.val()
		};
	}

	/**
	 * Get view
	 * @param {Objcet} params
	 * @returns html
	 */
	getView(params) {
		let aDeferred = $.Deferred();
		let progressIndicatorElement = $.progressIndicator({ position: 'html', blockInfo: { enabled: true } });
		AppConnector.request(params)
			.done(function (data) {
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
				aDeferred.resolve(data);
			})
			.fail(function (error) {
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
				app.showNotify({ text: app.vtranslate('JS_ERROR'), type: 'error' });
				aDeferred.reject(error);
			});

		return aDeferred.promise();
	}
	/**
	 * Register condition builder
	 */
	registerConditionBuilder() {
		let sourceModuleName = this.moduleField.val();
		if (sourceModuleName) {
			let sourceField = this.fieldsContainer.find('[name="source_field"]').val();
			this.container.find('.js-condition-builder').each((_, e) => {
				let conditionBuilder = new Vtiger_ConditionBuilder_Js($(e), { sourceModuleName, sourceField });
				conditionBuilder.registerEvents();
				let name = $(e).closest('.js-field-container').find('.js-condition-value').attr('name');
				this.conditionBuilders[name] = conditionBuilder;
			});
		}
	}
	/**
	 * Register toggle block
	 */
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
	}
	/**
	 * Register events
	 */
	registerEvents() {
		this.container = $('.js-picklist-dependent-container');
		this.form = this.container.find('form');
		this.moduleField = this.container.find('select[name="tabid"]');
		this.fieldsContainer = this.container.find('.js-dependent-fields');
		this.graphContainer = this.container.find('.js-dependency-tables-container');
		this.form.validationEngine(app.validationEngineOptions);
		this.registerBasicEvents();
		this.registerConditionBuilder();
		this.registerBlockToggleEvent();
	}
};
