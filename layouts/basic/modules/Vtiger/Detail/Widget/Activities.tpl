{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-Activities -->
	{assign var=WIDGET_UID value=\App\Layout::getUniqueId(\App\Language::translate($WIDGET['label'],$MODULE_NAME))}
	<div class="c-detail-widget js-detail-widget activityWidgetContainer" data-js="container">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}"
			 data-name="{$WIDGET['label']}">
			<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
				<div class="d-flex w-100 align-items-center py-1">
					<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse" data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
						<span class="mdi mdi-chevron-up" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
					<span class="mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}"></span>
					</div>
					<div class="widgetTitle u-text-ellipsis">
						<h5 class="mb-0">
							{if $WIDGET['label'] eq ''}
								{App\Language::translate('LBL_ACTIVITIES',$MODULE_NAME)}
							{else}
								{App\Language::translate($WIDGET['label'],$MODULE_NAME)}
							{/if}
						</h5>
					</div>
					<div class="ml-auto btn-group btn-group-toggle" data-toggle="buttons">
						<label class="btn btn-sm btn-outline-primary active">
							<input class="js-switch" type="radio" name="options" id="option1" data-js="change"
								   data-on-text="{App\Language::translate('LBL_CURRENT')}"
								   data-on-val="{if isset($WIDGET['switchHeader']['on'])}{\App\Purifier::encodeHtml($WIDGET['switchHeader']['on'])}{/if}"
								   data-basic-text="{App\Language::translate('LBL_CURRENT')}"
								   autocomplete="off"> {App\Language::translate('LBL_CURRENT')}
						</label>
						<label class="btn btn-sm btn-outline-primary">
							<input class="js-switch" type="radio" name="options" id="option2" data-js="change"
								   data-basic-text="{App\Language::translate('LBL_HISTORY')}"
								   data-off-text="data-off-text {App\Language::translate('LBL_HISTORY')}"
								   data-off-val="{if isset($WIDGET['switchHeader']['off'])}{\App\Purifier::encodeHtml($WIDGET['switchHeader']['off'])}{/if}"
								   autocomplete="off"> {App\Language::translate('LBL_HISTORY')}
						</label>
					</div>
					<button class="btn btn-sm btn-light ml-1 addButton createActivity"
							data-url="sourceModule={$RECORD->getModuleName()}&sourceRecord={$RECORD->getId()}&relationOperation=true"
							type="button"
							title="{App\Language::translate('LBL_ADD',$MODULE_NAME)}">
						<span class="fas fa-plus"></span>
					</button>
				</div>
			</div>
			<div class="c-detail-widget__content js-detail-widget-content collapse multi-collapse" id="{$WIDGET_UID}-collapse" aria-labelledby="{$WIDGET_UID}" data-js="container|value">
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Detail-Widget-Activities -->
{/strip}
