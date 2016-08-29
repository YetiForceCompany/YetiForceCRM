{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div id="addNotePadWidgetContainer" class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
					<h3 id="massEditHeader" class="modal-title">{vtranslate('LBL_ADD', $MODULE)} {vtranslate('LBL_ADD_WIDGET_CHARTS', $MODULE)}</h3>
				</div>
				<form class="form-horizontal validateForm sendByAjax" >
					<input type="hidden" name="module" value="{$MODULE_NAME}">
					<input type="hidden" name="action" value="CreateChart">
					<input type="hidden" name="parent" value="Settings">
					<input type="hidden" name="blockid">
					<input type="hidden" name="linkId">
					<input type="hidden" name="isDefault" value="0">
					<input type="hidden" name="width" value="4">
					<input type="hidden" name="height" value="3">
					<div class="form-group margin0px padding1per">
						<label class="col-sm-3 control-label">{vtranslate('LBL_NAME_CHART', $MODULE)}<span class="redColor">*</span> </label>
						<div class="col-sm-8 controls">
							<input type="text" name="chartName" class="form-control" data-validation-engine="validate[required]" />
						</div>
					</div>
					<div class="form-group margin0px padding1per">
						<label class="col-sm-3 control-label">{vtranslate('LBL_REPORT', $MODULE)}<span class="redColor">*</span></label>
						<div class="col-sm-8 controls">
							<select class="select2 widgetFilter width90 form-control" title="{vtranslate('LBL_SELECT_USER')}" name="reportId" style="margin-bottom:0;" data-validation-engine="validate[required]">
								{foreach item=REPORT_DATA key=REPORT_ID from=$LIST_REPORTS}
									<option value="{$REPORT_ID}">{$REPORT_DATA['reportname']}</option>
								{/foreach}
							</select>
						</div>
					</div>
					{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
				</form>
			
			</div>
		</div>
	</div>
{/strip}
