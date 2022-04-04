/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Notifications_List_Js',
	{},
	{
		registerSave: function (container, id) {
			var thisInstance = this;
			var containerValidate = container.find('.validationEngineContainer');
			var roleId = $('[name="roleMenu"]').val();
			containerValidate.validationEngine(app.validationEngineOptions);
			container.find('[name="saveButton"]').on('click', function () {
				if (containerValidate.validationEngine('validate')) {
					var params = {
						module: app.getModuleName(),
						parent: app.getParentModuleName(),
						action: 'SaveAjax',
						mode: 'saveType',
						name: container.find('[name="name"]').val(),
						width: container.find('[name="width"]').val(),
						height: container.find('[name="height"]').val(),
						roleId: roleId,
						id: id
					};
					var progress = jQuery.progressIndicator();
					AppConnector.request(params).done(function (data) {
						progress.progressIndicator({ mode: 'hide' });
						app.hideModalWindow();
						thisInstance.showTable();
					});
				}
			});
		},
		registerButtons: function () {
			var thisInstance = this;
			$('.createNotification').on('click', function () {
				var progress = jQuery.progressIndicator();
				app.showModalWindow(
					null,
					'index.php?module=Notifications&parent=Settings&view=CreateNotification',
					function (container) {
						progress.progressIndicator({ mode: 'hide' });
						thisInstance.registerSave(container, 0);
					}
				);
			});
			$('[name="roleMenu"]').on('change', function () {
				thisInstance.showTable();
			});
		},
		showTable: function () {
			var thisInstance = this;
			var container = $('.listWithNotifications');
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				view: 'ListContent',
				roleId: $('[name="roleMenu"]').val()
			};
			var progress = jQuery.progressIndicator();
			AppConnector.request(params).done(function (data) {
				progress.progressIndicator({ mode: 'hide' });
				container.html(data);
				thisInstance.registerTableEvents(container);
			});
		},
		registerTableEvents: function (container) {
			var thisInstance = this;
			container.find('.edit').on('click', function (e) {
				var currentTarget = $(e.currentTarget);
				var progress = jQuery.progressIndicator();
				app.showModalWindow(null, currentTarget.data('url'), function (container) {
					progress.progressIndicator({ mode: 'hide' });
					var trRow = currentTarget.closest('tr');
					thisInstance.registerSave(container, trRow.data('id'));
				});
			});
			container.find('.remove').on('click', function (e) {
				let removeButton = jQuery(e.currentTarget);
				let currentTrElement = removeButton.closest('tr');
				app.showConfirmModal({
					title: app.vtranslate('JS_DELETE_CONFIRMATION'),
					confirmedCallback: () => {
						let progress = jQuery.progressIndicator();
						AppConnector.request({
							module: app.getModuleName(),
							parent: app.getParentModuleName(),
							action: 'Delete',
							id: currentTrElement.data('id')
						}).done(function (data) {
							progress.progressIndicator({ mode: 'hide' });
							thisInstance.showTable();
						});
					}
				});
			});
		},
		registerEvents: function () {
			this.registerButtons();
			this.showTable();
		}
	}
);
