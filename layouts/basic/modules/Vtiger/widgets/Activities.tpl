{strip}
<div  class="summaryWidgetContainer activityWidgetContainer">
	<div class="widget_header row">
		<div class="col-xs-5">
			<h4 class="widgetTitle textOverflowEllipsis">
				{if $WIDGET['label'] eq ''}
					{vtranslate('LBL_ACTIVITIES',$MODULE_NAME)}
				{else}	
					{vtranslate($WIDGET['label'],$MODULE_NAME)}
				{/if}
			</h4>
		</div>
		<div class="col-xs-5">
			<span class="pull-right">
				<input class="switchBtn" title="{vtranslate('LBL_CHANGE_ACTIVITY_TYPE')}" type="checkbox" checked data-size="small" data-label-width="5" data-handle-width="100" data-on-text="{vtranslate('LBL_CURRENT')}" data-off-text="{vtranslate('LBL_HISTORY')}">
			</span>
		</div>
		<div class="col-xs-2">
			<button class="btn btn-sm btn-default pull-right addButton createActivity" data-url="sourceModule={$RECORD->getModuleName()}&sourceRecord={$RECORD->getId()}&relationOperation=true" type="button"
					 title="{vtranslate('LBL_ADD',$MODULE_NAME)}">
				<span class="glyphicon glyphicon-plus"></span>
			</button>
		</div>
	</div>
	<hr class="widgetHr">
	<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}">
		<div class="widget_contents">
		</div>
	</div>
</div>
{/strip}
