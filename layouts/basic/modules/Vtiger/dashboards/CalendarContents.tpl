{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="currentView" value="{$VIEW}" />
	<input type="hidden" id="activity_view" value="{$CURRENT_USER->get('activity_view')}" />
	<input type="hidden" id="time_format" value="{$CURRENT_USER->get('hour_format')}" />
	<input type="hidden" id="start_hour" value="{$CURRENT_USER->get('start_hour')}" />
	<input type="hidden" id="end_hour" value="{$CURRENT_USER->get('end_hour')}" />
	<input type="hidden" id="date_format" value="{$CURRENT_USER->get('date_format')}" />
	<input type="hidden" id="defaultDate" value="{$DEFAULTDATE}" />
	<div id="calendarview"></div>
{/strip}
