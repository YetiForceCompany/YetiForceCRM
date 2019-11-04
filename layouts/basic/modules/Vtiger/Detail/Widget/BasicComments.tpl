{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId(\App\Language::translate($WIDGET['label'],$MODULE_NAME))}"}
<div class="tpl-Detail-Widget-BasicComments c-detail-widget js-detail-widget BasicComments updatesWidgetContainer"
	data-js=”container”>
	<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}"
		data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}"
		data-limit="{$WIDGET['limit']}" data-js="data-url|data-type|data-limit">
		<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
			<input type="hidden" name="relatedModule" value="{$WIDGET['data']['relatedmodule']}" />
			<div class="c-detail-widget__header__container d-flex align-items-center my-1">
				<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse"
					data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
					<span class="mdi mdi-chevron-up" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
				</div>
				<div class="c-detail-widget__header__title">
					<h5 class="mb-0 modCT_{$WIDGET['label']}">
						{if $WIDGET['label'] eq ''}
							{\App\Language::translate($WIDGET['data']['relatedmodule'],$WIDGET['data']['relatedmodule'])}
						{else}
							{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}
						{/if}
					</h5>
				</div>
				<div
					class="c-detail-widget__actions q-fab z-fab row inline justify-center js-fab__container ml-auto quasar-reset">
					<button type="button" tabindex="0"
						class="js-fab__btn q-btn inline q-btn-item non-selectable no-outline q-btn--flat q-btn--round text-grey-6 q-focusable q-hoverable u-font-size-10px q-ml-auto">
						<div tabindex="-1" class="q-focus-helper"></div>
						<div class="q-btn__content text-center col items-center q-anchor--skip justify-center row">
							<i aria-hidden="true" class="mdi mdi-wrench q-icon"></i>
						</div>
					</button>
					<div class="q-fab__actions flex inline items-center q-fab__actions--left js-comment-actions">
						<div class="input-group input-group-sm">
							<input type="text" class="js-comment-search form-control"
								placeholder="{\App\Language::translate('LBL_COMMENTS_SEARCH','ModComments')}"
								aria-describedby="commentSearchAddon" data-container="widget" data-js="keypress|data">
							<div class="input-group-append">
								<button class="btn btn-light js-search-icon" type="button" data-js="click">
									<span class="fas fa-search fa-fw" title="{\App\Language::translate('LBL_SEARCH')}"></span>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="c-detail-widget__content js-detail-widget-collapse js-detail-widget-content collapse multi-collapse" id="{$WIDGET_UID}-collapse"
			data-storage-key="{$WIDGET['id']}" aria-labelledby="{$WIDGET_UID}" data-js="container|value">
		</div>
	</div>
</div>
{/strip}
