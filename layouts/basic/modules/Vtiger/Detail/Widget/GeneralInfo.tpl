{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<div class="c-detail-widget u-mb-13px c-detail-widget--general-info js-widget-general-info" data-js="edit/save">
		<div class="c-detail-widget__header">
			<h5 class="mb-0 py-2"> {\App\Language::translate('LBL_RECORD_SUMMARY',$MODULE_NAME)}</h5>
			<hr />
		</div>
		{include file=\App\Layout::getTemplatePath('Detail/Widget/GeneralInfoContent.tpl', $MODULE_NAME)}
	</div>
{/strip}
