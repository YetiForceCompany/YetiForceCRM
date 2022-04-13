/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Index_Js(
	'Settings_Kanban_Index_Js',
	{},
	{
		/**
		 * Register add modal
		 */
		registerAddModal() {
			$('.js-add-board').on('click', (e) => {
				app.showModalWindow(
					null,
					'index.php?module=Kanban&parent=Settings&view=AddModal&sourceModule=' + this.module,
					(modalContainer) => {
						modalContainer.find('.js-modal__save').on('click', function (e) {
							AppConnector.request({
								module: app.getModuleName(),
								parent: app.getParentModuleName(),
								action: 'Save',
								mode: 'add',
								field: modalContainer.find('.js-system-fields').val()
							})
								.done(function (data) {
									if (data.result.message) {
										app.hideModalWindow();
										window.location.reload();
									}
								})
								.fail(function (error, err) {
									app.errorLog(error, err);
								});
						});
					}
				);
			});
		},
		/**
		 * Register delete event
		 */
		registerDeleteEvent() {
			this.container.find('.js-delete').on('click', (e) => {
				let board = $(e.currentTarget).closest('.js-board');
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					action: 'Save',
					mode: 'delete',
					board: board.data('id')
				})
					.done(function (data) {
						if (data.result.message) {
							window.location.reload();
						}
					})
					.fail(function (error, err) {
						app.errorLog(error, err);
					});
			});
		},
		/**
		 * Register add modal
		 */
		registerFieldsEvents() {
			let fields = this.container.find('.js-sortable-fields');
			fields.on('sortable:change', (e) => {
				this.saveField($(e.currentTarget));
			});
			fields.on('change', (e) => {
				this.saveField($(e.currentTarget));
			});
			let boards = this.container.find('.js-boards');
			boards.sortable({
				containment: boards,
				items: boards.find('.js-board'),
				handle: '.js-drag',
				revert: true,
				tolerance: 'pointer',
				cursor: 'move',
				update: () => {
					this.saveSequence();
				}
			});
		},
		/**
		 * Save field
		 */
		saveField: function (element) {
			let board = element.closest('.js-board');
			AppConnector.request({
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'Save',
				mode: 'update',
				board: board.data('id'),
				type: element.data('type'),
				value: element.val()
			})
				.done(function (data) {
					if (data.result.message) {
						app.showNotify(data.result.message);
					}
				})
				.fail(function () {
					app.showNotify({
						text: app.vtranslate('JS_UNEXPECTED_ERROR'),
						type: 'error'
					});
				});
		},
		/**
		 * Save sequence
		 */
		saveSequence: function () {
			let boards = [];
			this.container.find('.js-board').each(function (index) {
				boards[index] = $(this).data('id');
			});
			AppConnector.request({
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				sourceModule: this.module,
				action: 'Save',
				mode: 'sequence',
				boards: boards
			})
				.done(function (data) {
					if (data.result.message) {
						app.showNotify(data.result.message);
					}
				})
				.fail(function () {
					app.showNotify({
						text: app.vtranslate('JS_UNEXPECTED_ERROR'),
						type: 'error'
					});
				});
		},
		/**
		 * Register module change
		 */
		registerModuleEvents: function () {
			$('.js-module-list').on('change', (e) => {
				window.location.href =
					'index.php?module=Kanban&parent=Settings&view=Index&sourceModule=' + $(e.currentTarget).val();
			});
		},
		/**
		 * Register events
		 */
		registerEvents() {
			this._super();
			this.module = $('#js-module-name').val();
			this.container = $('.js-fields-list');
			this.registerAddModal();
			this.registerDeleteEvent();
			this.registerFieldsEvents();
			this.registerModuleEvents();
		}
	}
);
