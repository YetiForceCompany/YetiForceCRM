/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Groups_Index_Js',
	{},
	{
		/**
		 * Register DataTable
		 */
		registerDataTable: function () {
			let table = this.contentContainer.find('.js-data-table');
			let form = this.contentContainer.find('.js-filter-form');
			let dataTable = app.registerDataTables(table, {
				order: [],
				columnDefs: [{ targets: -1, orderable: false }],
				processing: true,
				serverSide: true,
				searching: false,
				orderCellsTop: true,
				fixedHeader: true,
				dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'p>>" + "<'row'<'col-sm-12'tr>>",
				ajax: {
					url: table.data('url'),
					type: 'POST',
					data: function (data) {
						$.extend(data, form.serializeFormData());
					}
				},
				initComplete: function () {
					App.Fields.Picklist.showSelect2ElementView(form.find('select.select2,select.select2noactive'));
				}
			});
			table.find('thead input,thead select').each(function (i) {
				$(this).on('change', function () {
					dataTable.column(i).search(this.value).draw();
				});
			});
			table.on('click', 'tr', function (e) {
				if ($(e.target).hasClass('js-no-link')) return;
				const element = $(e.currentTarget);
				if (element.find('.js-detail-button').length) {
					let recordUrl = element.find('.js-detail-button').data('recordurl');
					window.location.href = recordUrl;
				}
			});
		},
		/**
		 * Load tab content
		 */
		loadTabContent: function () {
			let params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				view: 'Index'
			};
			let progress = jQuery.progressIndicator();
			AppConnector.request(params)
				.done((data) => {
					progress.progressIndicator({ mode: 'hide' });
					this.contentContainer.html(data);
					this.registerDataTable();
					app.registerFormsEvents(this.contentContainer);
				})
				.fail((_) => {
					app.showNotify({ text: app.vtranslate('JS_ERROR'), type: 'error' });
					progress.progressIndicator({ mode: 'hide' });
				});
		},
		/**
		 * Register events
		 */
		registerEvents: function (e) {
			if (app.getViewName() === 'Index') {
				this.contentContainer = $('.contentsDiv');
				this.loadTabContent();
			}
		}
	}
);
