{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if $WIZARD_STEP eq 'step1'}
		<div id="minilistWizardContainer" class='modelContainer modal fade' tabindex="-1">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header contentsBackground">
						<h5 class="modal-title" id="massEditHeader">
							<span class="fas fa-chart-pie mr-1"></span>
							{\App\Language::translate('LBL_MINI_LIST','Home')} {\App\Language::translate($MODULE, $MODULE)}
						</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form class="form-horizontal" method="post" action="javascript:;">
						<div class="modal-body">
							<input type="hidden" name="module" value="{$MODULE}" />
							<input type="hidden" name="action" value="MassSave" />
							<input type="hidden" id="widgetStep" value="" />
							<table class="table table-bordered">
								<tbody>
									<tr>
										<td class="fieldLabel alignMiddle textAlignCenter" nowrap><span class="redColor">*</span>{\App\Language::translate('LBL_WIDGET_NAME','Home')}</td>
										<td class="fieldValue">
											<input type="text" class="form-control" name="widgetTitle" value="">
										</td>
									</tr>
									<tr>
										<td class="fieldLabel alignMiddle textAlignCenter" nowrap><span class="redColor">*</span>{\App\Language::translate('LBL_SELECT_CHART','Home')}</td>
										<td class="fieldValue">
											<div class="input-group">
												<select class="form-control select2" name="chartType">
													{foreach from=$CHART_TYPES item=TYPE key=VALUE}
														<option value="{$VALUE}">{\App\Language::translate($TYPE, $MODULE)}</option>
													{/foreach}
												</select>
											</div>
										</td>
									</tr>
									<tr class="step1">
										<td class="fieldLabel alignMiddle textAlignCenter" nowrap><span class="redColor">*</span>{\App\Language::translate('LBL_SELECT_MODULE')}</td>
										<td class="fieldValue">
											<select class="form-control" name="module">
												<option></option>
												{foreach from=$MODULES item=MODULE_MODEL key=MODULE_NAME}
													<option value="{$MODULE_MODEL['name']}">{\App\Language::translate($MODULE_MODEL['name'], $MODULE_MODEL['name'])}</option>
												{/foreach}
											</select>
										</td>
									</tr>
									<tr class="step2"></tr>
									<tr class="step3"></tr>
								</tbody>
							</table>
						</div>
						{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
					</form>
				</div>
			</div>
		</div>
	{elseif $WIZARD_STEP eq 'step2'}
		<tr class="step2">
			<td class="fieldLabel alignMiddle textAlignCenter" nowrap><span class="redColor">*</span>{\App\Language::translate('LBL_VALUE_TYPE', 'Home')}</td>
			<td class="fieldValue">
				<select class="form-control valueType saveParam" name="valueType" size="2">
					<option value="count">{\App\Language::translate('LBL_NUMBER_OF_RECORDS','Home')}</option>
					<option value="sum">{\App\Language::translate('LBL_SUM','Home')}</option>
					<option value="avg">{\App\Language::translate('LBL_AVG','Home')}</option>
				</select>
			</td>
		</tr>
		<tr class="step2">
			<td class="fieldLabel alignMiddle textAlignCenter" nowrap><span class="redColor">*</span>{\App\Language::translate('LBL_FILTER')}</td>
			<td class="fieldValue">
				<select class="form-control filtersId" {if $CHART_TYPE!=='Funnel'}name="filtersId" multiple{else}name="filtersId[]"{/if} data-maximum-selection-length="{\AppConfig::performance('CHART_MULTI_FILTER_LIMIT')}">
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
				</select>
			</td>
		</tr>
	{elseif $WIZARD_STEP eq 'step3'}
		<tr class="step3">
			<td class="fieldLabel alignMiddle textAlignCenter" nowrap><span class="redColor">*</span>{\App\Language::translate('LBL_GROUP_FIELD','Home')}</td>
			<td class="fieldValue">
				<select class="form-control groupField" name="groupField" size="2" >
					{foreach from=$MODULE_FIELDS item=FIELDS key=BLOCK_NAME}
						<optgroup label="{\App\Language::translate($BLOCK_NAME,$SELECTED_MODULE)}">
							{foreach from=$FIELDS item=FIELD key=FIELD_NAME}
								<option value="{$FIELD_NAME}" data-field-type="{$FIELD->getFieldDataType()}">{\App\Language::translate($FIELD->getFieldLabel(),$SELECTED_MODULE)}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr class="step3">
			<td class="fieldLabel alignMiddle textAlignCenter" nowrap><span class="redColor">*</span>{\App\Language::translate('LBL_VALUE_FIELD','Home')}</td>
			<td class="fieldValue">
				<select class="form-control saveParam valueField" name="valueField" size="2" >
					{foreach from=$MODULE_FIELDS item=FIELDS key=BLOCK_NAME}
						<optgroup label="{\App\Language::translate($BLOCK_NAME,$SELECTED_MODULE)}">
							{foreach from=$FIELDS item=FIELD key=FIELD_NAME}
								{if in_array($FIELD->getFieldDataType(),['currency', 'double', 'percentage', 'integer'])}
									<option value="{$FIELD_NAME}" data-field-type="{$FIELD->getFieldDataType()}">{\App\Language::translate($FIELD->getFieldLabel(),$SELECTED_MODULE)}</option>
								{/if}
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</td>
		</tr>
	{elseif $WIZARD_STEP eq 'step4'}
		{if $CHART_TYPE == 'Funnel'  && in_array($GROUP_FIELD_MODEL->getFieldDataType(),['currency', 'double', 'percentage', 'integer'])}
			<tr class="step4">
				<td class="fieldLabel alignMiddle textAlignCenter" nowrap>{\App\Language::translate('LBL_GROUP_VALUES','Home')}</td>
				<td class="fieldValue">
					<select class="form-control select tags saveParam" multiple name="sectorField" size="2"></select>
				</td>
			</tr>
		{/if}
		{if in_array($CHART_TYPE,['Bar','Line','Pie','Axis','LinePlain','Donut','Horizontal']) && count($FILTERS)<=1}
			<tr class="step4">
				<td class="fieldLabel alignMiddle textAlignCenter" nowrap>{\App\Language::translate('LBL_DIVIDING_FIELD','Home')}</td>
				<td class="fieldValue">
					<select class="form-control saveParam" name="dividingField" size="2" >
						<option>{\App\Language::translate('--None--')}</option>
						{foreach from=$MODULE_FIELDS item=FIELDS key=BLOCK_NAME}
							<optgroup label="{\App\Language::translate($BLOCK_NAME,$SELECTED_MODULE)}">
								{foreach from=$FIELDS item=FIELD key=FIELD_NAME}
									<option value="{$FIELD_NAME}"
											data-field-type="{$FIELD->getFieldDataType()}">{\App\Language::translate($FIELD->getFieldLabel(),$SELECTED_MODULE)}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr class="step4">
				<td class="fieldLabel alignMiddle textAlignCenter" nowrap>
					{\App\Language::translate('LBL_COLORS_FROM_DIVIDING_FIELD','Home')}
				</td>
				<td class="fieldValue">
					<input type="checkbox" class="form-control saveParam" name="colorsFromDividingField" value="1" checked>
				</td>
			</tr>
		{/if}
		{if in_array($CHART_TYPE,['Bar','Horizontal','Line','LinePlain'])}
			<tr class="step4">
				<td class="fieldLabel alignMiddle textAlignCenter" nowrap>
					{\App\Language::translate('LBL_CHART_STACKED','Home')}
				</td>
				<td class="fieldValue">
					<input type="checkbox" class="form-control saveParam" name="stacked" value="1">
				</td>
			</tr>
		{/if}
		{if count($FILTERS)>1}
			<tr class="step4">
				<td class="fieldLabel alignMiddle textAlignCenter" nowrap>{\App\Language::translate('LBL_CHART_COLORS_FROM_FILTER','Home')}</td>
				<td class="fieldValue">
					<input type="checkbox" class="form-control saveParam" name="colorsFromFilter" value="1" checked>
				</td>
			</tr>
		{/if}
		<tr class="step4">
			<td class="fieldLabel alignMiddle textAlignCenter" nowrap>{\App\Language::translate('LBL_ADDITIONAL_FILTERS','Home')}</td>
			<td class="fieldValue">
				<select class="form-control saveParam" name="additionalFiltersFields" size="2" multiple data-maximum-selection-length="{\AppConfig::performance('CHART_ADDITIONAL_FILTERS_LIMIT')}">
					<option value="-">{\App\Language::translate('--None--')}</option>
					{foreach from=$MODULE_FIELDS item=FIELDS key=BLOCK_NAME}
						<optgroup label="{\App\Language::translate($BLOCK_NAME,$SELECTED_MODULE)}">
							{foreach from=$FIELDS item=FIELD key=FIELD_NAME}
								<option value="{$FIELD_NAME}"
										data-field-type="{$FIELD->getFieldDataType()}">{\App\Language::translate($FIELD->getFieldLabel(),$SELECTED_MODULE)}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</td>
		</tr>
	{/if}
{/strip}
