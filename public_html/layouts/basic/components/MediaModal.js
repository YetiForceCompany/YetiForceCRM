/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.AppComponents_MediaModal_Js = class {
	/**
	 * Set page
	 * @param {int} page
	 */
	setPage(page = 1) {
		let min = (page - 1) * this.pageLimit;
		let max = page * this.pageLimit;
		let tab = this.getActiveTab();
		let elements = tab.find('.js-icon-item:not(.not-match)');
		tab.find('.js-icon-item').addClass('d-none');
		elements.slice(min, max).removeClass('d-none');
		this.container.find('.js-page--set').data('id', page).find('.page-link').text(page);
		if (elements.eq(max + 1).length) {
			this.container.find('.js-page--next,.js-page--last').removeClass('disabled');
		} else {
			this.container.find('.js-page--next,.js-page--last').addClass('disabled');
		}
		if (min !== 0 && elements.eq(min - 1).length) {
			this.container.find('.js-page--previous,.js-page--first').removeClass('disabled');
		} else {
			this.container.find('.js-page--previous,.js-page--first').addClass('disabled');
		}
	}
	/**
	 * Get active tab
	 * @returns
	 */
	getActiveTab() {
		return this.container.find('.js-tab.active');
	}
	/**
	 * Register pagination events
	 */
	registerPaginationEvents() {
		this.container.on('click', '.js-page--next:not(.disabled)', () => {
			let currentPage = this.container.find('.js-page--set.active').data('id');
			this.setPage(parseInt(currentPage) + 1);
		});
		this.container.on('click', '.js-page--previous:not(.disabled)', () => {
			let currentPage = this.container.find('.js-page--set.active').data('id');
			if (currentPage !== 1) {
				this.setPage(currentPage - 1);
			}
		});
		this.container.on('click', '.js-page--first:not(.disabled)', () => {
			this.setPage(1);
		});
		this.container.on('click', '.js-page--last:not(.disabled)', () => {
			let elements = this.container.find('.js-icon-item:not(.not-match)');
			let totalPages = Math.ceil(elements.length / this.pageLimit);
			this.setPage(totalPages);
		});
	}
	/**
	 * Register Icon Search
	 */
	registerSearchIcon() {
		this.searchField.on('keyup', (e) => {
			let tab = this.getActiveTab();
			tab.find('.js-icon-item.not-match').removeClass('not-match');
			let value = e.currentTarget.value.toString().replace('"', '').toLowerCase();
			if (value.length) {
				tab.find(`.js-icon-item:not([data-icon-search*="${value}"])`).addClass('not-match');
			}
			this.setPage(1);
		});
	}
	/**
	 * Remove image item
	 * @param {Event} e
	 */
	removeItem(e) {
		let url = e.currentTarget.dataset.url;
		let progressIndicatorElement = $.progressIndicator({ position: 'html', blockInfo: { enabled: true } });
		AppConnector.request({
			async: false,
			url: url,
			type: 'POST',
			data: {}
		})
			.done((data) => {
				if (data.result) {
					e.currentTarget.closest('.js-icon-item').remove();
				}
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
			})
			.fail(function (error, err) {
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
				app.errorLog(error, err);
			});
	}
	registerImageEvents() {
		let fileInput = this.container.find('.js-icon-file');
		if (fileInput.length) {
			let fieldInfo = fileInput.data('fieldinfo') || {};
			this.container.find('.js-image-add').on('click', () => {
				fileInput.trigger('click');
			});
			let file = App.File.register(fileInput, {
				fileupload: {
					dataType: 'json',
					replaceFileInput: false,
					autoUpload: false,
					done: (e, data) => {
						const attach = data.result.result.attach;
						attach.forEach((fileAttach) => {
							if (typeof fileAttach.key === 'undefined') {
								return file.uploadError(e, data);
							}
							file.filesActive--;
							this.addImage(fileAttach);
						});
						file.fileInput.val('');
					}
				},
				formats: fieldInfo.formats,
				limit: fieldInfo.limit,
				maxFileSize: fieldInfo.maxFileSize,
				maxFileSizeDisplay: fieldInfo.maxFileSizeDisplay || ''
			});
		}

		this.container.find('.js-image-remove').on('click', (e) => {
			e.stopPropagation();
			app.showConfirmModal({
				text: app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE'),
				confirmedCallback: () => {
					this.removeItem(e);
				}
			});
		});
	}
	/**
	 * Add image to container
	 * @param {Object} data
	 */
	addImage(data) {
		const item = document.createElement('article');
		item.setAttribute('class', 'w-100 position-relative js-icon-item');
		item.setAttribute('data-icon-search', data.name.toLowerCase());
		item.setAttribute('data-name', data.name);
		item.setAttribute('data-type', 'image');
		item.setAttribute('data-src', data.src);
		item.setAttribute('data-key', data.key);

		const button = document.createElement('button');
		button.setAttribute('class', 'btn btn-light w-100 h-100');

		const image = document.createElement('img');
		image.setAttribute('class', 'icon-img--list');
		image.setAttribute('src', data.src);

		const span = document.createElement('span');
		span.setAttribute('class', 'c-grid-item--signature u-fs-xs');
		span.appendChild(document.createTextNode(data.name));

		button.appendChild(image);
		button.appendChild(span);
		item.appendChild(button);

		this.getActiveTab().find('#icons-results').append(item);
	}
	/**
	 * Register events
	 */
	registerEvents(container) {
		this.container = container;
		this.pageLimit = parseInt(this.container.find('.js-page-size').val());
		this.searchField = this.container.find('.js-icon-search');
		this.setPage();
		this.registerPaginationEvents();
		this.registerSearchIcon();
		this.container.on('shown.bs.tab', 'a[data-toggle="tab"]', () => {
			this.searchField.trigger('keyup');
		});
		this.registerImageEvents();
	}
};
