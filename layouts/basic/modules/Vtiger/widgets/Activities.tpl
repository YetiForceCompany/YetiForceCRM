{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<div  class="summaryWidgetContainer activityWidgetContainer">
	<div class="widget_header row">
		<div class="col-xs-5">
			<h4 class="widgetTitle textOverflowEllipsis">
				{if $WIDGET['label'] eq ''}
					{App\Language::translate('LBL_ACTIVITIES',$MODULE_NAME)}
				{else}	
					{App\Language::translate($WIDGET['label'],$MODULE_NAME)}
				{/if}
			</h4>
		</div>
		<div class="col-xs-5">
			<span class="float-right">
				<input class="switchBtn" title="{App\Language::translate('LBL_CHANGE_ACTIVITY_TYPE')}" type="checkbox" checked data-size="small" data-label-width="5" data-handle-width="100" data-on-text="{App\Language::translate('LBL_CURRENT')}" data-off-text="{App\Language::translate('LBL_HISTORY')}" data-basic-texton="{App\Language::translate('LBL_CURRENT')}" data-basic-textoff="{App\Language::translate('LBL_HISTORY')}">
			</span>
		</div>
		<div class="col-xs-2">
			<button class="btn btn-sm btn-light float-right addButton createActivity" data-url="sourceModule={$RECORD->getModuleName()}&sourceRecord={$RECORD->getId()}&relationOperation=true" type="button"
					 title="{App\Language::translate('LBL_ADD',$MODULE_NAME)}">
				<span class="fas fa-plus"></span>
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
