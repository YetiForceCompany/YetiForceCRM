{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="c-detail-widget mb-3 js-detail-widget">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}">
			<div class="c-detail-widget__header js-detail-widget-header">
				<div class="widgetTitle row">
					<div class="col-4">
						<h4 class="modCT_{$WIDGET['label']}">{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h4>
					</div>
					<div class="col-7">
						<select class="select2 relatedHistoryTypes" multiple>
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
			</div>
			<hr class="widgetHr">
			<div class="c-detail-widget__content js-detail-widget-content widgetContent">
			</div>
		</div>
	</div>
{/strip}
