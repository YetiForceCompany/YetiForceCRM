{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Detail-Widget-Updates c-detail-widget js-detail-widget" data-js="container">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}"
			 data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}">
			<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
				<div class="d-flex align-items-center py-1">
					<span class="mdi mdi-chevron-up mx-2 u-font-size-26" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
					<span class="mdi mdi-chevron-down mx-2 u-font-size-26" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}"></span>
						<div class="widgetTitle u-text-ellipsis">
							<h5 class="mb-0 modCT_{$WIDGET['label']}">
								{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}
							</h5>
						</div>
					{if isset($WIDGET['switchHeader'])}
						<div class="btn-group btn-group-toggle ml-auto" data-toggle="buttons">
							<label class="btn btn-sm btn-outline-primary active">
								<input class="js-switch" type="radio" name="options" id="option1" data-js="change"
									   data-on-val="{$WIDGET['switchHeader']['on']}" data-urlparams="whereCondition"
									   autocomplete="off"
									   checked> <span class="fas fa-redo" title="{$WIDGET['switchHeaderLables']['on']}"></span>
							</label>
							<label class="btn btn-sm btn-outline-primary">
								<input class="js-switch" type="radio" name="options" id="option2" data-js="change"
									   data-off-val="{$WIDGET['switchHeader']['off']}"
									   data-urlparams="whereCondition"
									   autocomplete="off"> <span class="fas fa-history" title="{$WIDGET['switchHeaderLables']['off']}"></span>
							</label>
						</div>
					{/if}
					{if $WIDGET['newChanege'] && $MODULE_MODEL->isPermitted('ReviewingUpdates') && $USER_MODEL->getId() eq $USER_MODEL->getRealId()}
						<div class="text-right ml-auto">
							<div class="btn-group">
									<div class="btn-group">
										<button id="btnChangesReviewedOn" type="button"
												class="btn btn-success btn-sm btnChangesReviewedOn"
												title="{\App\Language::translate('BTN_CHANGES_REVIEWED_ON', $WIDGET['moduleBaseName'])}">
											<span class="far fa-check-circle"></span>
										</button>
									</div>
							</div>
						</div>
					{/if}
				</div>
				<hr class="widgetHr mt-0"/>
			</div>
			<div class="c-detail-widget__content js-detail-widget-content collapse multi-collapse" id="{$WIDGET['label']}-collapse" aria-labelledby="{$WIDGET['label']}" data-js="container|value">
			</div>
		</div>
	</div>
{/strip}
