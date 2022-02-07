/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Base_SortOrderModal_JS',
	{},
	{
		/**
		 * Modal container
		 */
		container: false,
		/**
		 * Source container
		 */
		sourceContainer: false,
		/**
		 * Modal ID
		 */
		source: false,
		/**
		 * Load data
		 */
		loadData: function () {
			let orderBy = this.sourceContainer.find('#orderBy').val();
			orderBy = orderBy ? JSON.parse(orderBy) : [];
			if (orderBy && Object.keys(orderBy).length) {
				let baseOrderBy = this.container.find('.js-base-element');
				for (let field in orderBy) {
					if (!baseOrderBy.find("option[value='" + field + "']").length) {
						continue;
					}
					let element = this.addRow();
					element.find('select.js-orderBy').val(field).change();
					let sortIcon = element.find('.js-sort-icon-active');
					if (orderBy[field] && sortIcon.data('val') != orderBy[field]) {
						sortIcon.trigger('click');
					}
				}
			} else {
				this.addRow();
			}
		},
		/**
		 * Add new row
		 */
		addRow: function () {
			let sortContainer = this.container.find('.js-base-element').clone(true, true).removeClass('js-base-element');
			this.container.find('.js-sort-container').append(sortContainer);
			App.Fields.Picklist.showSelect2ElementView(sortContainer.find('select'));
			return sortContainer.removeClass('d-none');
		},
		/**
		 * Register list events
		 */
		registerListEvents: function () {
			this.container.find('.js-add').on('click', (e) => {
				this.addRow();
			});
			this.container.find('.js-clear').on('click', (e) => {
				$(e.currentTarget).closest('.js-sort-container_element').remove();
			});
			this.container.find('.js-sort-order-button').on('click', (e) => {
				let element = $(e.currentTarget).closest('.js-sort-container_element');
				element.find('.js-sort-icon').toggleClass('d-none js-sort-icon-active');
				element.find('.js-sort-order').val(element.find('.js-sort-icon-active').data('val'));
			});
			this.container.find('.js-modal__save').on('click', (e) => {
				e.preventDefault();
				this.sourceContainer.find('.js-list-reload').trigger('click', { orderby: this.getSortData() });
				app.hideModalWindow(null, this.source);
			});
		},
		/**
		 * Gets sort data
		 */
		getSortData: function () {
			let sortData = {};
			this.container.find('.js-sort-container_element:not(.js-base-element)').each(function () {
				let orderBy = $(this).find('.js-orderBy').val();
				if (orderBy) {
					sortData[orderBy] = $(this).find('.js-sort-order').val();
				}
			});
			return sortData;
		},
		/**
		 * Gets basic container
		 */
		getSourceContainer: function () {
			return $('[data-modalid=' + this.source + ']').closest(
				'.listViewContentDiv,.relatedContainer,.js-main-container'
			);
		},
		/**
		 * Register modal events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			this.container = modalContainer;
			this.source = modalContainer.closest('.js-modal-container').attr('id');
			this.sourceContainer = this.getSourceContainer();
			this.registerListEvents();
			this.loadData();
		}
	}
);
