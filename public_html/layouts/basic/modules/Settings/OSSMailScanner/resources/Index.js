/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_OSSMailScanner_Index_Js',
	{},
	{
		registerEvents: function () {
			const thisIstance = this,
				container = jQuery('.contentsDiv');
			$('.alert').alert();
			jQuery("select[id^='user_list_']").on('change', function () {
				thisIstance.saveCRMuser(jQuery(this).data('user'), jQuery(this).val());
			});
			jQuery('#email_search').on('change', function () {
				thisIstance.saveEmailSearchList(jQuery('#email_search').val());
			});
			container.find('.js-page-num').on('change', function () {
				thisIstance.reloadLogTable($(this).val() - 1);
			});
			container.find('.js-run-cron').on('click', function () {
				let buttonInstance = $(this);
				app.showNotify({
					text: app.vtranslate('start_cron'),
					type: 'info',
					animation: 'show'
				});
				buttonInstance.attr('disabled', true);
				let ajaxParams = {};
				ajaxParams.data = { module: 'OSSMailScanner', action: 'Cron' };
				ajaxParams.async = true;
				AppConnector.request(ajaxParams).done(function (data) {
					let params = {};
					if (data.success && 'ok' === data.result) {
						params = {
							text: app.vtranslate('end_cron_ok'),
							type: 'info',
							animation: 'show'
						};
					} else {
						params = {
							title: app.vtranslate('end_cron_error'),
							text: data.result,
							type: 'error',
							animation: 'show'
						};
					}
					app.showNotify(params);
					buttonInstance.attr('disabled', false);
					thisIstance.reloadLogTable(container.find('.js-page-num').val() - 1);
				});
			});
			container.on('click', '.js-stop-cron', function (e) {
				let ajaxParams = {},
					scanId = $(e.currentTarget).data('scan-id');
				ajaxParams.data = { module: 'OSSMailScanner', action: 'RestartCron', scanId: scanId };
				ajaxParams.async = true;
				AppConnector.request(ajaxParams).done(function (data) {
					if (data.success) {
						app.showNotify({
							text: data.result.data,
							type: 'info',
							animation: 'show'
						});
						container.find('.js-run-cron').attr('disabled', false);
					}
				});
				thisIstance.reloadLogTable(container.find('.js-page-num').val() - 1);
			});
		},
		saveActions: function (userid, vale) {
			AppConnector.request({
				module: 'OSSMailScanner',
				action: 'SaveActions',
				userid: userid,
				vale: vale
			}).done(function (data) {
				let response = data['result'];
				if (response['success']) {
					app.showNotify({
						text: response['data'],
						type: 'info'
					});
				} else {
					app.showNotify({
						text: response['data'],
						type: 'error'
					});
				}
			});
		},
		saveCRMuser: function (userid, value) {
			AppConnector.request({
				module: 'OSSMailScanner',
				action: 'SaveCRMuser',
				mode: 'user',
				userid: userid,
				value: value
			}).done(function (data) {
				let response = data['result'];
				if (response['success']) {
					app.showNotify({
						text: response['data'],
						type: 'info'
					});
				} else {
					app.showNotify({
						text: response['data'],
						type: 'error'
					});
				}
			});
		},
		isEmpty: function (val) {
			if (!!val || val === 0) {
				return val;
			}
			return '';
		},
		saveEmailSearchList: function (vale) {
			AppConnector.request({
				module: 'OSSMailScanner',
				action: 'SaveEmailSearchList',
				vale: vale
			}).done(function (data) {
				let response = data['result'];
				if (response['success']) {
					app.showNotify({
						text: response['data'],
						type: 'info'
					});
				} else {
					app.showNotify({
						text: response['data'],
						type: 'error'
					});
				}
			});
		},
		/**
		 * Function to reload table with given data from request
		 * @param {int} page
		 */
		reloadLogTable: function (page) {
			const self = this;
			let container = $('.contentsDiv'),
				limit = 30;
			AppConnector.request({
				module: 'OSSMailScanner',
				action: 'GetLog',
				start_number: page * limit
			}).done(function (data) {
				if (data.success) {
					let tab = container.find('table.js-log-list');
					tab.find('tbody tr').remove();
					for (let i = 0; i < data.result.length; i++) {
						let html =
							'<tr>' +
							'<td class="p-1">' +
							self.isEmpty(data.result[i]['id']) +
							'</td>' +
							'<td class="p-1">' +
							self.isEmpty(data.result[i]['start_time']) +
							'</td>' +
							'<td class="p-1">' +
							self.isEmpty(data.result[i]['end_time']) +
							'</td>' +
							'<td class="p-1">' +
							self.isEmpty(app.vtranslate(data.result[i]['status'])) +
							'</td>' +
							'<td class="p-1">' +
							self.isEmpty(data.result[i]['user']) +
							'</td>' +
							'<td class="p-1">' +
							self.isEmpty(data.result[i]['count']) +
							'</td>' +
							'<td class="p-1">' +
							self.isEmpty(data.result[i]['stop_user']) +
							'</td>' +
							'<td class="p-1">' +
							self.isEmpty(data.result[i]['action']) +
							'</td>' +
							'<td class="p-1">' +
							self.isEmpty(data.result[i]['info']) +
							'</td><td>';
						if (data.result[i]['status'] === 'In progress') {
							html +=
								'<button type="button" class="btn btn-danger js-stop-cron" data-scan-id="' + data.result[i]['id'] + '"';
							if (container.find('.js-run-cron').data('button-status')) {
								html += ' disabled';
							}
							html += '>' + app.vtranslate('JS_StopCron') + '</button>';
						}
						html += '</td></tr>';
						tab.append(html);
					}
				}
			});
		}
	}
);
