{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<style>
	.table tbody tr.error > td {
		background-color: #f2dede;
	}

	.table th, .table td {
		padding: 3px;
	}
</style>
<div class='editViewContainer ' id="tab_cron">
	<div class="widget_header row">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<table class="mt-2">
		<tr>
			<td>
				<button class="btn btn-success" id="run_cron" type="button"
						{if $STOP_BUTTON_STATUS neq 'false'}disabled{/if}>
					<span class="fa fa-caret-right u-mr-5px"></span>{\App\Language::translate('RunCron', 'OSSMailScanner')}
				</button>
			</td>
		</tr>
	</table>
	<br/>
	<div class="row col-12">
		<div class="row col-sm-10 col-md-8 col-lg-7 marginBottom10px">
			<div class="row col-sm-4">{\App\Language::translate('email_to_notify', 'OSSMailScanner')}: &nbsp;</div>
			<div class="col-sm-7"><input type="text" class="form-control"
										 title="{\App\Language::translate('email_to_notify', 'OSSMailScanner')}"
										 name="email_to_notify" value="{$WIDGET_CFG['cron']['email']}"/></div>
		</div>
		<div class='row col-sm-10 col-md-8 col-lg-7 marginBottom10px'>
			<div class="row col-sm-4">{\App\Language::translate('time_to_notify', 'OSSMailScanner')}: &nbsp;</div>
			<div class="col-sm-7"><input type="text" name="time_to_notify"
										 title="{\App\Language::translate('time_to_notify', 'OSSMailScanner')}"
										 class="form-control" value="{$WIDGET_CFG['cron']['time']}"/></div>
		</div>
	</div>
	<div class="d-flex justify-content-end">
		<select class="w-auto form-control" name="page_num"
				title="{\App\Language::translate('LBL_PAGE_NUMBER', $QUALIFIED_MODULE)}">
			{if $HISTORYACTIONLIST_NUM eq 0}
				<option vlaue="1">1</option>
			{/if}
			{for $i=1 to $HISTORYACTIONLIST_NUM}
				<option vlaue="{$i}">{$i}</option>
			{/for}
		</select>
	</div>
	<table class="table tableRWD table-bordered log-list">
		<thead>
		<tr class="listViewHeaders">
			<th>{\App\Language::translate('No', 'OSSMailScanner')}.</th>
			<th>{\App\Language::translate('startTime', 'OSSMailScanner')}</th>
			<th>{\App\Language::translate('endTime', 'OSSMailScanner')}</th>
			<th>{\App\Language::translate('status', 'OSSMailScanner')}</th>
			<th>{\App\Language::translate('who', 'OSSMailScanner')}</th>
			<th>{\App\Language::translate('count', 'OSSMailScanner')}</th>
			<th>{\App\Language::translate('stop_user', 'OSSMailScanner')}</th>
			<th>{\App\Language::translate('Action', 'OSSMailScanner')}</th>
			<th>{\App\Language::translate('Desc', 'OSSMailScanner')}</th>
			<th></th>
		</tr>
		</thead>
		{foreach item=item key=key from=$HISTORYACTIONLIST}
			<tr>
				<td>{$item['id']}</td>
				<td>{$item['start_time']}</td>
				<td>{$item['end_time']}</td>
				<td>{\App\Language::translate($item['status'], 'OSSMailScanner')}</td>
				<td>{$item['user']}</td>
				<td>{$item['count']}</td>
				<td>{$item['stop_user']}</td>
				<td>{\App\Language::translate($item['action'], 'OSSMailScanner')}</td>
				<td>{$item['info']}</td>
				<td>
					{if $item['status'] eq 'In progress'}
						<button type="button" class="btn btn-danger" id="manula_stop_cron"
								{if $STOP_BUTTON_STATUS eq 'false'}disabled{/if}>{\App\Language::translate('StopCron', 'OSSMailScanner')}</button>
					{/if}
				</td>
			</tr>
		{/foreach}
	</table>

</div>
</div>
{literal}
<script>
	jQuery(function () {
		jQuery('select[name="page_num"]').on('change', function () {
			reloadLogTable(jQuery(this).val() - 1);
		});

		jQuery('[name="time_to_notify"]').on('blur', function () {
			var value = jQuery(this).val();
			if (!!number_validate(value)) {
				saveWidgetConfig('time', jQuery(this).val(), 'cron');
			} else {
				var params = {
					text: app.vtranslate('JS_time_error'),
					type: 'error',
				};

				Vtiger_Helper_Js.showPnotify(params);
			}
		});
		jQuery('[name="email_to_notify"]').on('blur', function () {
			var value = jQuery(this).val();
			if (!!email_validate(value)) {
				saveWidgetConfig('email', value, 'cron');
			} else {
				var params = {
					text: app.vtranslate('JS_mail_error'),
					type: 'error',
				};

				Vtiger_Helper_Js.showPnotify(params);
			}
		});
		jQuery('#run_cron').on('click', function () {
			var paramsInfo = {
				text: app.vtranslate('start_cron'),
				type: 'info',
				animation: 'show'
			};
			Vtiger_Helper_Js.showPnotify(paramsInfo);
			jQuery('#run_cron').attr('disabled', true);
			var ajaxParams = {};
			ajaxParams.data = {module: 'OSSMailScanner', action: "Cron"},
				ajaxParams.async = true;
			AppConnector.request(ajaxParams).done(function (data) {
				var params = {};
				if (data.success && data.result == 'ok') {
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
				Vtiger_Helper_Js.showPnotify(params);
				jQuery('#run_cron').attr('disabled', false);
				reloadLogTable(jQuery('[name="page_num"]').val() - 1);
			});
		});
		jQuery('#manula_stop_cron').on('click', function () {
			var ajaxParams = {};
			ajaxParams.data = {module: 'OSSMailScanner', action: "RestartCron"},
				ajaxParams.async = true;

			AppConnector.request(ajaxParams).done(function (data) {
				if (data.success) {
					var params = {
						text: data.result.data,
						type: 'info',
						animation: 'show'
					}

					Vtiger_Helper_Js.showPnotify(params);
					jQuery('#run_cron').attr('disabled', false);
				}
			});
			reloadLogTable(jQuery('[name="page_num"]').val() - 1);
		})
	});

	function isEmpty(val) {
		if (!!val) {
			return val;
		}

		return '';
	}

	function number_validate(value) {
		var valid = !/^\s*$/.test(value) && !isNaN(value);
		return valid;
	}

	function reloadLogTable(page) {
		var limit = 30,
			ajaxParams = {module: 'OSSMailScanner', action: "GetLog", start_number: page * limit};

		AppConnector.request(ajaxParams).done(function (data) {
			if (data.success) {
				var tab = jQuery('table.log-list');
				tab.find('tbody tr').remove();
				for (i = 0; i < data.result.length; i++) {

					var html = '<tr>'
						+ '<td>' + isEmpty(data.result[i]['id']) + '</td>'
						+ '<td>' + isEmpty(data.result[i]['start_time']) + '</td>'
						+ '<td>' + isEmpty(data.result[i]['end_time']) + '</td>'
						+ '<td>' + isEmpty(app.vtranslate(data.result[i]['status'])) + '</td>'
						+ '<td>' + isEmpty(data.result[i]['user']) + '</td>'
						+ '<td>' + isEmpty(data.result[i]['count']) + '</td>'
						+ '<td>' + isEmpty(data.result[i]['stop_user']) + '</td>'
						+ '<td>' + isEmpty(data.result[i]['action']) + '</td>'
						+ '<td>' + isEmpty(data.result[i]['info']) + '</td>'
						+ '<td>';

					if (data.result[i]['status'] == 'In progress') {
						html += '<button type="button" class="b	tn btn-danger" id="manula_stop_cron"';

						if (!{/literal}{$STOP_BUTTON_STATUS}{literal}) {
							html += 'disabled';
						}

						html += '>' + app.vtranslate('JS_StopCron') + '</button></td>';
					}

					html += '</tr>';

					tab.append(html);
				}
			}
		});
	}

	function email_validate(src) {
		var regex = /^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,63}$/;
		return regex.test(src);
	}

	function saveWidgetConfig(name, value, type) {
		var params = {
			'module': 'OSSMailScanner',
			'action': "SaveWidgetConfig",
			'conf_type': type,
			'name': name,
			'value': value
		}
		AppConnector.request(params).done(function (data) {
			var response = data['result'];
			if (response['success']) {
				var params = {
					text: response['data'],
					type: 'info',
					animation: 'show'
				};
				Vtiger_Helper_Js.showPnotify(params);
			} else {
				var params = {
					text: response['data'],
					animation: 'show'
				};
				Vtiger_Helper_Js.showPnotify(params);
			}
		});
	}
</script>
{/literal}
