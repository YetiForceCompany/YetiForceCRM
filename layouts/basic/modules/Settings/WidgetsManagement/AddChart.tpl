{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div id="addNotePadWidgetContainer" class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button data-dismiss="modal" class="close" title="{\App\Language::translate('LBL_CLOSE')}">&times;</button>
					<h3 id="massEditHeader" class="modal-title">{\App\Language::translate('LBL_ADD', $MODULE)} {\App\Language::translate('LBL_ADD_WIDGET_CHARTS', $MODULE)}</h3>
				</div>
				<form class="form-horizontal validateForm sendByAjax" >
					<input type="hidden" name="module" value="{$MODULE_NAME}" />
					<input type="hidden" name="action" value="CreateChart" />
					<input type="hidden" name="parent" value="Settings" />
					<input type="hidden" name="blockid" />
					<input type="hidden" name="linkId" />
					<input type="hidden" name="isDefault" value="0" />
					<input type="hidden" name="width" value="4" />
					<input type="hidden" name="height" value="3" />
					<div class="form-group margin0px padding1per">
						<label class="col-sm-3 col-form-label">{\App\Language::translate('LBL_NAME_CHART', $MODULE)}<span class="redColor">*</span> </label>
						<div class="col-sm-8 controls">
							<input type="text" name="chartName" class="form-control" data-validation-engine="validate[required]" />
						</div>
					</div>
					<div class="form-group margin0px padding1per">
						<label class="col-sm-3 col-form-label">{\App\Language::translate('LBL_REPORT', $MODULE)}<span class="redColor">*</span></label>
						<div class="col-sm-8 controls">
							<select class="select2 widgetFilter form-control" title="{\App\Language::translate('LBL_REPORT', $MODULE)}" name="reportId" data-validation-engine="validate[required]">
								{foreach item=REPORT_NAME key=REPORT_ID from=$LIST_REPORTS}
									<option value="{$REPORT_ID}">{$REPORT_NAME}</option>
								{/foreach}
							</select>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('ModalFooter.tpl', $MODULE)}
				</form>

			</div>
		</div>
	</div>
{/strip}
