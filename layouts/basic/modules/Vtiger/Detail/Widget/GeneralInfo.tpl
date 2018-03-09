{strip}
{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
<div class="o-detail-widget o-detail-widget--general-info js-widget-general-info">
	<div class="o-detail-widget__header">
		<h4> {\App\Language::translate('LBL_RECORD_SUMMARY',$MODULE_NAME)}</h4>
		<hr>
	</div>
		{include file=\App\Layout::getTemplatePath('SummaryViewContents.tpl')}
	</div>
{/strip}
