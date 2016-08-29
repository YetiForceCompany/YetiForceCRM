{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div  class="summaryWidgetContainer activityWidgetContainer">
		<div class="widget_header row">
			<div class="col-xs-5">
				<h4 class="widgetTitle textOverflowEllipsis">
					{if $WIDGET['label'] eq ''}
						{vtranslate('LBL_COUNT_RECORDS_WIDGET',$MODULE_NAME)}
					{else}	
						{vtranslate($WIDGET['label'],$MODULE_NAME)}
					{/if}
				</h4>
			</div>
		</div>
		<hr class="widgetHr">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}">
			<div class="widget_contents">
				
			</div>
		</div>
	</div>
{/strip}
