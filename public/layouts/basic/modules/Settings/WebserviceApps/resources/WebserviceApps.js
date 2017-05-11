/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class('Settings_WebserviceApps_Index_Js', {}, {
	registerTableEvents: function () {
		var thisInstance = this;
		var container = $('.configContainer');
		container.find('.edit').on('click', function (e) {
			var currentTarget = $(e.currentTarget);
			var trRow = currentTarget.closest('tr');
			thisInstance.showFormToEditKey(trRow.data('id'));
		});
		container.find('.remove').on('click', function (e) {
			var removeButton = jQuery(e.currentTarget);
			var currentTrElement = removeButton.closest('tr');
			var message = app.vtranslate('JS_DELETE_CONFIRMATION');
			Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
					function (e) {
						var params = {
							module: app.getModuleName(),
							parent: app.getParentModuleName(),
							action: 'Delete',
							id: currentTrElement.data('id')
						};
						var progress = jQuery.progressIndicator();
						AppConnector.request(params).then(function (data) {
							progress.progressIndicator({'mode': 'hide'});
							thisInstance.loadTable();
						});
					},
					function (error, err) {
					}
			);
		});
	},
	loadTable: function () {
		var thisInstance = this;
		var params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			view: 'Index',
		};
		var progress = jQuery.progressIndicator();
		AppConnector.request(params).then(function (data) {
			progress.progressIndicator({'mode': 'hide'});
			$('.configContainer').html(data);
			thisInstance.registerTableEvents();
		});
	},
	showFormToEditKey: function (id) {
		var thisInstance = this;
		var params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			view: 'CreateApp',
		};
		if (id != '') {
			params['record'] = id;
		}
		var progress = jQuery.progressIndicator();
		AppConnector.request(params).then(function (data) {
			progress.progressIndicator({'mode': 'hide'});
			app.showModalWindow(data, function (container) {
				Vtiger_Edit_Js.getInstance().registerEvents();
				var form = container.find('form');
				form.validationEngine(app.validationEngineOptions);
				container.find('[name="saveButton"]').on('click', function () {
					if (form.validationEngine('validate')) {
						var params = {
							module: app.getModuleName(),
							parent: app.getParentModuleName(),
							action: 'SaveAjax',
							name: container.find('[name="name"]').val(),
							url: container.find('[name="addressUrl"]').val(),
							status: container.find('[name="status"]').is(':checked'),
							type: container.find('.typeServer').val(),
							pass: container.find('[name="pass"]').val(),
							accounts: container.find('[name="accountsid"]').val(),
						};
						if (id != '') {
							params['id'] = id;
						}
						AppConnector.request(params).then(function (data) {
							thisInstance.loadTable();
							app.hideModalWindow();
						});
					}
				});
			});
		});
	},
	registerAddButton: function () {
		var thisInstance = this
		$('.createKey').on('click', function () {
			thisInstance.showFormToEditKey();
		});
	},
	registerEvents: function () {
		this.registerAddButton();
		this.registerTableEvents();
	}
})
