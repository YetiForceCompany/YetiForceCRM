<div class="summaryWidgetContainer">
	<div class="widget_header row">
		<span class="col-md-5 margin0px"><h4>{vtranslate($WIDGET['label'],$MODULE_NAME)}</h4></span>
	</div>
	<div class="defaultMarginP">{vtlib\Functions::removeHtmlTags(array('link', 'style', 'a', 'img', 'script', 'base'),decode_html($RECORD->get($WIDGET['data']['field_name'])))}</div>
</div>
