{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="currentView" value="{$VIEW}" />
	<input type="hidden" id="activity_view" value="{\App\Purifier::encodeHtml($CURRENT_USER->get('activity_view'))}" />
	<input type="hidden" id="time_format" value="{$CURRENT_USER->get('hour_format')}" />
	<input type="hidden" id="start_hour" value="{$CURRENT_USER->get('start_hour')}" />
	<input type="hidden" id="end_hour" value="{$CURRENT_USER->get('end_hour')}" />
	<input type="hidden" id="date_format" value="{$CURRENT_USER->get('date_format')}" />
	<input type="hidden" id="eventLimit" value="{$EVENT_LIMIT}" />
	<input type="hidden" id="weekView" value="{$WEEK_VIEW}" />
	<input type="hidden" id="dayView" value="{$DAY_VIEW}" />
	<div class="calendarViewContainer rowContent">
		<div class="widget_header d-flex align-items-center">
			<div class="px-2">
				{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS='listViewMassActions pull-left paddingLeftMd'}
			</div>
			<div class="px-2 mr-auto">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="o-calendar-container">
			<p class="m-0"><!-- Divider --></p>
			<div id="calendarview"></div>
		</div>
	</div>
{/strip}
