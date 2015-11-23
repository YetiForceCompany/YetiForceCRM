<div  class="summaryWidgetContainer activityWidgetContainer">
	<div class="widget_header row">
		<span class="col-md-4"><h4 class="textOverflowEllipsis">{vtranslate('LBL_ACTIVITIES',$MODULE_NAME)}</h4></span>
		<span class="col-md-5">
			<span class="pull-right">
				<input class="switchBtn" title="{vtranslate('LBL_CHANGE_ACTIVITY_TYPE')}" type="checkbox" checked data-size="small" data-label-width="5" data-handle-width="100" data-on-text="{vtranslate('LBL_CURRENT')}" data-off-text="{vtranslate('LBL_HISTORY')}">
			</span>
		</span>
		<div class="col-md-3">
			<button class="btn btn-sm btn-default pull-right addButton createActivity" data-url="sourceModule={$RECORD->getModuleName()}&sourceRecord={$RECORD->getId()}&relationOperation=true" type="button">
				<span class="glyphicon glyphicon-plus" border="0" title="{vtranslate('LBL_ADD',$MODULE_NAME)}" alt="{vtranslate('LBL_ADD',$MODULE_NAME)}"></span>
			</button>
		</div>
	</div>
	<hr class="widgetHr">
	<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}">
		<div class="widget_contents">
		</div>
	</div>
</div>
