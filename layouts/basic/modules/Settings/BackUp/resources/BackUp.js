/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("Settings_BackUp_Index_Js", {}, {
	performBackup: function (action) {
		var thisInstance = this;
		var params = {
			parent: app.getParentModuleName(),
			module: 'BackUp',
			action: 'Backup',
			mode: 'perform',
			step: action,
		}
		AppConnector.request(params).then(function (data) {
			Vtiger_Helper_Js.showPnotify({text: data.result.message, type: 'info'});
		});
	},
	registerPerformBackup: function (content) {
		var thisInstance = this;
		content.find('.runBackup').on('click', function (e) {
			thisInstance.performBackup(1);
		});
	},
	registerStopBackup: function (content) {
		content.find('.stopBackup').on('click', function (e) {
			var params = {
				parent: app.getParentModuleName(),
				module: 'BackUp',
				action: 'Backup',
				mode: 'stopBackup',
			}
			AppConnector.request(params).then(
					function (data) {
						location.reload();
					}
			);
		});
	},
	updateProgressBar: function (content) {
		var time = 3000;
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var params = {
			parent: app.getParentModuleName(),
			module: 'BackUp',
			action: 'Backup',
			mode: 'progress',
			id: content.find('.backupID').val(),
		}
		AppConnector.request(params).then(
				function (data) {
					if (data.result != false) {
						$.each(data.result, function (index, value) {
							var row = content.find('.row-bar.' + index);
							var progress = row.find('.progress');
							row.find('.progress-bar').width(value + '%');
							row.find('.precent').text(value);
							if (value >= 100 && index != 'mainBar') {
								progress.removeClass('active');
								progress.removeClass('progress-info');
							} else if (index != 'mainBar') {
								progress.addClass('progress-success');
							}
						});
						setTimeout(function () {
							thisInstance.updateProgressBar(content);
						}, time);
					}
					aDeferred.resolve(data.result);
				},
				function (error) {
					aDeferred.reject();
				}
		);
		return aDeferred.promise();
	},
	registerProgressBar: function (content) {
		var thisInstance = this;
		thisInstance.updateProgressBar(content);
	},
	registerSaveBackupSetting: function (content) {
		var thisInstance = this;
		content.find('.configField').on('switchChange.bootstrapSwitch', function (event, state) {
			var target = $(event.currentTarget);
			thisInstance.registerSave(target, state);
		}).on('change', function (e) {
			var target = $(e.currentTarget);
			thisInstance.registerSave(target, target.val());
		});
	},
	registerSave: function (target, value) {
		var params = {};
		params['type'] = target.data('type');
		params['param'] = target.attr('name');
		params['val'] = value;

		app.saveAjax('updateSettings', params).then(function (data) {
			Settings_Vtiger_Index_Js.showMessage({type: 'success', text: data.result.message});
		});
	},
	registerSaveFTPConfigEvent: function (content) {
		var thisInstance = this;
		content.find('#saveFtpConfig').on('click', function (e) {
			var isValid = thisInstance.validFtpSettings();
			if (false === isValid)
				return false;

			var ftpHost = jQuery('#tab_2 [name="host"]').val();
			var ftpLogin = jQuery('#tab_2 [name="login"]').val();
			var ftpPassword = jQuery('#tab_2 [name="password"]').val();
			var ftpPort = jQuery('#tab_2 [name="port"]').val();
			var ftpPath = jQuery('#tab_2 [name="path"]').val();
			var ftpActive = jQuery('#tab_2 [name="active"]').is(':checked');
			var params = {};
			params.data = {
				parent: app.getParentModuleName(),
				module: 'BackUp',
				action: 'Backup',
				mode: 'saveftp',
				ftpservername: ftpHost,
				ftplogin: ftpLogin,
				ftppassword: ftpPassword,
				ftpport: ftpPort,
				ftppath: ftpPath,
				ftpactive: ftpActive
			};
			params.dataType = 'json';
			AppConnector.request(params).then(function (data) {
				var response = data['result'];
				if (response.fptConnection == true) {
					$('#connection-status').css('background-color', '#5bb75b');
					var params = {
						text: app.vtranslate(response.message),
						animation: 'show',
						type: 'info'
					};
					Vtiger_Helper_Js.showPnotify(params);
				} else {
					$('#connection-status').css('background-color', 'red');
					var params = {
						text: app.vtranslate(response.message),
						animation: 'show',
						type: 'error'
					};
					Vtiger_Helper_Js.showPnotify(params);
				}
			});
			e.preventDefault();
			return;
		});
	},
	validFtpSettings: function () {
		var ftpHost = jQuery('#tab_2 [name="host"]').val();
		var ftpLogin = jQuery('#tab_2 [name="login"]').val();
		var ftpPassword = jQuery('#tab_2 [name="password"]').val();
		var ftpPort = jQuery('#tab_2 [name="port"]').val();
		var result = true;
		if (0 == ftpHost.length || 0 == ftpLogin.length || 0 == ftpPassword.length) {
			var params = {
				text: app.vtranslate('JS_MANDATORY_FIELDS_EMPTY'),
				animation: 'show',
				type: 'error'
			};
			Vtiger_Helper_Js.showPnotify(params);
			result = false;
		}

		if (isNaN(ftpPort)) {
			var params = {
				text: app.vtranslate('JS_PORT_ONLY_NUMBERS'),
				animation: 'show',
				type: 'error'
			};
			Vtiger_Helper_Js.showPnotify(params);
			result = false;
		}
		return result
	},
	registerNextPagePaginationEvent: function (content) {
		var thisInstance = this;
		content.find('#listViewNextPageButton').on('click', function (e) {
			var ftpButtonTitle = content.find('.ftp-button').attr('title');
			var params = {};
			var offset = content.find('.offset').val();
			var page = parseInt(content.find('.current-page').val()) + 1;
			params.data = {
				module: 'BackUp',
				action: 'Pagination',
				offset: offset,
				page: page,
				ajaxCall: true,
				parent: app.getParentModuleName()

			};
			params.dataType = 'json';
			AppConnector.request(params).then(function (data) {
				var response = data['result'];
				if (data.success == true) {
					thisInstance.registerUpdateBackUpList(content, data);
				}
			});
			e.preventDefault();
			return;
		});
	},
	registerPrevPagePaginationEvent: function (content) {
		var thisInstance = this;
		content.find('#listViewPreviousPageButton').on('click', function (e) {
			var params = {};
			var offset = content.find('.offset').val();
			var page = parseInt(content.find('.current-page').val()) - 1;
			params.data = {
				module: 'BackUp',
				action: 'Pagination',
				offset: offset,
				page: page,
				ajaxCall: true,
				parent: app.getParentModuleName()
			};
			params.dataType = 'json';
			AppConnector.request(params).then(function (data) {
				var response = data['result'];
				if (data.success == true) {
					thisInstance.registerUpdateBackUpList(content, data);
				}
			});
			e.preventDefault();
			return;
		});
	},
	registerSetPaginationNavigation: function (content) {
		var nextPage = content.find('.next-page').val();
		var pregPage = content.find('.prev-page').val();
		if (nextPage == true) {
			content.find('#listViewNextPageButton').prop('disabled', false);
		} else {
			content.find('#listViewNextPageButton').prop('disabled', true);
		}
		if (pregPage == true) {
			content.find('#listViewPreviousPageButton').prop('disabled', false);
		} else {
			content.find('#listViewPreviousPageButton').prop('disabled', true);
		}
	},
	registerUpdateBackUpList: function (content, data) {
		var thisInstance = this;
		content.find('.offset').val(data.result.offset);
		content.find('.current-page').val(data.result.page);
		content.find('.next-page').val(data.result.nextPage);
		content.find('.prev-page').val(data.result.prevPage);
		thisInstance.registerSetPaginationNavigation(content);
		var ftpButtonTitle = content.find('.ftp-button').attr('title');

		content.find('.backup-list').empty();
		jQuery(data.result.backups).each(function () {
			content.find('.backup-list').append('<tr><td><label class="marginRight5px" >' + this.starttime + '</label></td><td><label class="marginRight5px" >' + this.endtime + '</label></td><td><label class="marginRight5px" >' + this.filename + '</label></td><td><label class="marginRight5px" >' + this.status + '</label></td><td><label class="marginRight5px" >' + this.backuptime + '</label></td></tr>')
		});
	},
	registerEvents: function () {
		var content = $('.contentsDiv');

		this.registerPerformBackup(content);
		this.registerStopBackup(content);
		this.registerProgressBar(content);
		this.registerSaveBackupSetting(content);
		this.registerSaveFTPConfigEvent(content);
		this.registerSetPaginationNavigation(content);
		this.registerNextPagePaginationEvent(content);
		this.registerPrevPagePaginationEvent(content);
	}
});
