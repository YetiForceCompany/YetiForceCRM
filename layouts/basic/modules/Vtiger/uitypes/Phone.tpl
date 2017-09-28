{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{if AppConfig::main('phoneFieldAdvancedVerification',false)}
		{assign var="PHONE_DETAIL" value=App\Fields\Phone::getDetails($FIELD_MODEL->get('fieldvalue'))}
		<div class="input-group">
			<div class="input-group-addon noSpaces">
				<select name="{$FIELD_MODEL->getFieldName()}_country" id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}_dropDown" class="chzn-select phoneCountryList" required="required">
					{foreach key=KEY item=ROW from=App\Fields\Country::getAll()}
						<option value="{$KEY}" {if $PHONE_DETAIL && $PHONE_DETAIL['country'] == $KEY} selected {/if} title="{\App\Language::translate($KEY, 'Other.Country')}">{\App\Language::translate($KEY, 'Other.Country')}</option>
					{/foreach}
				</select>
			</div>
			{if $PHONE_DETAIL['geocoding'] || $PHONE_DETAIL['carrier']}
				{assign var="TITLE" value=$PHONE_DETAIL['geocoding']|cat:' '|cat:$PHONE_DETAIL['carrier']}
			{else}
				{assign var="TITLE" value=\App\Language::translate($FIELD_MODEL->get('label'), $MODULE)}
			{/if}
			<input name="{$FIELD_MODEL->getFieldName()}" value="{$PHONE_DETAIL['number']}" id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}" title="{$TITLE}" type="text" class="form-control" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" 
				  data-advanced-verification="1"  data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} {if $FIELD_MODEL->get('fieldparams') != ''}data-inputmask="'mask': '{$FIELD_MODEL->get('fieldparams')}'"{/if} />
		</div>
	{else}
		<input name="{$FIELD_MODEL->getFieldName()}" value="{$FIELD_MODEL->get('fieldvalue')}" id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}" title="{\App\Language::translate($FIELD_MODEL->get('label'), $MODULE)}" type="text" class="form-control" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" 
			   data-advanced-verification="0" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} {if $FIELD_MODEL->get('fieldparams') != ''}data-inputmask="'mask': '{$FIELD_MODEL->get('fieldparams')}'"{/if} />
	{/if}
{/strip}
