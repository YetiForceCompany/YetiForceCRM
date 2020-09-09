/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_AdminAccess_Index_Js',
	{},
	{
		/**
		 * Register DataTable
		 */
		registerDataTable: function () {
			let table = $('.js-data-table');
			let form = $('.js-filter-form');
			let selectFields = form.find('select.select2');
			if (selectFields.length) {
				selectFields.select2('destroy');
			}
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
					url: 'index.php?module=AdminAccess&parent=Settings&action=GetData&mode=access',
					type: 'POST',
					data: function (data) {
						$.extend(data, form.serializeFormData());
					}
				},
				initComplete: function () {
					App.Fields.Picklist.showSelect2ElementView(
						form.find('select.select2,select.select2noactive')
					);
				}
			});
			table.find('thead input,thead select').each(function (i) {
				$(this).on('change', function () {
					dataTable.column(i).search(this.value).draw();
				});
			});
		},

		/**
		 * Register events
		 */
		registerEvents: function () {
			this.registerDataTable();
		}
	}
);
