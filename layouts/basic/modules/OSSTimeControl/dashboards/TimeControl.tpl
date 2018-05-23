{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<script type="text/javascript">
	YetiForce_Bar_Widget_Js('YetiForce_TimeControl_Widget_Js',{}, {
		getBasicOptions:function getBasicOptions(chartData){
			return {
				maintainAspectRatio: false,
				title: {
					display: false
				},
				legend: {
					display: true
				},
				scales: {
					yAxes: [{
							stacked: true,
							ticks:{
								callback:function formatYAxisTick(value, index, values){
									return app.formatToHourText(value,'short',false,false);
								}
							}
						}],
					xAxes: [{
							stacked: true,
							ticks:{
								minRotation:0,
							}
						}]
				},
				tooltips: {
					callbacks: {
						label: function (tooltipItem, data) {
							return chartData.datasets[tooltipItem.datasetIndex].original_label + ': ' + chartData.datasets[tooltipItem.datasetIndex].dataFormatted[tooltipItem.index];
						},
						title: function (tooltipItems, data) {
							return chartData.fullLabels[tooltipItems[0].index];
						}
					}
				},
			};
		},
	});
</script>
<div class="dashboardWidgetHeader">
	{foreach key=index item=cssModel from=$STYLES}
		<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
	{/foreach}
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	<div class="row">
		<div class="col-md-8">
			<h5 class="dashboardTitle h6" title="{App\Purifier::encodeHtml(App\Language::translate($WIDGET->getTitle(), $MODULE_NAME))}"><strong>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(),$MODULE_NAME)}</strong></h5>
		</div>
		<div class="col-md-4">
			<div class="box float-right">
				{if \App\Privilege::isPermitted('OSSTimeControl', 'CreateView')}
					<a class="btn btn-sm btn-light" onclick="Vtiger_Header_Js.getInstance().quickCreateModule('OSSTimeControl'); return false;">
						<span class="fas fa-plus" title="{\App\Language::translate('LBL_ADD_RECORD')}"></span>
					</a>
				{/if}
				<a class="btn btn-sm btn-light" href="javascript:void(0);" name="drefresh" data-url="{$WIDGET->getUrl()}&linkid={$WIDGET->get('linkid')}&content=data">
					<span class="fas fa-sync-alt" title="{\App\Language::translate('LBL_REFRESH')}"></span>
				</a>
				{if !$WIDGET->isDefault()}
					<a class="btn btn-sm btn-light" name="dclose" class="widget" data-url="{$WIDGET->getDeleteUrl()}">
						<span class="fas fa-times" title="{\App\Language::translate('LBL_CLOSE')}"></span>
					</a>
				{/if}
			</div>
		</div>
	</div>
	<hr class="widgetHr" />
	<div class="row" >
		<div class="col-md-6">
			<div class="input-group input-group-sm">
				<div class=" input-group-prepend">
					<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
						<span class="fas fa-calendar-alt"></span>
					</span>
				</div>
				<input type="text" name="time" title="{\App\Language::translate('LBL_CHOOSE_DATE')}" class="dateRangeField widgetFilter form-control text-center" value="{implode(',',$DTIME)}" />
			</div>
		</div>
		<div class="col-md-6">
			{if $SOURCE_MODULE && AppConfig::performance('SEARCH_SHOW_OWNER_ONLY_IN_LIST')}
				{assign var=USERS_GROUP_LIST value=\App\Fields\Owner::getInstance($SOURCE_MODULE)->getUsersAndGroupForModuleList(false,$USER_CONDITIONS)}
				{assign var=ACCESSIBLE_USERS value=$USERS_GROUP_LIST['users']}
			{else}
				{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
			{/if}
			<div class="input-group input-group-sm flex-nowrap">
				<span class="input-group-prepend">
					<span class="input-group-text">
						<span class="fas fa-user iconMiddle"></span></span>
				</span>
				<div class="select2Wrapper">
					<select class="widgetFilter form-control select2" aria-label="Small" aria-describedby="inputGroup-sizing-sm" title="{\App\Language::translate('LBL_SELECT_USER')}" name="user" style="margin-bottom:0;"
							{if AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
								data-ajax-search="1" data-ajax-url="index.php?module={$MODULE_NAME}&action=Fields&mode=getOwners&fieldName=assigned_user_id&result[]=users" data-minimum-input="{AppConfig::performance('OWNER_MINIMUM_INPUT_LENGTH')}"
							{/if}>
						{if !AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
							<optgroup label="{\App\Language::translate('LBL_USERS')}">
								{foreach key=OWNER_ID item=OWNER_NAME from=$ACCESSIBLE_USERS}
									<option title="{$OWNER_NAME}" {if $OWNER_ID eq $USERID } selected {/if} value="{$OWNER_ID}">
										{$OWNER_NAME}
									</option>
								{/foreach}
							</optgroup>
						{/if}
					</select>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file=\App\Layout::getTemplatePath('dashboards/TimeControlContents.tpl', $MODULE_NAME)}
</div>
