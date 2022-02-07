/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Dependencies_Credits_Js',
	{},
	{
		/**
		 *
		 * @returns {jQuery}
		 */
		getContainer: function () {
			if (!this.container) {
				this.container = $('.js-table-container');
			}
			return this.container;
		},
		/**
		 *
		 * @param contentData
		 * @returns {jQuery}
		 */
		registerDataTables: function (contentData) {
			$.extend($.fn.dataTable.defaults, {
				bPaginate: false,
				order: [],
				language: {
					sZeroRecords: app.vtranslate('JS_NO_RESULTS_FOUND'),
					sInfo: app.vtranslate('JS_S_INFO'),
					sInfoEmpty: app.vtranslate('JS_S_INFO_EMPTY'),
					sSearch: app.vtranslate('JS_SEARCH'),
					sEmptyTable: app.vtranslate('JS_NO_RESULTS_FOUND'),
					sInfoFiltered: app.vtranslate('JS_S_INFO_FILTERED'),
					sLoadingRecords: app.vtranslate('JS_LOADING_OF_RECORDS'),
					sProcessing: app.vtranslate('JS_LOADING_OF_RECORDS'),
					oAria: {
						sSortAscending: app.vtranslate('JS_S_SORT_ASCENDING'),
						sSortDescending: app.vtranslate('JS_S_SORT_DESCENDING')
					}
				}
			});
			return contentData.find('.dataTableWithRecords').DataTable();
		},
		/**
		 *
		 * @param container
		 */
		showMore: function (container) {
			container.find('.js-show-more').on('click', function (e) {
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					view: 'LibraryMoreInfo',
					type: $(this).attr('data-type'),
					libraryName: $(this).attr('data-library-name')
				}).done(function (response) {
					app.showModalWindow(response);
				});
			});
		},
		/**
		 *
		 * @param container
		 */
		showLicense: function (container) {
			container.find('.js-show-license').on('click', function (e) {
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					view: 'LibraryLicense',
					license: $(this).attr('data-license')
				}).done(function (response) {
					app.showModalWindow(response);
				});
			});
		},
		registerEvents: function () {
			var container = this.getContainer();
			this.registerDataTables(container);
			this.showMore(container);
			this.showLicense(container);
			if (app.getUrlVar('displayLicenseModal')) {
				container.find('tr[data-name="' + app.getUrlVar('displayLicenseModal') + '"] .js-show-license').click();
			}
		}
	}
);
