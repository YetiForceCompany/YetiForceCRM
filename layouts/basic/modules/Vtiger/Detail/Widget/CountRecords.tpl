{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div  class="c-detail-widget u-mb-13px js-detail-widget activityWidgetContainer" data-js=”container”>
		<div class="c-detail-widget__header js-detail-widget-header row" data-js=”container|value>
			<div class="col-5">
				<h4 class="widgetTitle u-text-ellipsis">
					{if $WIDGET['label'] eq ''}
						{\App\Language::translate('LBL_COUNT_RECORDS_WIDGET',$MODULE_NAME)}
					{else}	
						{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}
					{/if}
				</h4>
			</div>
		</div>
		<hr class="widgetHr">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}">
			<div class="c-detail-widget__content js-detail-widget-content" data-js=”container|value”>

			</div>
		</div>
	</div>
{/strip}
