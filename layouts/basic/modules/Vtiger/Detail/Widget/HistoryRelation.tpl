{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Detail-Widget-HistoryRelation -->
	{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId(\App\Language::translate($WIDGET['label'],$MODULE_NAME))}"}
	<div class="c-detail-widget js-detail-widget" data-js="container">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}">
			<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
				<div class="d-flex w-100 align-items-center py-1">
					<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse" data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
						<span class="mdi mdi-chevron-up" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
					<span class="mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}"></span>
					</div>
					<h5 class="mb-0 mr-1 modCT_{$WIDGET['label']}">{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
					<div class="ml-auto w-100 u-max-w-md-60 input-group-sm">
						<select class="select2 relatedHistoryTypes" multiple>
							{foreach from=Vtiger_HistoryRelation_Widget::getActions() item=ACTIONS}
								<option selected value="{$ACTIONS}">{\App\Language::translate($ACTIONS, $ACTIONS)}</option>
							{/foreach}
						</select>
					</div>
					<button type="button" title="{\App\Language::translate('LBL_FULLSCREEN')}" data-title="{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}" class="widgetFullscreen btn btn-sm btn-light ml-1">
						<span class="fas fa-expand-arrows-alt"></span>
					</button>
				</div>
			</div>
			<div class="c-detail-widget__content widgetContent js-detail-widget-content collapse multi-collapse" id="{$WIDGET_UID}-collapse" aria-labelledby="{$WIDGET_UID}" data-js="container|value">
			</div>
		</div>
	</div>
<!-- /tpl-Base-Detail-Widget-HistoryRelation -->
{/strip}
