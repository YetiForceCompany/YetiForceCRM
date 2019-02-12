{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="c-detail-widget u-mb-13px js-detail-widget" data-js="container">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}">
			<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
				<div class="widgetTitle form-row align-items-center py-1">
					<div class="col-4">
						<h5 class="mb-0 modCT_{$WIDGET['label']}">{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
					</div>
					<div class="col-7">
						<select class="select2 form-control form-control-sm relatedHistoryTypes" multiple>
							{foreach from=Vtiger_HistoryRelation_Widget::getActions() item=ACTIONS}
								<option selected value="{$ACTIONS}">{\App\Language::translate($ACTIONS, $ACTIONS)}</option>
							{/foreach}
						</select>
					</div>
					<div class="col-1 text-right">
						<button type="button" title="{\App\Language::translate('LBL_FULLSCREEN')}" data-title="{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}" class="widgetFullscreen btn btn-sm btn-light">
							<span class="fas fa-expand-arrows-alt"></span>
						</button>
					</div>
				</div>
				<hr class="widgetHr">
			</div>
			<div class="c-detail-widget__content js-detail-widget-content widgetContent" data-js="container|value">
			</div>
		</div>
	</div>
{/strip}
