<div class="summaryWidgetContainer">
	<div class="widget_header row-fluid">
		<span class="span5 margin0px"><h4>{vtranslate($WIDGET['label'],$MODULE_NAME)}</h4></span>
	</div>
	{assign var="BODY" value=Vtiger_Functions::removeHtmlTags('style',decode_html($RECORD->get($WIDGET['data']['field_name'])))}
	<div class="defaultMarginP">{$BODY}</div>
</div>