{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-Extended-CalendarView -->
	<input value="{$VIEW}" type="hidden" id="currentView"/>
	<input value="{\App\Purifier::encodeHtml($CURRENT_USER->get('activity_view'))}" type="hidden" id="activity_view"/>
	<input value="{$CURRENT_USER->get('hour_format')}" type="hidden" id="time_format"/>
	<input value="{$CURRENT_USER->get('start_hour')}" type="hidden" id="start_hour"/>
	<input value="{$CURRENT_USER->get('end_hour')}" type="hidden" id="end_hour"/>
	<input value="{$CURRENT_USER->get('date_format')}" type="hidden" id="date_format"/>
	<input value="current" type="hidden" id="showType"/>
	<input value="workDays" type="hidden" id="switchingDays"/>
	<input value="{$EVENT_LIMIT}" type="hidden" id="eventLimit"/>
	<input value="{$WEEK_VIEW}" type="hidden" id="weekView"/>
	<input value="{$DAY_VIEW}" type="hidden" id="dayView"/>
	<input value="{\App\Purifier::encodeHtml(\App\Json::encode(\AppConfig::module('Calendar', 'HIDDEN_DAYS_IN_CALENDAR_VIEW')))}"
		   type="hidden" id="hiddenDays"/>
	<input value="{\App\Purifier::encodeHtml($ACTIVITY_STATE_LABELS)}" type="hidden" id="activityStateLabels"/>
	<div class="calendarViewContainer rowContent col-md-12 paddingLefttZero col-xs-12">
		<div class="widget_header row marginbottomZero marginRightMinus20">
			<div class="pull-left paddingLeftMd">
				{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS='listViewMassActions pull-left paddingLeftMd'}
			</div>
			<div class="col-xs-10 col-sm-7">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="alert alert-info marginTop10 hide" id="moduleCacheAlert" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			{\App\Language::translate('LBL_CACHE_SELECTED_FILTERS', $MODULE_NAME)}&nbsp;
			<button type="button"
					class="pull-right btn btn-warning btn-xs marginRight10 cacheClear">{\App\Language::translate('LBL_CACHE_CLEAR', $MODULE_NAME)}</button>
		</div>
		<div class="hide">
			{foreach item=ITEM from=$ACTIVITY_TYPE}
				<span class="btn btn-success buttonCBr_Calendar_activitytype_{$ITEM}"
					  value="{$ITEM}">{\App\Language::translate($ITEM,$MODULE)}</span>
			{/foreach}
		</div>
		<div class="row">
			<div id="datesColumn">
				<p><!-- Divider --></p>
				<div class="col-md-1 col-sm-1 hidden-xs">
					<div class="dateList">
					</div>

					<div class="subDateList">
					</div>
				</div>
				<div id="calendarview" class="col-md-11 paddingLefttZero bottom_margin"></div>
			</div>
		</div>
		<div class="o-calendar-container">
			<div id="calendarview"></div>
		</div>
	</div>
{/strip}

