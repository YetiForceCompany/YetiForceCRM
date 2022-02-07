/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_AdminAccess_Index_Js',
	{},
	{
		/**
		 * Register DataTable
		 */
		registerDataTable: function (container) {
			let table = container.find('.js-data-table');
			let form = container.find('.js-filter-form');
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
					App.Fields.DateTime.register(form);
				}
			});
			table.find('thead input,thead select').each(function (i) {
				$(this)
					.on('change', function () {
						dataTable.column(i).search(this.value).draw();
					})
					.on('apply.daterangepicker', function () {
						setTimeout((_) => {
							dataTable.column(i).search(this.value).draw();
						}, 10);
					});
			});
		},
		/**
		 * Register tab events
		 * @param {jQuery} contentContainer
		 */
		registerTabEvents: function (contentContainer) {
			this.registerDataTable(contentContainer);
		},
		/**
		 * Load tab content
		 * @param {jQuery} contentContainer
		 */
		loadTabContent: function (contentContainer = $('.js-tab.active')) {
			let params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				view: 'Index',
				mode: contentContainer.attr('id')
			};
			let progress = jQuery.progressIndicator();
			AppConnector.request(params)
				.done((data) => {
					progress.progressIndicator({ mode: 'hide' });
					contentContainer.html(data);
					this.registerTabEvents(contentContainer);
					app.registerFormsEvents(contentContainer);
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
			this.loadTabContent();
			$('#tabs a[data-toggle="tab"]').on('shown.bs.tab', (_) => {
				this.loadTabContent();
			});
		}
	}
);
