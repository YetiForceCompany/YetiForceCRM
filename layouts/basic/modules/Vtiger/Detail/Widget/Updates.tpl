{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-Updates -->
	{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId($WIDGET['id']|cat:_)}"}
	<div class="tpl-Detail-Widget-Updates c-detail-widget js-detail-widget" data-js="container">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}">
			<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
				<div class="c-detail-widget__header__container d-flex align-items-center py-1 pr-3">
					<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse" data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
						<span class="u-transform_rotate-180deg mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
					</div>
					<div class="c-detail-widget__header__title">
						<h5 class="mb-0 modCT_{$WIDGET['moduleBaseName']}" title="{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}">
							{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}
						</h5>
					</div>
					<div class="row inline justify-center js-hb__container ml-auto">
						<button type="button" tabindex="0" class="btn js-hb__btn u-hidden-block-btn text-grey-6 py-0 px-1">
							<div class="text-center col items-center justify-center row">
								<i aria-hidden="true" class="mdi mdi-wrench"></i>
							</div>
						</button>
						<div class="u-hidden-block items-center js-comment-actions">
							{if isset($WIDGET['switchHeader'])}
								<div class="btn-group btn-group-toggle ml-auto" data-toggle="buttons">
									<label class="btn btn-sm btn-outline-primary active">
										<input class="js-switch" type="radio" name="options" id="condition-option1" data-js="change" data-on-val="{$WIDGET['switchHeader']['on']}" data-urlparams="whereCondition" autocomplete="off" checked> <span
											class="fas fa-redo" title="{$WIDGET['switchHeaderLables']['on']}"></span>
									</label>
									<label class="btn btn-sm btn-outline-primary">
										<input class="js-switch" type="radio" name="options" id="condition-option2" data-js="change" data-off-val="{$WIDGET['switchHeader']['off']}" data-urlparams="whereCondition" autocomplete="off">
										<span class="fas fa-history" title="{$WIDGET['switchHeaderLables']['off']}"></span>
									</label>
								</div>
							{/if}
							{if $WIDGET['newChanege'] && $MODULE_MODEL->isPermitted('ReviewingUpdates') && $USER_MODEL->getId() eq $USER_MODEL->getRealId()}
								<div class="btn-group">
									<button id="btnChangesReviewedOn" type="button" class="btn btn-success btn-sm btnChangesReviewedOn ml-1" title="{\App\Language::translate('BTN_CHANGES_REVIEWED_ON', $WIDGET['moduleBaseName'])}">
										<span class="far fa-check-circle"></span>
									</button>
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
