{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Detail-Widget-Updates -->
{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId(\App\Language::translate($WIDGET['label'],$MODULE_NAME))}"}
<div class="tpl-Detail-Widget-Updates c-detail-widget js-detail-widget" data-js="container">
	<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}">
		<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
			<div class="c-detail-widget__header__container d-flex align-items-center py-1 pr-3">
				<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse" data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
					<span class="mdi mdi-chevron-up" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
				</div>
				<div class="c-detail-widget__header__title">
					<h5 class="mb-0 modCT_{$WIDGET['label']}" title="{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}">
						{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}
					</h5>
				</div>
				<div class="c-detail-widget__actions q-fab z-fab row inline justify-center js-fab__container ml-auto quasar-reset">
					<button type="button" tabindex="0" class="js-fab__btn q-btn inline q-btn-item non-selectable no-outline q-btn--flat q-btn--round text-grey-6 q-focusable q-hoverable u-font-size-10px q-ml-auto">
						<div tabindex="-1" class="q-focus-helper"></div>
						<div class="q-btn__content text-center col items-center q-anchor--skip justify-center row">
							<i aria-hidden="true" class="mdi mdi-wrench q-icon"></i>
						</div>
					</button>
					<div class="q-fab__actions flex inline items-center q-fab__actions--left js-comment-actions">
						{if isset($WIDGET['switchHeader'])}
							<div class="btn-group btn-group-toggle ml-auto" data-toggle="buttons">
								<label class="btn btn-sm btn-outline-primary active">
									<input class="js-switch" type="radio" name="options" id="option1" data-js="change" data-on-val="{$WIDGET['switchHeader']['on']}" data-urlparams="whereCondition" autocomplete="off" checked> <span
										class="fas fa-redo" title="{$WIDGET['switchHeaderLables']['on']}"></span>
								</label>
								<label class="btn btn-sm btn-outline-primary">
									<input class="js-switch" type="radio" name="options" id="option2" data-js="change" data-off-val="{$WIDGET['switchHeader']['off']}" data-urlparams="whereCondition" autocomplete="off">
									<span class="fas fa-history" title="{$WIDGET['switchHeaderLables']['off']}"></span>
								</label>
							</div>
						{/if}
						{if $WIDGET['newChanege'] && $MODULE_MODEL->isPermitted('ReviewingUpdates') && $USER_MODEL->getId() eq $USER_MODEL->getRealId()}
							<div class="text-right ml-auto">
								<div class="btn-group">
									<div class="btn-group">
										<button id="btnChangesReviewedOn" type="button" class="btn btn-success btn-sm btnChangesReviewedOn" title="{\App\Language::translate('BTN_CHANGES_REVIEWED_ON', $WIDGET['moduleBaseName'])}">
											<span class="far fa-check-circle"></span>
										</button>
									</div>
								</div>
							</div>
						{/if}
					</div>
				</div>
			</div>
		</div>
		<div class="c-detail-widget__content js-detail-widget-collapse js-detail-widget-content collapse multi-collapse" id="{$WIDGET_UID}-collapse" data-storage-key="{$WIDGET['id']}" aria-labelledby="{$WIDGET_UID}" data-js="container|value">
		</div>
	</div>
</div>
<!-- /tpl-Base-Detail-Widget-Updates -->
{/strip}
