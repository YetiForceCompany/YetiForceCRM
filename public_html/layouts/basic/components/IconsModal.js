/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.AppComponents_IconsModal_Js = class {
	/**
	 * Set page
	 * @param {int} page
	 */
	setPage(page = 1) {
		let min = (page - 1) * this.pageLimit;
		let max = page * this.pageLimit;
		let elements = this.container.find('.js-icon-item:not(.not-match)');
		this.container.find('.js-icon-item').addClass('d-none');
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
		this.container.find('.js-icon-search').on('keyup', (e) => {
			this.container.find('.js-icon-item.not-match').removeClass('not-match');
			let value = e.currentTarget.value.toString().replace('"', '').toLowerCase();
			if (value.length) {
				this.container.find(`.js-icon-item:not([data-icon-search*="${value}"])`).addClass('not-match');
			}
			this.setPage(1);
		});
	}
	/**
	 * Register events
	 */
	registerEvents(container) {
		this.container = container;
		this.pageLimit = parseInt(this.container.find('.js-page-size').val());
		this.setPage();
		this.registerPaginationEvents();
		this.registerSearchIcon();
	}
};
