{*
<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Detail-Widget-HistoryRelation -->
{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId(\App\Language::translate($WIDGET['label'],$MODULE_NAME))}"}
<div class="c-detail-widget js-detail-widget" data-js="container">
	<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}"
		data-type="{$WIDGET['type']}">
		<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
			<div class="c-detail-widget__header__container d-flex w-100 align-items-center py-1">
				<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse"
					data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
					<span class="mdi mdi-chevron-up" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>

				</div>
				<div class="c-detail-widget__header__title">
					<h5 class="mb-0 modCT_{$WIDGET['label']}">
						{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
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
