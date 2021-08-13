/* {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Vtiger_Kanban_Js',
	{},
	{
		/**
		 * Get params
		 */
		getParams: function () {
			const params = [];
			this.page.find('.js-params').each(function () {
				let element = $(this);
				params[element.attr('name')] = element.val();
			});
			return $.extend(
				{
					module: app.getModuleName(),
					view: 'Kanban',
					board: this.page.find('.js-board-tab.active').data('id')
				},
				params
			);
		},
		loadKanban: function () {
			const params = this.getParams(),
				urlParams = Object.assign({}, params);
			delete urlParams.orderBy;
			app.changeUrl(urlParams);
			AppConnector.request(params)
				.done((responseData) => {
					this.container.html(responseData);
					this.registerSortable();
				})
				.fail((status, errorThrown) => {
					app.showNotify({
						title: app.vtranslate('JS_ERROR'),
						text: errorThrown,
						type: 'error'
					});
				});
		},
		registerSortable: function () {
			const boards = this.container.find('.js-kanban-records');
			boards.sortable({
				containment: this.container,
				items: boards.find('.js-kanban-record'),
				cancel: '.js-kanban-disabled',
				connectWith: boards,
				placeholder: 'c-kanban__highlight',
				revert: true,
				tolerance: 'pointer',
				cursor: 'move',
				update: (e, ui) => {
					if (ui.sender == null) {
						const records = ui.item.closest('.js-kanban-records');
						Vtiger_Edit_Js.saveAjax({
							record: ui.item.data('id'),
							field: records.data('field'),
							value: records.data('value')
						})
							.done(() => {
								this.loadKanban();
							})
							.fail(function (error, err) {
								app.errorLog(error, err);
							});
					}
				},
				start: (e, ui) => {
					ui.placeholder.height(ui.helper.outerHeight());
				}
			});
		},
		/**
		 * Registers Kanban view events.
		 */
		registerEvents: function () {
			this.page = $('#centerPanel');
			this.container = $('.js-kanban-container');
			this.registerSortable();
			this.page.on('click', '.js-board-tab', (e) => {
				this.page.find('.js-board-tab.active').removeClass('active');
				$(e.currentTarget).addClass('active');
				this.loadKanban();
			});
			this.page.on('change', '.js-params', () => {
				this.loadKanban();
			});
			this.page.on('click', '.js-list-reload', (e, data) => {
				this.page.find('#orderBy').val(JSON.stringify(data.orderby)).trigger('change');
			});
		}
	}
);
