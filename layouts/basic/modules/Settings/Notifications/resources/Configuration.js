jQuery.Class('Settings_Notifications_Configuration_Js', {}, {
	registerTableEvents: function (container) {
		container.find('.shareOwners').on('change', function (e) {
			var currentTarget = $(e.currentTarget);
			var trRow = currentTarget.closest('.trRow');
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SaveAjax',
				mode: 'saveConfig',
				owners: currentTarget.val(),
				srcModule: trRow.data('module'),
			};
			var progress = jQuery.progressIndicator();
			AppConnector.request(params).then(function (data) {
				progress.progressIndicator({'mode': 'hide'});
				var params = {
					title: app.vtranslate('JS_MESSAGE'),
					text: app.vtranslate('JS_SAVE_CAHNGES'),
					type: 'success',
					animation: 'show'
				};
				Vtiger_Helper_Js.showMessage(params);
			});
		});
		container.find('.selectAll').on('click', function(e){
			var currentTarget = $(e.currentTarget);
			var selector = currentTarget.closest('.trRow').find('.shareOwners');
			selector.find('option').prop('selected', true);  
			selector.trigger('chosen:updated');
			selector.trigger('change');
		});
		container.find('.removeAll').on('click', function(e){
			var currentTarget = $(e.currentTarget);
			var selector = currentTarget.closest('.trRow').find('.shareOwners');
			selector.find('option').prop('selected', false);  
			selector.trigger('chosen:updated');
			selector.trigger('change');
		});
	},
	registerEvents: function () {
		var container = $('.listModules');
		this.registerTableEvents(container);
	}
});
