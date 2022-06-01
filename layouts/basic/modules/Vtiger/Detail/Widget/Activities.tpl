{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-Activities -->
	{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId($WIDGET['id']|cat:_)}"}
	<div class="c-detail-widget js-detail-widget activityWidgetContainer" data-js="container">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}" data-name="{$WIDGET['label']}">
			<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
				<div class="c-detail-widget__header__container d-flex w-100 align-items-center py-1">
					<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse"
						data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
						<span class="u-transform_rotate-180deg mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
					</div>
					<div class="c-detail-widget__header__title">
						<h5 class="mb-0 modCT_Calendar" title="{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}">
							{if empty($WIDGET['label'])}
								{App\Language::translate('LBL_ACTIVITIES',$MODULE_NAME)}
							{else}
								{App\Language::translate($WIDGET['label'],$MODULE_NAME)}
							{/if}
						</h5>
					</div>
					<div
						class="row inline justify-center js-hb__container ml-auto">
						<button type="button" tabindex="0" class="btn js-hb__btn u-hidden-block-btn text-grey-6 py-0 px-1">
							<div class="text-center col items-center justify-center row">
								<i aria-hidden="true" class="mdi mdi-wrench q-icon"></i>
							</div>
						</button>
						<div class="u-hidden-block items-center js-comment-actions">
							{if isset($WIDGET['switchTypeInHeader'])}
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-sm btn-outline-primary active">
										<input class="js-switch" type="radio" name="options" id="options-option1" data-js="change"
											data-on-text="{App\Language::translate('LBL_CURRENT')}"
											data-params="{\App\Purifier::encodeHtml(\App\Json::encode(['orderby' => ['date_start' => 'ASC', 'time_start' => 'ASC']]))}"
											data-on-val="{if isset($WIDGET['switchTypeInHeader']['on'])}{\App\Purifier::encodeHtml($WIDGET['switchTypeInHeader']['on'])}{/if}"
											data-basic-text="{App\Language::translate('LBL_CURRENT')}" autocomplete="off" checked="checked" data-urlparams="search_params">
										{App\Language::translate('LBL_CURRENT')}
									</label>
									<label class="btn btn-sm btn-outline-primary">
										<input class="js-switch" type="radio" name="options" id="options-option2" data-js="change"
											data-basic-text="{App\Language::translate('LBL_HISTORY')}"
											data-off-text="data-off-text {App\Language::translate('LBL_HISTORY')}"
											data-params="{\App\Purifier::encodeHtml(\App\Json::encode(['orderby' => ['date_start' => 'DESC', 'time_start' => 'DESC']]))}"
											data-off-val="{if isset($WIDGET['switchTypeInHeader']['off'])}{\App\Purifier::encodeHtml($WIDGET['switchTypeInHeader']['off'])}{/if}"
											autocomplete="off" data-urlparams="search_params"> {App\Language::translate('LBL_HISTORY')}
									</label>
								</div>
							{/if}
							{if !$IS_READ_ONLY}
								<button class="btn btn-sm btn-light addButton createActivity"
									data-url="sourceModule={$RECORD->getModuleName()}&sourceRecord={$RECORD->getId()}&relationOperation=true"
									type="button" title="{App\Language::translate('LBL_ADD',$MODULE_NAME)}">
									<span class="fas fa-plus"></span>
								</button>
							{/if}
						</div>
					</div>
				</div>
			</div>
			<div class="c-detail-widget__content js-detail-widget-collapse js-detail-widget-content collapse multi-collapse" id="{$WIDGET_UID}-collapse"
				data-storage-key="{$WIDGET['id']}" aria-labelledby="{$WIDGET_UID}" data-js="container|value">
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Detail-Widget-Activities -->
{/strip}
