{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-Activities -->
	<div class="c-detail-widget u-mb-13px js-detail-widget activityWidgetContainer" data-js="container">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}"
			 data-name="{$WIDGET['label']}">
			<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
				<div class="form-row align-items-center py-1">
					<div class="col-9 col-md-5 col-sm-6">
						<div class="widgetTitle u-text-ellipsis">
							<h5 class="mb-0">
								{if $WIDGET['label'] eq ''}
									{App\Language::translate('LBL_ACTIVITIES',$MODULE_NAME)}
								{else}
									{App\Language::translate($WIDGET['label'],$MODULE_NAME)}
								{/if}
							</h5>
						</div>
					</div>
					<div class="btn-group btn-group-toggle" data-toggle="buttons">
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
					<div class="col float-right">
						<button class="btn btn-sm btn-light float-right addButton createActivity"
								data-url="sourceModule={$RECORD->getModuleName()}&sourceRecord={$RECORD->getId()}&relationOperation=true"
								type="button"
								title="{App\Language::translate('LBL_ADD',$MODULE_NAME)}">
							<span class="fas fa-plus"></span>
						</button>
					</div>
				</div>
				<hr class="widgetHr">
			</div>
			<div class="c-detail-widget__content js-detail-widget-content" data-js="container|value">
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Detail-Widget-Activities -->
{/strip}
