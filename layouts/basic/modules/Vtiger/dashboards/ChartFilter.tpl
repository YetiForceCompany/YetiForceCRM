{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	{if $WIZARD_STEP eq 'step1'}
		<div id="minilistWizardContainer" class='modelContainer modal fade' tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header contentsBackground">
						<button data-dismiss="modal" class="close" title="{\App\Language::translate('LBL_CLOSE')}">&times;</button>
						<h3 class="modal-title" id="massEditHeader">{\App\Language::translate('LBL_MINI_LIST', $MODULE)} {\App\Language::translate($MODULE, $MODULE)}</h3>
					</div>
					<form class="form-horizontal" method="post" action="javascript:;">
						<div class="modal-body">
							<input type="hidden" name="module" value="{$MODULE}" />
							<input type="hidden" name="action" value="MassSave" />

							<table class="table table-bordered">
								<tbody>
									<tr>
										<td class="fieldLabel alignMiddle textAlignCenter" nowrap>{\App\Language::translate('LBL_WIDGET_NAME')}</td>
										<td class="fieldValue">
											<input type="text" class="form-control" name="widgetTitle" value="">
										</td>
									</tr>
									<tr>
										<td class="fieldLabel alignMiddle textAlignCenter" nowrap>{\App\Language::translate('LBL_SELECT_CHART')}</td>
										<td class="fieldValue">
											<div class="input-group">
												<select class="form-control select2" name="chartType">
													{foreach from=$CHART_TYPES item=TYPE key=VALUE}
														<option value="{$VALUE}">{\App\Language::translate($TYPE, $MODULE)}</option>
													{/foreach}
												</select>
												<span class="input-group-addon hide isColorContainer">
													<input type="checkbox" class="isColor popoverTooltip" data-content="{\App\Language::translate('LBL_CHART_COLOR_DESCRIPTION',$MODULE)}">
												</span>
											</div>
										</td>
									</tr>
									<tr>
										<td class="fieldLabel alignMiddle textAlignCenter" nowrap>{\App\Language::translate('LBL_SELECT_MODULE')}</td>
										<td class="fieldValue">
											<select class="form-control" name="module">
												<option></option>
												{foreach from=$MODULES item=MODULE_MODEL key=MODULE_NAME}
													<option value="{$MODULE_MODEL['name']}">{\App\Language::translate($MODULE_MODEL['name'], $MODULE_MODEL['name'])}</option>
												{/foreach}
											</select>
										</td>
									</tr>
									<tr>
										<td class="fieldLabel alignMiddle textAlignCenter" nowrap>{\App\Language::translate('LBL_FILTER')}</td>
										<td class="fieldValue">
											<select class="form-control" name="filterid">
												<option></option>
											</select>
										</td>
									</tr>
									<tr>
										<td class="fieldLabel alignMiddle textAlignCenter" nowrap>{\App\Language::translate('LBL_GROUP_FIELD')}</td>
										<td class="fieldValue">
											<select class="form-control" name="groupField" size="2" >
												<option></option>
											</select>
										</td>
									</tr>
									<tr class="hide sectorContainer">
										<td class="fieldLabel alignMiddle textAlignCenter" nowrap>{\App\Language::translate('LBL_SECTOR')}</td>
										<td class="fieldValue">
											<select class="form-control select2" multiple name="sectorField" size="2" >
											</select>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
					</form>
				</div>
			</div>
		</div>
	{elseif $WIZARD_STEP eq 'step2'}
		<option></option>
		{foreach from=$ALLFILTERS item=FILTERS key=FILTERGROUP}
			<optgroup label="{\App\Language::translate($FILTERGROUP,$SELECTED_MODULE)}">
				{foreach from=$FILTERS item=FILTER key=FILTERNAME}
					{if $FILTER->get('setmetrics') eq 1}
						<option value="{$FILTER->getId()}">{\App\Language::translate($FILTER->get('viewname'),$SELECTED_MODULE)}</option>
					{/if}
				{/foreach}
			</optgroup>
		{/foreach}
	{elseif $WIZARD_STEP eq 'step3'}
		<option></option>
		{foreach from=$MODULE_FIELDS item=FIELD key=FIELD_NAME}
			<option value="{$FIELD_NAME}" data-field-type="{$FIELD->getFieldDataType()}">{\App\Language::translate($FIELD->getFieldLabel(),$SELECTED_MODULE)}</option>
		{/foreach}
	{/if}
{/strip}
