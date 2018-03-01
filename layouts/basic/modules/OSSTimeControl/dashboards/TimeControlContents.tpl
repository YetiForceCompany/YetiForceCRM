{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if $DATA['show_chart'] }
		{assign var=SHOWING_ICON value=$TCPMODULE_MODEL->get('timeControlWidget')}
		<div class="summary-left float-left" style="text-align:center;margin-left:2%;">
			{*if $SHOWING_ICON.workingDays eq 'true'}
			<span class="summary-detail">
			<img class=" summary-img" src="{\App\Layout::getImagePath('all_days.png')}" alt="All days" title="{\App\Language::translate('LBL_ALLDAYS_INFO', $MODULE_NAME)}" />
			<span class="summary-text">{$ALLDAYS}</span>
			</span>
			<span class="summary-detail">
			<span>
			<span style="margin-top:6px; vertical-align:top;" class="fas fa-calendar-alt " title="{\App\Language::translate('LBL_WORKDAYS_INFO', $MODULE_NAME)}"></span>
			</span>
			<span class="summary-text">{$WORKDAYS}</span>
			</span>
			<span class="summary-detail">
			<img class=" summary-img" src="{\App\Layout::getImagePath('weekend_days.png')}" alt="Weekend days" title="{\App\Language::translate('LBL_WEEKENDDAYS_INFO', $MODULE_NAME)}" />
			<span class="summary-text">
			{$WEEKENDDAYS}
			</span>
			</span>
			{/if}
			{if $SHOWING_ICON.holidays eq 'true'}
			<span class="summary-detail">
			<img class=" summary-img" src="{\App\Layout::getImagePath('ecclesiastical.png')}" alt="Ecclesiastical" title="{\App\Language::translate('LBL_ECCLESIASTICAL_INFO', $MODULE_NAME)}" />
			<span class="summary-text">
			{if $ECCLESIASTICAL}
			{$ECCLESIASTICAL}
			{else}
			0
			{/if}
			</span>
			</span>
			<span class="summary-detail">
			<img class=" summary-img"  src="{\App\Layout::getImagePath('national.png')}" alt="National" title="{\App\Language::translate('LBL_NATIONAL_INFO', $MODULE_NAME)}" />
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
		<div class="summary-right float-right" style="text-align:center;">
		<span class="summary-detail">
		<img class=" summary-img" src="{\App\Layout::getImagePath('worked_days.png')}" alt="Worked days" title="{\App\Language::translate('LBL_WORKEDDAYS_INFO', $MODULE_NAME)}" />
		<span class="summary-text">
		{if $WORKEDDAYS}
		{$WORKEDDAYS}
		{else}
		0
		{/if}
		</span>
		</span>
		<span class="summary-detail">
		<img class=" summary-img" src="{\App\Layout::getImagePath('holiday_days.png')}" alt="Holiday days" title="{\App\Language::translate('LBL_HOLIDAYDAYS_INFO', $MODULE_NAME)}" />
		<span class="summary-text">
		{if $HOLIDAYDAYS}
		{$HOLIDAYDAYS}
		{else}
		0
		{/if}
		</span>
		</span>
		<span class="summary-detail">
		<img class=" summary-img" src="{\App\Layout::getImagePath('average_working_time.png')}" alt="Average working time" title="{\App\Language::translate('LBL_AVERAGEWORKTIME_INFO', $MODULE_NAME)}" />
		<span class="summary-text">
		{if $AVERAGEWORKTIME}
		{$AVERAGEWORKTIME}
		{else}
		0
		{/if}
		</span>
		</span>
		<span class="summary-detail">
		<img class=" summary-img" src="{\App\Layout::getImagePath('average_break_time.png')}" alt="Average breaking time" title="{\App\Language::translate('LBL_AVERAGEBREAKTIME_INFO', $MODULE_NAME)}" />
		<span class="summary-text">
		{$AVERAGEBREAKTIME}
		</span>
		</span>
		</div>
		{/if*}
		<div class="clearfix"></div>
		<div class="widgetChartContainer"><canvas></canvas></div>
	{else}
		<span class="noDataMsg">
			{\App\Language::translate('LBL_NO_RECORDS_MATCHED_THIS_CRITERIA')}
		</span>
	{/if}
	<input class="widgetData" type="hidden" value='{\App\Purifier::encodeHtml(\App\Json::encode($DATA))}' />
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
