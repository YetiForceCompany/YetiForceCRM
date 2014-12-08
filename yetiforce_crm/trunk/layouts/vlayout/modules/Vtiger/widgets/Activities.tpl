<div  class="summaryWidgetContainer">
	<div class="widget_header row-fluid">
		<span class="span8"><h4 class="textOverflowEllipsis">{vtranslate('LBL_ACTIVITIES',$MODULE_NAME)}</h4></span>
		<span class="span4"><button class="btn pull-right addButton createActivity" data-url="sourceModule={$RECORD->getModuleName()}&sourceRecord={$RECORD->getId()}&relationOperation=true" type="button"><strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong></button></span>
	</div>
	<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}">
		<div class="widget_contents">
		</div>
	</div>
</div>