{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Detail-Widget-DetailView c-detail-widget js-detail-widget" data-js="container">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['type']}">
			<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
				<div class="d-flex w-100 align-items-center py-1">
					<span class="mdi mdi-chevron-up mx-2 u-font-size-26" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
					<span class="mdi mdi-chevron-down mx-2 u-font-size-26" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}"></span>
					<h5 class="mb-0">
						{if $WIDGET['label'] eq ''}
							{App\Language::translate('LBL_ACTIVITIES',$MODULE_NAME)}
						{else}
							{App\Language::translate($WIDGET['label'],$MODULE_NAME)}
						{/if}
					</h5>
				</div>
			</div>
			<div class="c-detail-widget__content js-detail-widget-content collapse multi-collapse" id="{$WIDGET['label']}-collapse" aria-labelledby="{$WIDGET['label']}" data-js="container|value">
			</div>
		</div>
	</div>
{/strip}
