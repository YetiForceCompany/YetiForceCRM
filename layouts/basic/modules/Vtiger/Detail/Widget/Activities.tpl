{*
<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Detail-Widget-Activities -->
{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId(\App\Language::translate($WIDGET['label'],$MODULE_NAME))}"}
<div class="c-detail-widget js-detail-widget activityWidgetContainer" data-js="container">
	<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}">
		<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
			<div class="c-detail-widget__header__container d-flex w-100 align-items-center py-1">
				<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse"
					data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
					<span class="mdi mdi-chevron-up" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>

				</div>
				<div class="c-detail-widget__header__title">
					<h5 class="mb-0 modCT_{$WIDGET['label']}" title="{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}">
						{if $WIDGET['label'] eq ''}
						{App\Language::translate('LBL_ACTIVITIES',$MODULE_NAME)}
						{else}
						{App\Language::translate($WIDGET['label'],$MODULE_NAME)}
						{/if}
					</h5>
				</div>
				<div
					class="c-detail-widget__actions q-fab z-fab row inline justify-center js-comment-actions__container ml-auto quasar-reset">
					<button type="button" tabindex="0"
						class="js-comment-actions__btn q-btn inline q-btn-item non-selectable no-outline q-btn--flat q-btn--round text-grey-6 q-focusable q-hoverable u-font-size-10px q-ml-auto">
						<div tabindex="-1" class="q-focus-helper"></div>
						<div class="q-btn__content text-center col items-center q-anchor--skip justify-center row">
							<i aria-hidden="true" class="mdi mdi-wrench q-icon"></i>
						</div>
					</button>
					<div class="q-fab__actions flex inline items-center q-fab__actions--left js-comment-actions">
						<div class="btn-group btn-group-toggle" data-toggle="buttons">
							<label class="btn btn-sm btn-outline-primary active">
								<input class="js-switch" type="radio" name="options" id="option1" data-js="change"
									data-on-text="{App\Language::translate('LBL_CURRENT')}"
									data-on-val="{if isset($WIDGET['switchHeader']['on'])}{\App\Purifier::encodeHtml($WIDGET['switchHeader']['on'])}{/if}"
									data-basic-text="{App\Language::translate('LBL_CURRENT')}" autocomplete="off">
								{App\Language::translate('LBL_CURRENT')}
							</label>
							<label class="btn btn-sm btn-outline-primary">
								<input class="js-switch" type="radio" name="options" id="option2" data-js="change"
									data-basic-text="{App\Language::translate('LBL_HISTORY')}"
									data-off-text="data-off-text {App\Language::translate('LBL_HISTORY')}"
									data-off-val="{if isset($WIDGET['switchHeader']['off'])}{\App\Purifier::encodeHtml($WIDGET['switchHeader']['off'])}{/if}"
									autocomplete="off"> {App\Language::translate('LBL_HISTORY')}
							</label>
						</div>
						<button class="btn btn-sm btn-light addButton createActivity"
							data-url="sourceModule={$RECORD->getModuleName()}&sourceRecord={$RECORD->getId()}&relationOperation=true"
							type="button" title="{App\Language::translate('LBL_ADD',$MODULE_NAME)}">
							<span class="fas fa-plus"></span>
						</button>
					</div>
				</div>
			</div>
		</div>
		<div class="c-detail-widget__content js-detail-widget-content collapse multi-collapse" id="{$WIDGET_UID}-collapse"
			data-storage-key="{$WIDGET['id']}" aria-labelledby="{$WIDGET_UID}" data-js="container|value">
		</div>
	</div>
</div>
<!-- /tpl-Base-Detail-Widget-Activities -->
{/strip}
