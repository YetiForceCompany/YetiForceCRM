{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="currentView" value="{$VIEW}" />
	<input type="hidden" id="activity_view" value="{$CURRENT_USER->getDetail('activity_view')}" />
	<input type="hidden" id="date_format" value="{$CURRENT_USER->getDetail('date_format')}" />
	<input type="hidden" id="defaultDate" value="{$DEFAULTDATE}" />
	<div class="js-calendar__container" data-js="fullcalendar"></div>
{/strip}
