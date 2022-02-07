{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{include file=\App\Layout::getTemplatePath('QuickCreate.tpl', 'Vtiger')}
	<input value="{\App\Config::module($MODULE_NAME, 'CHECK_IF_RECORDS_HAS_TIME_CONTROL')}" type="hidden" id="checkIfRecordHasTimeControl">
	<input value="{\App\Config::module($MODULE_NAME, 'CHECK_IF_RELATED_TICKETS_ARE_CLOSED')}" type="hidden" id="checkIfRelatedTicketsAreClosed">
	<input value='{\App\Purifier::encodeHtml(\App\Json::encode(array_flip(\App\RecordStatus::getStates($MODULE_NAME, \App\RecordStatus::RECORD_STATE_CLOSED))))}' type="hidden" id="closeTicketForStatus">
{/strip}
