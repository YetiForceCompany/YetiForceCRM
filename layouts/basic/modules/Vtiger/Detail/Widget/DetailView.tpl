{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Detail-Widget-DetailView c-detail-widget js-detail-widget" data-js="container">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['type']}">
			<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
				<div class="col-sm-5">
					<h5 class="mb-0 py-2">
						{if $WIDGET['label'] eq ''}
							{App\Language::translate('LBL_ACTIVITIES',$MODULE_NAME)}
						{else}
							{App\Language::translate($WIDGET['label'],$MODULE_NAME)}
						{/if}
					</h5>
				</div>
			</div>
			<hr class="widgetHr">
			<div class="c-detail-widget__content js-detail-widget-content" data-js="container|value">
			</div>
		</div>
	</div>
{/strip}
