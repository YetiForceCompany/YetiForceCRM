{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-dashboards-Updates dashboardWidgetHeader">
		<input type="hidden" value="{$WIDGET->get('id')}" id="updatesWidgetId">
		<input type="hidden" value="{$WIDGET->get('data')}" id="widgetData">
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
			<a class="js-show-update-widget-config btn btn-sm btn-light" data-js="click"">
					<span class="fas fa-cog" title="{\App\Language::translate('LBL_WIDGET_CONFIGURATOR')}"></span>
			</a>
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderButtons.tpl', $MODULE_NAME)}
		</div>
		<hr class="widgetHr" />
		<div class="row no-gutters justify-content-end">
			<div class="col-ceq-xsm-6 input-group input-group-sm">
				<div class="input-group-prepend">
					<span class="input-group-text">
						<span class="fas fa-filter"></span>
					</span>
				</div>
				<select class="widgetFilter form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm" id="historyType" title="{\App\Language::translate('LBL_HISTORY_TYPE')}" name="type">
					<option title="{\App\Language::translate('LBL_ALL')}" value="all" {if isset($DATA['type']) && $DATA['type'] eq 'all'}selected{/if}>{\App\Language::translate('LBL_ALL')}</option>
						<option title="{\App\Language::translate('LBL_COMMENTS')}" value="comments" {if isset($DATA['type']) && $DATA['type'] eq 'comments'}selected{/if}>{\App\Language::translate('LBL_COMMENTS')}</option>
						<option value="updates" title="{\App\Language::translate('LBL_UPDATES')}" {if isset($DATA['type']) && $DATA['type'] eq 'updates'}selected{/if}>{\App\Language::translate('LBL_UPDATES')}</option>
					</select>
				</div>
			</div>
		</div>
		<div class="dashboardWidgetContent">
			{include file=\App\Layout::getTemplatePath('dashboards/UpdatesContents.tpl', $MODULE_NAME)}
		</div>
		{/strip}
