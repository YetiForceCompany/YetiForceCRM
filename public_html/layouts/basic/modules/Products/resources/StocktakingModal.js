/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.Products_StocktakingModal_JS = class {
	/**
	 * Show step by name.
	 * @param {string} name
	 */
	showStep(name) {
		this.container.find('.js-step').addClass('d-none');
		this.container.find(`.js-step[data-step="${name}"]`).removeClass('d-none');
	}
	/**
	 * Register analyze file events.
	 */
	registerAnalyzeFile() {
		let form = this.container.find('form');
		form.validationEngine(app.validationEngineOptions);
		this.container.find('.js-send-file').on('click', () => {
			if (!form.validationEngine('validate')) {
				return;
			}
			let formData = new FormData(form[0]);
			formData.append('module', 'Products');
			formData.append('action', 'StocktakingModal');
			formData.append('mode', 'analyzeFile');
			let progress = $.progressIndicator({ position: 'html', blockInfo: { enabled: true } });
			AppConnector.request({
				url: 'index.php',
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false
			})
				.done((response) => {
					this.showStep('selectColumn');
					this.container.find('.js-encoding').val(response.result.encoding);
					this.container.find('.js-count').val(response.result.count);
					this.container.find('.js-randomKey').val(response.result.randomKey);
					let eanField = this.container.find('#skuColumnSeq');
					let qtyField = this.container.find('#qtyColumnSeq');
					$.each(response.result.column, function (index, value) {
						eanField.append(`<option value="${index}">${value}</option>`);
						qtyField.append(`<option value="${index}">${value}</option>`);
					});
					qtyField.val(1);
					App.Fields.Picklist.showSelect2ElementView(qtyField);
					App.Fields.Picklist.showSelect2ElementView(eanField);
					progress.progressIndicator({ mode: 'hide' });
				})
				.fail(() => {
					progress.progressIndicator({ mode: 'hide' });
					app.showNotify({
						text: app.vtranslate('JS_ERROR'),
						type: 'error'
					});
				});
		});
	}
	/**
	 * Register compare stock levels.
	 */
	registerCompare() {
		let form = this.container.find('form');
		this.container.find('.js-compare').on('click', () => {
			let formData = form.serializeFormData();
			formData['module'] = 'Products';
			formData['action'] = 'StocktakingModal';
			formData['mode'] = 'compare';
			let progress = $.progressIndicator({ position: 'html', blockInfo: { enabled: true } });
			AppConnector.request(formData)
				.done((response) => {
					this.showStep('showCompare');
					this.container.find('.js-entries-update').val(response.result.update);
					this.container.find('.js-entries-no-update').val(response.result.same);
					this.container.find('.js-entries-not-found').val(response.result.counterNotFound);
					this.container.find('.js-list-entries-not-found').val(response.result.notFound);
					this.toUpdate = response.result.toUpdate;
					if (response.result.update === 0) {
						this.container.find('.js-import').addClass('d-none');
					} else {
						if (formData['storage'] != 0) {
							this.container.find('.js-record-name').removeClass('d-none');
						}
					}
					progress.progressIndicator({ mode: 'hide' });
				})
				.fail(() => {
					progress.progressIndicator({ mode: 'hide' });
					app.showNotify({
						text: app.vtranslate('JS_ERROR'),
						type: 'error'
					});
				});
		});
	}
	/**
	 * Register importing stock differences.
	 */
	registerImport() {
		let form = this.container.find('form');
		this.container.find('.js-import').on('click', () => {
			let formData = form.serializeFormData();
			if (formData['storage'] != 0 && !form.validationEngine('validate')) {
				return;
			}
			formData['module'] = 'Products';
			formData['action'] = 'StocktakingModal';
			formData['mode'] = 'import';
			formData['records'] = this.toUpdate;
			let progress = $.progressIndicator({ position: 'html', blockInfo: { enabled: true } });
			AppConnector.request(formData)
				.done((response) => {
					this.showStep('showSummary');
					if (response.result.product) {
						this.container.find('.js-imported').removeClass('d-none');
						this.container.find('.js-imported-counter').val(response.result.product);
					}
					if (response.result.igin) {
						this.container
							.find('.js-btn-igin')
							.removeClass('d-none')
							.attr('href', 'index.php?module=IGIN&view=Detail&record=' + response.result.igin);
					}
					if (response.result.iidn) {
						this.container
							.find('.js-btn-iidn')
							.removeClass('d-none')
							.attr('href', 'index.php?module=IIDN&view=Detail&record=' + response.result.iidn);
					}
					if (response.result.igin || response.result.iidn) {
						this.container.find('.js-alert').removeClass('d-none');
					}
					progress.progressIndicator({ mode: 'hide' });
				})
				.fail(() => {
					progress.progressIndicator({ mode: 'hide' });
					app.showNotify({
						text: app.vtranslate('JS_ERROR'),
						type: 'error'
					});
				});
		});
	}
	/**
	 * Register modal events.
	 * @param {jQuery} modalContainer
	 */
	registerEvents(modalContainer) {
		this.container = modalContainer;
		this.registerAnalyzeFile();
		this.registerCompare();
		this.registerImport();
	}
};
