{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<!-- tpl-Settings-OSSMailScanner-logs -->
<div class="editViewContainer" id="tab_cron">
	<div class="o-breadcrumb widget_header row">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<table class="mt-2">
		<tr>
			<td>
				<button class="btn btn-success js-run-cron" id="run_cron" type="button"
					{if $STOP_BUTTON_STATUS}disabled="disabled" {/if} data-button-status="{!$STOP_BUTTON_STATUS}"
					data-js="change|value|data-button-status">
					<span class="fa fa-caret-right u-mr-5px"></span>{\App\Language::translate('RunCron', 'OSSMailScanner')}
				</button>
			</td>
		</tr>
	</table>
	<br />
	<div class="row col-12">
		<div class="row col-sm-10 col-md-8 col-lg-7 marginBottom10px">
			<div class="row col-sm-4">{\App\Language::translate('email_to_notify', 'OSSMailScanner')}: &nbsp;</div>
			<div class="col-sm-7"><input type="text" class="form-control"
					title="{\App\Language::translate('email_to_notify', 'OSSMailScanner')}"
					name="email_to_notify" value="{$WIDGET_CFG['cron']['email']}" /></div>
		</div>
		<div class='row col-sm-10 col-md-8 col-lg-7 marginBottom10px'>
			<div class="row col-sm-4">{\App\Language::translate('time_to_notify', 'OSSMailScanner')}: &nbsp;</div>
			<div class="col-sm-7"><input type="text" name="time_to_notify"
					title="{\App\Language::translate('time_to_notify', 'OSSMailScanner')}"
					class="form-control" value="{$WIDGET_CFG['cron']['time']}" /></div>
		</div>
	</div>
	<div class="d-flex justify-content-end">
		<select class="w-auto form-control js-page-num" name="page_num"
			title="{\App\Language::translate('LBL_PAGE_NUMBER', $QUALIFIED_MODULE)}" data-js="change|value">
			{if $HISTORYACTIONLIST_NUM eq 0}
				<option vlaue="1">1</option>
			{/if}
			{for $i=1 to $HISTORYACTIONLIST_NUM}
				<option vlaue="{$i}">{$i}</option>
			{/for}
		</select>
	</div>
	<table class="table tableRWD table-bordered js-log-list" data-js="container">
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
				<td class="p-1">{$item['id']}</td>
				<td class="p-1">{$item['start_time']}</td>
				<td class="p-1">{$item['end_time']}</td>
				<td class="p-1">{\App\Language::translate($item['status'], 'OSSMailScanner')}</td>
				<td class="p-1">{$item['user']}</td>
				<td class="p-1">{$item['count']}</td>
				<td class="p-1">{$item['stop_user']}</td>
				<td class="p-1">{\App\Language::translate($item['action'], 'OSSMailScanner')}</td>
				<td class="p-1">{$item['info']}</td>
				<td class="p-1">
					{if $item['status'] eq 'In progress'}
						<button type="button" class="btn btn-danger js-stop-cron" data-scan-id="{$item['id']}">
							{\App\Language::translate('StopCron', 'OSSMailScanner')}
						</button>
					{/if}
				</td>
			</tr>
		{/foreach}
	</table>
</div>
<!-- /tpl-Settings-OSSMailScanner-logs -->
