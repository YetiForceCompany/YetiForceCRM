{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-MapCoordinates -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=PARAMS value=$FIELD_MODEL->getFieldParams()}
	{assign var=TABINDEX value=$FIELD_MODEL->getTabIndex()}
	{assign var=IS_EDITABLE_READ_ONLY value=$FIELD_MODEL->isEditableReadOnly()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	{if empty($FIELD_VALUE['type'])}
		{if empty($PARAMS['type'])}
			{assign var=VALUE_TYPE value='decimal'}
		{else}
			{assign var=VALUE_TYPE value=$PARAMS['type']}
		{/if}
	{else}
		{assign var=VALUE_TYPE value=$FIELD_VALUE['type']}
	{/if}
	<div class="tpl-Edit-Field-MapCoordinates input-group js-map-coordinates {$WIDTHTYPE_GROUP}">
		<input name="{$FIELD_MODEL->getFieldName()}[decimal][lat]" id="{$MODULE_NAME}_editView_fieldName_{$FIELD_MODEL->getName()}_lat" title="{\App\Language::translate('FL_LAT', 'OpenStreetMap')}" type="text" class="form-control js-popover-tooltip js-geo-value {if $VALUE_TYPE !== 'decimal'}d-none{/if}" tabindex="{$TABINDEX}"
			placeholder="{\App\Language::translate('FL_LAT', 'OpenStreetMap')}"
			value="{\App\Purifier::encodeHtml($FIELD_VALUE['decimal']['lat'])}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}data-fieldinfo='{$FIELD_INFO}' {if $IS_EDITABLE_READ_ONLY}readonly="readonly" {else} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {/if} data-placement="bottom" data-type="decimal" data-key="lat" />
		<input name="{$FIELD_MODEL->getFieldName()}[decimal][lon]" id="{$MODULE_NAME}_editView_fieldName_{$FIELD_MODEL->getName()}_lon" title="{\App\Language::translate('FL_LON', 'OpenStreetMap')}" type="text" class="form-control js-popover-tooltip js-geo-value {if $VALUE_TYPE !== 'decimal'}d-none{/if}" tabindex="{$TABINDEX}"
			placeholder="{\App\Language::translate('FL_LON', 'OpenStreetMap')}"
			value="{\App\Purifier::encodeHtml($FIELD_VALUE['decimal']['lon'])}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}data-fieldinfo='{$FIELD_INFO}' {if $IS_EDITABLE_READ_ONLY}readonly="readonly" {else} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {/if} data-placement="bottom" data-type="decimal" data-key="lon" />
		<input name="{$FIELD_MODEL->getFieldName()}[degrees][lat]" id="{$MODULE_NAME}_editView_fieldName_{$FIELD_MODEL->getName()}_lat"
			title="{\App\Language::translate('FL_LAT', 'OpenStreetMap')}" type="text" class="form-control js-popover-tooltip js-geo-value {if $VALUE_TYPE !== 'degrees'}d-none{/if}"
			tabindex="{$TABINDEX}" placeholder="{\App\Language::translate('FL_LAT', 'OpenStreetMap')}"
			value="{\App\Purifier::encodeHtml($FIELD_VALUE['degrees']['lat'])}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}data-fieldinfo='{$FIELD_INFO}' {if $IS_EDITABLE_READ_ONLY}readonly="readonly" {else} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {/if} data-placement="bottom" data-type="degrees" data-key="lat" />
		<input name="{$FIELD_MODEL->getFieldName()}[degrees][lon]" id="{$MODULE_NAME}_editView_fieldName_{$FIELD_MODEL->getName()}_lon"
			title="{\App\Language::translate('FL_LON', 'OpenStreetMap')}" type="text" class="form-control js-popover-tooltip js-geo-value {if $VALUE_TYPE !== 'degrees'}d-none{/if}"
			tabindex="{$TABINDEX}" placeholder="{\App\Language::translate('FL_LON', 'OpenStreetMap')}"
			value="{\App\Purifier::encodeHtml($FIELD_VALUE['degrees']['lon'])}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}data-fieldinfo='{$FIELD_INFO}' {if $IS_EDITABLE_READ_ONLY}readonly="readonly" {else} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {/if} data-placement="bottom" data-type="degrees" data-key="lon" />
		<input name="{$FIELD_MODEL->getFieldName()}[codeplus]" id="{$MODULE_NAME}_editView_fieldName_{$FIELD_MODEL->getName()}_lat" title="{\App\Language::translate('LBL_CODE_PLUS', 'OpenStreetMap')}" type="text" class="form-control js-popover-tooltip js-geo-value {if $VALUE_TYPE !== 'codeplus'}d-none{/if}"
			tabindex="{$TABINDEX}" placeholder="{\App\Language::translate('LBL_CODE_PLUS',  'OpenStreetMap')}"
			value="{\App\Purifier::encodeHtml($FIELD_VALUE['codeplus'])}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}data-fieldinfo='{$FIELD_INFO}' {if $IS_EDITABLE_READ_ONLY}readonly="readonly" {else} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {/if} data-placement="bottom" data-type="codeplus" />
		<div class="input-group-append p-0 {if empty($PARAMS['showType'])}d-none{/if}">
			<select name="{$FIELD_MODEL->getFieldName()}[type]" id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}_type_{\App\Layout::getUniqueId()}" class="select2 js-geo-type" required="required" tabindex="{$TABINDEX}" data-dropdown-auto-width="true" data-minimum-results-for-search="Infinity" {if $IS_EDITABLE_READ_ONLY}readonly="readonly" {/if}>
				{foreach item=COOR_LABEL key=COOR_KEY from=\App\Fields\MapCoordinates::COORDINATE_FORMATS}
					<option value="{$COOR_KEY}" {if $VALUE_TYPE === $COOR_KEY}selected{/if}>
						{\App\Language::translate($COOR_LABEL, 'OpenStreetMap')}
					</option>
				{/foreach}
			</select>
		</div>
		{if !empty($PARAMS['showMap'])}
			<div class="input-group-append">
				<button type="button" class="btn btn-info js-my-location__btn js-popover-tooltip" title="{\App\Language::translate('LBL_MY_LOCATION', 'OpenStreetMap')}" data-placement="top" tabindex="{$TABINDEX}" {if $IS_EDITABLE_READ_ONLY}disabled="disabled" {/if} data-js="click|popover">
					<span class="fa-solid fa-location-crosshairs"></span>
				</button>
			</div>
		{/if}
		{if !empty($PARAMS['showLocation'])}
			<div class="input-group-append">
				<button type="button" class="btn btn-warning js-map-edit__btn js-popover-tooltip" title="{\App\Language::translate('LBL_SHOW_MAP', 'OpenStreetMap')}" data-placement="top" tabindex="{$TABINDEX}" {if $IS_EDITABLE_READ_ONLY}disabled="disabled" {/if} data-js="click|popover">
					<span class="fa-solid fa-map-location-dot"></span>
				</button>
			</div>
		{/if}
	</div>
	<!-- /tpl-Base-Edit-Field-MapCoordinates -->
{/strip}
