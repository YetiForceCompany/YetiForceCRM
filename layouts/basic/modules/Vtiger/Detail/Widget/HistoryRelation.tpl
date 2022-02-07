{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-HistoryRelation -->
	{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId($WIDGET['id']|cat:_)}"}
	<div class="c-detail-widget js-detail-widget" data-js="container">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}"
			data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}">
			<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
				<div class="c-detail-widget__header__container d-flex w-100 align-items-center py-1">
					<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse"
						data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
						<span class="u-transform_rotate-180deg mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
					</div>
					<div class="c-detail-widget__header__title">
						<h5 class="mb-0">{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
					</div>
					<div
						class="row inline justify-center js-hb__container ml-auto">
						<button type="button" tabindex="0"
							class="btn js-hb__btn u-hidden-block-btn text-grey-6 py-0 px-1">
							<div class="text-center col items-center justify-center row">
								<i aria-hidden="true" class="mdi mdi-wrench q-icon"></i>
							</div>
						</button>
						<div class="u-hidden-block items-center js-comment-actions">
							<button type="button" title="{\App\Language::translate('LBL_FULLSCREEN')}"
								data-title="{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}"
								class="widgetFullscreen btn btn-sm btn-light ml-1">
								<span class="fas fa-expand-arrows-alt"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="c-detail-widget__content js-detail-widget-collapse collapse multi-collapse pt-1" id="{$WIDGET_UID}-collapse"
				data-storage-key="{$WIDGET['id']}" aria-labelledby="{$WIDGET_UID}" data-js="container|value">
				<div class="w-100 mb-2 input-group-sm">
					<select class="select2 relatedHistoryTypes" multiple>
						{foreach from=Vtiger_HistoryRelation_Widget::getActions() item=ACTIONS}
							<option selected value="{$ACTIONS}">{\App\Language::translate($ACTIONS, $ACTIONS)}</option>
						{/foreach}
					</select>
				</div>
				<div class="js-detail-widget-content widgetContent"></div>
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Detail-Widget-HistoryRelation -->
{/strip}
