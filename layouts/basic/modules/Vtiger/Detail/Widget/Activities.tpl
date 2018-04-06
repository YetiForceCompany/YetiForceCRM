{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div  class="c-detail-widget mb-3 js-detail-widget activityWidgetContainer">
		<div class="c-detail-widget__header js-detail-widget-header">
			<div class="row align-items-center">
				<div class="col-9 col-md-5 col-sm-6">
					<div class="widgetTitle u-text-ellipsis">
						<h4>
							{if $WIDGET['label'] eq ''}
								{App\Language::translate('LBL_ACTIVITIES',$MODULE_NAME)}
							{else}	
								{App\Language::translate($WIDGET['label'],$MODULE_NAME)}
							{/if}
						</h4>
					</div>
				</div>

				<div class="col-8 col-md-4 col-sm-3">
						<input class="switchBtn" title="{App\Language::translate('LBL_CHANGE_ACTIVITY_TYPE')}" type="checkbox" checked data-size="small" data-label-width="5" data-handle-width="100" data-on-text="{App\Language::translate('LBL_CURRENT')}" data-off-text="{App\Language::translate('LBL_HISTORY')}" data-basic-texton="{App\Language::translate('LBL_CURRENT')}" data-basic-textoff="{App\Language::translate('LBL_HISTORY')}">
				</div>
				<div class="col float-right py-1">
					<button class="btn btn-sm btn-light float-right addButton createActivity" data-url="sourceModule={$RECORD->getModuleName()}&sourceRecord={$RECORD->getId()}&relationOperation=true" type="button"
							title="{App\Language::translate('LBL_ADD',$MODULE_NAME)}">
						<span class="fas fa-plus"></span>
					</button>
				</div>
			</div>
			<hr class="widgetHr">
		</div>
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}">
			<div class="c-detail-widget__content js-detail-widget-content">
			</div>
		</div>
	</div>
{/strip}
