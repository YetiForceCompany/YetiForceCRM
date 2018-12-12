{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Calendar-Standard-QuickCreate quick-calendar-modal">
		<input name="defaultOtherEventDuration"
			   value="{\App\Purifier::encodeHtml($USER_MODEL->get('othereventduration'))}" type="hidden"/>
		<input value="{AppConfig::module($MODULE, 'CALENDAR_VIEW')}" type="hidden" class="js-calendar-type" data-js="value">
		{include file=\App\Layout::getTemplatePath('QuickCreate.tpl', 'Vtiger')}
		<input value="{AppConfig::module($MODULE, 'SHOW_DAYS_QUICKCREATE')}" type="hidden" class="showEventsTable" id="showEventsTable">
	</div>
{/strip}
