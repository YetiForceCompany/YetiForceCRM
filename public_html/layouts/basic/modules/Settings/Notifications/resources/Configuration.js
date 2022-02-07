/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Notifications_Configuration_Js',
	{},
	{
		container: false,
		/**
		 * Function to register the change event for layout editor modules list
		 */
		registerModulesChangeEvent: function (container) {
			var thisInstance = this;
			container.find('#supportedModule').on('change', function (e) {
				var progress = thisInstance.progress();
				var params = {};
				params['module'] = app.getModuleName();
				params['parent'] = app.getParentModuleName();
				params['view'] = app.getViewName();
				params['srcModule'] = jQuery(e.currentTarget).val();
				AppConnector.requestPjax(params)
					.done(function (data) {
						progress.progressIndicator({ mode: 'hide' });
						container.html(data);
						App.Fields.Picklist.changeSelectElementView(container);
						thisInstance.registerEvents();
					})
					.fail(function (textStatus, errorThrown) {
						progress.progressIndicator({ mode: 'hide' });
						app.errorLog(textStatus, errorThrown);
					});
			});
		},
		progress: function () {
			return jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
		},
		registerButtonEvents: function (container) {
			var thisInstance = this;
			container.find('.wrapperTrash, .wrapperLock').on('click', '.fas', function (e) {
				var progress = thisInstance.progress();
				var element = jQuery(e.currentTarget);
				var mode = element.data('mode');
				var dataElement = element.closest('tr');
				var params = {
					members: dataElement.data('value'),
					srcModule: container.find('#supportedModule').val()
				};
				if (mode === 'lock') {
					params.lock = dataElement.data('lock') === 0 ? 1 : 0;
				}
				app.saveAjax(mode, null, params).done(function (data) {
					progress.progressIndicator({ mode: 'hide' });
					thisInstance.refreshView();
				});
			});
			container.find('.addUser, .wrapperExceptions').on('click', function (e) {
				e.stopPropagation();
				e.preventDefault();
				var reload = true;
				var element = jQuery(e.currentTarget);
				var url =
					'index.php?module=' +
					app.getModuleName() +
					'&parent=' +
					app.getParentModuleName() +
					'&srcModule=' +
					container.find('#supportedModule').val() +
					'&view=Members';
				if (element.hasClass('wrapperExceptions')) {
					url += '&mode=' + element.data('mode') + '&member=' + element.closest('tr').data('value');
					reload = false;
				}
				app.showModalWindow(null, url, function (data) {
					var form = data.find('form');
					form.on('submit', function (e) {
						e.preventDefault();
						var progress = thisInstance.progress();
						var params = form.serializeFormData();
						app.saveAjax(params.mode, null, params).done(function (data) {
							progress.progressIndicator({ mode: 'hide' });
							app.hideModalWindow();
							if (reload) {
								thisInstance.refreshView();
							}
						});
					});
				});
			});
		},
		refreshView: function () {
			this.getContainer().find('#supportedModule').trigger('change');
		},
		getContainer: function () {
			if (this.container === false) {
				this.container = jQuery('div.contentsDiv');
			}
			return this.container;
		},
		registerEvents: function () {
			var container = this.getContainer();
			this.registerModulesChangeEvent(container);
			app.registerDataTables(container.find('.dataTable'));
			this.registerButtonEvents(container);
		}
	}
);
