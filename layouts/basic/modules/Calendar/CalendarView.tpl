{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="currentView" value="{$VIEW}" />
	<input type="hidden" id="activity_view" value="{\App\Purifier::encodeHtml($CURRENT_USER->get('activity_view'))}" />
	<input type="hidden" id="time_format" value="{$CURRENT_USER->get('hour_format')}" />
	<input type="hidden" id="start_hour" value="{$CURRENT_USER->get('start_hour')}" />
	<input type="hidden" id="end_hour" value="{$CURRENT_USER->get('end_hour')}" />
	<input type="hidden" id="date_format" value="{$CURRENT_USER->get('date_format')}" />
	<input type="hidden" id="showType" value="current" />
	<input type="hidden" id="switchingDays" value="workDays" />
	<input type="hidden" id="eventLimit" value="{$EVENT_LIMIT}" />
	<input type="hidden" id="weekView" value="{$WEEK_VIEW}" />
	<input type="hidden" id="dayView" value="{$DAY_VIEW}" />
	<input type="hidden" id="hiddenDays" value="{\App\Purifier::encodeHtml(\App\Json::encode(AppConfig::module('Calendar', 'HIDDEN_DAYS_IN_CALENDAR_VIEW')))}" />
	<input type="hidden" id="activityStateLabels" value="{\App\Purifier::encodeHtml($ACTIVITY_STATE_LABELS)}" />
	<div class="calendarViewContainer rowContent">
		<div class="widget_header d-flex align-items-center js-breadcrumb" data-js="height">
			<div class="px-2">
				{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS='listViewMassActions pull-left paddingLeftMd'}
			</div>
			<div class="px-2 mr-auto">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
			<div class="px-2">
				<button class="btn btn-light btn-sm addButton marginRight10">
					<span class="fas fa-plus"></span>
				</button>
			</div>
		</div>
		<div class="alert alert-info d-none mt-2 mb-0" id="moduleCacheAlert" role="alert">
			<div class="d-flex">
				<div class="mr-auto align-self-center">
					{\App\Language::translate('LBL_CACHE_SELECTED_FILTERS', $MODULE_NAME)}
				</div>
				<button type="button" class="btn btn-warning btn-sm cacheClear px-2">{\App\Language::translate('LBL_CACHE_CLEAR', $MODULE_NAME)}</button>
				<button type="button" class="close px-2 pb-1" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		</div>
		<div class="o-calendar-container">
			<div id="calendarview"></div>
		</div>
	</div>
{/strip}
