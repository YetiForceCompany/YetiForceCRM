/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'AppComponents_InterestsConflict_Js',

	{
		/**
		 * Register unlock tab events
		 * @param {jQuery} container
		 */
		registerUnlock: function (container) {
			let form = container.find('.js-filter-form');
			App.Fields.Date.registerRange(form);
			let table = $('#js-unlock-table');
			if (table.hasClass('dataTable')) {
				table.DataTable().clear().destroy();
			}
			let dt = app.registerDataTables(table, {
				processing: true,
				serverSide: true,
				paging: true,
				searching: false,
				lengthChange: false,
				pageLength: 15,
				ajax: {
					url: 'index.php?module=AppComponents&action=InterestsConflict&mode=getUnlock',
					type: 'POST',
					data: function (data) {
						data = $.extend(data, form.serializeFormData());
					}
				},
				order: [[0, 'desc']],
				columns: [
					{ data: 'date_time' },
					{ data: 'user_id' },
					{
						data: 'status',
						render: function (data, type, row, meta) {
							switch (data) {
								case 1:
									return `<span class="fas fa-check text-success" title="${app.vtranslate(
										'JS_UNLOCK_STATUS_ACCEPTED'
									)}"></span>`;
								case 2:
									return `<span class="fas fa-times text-danger" title="${app.vtranslate(
										'JS_UNLOCK_STATUS_REJECTED'
									)}"></span>`;
								case 3:
									return `<span class="fas fa-slash text-dark" title="${app.vtranslate(
										'JS_UNLOCK_STATUS_CANCELED'
									)}"></span>`;
								default:
									return `<span class="fas fa-question text-warning" title="${app.vtranslate(
										'JS_UNLOCK_STATUS_NEW'
									)}"></span>`;
							}
						}
					},
					{
						data: 'related'
					},
					{
						orderable: false,
						data: 'comment'
					},
					{
						orderable: false,
						data: function (row) {
							let action = row['info']
								? `<span class="fas fa-info-circle text-primary js-popover-tooltip mr-2" data-content="${row['info']}"></span>`
								: '';
							if (row['status'] === 0) {
								action += `<button type="button" class="btn btn-success btn-sm js-update" data-id="${
									row['id']
								}" data-status="1" title="${app.vtranslate(
									'BTN_UNLOCK_STATUS_ACTION_ACCEPT'
								)}" data-js="click"><span class="fas fa-check"></span></button><button type="button" class="btn btn-danger btn-sm ml-2 js-update" data-id="${
									row['id']
								}" data-status="2" title="${app.vtranslate(
									'BTN_UNLOCK_STATUS_ACTION_REJECT'
								)}" data-js="click"><span class="fas fa-times"></span></button>`;
							}
							return action;
						},
						defaultContent: ''
					}
				]
			});
			container.find('input,select').on('change', function () {
				dt.ajax.reload();
			});
			table.off('click', '.js-update').on('click', '.js-update', function () {
				AppConnector.request({
					module: 'AppComponents',
					action: 'InterestsConflict',
					mode: 'updateUnlockStatus',
					id: this.dataset.id,
					status: this.dataset.status
				})
					.done(function () {
						app.showNotify({
							text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
							type: 'success'
						});
						dt.ajax.reload(null, false);
					})
					.fail(function () {
						app.showNotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
					});
			});
		},
		/**
		 * Register confirmations tab events
		 * @param {jQuery} container
		 */
		registerConfirmations: function (container) {
			let form = container.find('.js-filter-form');
			App.Fields.Date.registerRange(form);
			let table = $('#js-confirm-table');
			if (table.hasClass('dataTable')) {
				table.DataTable().clear().destroy();
			}
			let dt = app.registerDataTables(table, {
				processing: true,
				serverSide: true,
				paging: true,
				searching: false,
				lengthChange: false,
				pageLength: 15,
				ajax: {
					url: 'index.php?module=AppComponents&action=InterestsConflict&mode=getConfirm',
					type: 'POST',
					data: function (data) {
						data = $.extend(data, form.serializeFormData());
					}
				},
				order: [[0, 'desc']],
				columns: [
					{ data: 'date_time' },
					{ data: 'user' },
					{
						data: 'status',
						render: function (data, type, row, meta) {
							switch (data) {
								case 0:
									return '<span class="fas fa-times text-success"></span>';
								case 1:
									return '<span class="fas fa-check text-danger"></span>';
								case 2:
									return '<span class="fas fa-slash text-dark"></span>';
								default:
									return '<span class="fas fa-question"></span>';
							}
						}
					},
					{
						data: 'related'
					},
					{
						class: 'details-control',
						orderable: false,
						data: function (row) {
							let action = row['info']
								? `<span class="fas fa-info-circle text-primary js-popover-tooltip mr-2" data-content="${row['info']}"></span>`
								: '';
							if (row['db'] !== 'base') {
								return action;
							}
							action += `<button type="button" class="btn btn-primary btn-sm js-update" data-user="${
								row['user_id']
							}" data-related="${row['related_id']}"  title="${app.vtranslate(
								'JS_INTERESTS_CONFLICT_SET_CANCELED'
							)}"><span class="fas fa-minus"></span></button>`;
							return action;
						},
						defaultContent: ''
					}
				]
			});
			container.find('input,select').on('change', function () {
				dt.ajax.reload();
			});
			table.off('click', '.js-update').on('click', '.js-update', function () {
				app.showConfirmModal({
					title: app.vtranslate('JS_ENTER_A_REASON'),
					showDialog: true,
					multiLineDialog: true,
					confirmedCallback: (notice, value) => {
						AppConnector.request({
							module: 'AppComponents',
							action: 'InterestsConflict',
							mode: 'updateConfirmStatus',
							id: this.dataset.user,
							baseRecord: this.dataset.related,
							comment: value
						})
							.done(function () {
								app.showNotify({
									text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
									type: 'success'
								});
								dt.ajax.reload(null, false);
							})
							.fail(function () {
								app.showNotify({
									text: app.vtranslate('JS_ERROR'),
									type: 'error'
								});
							});
					}
				});
			});
		}
	},
	{
		/**
		 * Register events
		 */
		registerEvents: function () {
			let container = $('.contentsDiv');
			switch (CONFIG['mode']) {
				case 'unlock':
					AppComponents_InterestsConflict_Js.registerUnlock(container);
					break;
				case 'confirm':
					AppComponents_InterestsConflict_Js.registerConfirmations(container);
					break;
			}
		}
	}
);
