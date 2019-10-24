{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=WIDGET_UID value=\App\Layout::getUniqueId(\App\Language::translate($WIDGET['label'],$MODULE_NAME))}
	<div class="tpl-Detail-Widget-BasicComments c-detail-widget js-detail-widget BasicComments updatesWidgetContainer"
		 data-js=”container”>
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}"
			 data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}"
			 data-limit="{$WIDGET['limit']}"
			 data-js="data-url|data-type|data-limit">
			<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
				<input type="hidden" name="relatedModule" value="{$WIDGET['data']['relatedmodule']}"/>
				<div class="d-flex align-items-center my-1">
					<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse" data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
						<span class="mdi mdi-chevron-up" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
						<span class="mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}"></span>
					</div>
					<div class="mr-2">
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
					<div class="input-group input-group-sm u-max-w-250px ml-auto">
						<input type="text" class="js-comment-search form-control"
								placeholder="{\App\Language::translate('LBL_COMMENTS_SEARCH','ModComments')}"
								aria-describedby="commentSearchAddon"
								data-container="widget"
								data-js="keypress|data">
						<div class="input-group-append">
							<button class="btn btn-light js-search-icon" type="button"
									data-js="click">
								<span class="fas fa-search fa-fw" title="{\App\Language::translate('LBL_SEARCH')}"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="c-detail-widget__content js-detail-widget-content collapse multi-collapse" id="{$WIDGET_UID}-collapse" aria-labelledby="{$WIDGET_UID}" data-js="container|value">
			</div>
		</div>
	</div>
{/strip}
