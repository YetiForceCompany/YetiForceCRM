{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<script type="text/javascript">
	YetiForce_Bar_Widget_Js('YetiForce_TimeControl_Widget_Js', {}, {
		getBasicOptions: function getBasicOptions(chartData) {
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
						ticks: {
							callback: function formatYAxisTick(value, index, values) {
								return app.formatToHourText(value, 'short', false, false);
							}
						}
					}],
					xAxes: [{
						stacked: true,
						ticks: {
							minRotation: 0,
						}
					}]
				},
				tooltips: {
					callbacks: {
						label: function(tooltipItem, data) {
							return chartData.datasets[tooltipItem.datasetIndex].original_label + ': ' + chartData.datasets[tooltipItem.datasetIndex].dataFormatted[tooltipItem.index];
						},
						title: function(tooltipItems, data) {
							return chartData.fullLabels[tooltipItems[0].index];
						}
					}
				},
			};
		},
	});
</script>
<div class="dashboardWidgetHeader">
	<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
		{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME) CLASSNAME="col-md-10"}
		<div class="d-inline-flex">
			{if \App\Privilege::isPermitted($MODULE_NAME, 'CreateView')}
				<button class="btn btn-sm btn-light  js-widget-quick-create" data-js="click" type="button"
					data-module-name="{$MODULE_NAME}">
					<span class="fas fa-plus" title="{\App\Language::translate('LBL_ADD_RECORD')}"></span>
				</button>
			{/if}
			<button class="btn btn-sm btn-light js-widget-refresh" title="{\App\Language::translate('LBL_REFRESH')}" data-url="{$WIDGET->getUrl()}&content=data" data-js="click">
				<span class="fas fa-sync-alt"></span>
			</button>
			{if !$WIDGET->isDefault()}
				<button class="btn btn-sm btn-light js-widget-remove" title="{\App\Language::translate('LBL_CLOSE')}" data-url="{$WIDGET->getDeleteUrl()}" data-js="click">
					<span class="fas fa-times"></span>
				</button>
			{/if}
		</div>
	</div>
	<hr class="widgetHr" />
	<div class="row no-gutters">
		<div class="col-ceq-xsm-6">
			<div class="input-group input-group-sm">
				<div class=" input-group-prepend">
					<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
						<span class="fas fa-calendar-alt"></span>
					</span>
				</div>
				<input type="text" name="time" title="{\App\Language::translate('LBL_CHOOSE_DATE')}"
					class="dateRangeField widgetFilter form-control text-center" value="{implode(',',$DTIME)}" />
			</div>
		</div>
		<div class="col-ceq-xsm-6">
			{if $SOURCE_MODULE && App\Config::performance('SEARCH_SHOW_OWNER_ONLY_IN_LIST') && !\App\Config::module($SOURCE_MODULE, 'DISABLED_SHOW_OWNER_ONLY_IN_LIST', false)}
				{assign var=USERS_GROUP_LIST value=\App\Fields\Owner::getInstance($SOURCE_MODULE)->getUsersAndGroupForModuleList(false,$USER_CONDITIONS)}
				{assign var=ACCESSIBLE_USERS value=$USERS_GROUP_LIST['users']}
			{else}
				{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
			{/if}
			<div class="input-group input-group-sm">
				<span class="input-group-prepend">
					<span class="input-group-text">
						<span class="fas fa-user iconMiddle"></span></span>
				</span>
				<select class="widgetFilter form-control select2" aria-label="Small"
					aria-describedby="inputGroup-sizing-sm" title="{\App\Language::translate('LBL_SELECT_USER')}"
					name="user" style="margin-bottom:0;"
					{if App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
						data-ajax-search="1" data-ajax-url="index.php?module={$MODULE_NAME}&action=Fields&mode=getOwners&fieldName=assigned_user_id&result[]=users" data-minimum-input="{App\Config::performance('OWNER_MINIMUM_INPUT_LENGTH')}"
					{/if}>
					{if !App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
						<optgroup label="{\App\Language::translate('LBL_USERS')}">
							{foreach key=OWNER_ID item=OWNER_NAME from=$ACCESSIBLE_USERS}
								<option title="{$OWNER_NAME}" {if $OWNER_ID eq $USERID } selected {/if}
									value="{$OWNER_ID}">
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
<div class="dashboardWidgetContent">
	{include file=\App\Layout::getTemplatePath('dashboards/TimeControlContents.tpl', $MODULE_NAME)}
</div>
