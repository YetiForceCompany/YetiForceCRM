{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-dashboards-ChartFilter -->
	{assign "COL_LBL" "col-sm-12 col-md-4 col-lg-3"}
	{assign "COL_CTRL" "col-sm-12 col-md-8 col-lg-9"}
	{if $WIZARD_STEP eq 'step1'}
		<div id="minilistWizardContainer" class='modelContainer modal fade' tabindex="-1">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header contentsBackground">
						<h5 class="modal-title" id="massEditHeader">
							<span class="fas fa-chart-pie mr-1"></span>
							{\App\Language::translate('LBL_ADD_CHART_FILTER')} {\App\Language::translate($MODULE_NAME, $MODULE_NAME)}
						</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form class="validateForm u-word-break" method="post" action="javascript:;">
						<div class="modal-body">
							<div class="container-fluid pt-3">
									<input type="hidden" name="module" value="{$MODULE_NAME}"/>
									<input type="hidden" name="action" value="MassSave"/>
									<input type="hidden" id="widgetStep" value=""/>
									<div class="form-group row">
										<div class="{$COL_LBL}"><label>{\App\Language::translate('LBL_WIDGET_NAME','Home')}</label></div>
										<div class="{$COL_CTRL}"><input type="text" class="form-control" name="widgetTitle" value=""></div>
									</div>
									<div class="form-group row">
										<div class="{$COL_LBL}"><label><span class="redColor">*</span>{\App\Language::translate('LBL_SELECT_CHART','Home')}</label></div>
										<div class="{$COL_CTRL}">
											<select class="form-control select2" name="chartType">
												{foreach from=$CHART_TYPES item=TYPE key=VALUE}
													<option value="{$VALUE}">{\App\Language::translate($TYPE, $MODULE_NAME)}</option>
												{/foreach}
											</select>
										</div>
									</div>
									<div class="step1 form-group row">
											<div class="{$COL_LBL}"><label><span class="redColor">*</span>{\App\Language::translate('LBL_SELECT_MODULE')}</label></div>
											<div class="{$COL_CTRL}">
												<select class="form-control" name="module">
													<option></option>
													{foreach from=$MODULES item=MODULE_MODEL key=MODULE_THIS_NAME}
														<option value="{$MODULE_MODEL['name']}">{\App\Language::translate($MODULE_MODEL['name'], $MODULE_MODEL['name'])}</option>
													{/foreach}
												</select>
											</div>
									</div>
									<div class="step2"></div>
									<div class="step3"></div>
							</div>
						</div>
						{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE_NAME) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL' MODULE=$MODULE_NAME}
				</div>
			</div>
		</div>
	{elseif $WIZARD_STEP eq 'step2'}
		<div class="step2 form-group row">
			<div class="{$COL_LBL}"><label><span class="redColor">*</span>{\App\Language::translate('LBL_VALUE_TYPE', 'Home')}</label></div>
			<div class="{$COL_CTRL}">
				<select class="form-control valueType saveParam" name="valueType" size="2">
					<option value="count">{\App\Language::translate('LBL_NUMBER_OF_RECORDS','Home')}</option>
					{if $IS_NUMERAL_VALUE}
						<option value="sum">{\App\Language::translate('LBL_SUM','Home')}</option>
						<option value="avg">{\App\Language::translate('LBL_AVG','Home')}</option>
					{/if}
				</select>
			</div>
		</div>
		<div class="step2 form-group row">
			<div class="{$COL_LBL}"><label><span class="redColor">*</span>{\App\Language::translate('LBL_FILTER')}</label></div>
			<div class="{$COL_CTRL}">
				<select class="form-control filtersId" {if $CHART_TYPE!=='Funnel'}name="filtersId"
						multiple="multiple" {else}name="filtersId[] "{/if}
						data-validation-engine="validate[required]" data-maximum-selection-length="{\AppConfig::performance('CHART_MULTI_FILTER_LIMIT')}">
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
			</div>
		</div>
	{elseif $WIZARD_STEP eq 'step3'}
		<div class="step3 form-group row">
			<div class="{$COL_LBL}"><label><span class="redColor">*</span>{\App\Language::translate('LBL_GROUP_FIELD','Home')}</label></div>
			<div class="{$COL_CTRL}">
				<select class="form-control groupField" name="groupField" size="2" data-validation-engine="validate[required]">
					{foreach from=$MODULE_FIELDS item=FIELDS key=BLOCK_NAME}
						<optgroup label="{\App\Language::translate($BLOCK_NAME,$SELECTED_MODULE)}">
							{foreach from=$FIELDS item=FIELD key=FIELD_NAME}
								<option value="{$FIELD_NAME}"
										data-field-type="{$FIELD->getFieldDataType()}">{\App\Language::translate($FIELD->getFieldLabel(),$SELECTED_MODULE)}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</div>
		</div>
		{if $VALUE_TYPE!=='count'}
		<div class="step3 form-group row">
			<div class="{$COL_LBL}"><label><span class="redColor">*</span>{\App\Language::translate('LBL_VALUE_FIELD','Home')}</label></div>
			<div class="{$COL_CTRL}">
				<select class="form-control saveParam valueField" name="valueField" size="2" data-validation-engine="validate[required]">
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
			</div>
		</div>
		{/if}
	{elseif $WIZARD_STEP eq 'step4'}
		{if $CHART_TYPE == 'Funnel'  && in_array($GROUP_FIELD_MODEL->getFieldDataType(),['currency', 'double', 'percentage', 'integer'])}
			<div class="step4 form-group row">
				<div class="{$COL_LBL}"><label>{\App\Language::translate('LBL_GROUP_VALUES','Home')}</label></div>
				<div class="{$COL_CTRL}">
					<select class="form-control select saveParam" data-select="tags" multiple name="sectorField" size="2"></select>
				</div>
			</div>
		{/if}
		{if in_array($CHART_TYPE,['Bar','Line','Pie','LinePlain','Donut','Horizontal']) && count($FILTERS)<=1}
			<div class="step4 form-group row">
				<div class="{$COL_LBL}"><label>{\App\Language::translate('LBL_DIVIDING_FIELD','Home')}</label></div>
				<div class="{$COL_CTRL}">
					<select class="form-control saveParam" name="dividingField" size="2" data-allow-clear="true">
						{foreach from=$MODULE_FIELDS item=FIELDS key=BLOCK_NAME}
							<optgroup label="{\App\Language::translate($BLOCK_NAME,$SELECTED_MODULE)}">
								{foreach from=$FIELDS item=FIELD key=FIELD_NAME}
									<option value="{$FIELD_NAME}"
											data-field-type="{$FIELD->getFieldDataType()}">{\App\Language::translate($FIELD->getFieldLabel(),$SELECTED_MODULE)}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="step4 form-group row">
				<div class="{$COL_LBL}"><label>{\App\Language::translate('LBL_COLORS_FROM_DIVIDING_FIELD','Home')}</label></div>
				<div class="{$COL_CTRL}">
					<input type="checkbox" class="form-control saveParam mx-auto" name="colorsFromDividingField" value="1" checked>
				</div>
			</div>
		{/if}
		{if in_array($CHART_TYPE,['Bar','Horizontal','Line','LinePlain'])}
			<div class="step4 form-group row">
				<div class="{$COL_LBL}"><label>{\App\Language::translate('LBL_CHART_STACKED','Home')}</label></div>
				<div class="{$COL_CTRL}">
					<input type="checkbox" class="form-control saveParam mx-auto" name="stacked" value="1">
				</div>
			</div>
		{/if}
		{if count($FILTERS)>1}
			<div class="step4 form-group row">
				<div class="{$COL_LBL}"><label>{\App\Language::translate('LBL_CHART_COLORS_FROM_FILTER','Home')}</label></div>
				<div class="{$COL_CTRL}">
					<input type="checkbox" class="form-control saveParam" name="colorsFromFilter" value="1" checked>
				</div>
			</div>
		{/if}
		<div class="step4 form-group row">
			<div class="{$COL_LBL}"><label>{\App\Language::translate('LBL_ADDITIONAL_FILTERS','Home')}</label></div>
			<div class="{$COL_CTRL}">
				<select class="form-control saveParam" name="additionalFiltersFields" size="2" multiple data-maximum-selection-length="{\AppConfig::performance('CHART_ADDITIONAL_FILTERS_LIMIT')}">
					{foreach from=$MODULE_FIELDS item=FIELDS key=BLOCK_NAME}
						<optgroup label="{\App\Language::translate($BLOCK_NAME,$SELECTED_MODULE)}">
							{foreach from=$FIELDS item=FIELD key=FIELD_NAME}
								<option value="{$FIELD_NAME}" data-field-type="{$FIELD->getFieldDataType()}">{\App\Language::translate($FIELD->getFieldLabel(),$SELECTED_MODULE)}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</div>
		</div>
	{/if}
	<!-- /tpl-dashboards-ChartFilter -->
{/strip}
