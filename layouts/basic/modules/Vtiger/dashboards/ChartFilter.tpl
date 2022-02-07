{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-dashboards-ChartFilter -->
	{assign "COL_LBL" "col-sm-12 col-md-4 col-lg-3 col-form-label"}
	{assign "COL_CTRL" "col-sm-12 col-md-8 col-lg-9"}
	{if $WIZARD_STEP eq 'step1'}
		<form class="validateForm u-word-break" method="post" action="javascript:;">
			{if $WIDGET_MODEL->getId()}
				<input type="hidden" name="templateId" class="js-widget-id" value="{$WIDGET_MODEL->getId()}">
			{/if}
			<input type="hidden" name="linkId" class="js-link-id" value="{$WIDGET_MODEL->get('linkid')}">
			<div class="modal-body">
				<div class="container-fluid pt-3">
					<input type="hidden" id="widgetStep" value="" />
					<div>
						<div class="form-group row mb-2">
							<label class="{$COL_LBL}">{\App\Language::translate('LBL_WIDGET_NAME','Home')}</label>
							<div class="{$COL_CTRL}"><input type="text" class="form-control" name="title" value="{$WIDGET_MODEL->getValueForEditView('title')}"></div>
						</div>
						<div class="form-group row mb-2">
							<label class="{$COL_LBL}"><span class="redColor">*</span>{\App\Language::translate('LBL_SELECT_CHART','Home')}</label>
							<div class="{$COL_CTRL}">
								{assign "VALUE_CHART_TYPE" $WIDGET_MODEL->getValueForEditView('chartType')}
								<select class="form-control select2 saveParam" name="chartType">
									{foreach from=$CHART_TYPES item=TYPE key=VALUE}
										<option value="{$VALUE}" {if $VALUE === $VALUE_CHART_TYPE} selected{/if}>{\App\Language::translate($TYPE, $MODULE_NAME)}</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>
					<div class="step1">
						<div class="form-group row mb-2">
							<label class="{$COL_LBL}"><span class="redColor">*</span>{\App\Language::translate('LBL_SELECT_MODULE')}</label>
							<div class="{$COL_CTRL}">
								{assign "VALUE_MODULE" $WIDGET_MODEL->getValueForEditView('module')}
								<select class="form-control saveParam" name="module">
									<option></option>
									{foreach from=$MODULES item=MODULE_MODEL key=MODULE_THIS_NAME}
										<option value="{$MODULE_MODEL['name']}" {if $MODULE_MODEL['name'] === $VALUE_MODULE} selected{/if}>{\App\Language::translate($MODULE_MODEL['name'], $MODULE_MODEL['name'])}</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>
					<div class="step2"></div>
					<div class="step3"></div>
					<div class="step4"></div>
					<div class="step5"></div>
				</div>
			</div>
			<div class="js-chart-footer" style="display: none;">
				{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE_NAME) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL' MODULE=$MODULE_NAME}
			</div>
		</form>
	{elseif $WIZARD_STEP eq 'step2'}
		<div class="form-group row mb-2">
			<label class="{$COL_LBL}"><span class="redColor">*</span>{\App\Language::translate('LBL_FILTER')}</label>
			<div class="{$COL_CTRL}">
				{assign "VALUE_FILTER" $WIDGET_MODEL->getValueForEditView('filterid')}
				<select class="form-control filtersId" {if $CHART_TYPE!=='Funnel' && $CHART_TYPE!=='Table' }name="filtersId"
					multiple="multiple" {else}name="filtersId[]" 
					{/if}
					data-validation-engine="validate[required]" data-maximum-selection-length="{\App\Config::performance('CHART_MULTI_FILTER_LIMIT')}">
					<option></option>
					{foreach from=$ALLFILTERS item=FILTERS key=FILTERGROUP}
						<optgroup label="{\App\Language::translate($FILTERGROUP,$SELECTED_MODULE)}">
							{foreach from=$FILTERS item=FILTER key=FILTERNAME}
								{if $FILTER->get('setmetrics') eq 1}
									<option value="{$FILTER->getId()}" {if in_array($FILTER->getId(), $VALUE_FILTER)} selected{/if}>{\App\Language::translate($FILTER->get('viewname'),$SELECTED_MODULE)}</option>
								{/if}
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="form-group row mb-2">
			<label class="{$COL_LBL}"><span class="redColor">*</span>{\App\Language::translate('LBL_VALUE_TYPE', 'Home')}</label>
			<div class="{$COL_CTRL}">
				{assign "VALUE_TYPE" $WIDGET_MODEL->getValueForEditView('valueType')}
				<select class="form-control valueType saveParam" name="valueType" size="2">
					<option value="count" {if 'count' === $VALUE_TYPE} selected{/if}>{\App\Language::translate('LBL_NUMBER_OF_RECORDS','Home')}</option>
					{if $IS_NUMERAL_VALUE}
						<option value="sum" {if 'sum' === $VALUE_TYPE} selected{/if}>{\App\Language::translate('LBL_SUM','Home')}</option>
						<option value="avg" {if 'avg' === $VALUE_TYPE} selected{/if}>{\App\Language::translate('LBL_AVG','Home')}</option>
					{/if}
				</select>
			</div>
		</div>
	{elseif $WIZARD_STEP eq 'step3'}
		<div class="form-group row mb-2">
			<div class="{$COL_LBL}"><label><span class="redColor">*</span>{\App\Language::translate('LBL_GROUP_FIELD','Home')}</label></div>
			<div class="{$COL_CTRL}">
				{assign "VALUE_GROUP_FIELD" $WIDGET_MODEL->getValueForEditView('groupField')}
				<select class="form-control groupField saveParam" name="groupField" size="2" data-validation-engine="validate[required]">
					{foreach from=$MODULE_FIELDS item=FIELDS key=BLOCK_NAME}
						<optgroup label="{\App\Language::translate($BLOCK_NAME,$SELECTED_MODULE)}">
							{foreach from=$FIELDS item=FIELD key=FIELD_NAME}
								<option value="{$FIELD_NAME}"
									data-field-type="{$FIELD->getFieldDataType()}"
									{if $FIELD_NAME === $VALUE_GROUP_FIELD} selected{/if}>{\App\Language::translate($FIELD->getFieldLabel(),$SELECTED_MODULE)}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</div>
		</div>
		{if $VALUE_TYPE !== 'count'}
			<div class="form-group row mb-2">
				<div class="{$COL_LBL}"><label><span class="redColor">*</span>{\App\Language::translate('LBL_VALUE_FIELD','Home')}</label></div>
				<div class="{$COL_CTRL}">
					{assign "VALUE_FIELD" $WIDGET_MODEL->getValueForEditView('valueField')}
					<select class="form-control saveParam valueField" name="valueField" size="2" data-validation-engine="validate[required]">
						{foreach from=$MODULE_FIELDS item=FIELDS key=BLOCK_NAME}
							<optgroup label="{\App\Language::translate($BLOCK_NAME,$SELECTED_MODULE)}">
								{foreach from=$FIELDS item=FIELD key=FIELD_NAME}
									{if in_array($FIELD->getFieldDataType(), $REQUIRED_FIELD_TYPE)}
										<option value="{$FIELD_NAME}" data-field-type="{$FIELD->getFieldDataType()}" {if $FIELD_NAME === $VALUE_FIELD} selected{/if}>{\App\Language::translate($FIELD->getFieldLabel(),$SELECTED_MODULE)}</option>
									{/if}
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</div>
			</div>
		{/if}
	{elseif $WIZARD_STEP eq 'step4'}
		{if $SHOW_GROUP_VALUES}
			<div class="form-group row mb-2">
				<div class="{$COL_LBL}"><label>{\App\Language::translate('LBL_GROUP_VALUES','Home')}</label></div>
				<div class="{$COL_CTRL}">
					{assign "VALUE_SECTOR_FIELD" $WIDGET_MODEL->getValueForEditView('sectorField')}
					<select class="form-control select saveParam" data-select="tags" name="sectorField">
						<optgroup class="p-0">
							<option value="">{\App\Language::translate('LBL_SELECT_OPTION')}</option>
						</optgroup>
						{foreach from=$GROUP_VALUES item=LABEL key=KEY}
							<option value="{$KEY}" {if $KEY === $VALUE_SECTOR_FIELD} selected{/if}>{\App\Language::translate($LABEL, $MODULE_NAME)}</option>
						{/foreach}
					</select>
				</div>
			</div>
		{elseif in_array($CHART_TYPE,['Bar','Line','Pie','LinePlain','Donut','Horizontal', 'Table']) && count($FILTERS)<=1}
			<div class="form-group row mb-2">
				<div class="{$COL_LBL}"><label>{\App\Language::translate('LBL_DIVIDING_FIELD','Home')}</label></div>
				<div class="{$COL_CTRL}">
					{assign "VALUE_DIVIDING_FIELD" $WIDGET_MODEL->getValueForEditView('dividingField')}
					<select class="form-control saveParam" name="dividingField" size="2" data-allow-clear="true">
						{foreach from=$MODULE_FIELDS item=FIELDS key=BLOCK_NAME}
							<optgroup label="{\App\Language::translate($BLOCK_NAME,$SELECTED_MODULE)}">
								{foreach from=$FIELDS item=FIELD key=FIELD_NAME}
									<option value="{$FIELD_NAME}"
										data-field-type="{$FIELD->getFieldDataType()}"
										{if $FIELD_NAME === $VALUE_DIVIDING_FIELD} selected{/if}>{\App\Language::translate($FIELD->getFieldLabel(),$SELECTED_MODULE)}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group row d-none js-sector-container mb-2">
				<div class="{$COL_LBL}"><label>{\App\Language::translate('LBL_GROUP_VALUES','Home')}</label></div>
				<div class="{$COL_CTRL}">
					{assign "VALUE_SECTOR_FIELD" $WIDGET_MODEL->getValueForEditView('sectorField')}
					<select class="form-control select saveParam" data-select="tags" name="sectorField" disabled>
						<optgroup class="p-0">
							<option value="">{\App\Language::translate('LBL_SELECT_OPTION')}</option>
						</optgroup>
						{foreach from=$GROUP_VALUES item=LABEL key=KEY}
							<option value="{$KEY}" {if $KEY === $VALUE_SECTOR_FIELD} selected{/if}>{\App\Language::translate($LABEL, $MODULE_NAME)}</option>
						{/foreach}
					</select>
				</div>
			</div>
			{if $CHART_TYPE neq 'Table'}
				<div class="form-group row mb-2">
					{assign "VALUE_COLORS" $WIDGET_MODEL->getValueForEditView('colorsFromDividingField')}
					<div class="{$COL_LBL}"><label>{\App\Language::translate('LBL_COLORS_FROM_DIVIDING_FIELD','Home')}</label></div>
					<div class="{$COL_CTRL} m-auto">
						<input type="hidden" name="colorsFromDividingField" value="0">
						<input type="checkbox" class="form-control saveParam" name="colorsFromDividingField" value="1" {if $VALUE_COLORS} checked{/if}>
					</div>
				</div>
			{/if}
		{/if}
		{if in_array($CHART_TYPE,['Bar','Horizontal','Line','LinePlain'])}
			<div class="form-group row mb-2">
				{assign "VALUE_STACKED" $WIDGET_MODEL->getValueForEditView('stacked')}
				<div class="{$COL_LBL}"><label>{\App\Language::translate('LBL_CHART_STACKED','Home')}</label></div>
				<div class="{$COL_CTRL} m-auto">
					<input type="hidden" name="stacked" value="0">
					<input type="checkbox" class="form-control saveParam" name="stacked" value="1" {if $VALUE_STACKED} checked{/if}>
				</div>
			</div>
		{/if}
		{if count($FILTERS)>1}
			<div class="form-group row mb-2">
				{assign "VALUE_COLORS" $WIDGET_MODEL->getValueForEditView('colorsFromFilter')}
				<div class="{$COL_LBL}"><label>{\App\Language::translate('LBL_CHART_COLORS_FROM_FILTER','Home')}</label></div>
				<div class="{$COL_CTRL} m-auto">
					<input type="hidden" name="colorsFromFilter" value="0">
					<input type="checkbox" class="form-control saveParam" name="colorsFromFilter" value="1" {if $VALUE_COLORS} checked{/if}>
				</div>
			</div>
		{/if}
		<div class="form-group row mb-2">
			<div class="{$COL_LBL}"><label>{\App\Language::translate('LBL_ADDITIONAL_FILTERS','Home')}</label></div>
			<div class="{$COL_CTRL}">
				{assign "VALUE_ADDITIONAL_FIELD" $WIDGET_MODEL->getValueForEditView('additionalFiltersFields')}
				<select class="form-control saveParam" name="additionalFiltersFields" size="2" multiple data-maximum-selection-length="{\App\Config::performance('CHART_ADDITIONAL_FILTERS_LIMIT')}">
					{foreach from=$MODULE_FIELDS item=FIELDS key=BLOCK_NAME}
						<optgroup label="{\App\Language::translate($BLOCK_NAME,$SELECTED_MODULE)}">
							{foreach from=$FIELDS item=FIELD key=FIELD_NAME}
								<option value="{$FIELD_NAME}" data-field-type="{$FIELD->getFieldDataType()}" {if in_array($FIELD_NAME, $VALUE_ADDITIONAL_FIELD)} selected{/if}>{\App\Language::translate($FIELD->getFieldLabel(),$SELECTED_MODULE)}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</div>
		</div>
		{if $CHART_TYPE eq 'Table'}
			<div class="form-group row mb-2">
				{assign "VALUE_SUMMARY" $WIDGET_MODEL->getValueForEditView('summary')}
				<div class="{$COL_LBL}"><label>{\App\Language::translate('LBL_WIDGET_SHOW_SUMMARY','Home')}</label></div>
				<div class="{$COL_CTRL} m-auto">
					<input type="hidden" name="summary" value="0">
					<input type="checkbox" class="form-control saveParam" name="summary" value="1" {if $VALUE_SUMMARY} checked{/if}>
				</div>
			</div>
		{/if}
	{elseif $WIZARD_STEP eq 'step5' }
		{if in_array($CHART_TYPE,['Bar','Horizontal','Line','LinePlain']) && !$DIVIDING_FIELD && !$STACKED && !$SECTOR_FIELD && count($FILTERS)<=1}
			<div class="form-group row mb-2">
				<div class="{$COL_LBL}"><label>{\App\Language::translate('LBL_SORTING_SETTINGS')}</label></div>
				<div class="{$COL_CTRL}">
					{assign "VALUE_SORT" $WIDGET_MODEL->getValueForEditView('sortOrder')}
					<select class="form-control select saveParam" name="sortOrder">
						<optgroup class="p-0">
							<option value="">{\App\Language::translate('LBL_SELECT_OPTION')}</option>
						</optgroup>
						<option value="{\App\Db::DESC}" {if \App\Db::DESC === $VALUE_SORT} selected{/if}>{\App\Language::translate('LBL_SORT_DESCENDING')}</option>
						<option value="{\App\Db::ASC}" {if \App\Db::ASC === $VALUE_SORT} selected{/if}>{\App\Language::translate('LBL_SORT_ASCENDING')}</option>
					</select>
				</div>
			</div>
		{/if}
	{/if}
	<!-- /tpl-dashboards-ChartFilter -->
{/strip}
