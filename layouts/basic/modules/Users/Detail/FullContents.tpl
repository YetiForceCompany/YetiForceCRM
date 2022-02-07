{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Users-Detail-FullContents -->
	<input type="hidden" name="timeFormatOptions" data-value="{\App\Purifier::encodeHtml($DAY_STARTS)}" />
	{include file=\App\Layout::getTemplatePath('DetailViewBlockLink.tpl', $MODULE_NAME) TYPE_VIEW='DetailTop'}
	{include file=\App\Layout::getTemplatePath('Detail/BlocksView.tpl', $MODULE_NAME) RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
	{include file=\App\Layout::getTemplatePath('DetailViewBlockLink.tpl', $MODULE_NAME) TYPE_VIEW='DetailBottom'}
	<!-- /tpl-Users-Detail-FullContents -->
{/strip}
