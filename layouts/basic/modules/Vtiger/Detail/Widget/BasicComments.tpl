{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Detail-Widget-BasicComments c-detail-widget u-mb-13px js-detail-widget BasicComments updatesWidgetContainer"
		 data-js=”container”>
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}"
			 data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}"
			 data-limit="{$WIDGET['limit']}"
			 data-js="data-url|data-type|data-limit">
			<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
				<input type="hidden" name="relatedModule" value="{$WIDGET['data']['relatedmodule']}"/>
				<div class="form-row align-items-center my-1">
					<div class="col-9 col-md-5 col-sm-6">
						<div class="widgetTitle u-text-ellipsis">
							<h5 class="mb-0 modCT_{$WIDGET['label']}">
								{if $WIDGET['label'] eq ''}
									{\App\Language::translate($WIDGET['data']['relatedmodule'],$WIDGET['data']['relatedmodule'])}
								{else}
									{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}
								{/if}
							</h5>
						</div>
					</div>
				</div>
				<hr class="widgetHr"/>
			</div>
			<div class="c-detail-widget__content js-detail-widget-content" data-js="container|value">
			</div>
		</div>
	</div>
{/strip}
