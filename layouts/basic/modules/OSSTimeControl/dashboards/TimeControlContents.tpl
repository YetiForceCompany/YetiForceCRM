{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
{if count($DATA) gt 0 }
	{assign var=SHOWING_ICON value=$TCPMODULE_MODEL->get('timeControlWidget')}
	<div class="summary-left pull-left" style="text-align:center;margin-left:2%;">
		{*if $SHOWING_ICON.workingDays eq 'true'}
			<span class="summary-detail">
				<img class=" summary-img" src="{vimage_path('all_days.png')}" alt="All days" title="{\App\Language::translate('LBL_ALLDAYS_INFO', $MODULE_NAME)}"/>
				<span class="summary-text">{$ALLDAYS}</span>
			</span>
			<span class="summary-detail">
				<span>
					<span style="margin-top:6px; vertical-align:top;" class="glyphicon glyphicon-calendar " title="{\App\Language::translate('LBL_WORKDAYS_INFO', $MODULE_NAME)}"></span>
				</span>
				<span class="summary-text">{$WORKDAYS}</span>
			</span>
			<span class="summary-detail">
				<img class=" summary-img" src="{vimage_path('weekend_days.png')}" alt="Weekend days" title="{\App\Language::translate('LBL_WEEKENDDAYS_INFO', $MODULE_NAME)}" />
				<span class="summary-text">
				{$WEEKENDDAYS}
				</span>
			</span>
		{/if}
		{if $SHOWING_ICON.holidays eq 'true'}
			<span class="summary-detail">
				<img class=" summary-img" src="{vimage_path('ecclesiastical.png')}" alt="Ecclesiastical" title="{\App\Language::translate('LBL_ECCLESIASTICAL_INFO', $MODULE_NAME)}"/>
				<span class="summary-text">
					{if $ECCLESIASTICAL}
						{$ECCLESIASTICAL}
					{else}
						0
					{/if}
				</span>
			</span>
			<span class="summary-detail">
				<img class=" summary-img"  src="{vimage_path('national.png')}" alt="National" title="{\App\Language::translate('LBL_NATIONAL_INFO', $MODULE_NAME)}"/>
				<span class="summary-text">
					{if $NATIONAL}
						{$NATIONAL}
					{else}
						0
					{/if}
				</span>
			</span>
		{/if*}

	</div>
	{*if $SHOWING_ICON.workingTime eq 'true'}
		<div class="summary-right pull-right" style="text-align:center;">
			<span class="summary-detail">
				<img class=" summary-img" src="{vimage_path('worked_days.png')}" alt="Worked days" title="{\App\Language::translate('LBL_WORKEDDAYS_INFO', $MODULE_NAME)}"/>
				<span class="summary-text">
					{if $WORKEDDAYS}
						{$WORKEDDAYS}
					{else}
						0
					{/if}
				</span>
			</span>
			<span class="summary-detail">
				<img class=" summary-img" src="{vimage_path('holiday_days.png')}" alt="Holiday days" title="{\App\Language::translate('LBL_HOLIDAYDAYS_INFO', $MODULE_NAME)}"/>
				<span class="summary-text">
					{if $HOLIDAYDAYS}
						{$HOLIDAYDAYS}
					{else}
						0
					{/if}
				</span>
			</span>
			<span class="summary-detail">
				<img class=" summary-img" src="{vimage_path('average_working_time.png')}" alt="Average working time" title="{\App\Language::translate('LBL_AVERAGEWORKTIME_INFO', $MODULE_NAME)}"/>
				<span class="summary-text">
					{if $AVERAGEWORKTIME}
						{$AVERAGEWORKTIME}
					{else}
						0
					{/if}
				</span>
			</span>
			<span class="summary-detail">
				<img class=" summary-img" src="{vimage_path('average_break_time.png')}" alt="Average breaking time" title="{\App\Language::translate('LBL_AVERAGEBREAKTIME_INFO', $MODULE_NAME)}"/>
				<span class="summary-text">
					{$AVERAGEBREAKTIME}
				</span>
			</span>
		</div>
	{/if*}
	<div class="clearfix"></div>
	<div class="widgetChartContainer" style="height:100%;width:98%"></div>
{else}
	<span class="noDataMsg">
		{\App\Language::translate('LBL_NO_RECORDS_MATCHED_THIS_CRITERIA')}
	</span>
{/if}
<input class="widgetData" type="hidden" value='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($DATA))}' />
<style>
.summary-text{
	font-size: 20px;
	vertical-align: super;
}
.summary-img{
	margin-right: 3px;
}
.summary-detail{
	margin-right: 7px;
}
</style>
{/strip}
