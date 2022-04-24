{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Base-Edit-Field-Phone -->
	{assign var=TABINDEX value=$FIELD_MODEL->getTabIndex()}
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var="PARAMS" value=$FIELD_MODEL->getFieldParams()}
	{assign var="NUMBER" value=$FIELD_MODEL->get('fieldvalue')}
	<div>
		{if \App\Config::main('phoneFieldAdvancedVerification',false)}
			{if $NUMBER}
				{assign var="PHONE_DETAIL" value=App\Fields\Phone::getDetails($NUMBER)}
				{assign var="COUNTRY" value=$PHONE_DETAIL['country']}
			{else}
				{assign var="PHONE_DETAIL" value=[]}
				{if !\App\Config::component('Phone', 'defaultPhoneCountry')}
					{assign var="COUNTRY" value=\App\Language::getLanguageRegion()}
				{else}
					{assign var="COUNTRY" value=''}
				{/if}
			{/if}
			{assign var="FIELD_NAME_EXTRA" value=$FIELD_MODEL->getFieldName()|cat:'_extra'}
			{assign var="FIELD_MODEL_EXTRA" value=$FIELD_MODEL->getModule()->getFieldByName($FIELD_NAME_EXTRA)}
			{assign var="ACTIVE_EXTRA_FIELD" value=!empty($VIEW) && ($VIEW == 'Edit' || $VIEW == 'QuickCreateAjax') && $FIELD_MODEL_EXTRA && $FIELD_MODEL_EXTRA->isWritable()}
			{assign var=PICKLIST_VALUES value=App\Fields\Country::getAll('phone')}
			{assign var=IS_LAZY value=count($PICKLIST_VALUES) > \App\Config::performance('picklistLimit')}
			<div class="form-row">
				<div class="{if $ACTIVE_EXTRA_FIELD}col-md-8{else}col-md-12{/if}">
					<div class="input-group {$WIDTHTYPE_GROUP} phoneGroup mb-1">
						<div class="input-group-prepend m-0 p-0">
							<select name="{$FIELD_MODEL->getFieldName()}_country" tabindex="{$TABINDEX}"
								{if $IS_LAZY} data-select-lazy="true" {/if}
								id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}_dropDown_{\App\Layout::getUniqueId()}" class="select2 phoneCountryList" data-template-result="prependDataTemplate" data-template-selection="prependDataTemplate" required="required" data-dropdown-auto-width="true" {if $FIELD_MODEL->isEditableReadOnly()} readonly="readonly" {/if}>
								{foreach key=KEY item=ROW from=$PICKLIST_VALUES}
									{assign var=TRANSLATE value=\App\Language::translateSingleMod($ROW['name'],'Other.Country')}
									<option value="{$KEY}" {if $COUNTRY === $KEY} selected {/if} title="{$TRANSLATE}" data-template="<span><span class='fi fi-{$KEY|lower} mr-2'></span>{$TRANSLATE}</span>">{$TRANSLATE}</option>
								{/foreach}
							</select>
						</div>
						{if $PHONE_DETAIL && (isset($PHONE_DETAIL['geocoding']) || isset($PHONE_DETAIL['carrier']))}
							{assign var="TITLE" value=$PHONE_DETAIL['geocoding']|cat:' '|cat:$PHONE_DETAIL['carrier']}
						{else}
							{assign var="TITLE" value=\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}
						{/if}
						{if $PHONE_DETAIL && isset($PHONE_DETAIL['number'])}
							{assign var="NUMBER" value=$PHONE_DETAIL['number']}
						{/if}
						<input name="{$FIELD_MODEL->getFieldName()}" class="form-control" value="{$NUMBER}" id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}"
							title="{$TITLE}" placeholder="{$TITLE}" type="text" tabindex="{$TABINDEX}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-advanced-verification="1" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if} {if isset($PARAMS['mask'])}data-inputmask="'mask': {\App\Purifier::encodeHtml(\App\Json::encode($PARAMS['mask']))}" {/if} />
					</div>
				</div>
				{if $ACTIVE_EXTRA_FIELD}
					{assign var="PARAMS_EXTRA" value=$FIELD_MODEL->getFieldParams()}
					<div class="col-md-4">
						{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL_EXTRA->getFieldInfo()))}
						{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL_EXTRA->getValidator()}
						<input name="{$FIELD_NAME_EXTRA}" class="form-control" title="{\App\Language::translate($FIELD_MODEL_EXTRA->getFieldLabel(), $MODULE)}" tabindex="{$TABINDEX}"
							placeholder="{\App\Language::translate($FIELD_MODEL_EXTRA->getFieldLabel(), $MODULE)}" value="{if $RECORD}{$FIELD_MODEL_EXTRA->getEditViewDisplayValue($RECORD->get($FIELD_NAME_EXTRA), $RECORD)}{/if}" id="{$MODULE}_editView_fieldName_{$FIELD_NAME_EXTRA}" type="text" data-validation-engine="validate[{if $FIELD_MODEL_EXTRA->isMandatory() eq true} required,{/if}{if $FIELD_MODEL->get('maximumlength')}maxSize[{$FIELD_MODEL->get('maximumlength')}],{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} {if $FIELD_MODEL_EXTRA->isEditableReadOnly()}readonly="readonly" {/if} {if isset($PARAMS_EXTRA['mask'])}data-inputmask="'mask': {\App\Purifier::encodeHtml(\App\Json::encode($PARAMS_EXTRA['mask']))}" {/if} />
					</div>
				{/if}
			</div>
		{else}
			<input name="{$FIELD_MODEL->getFieldName()}" tabindex="{$TABINDEX}" value="{\App\Purifier::encodeHtml($NUMBER)}" id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" placeholder="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" type="text" class="form-control" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}{if $FIELD_MODEL->get('maximumlength')}maxSize[{$FIELD_MODEL->get('maximumlength')}],{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-advanced-verification="0" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if} {if isset($PARAMS['mask'])}data-inputmask="'mask': {\App\Purifier::encodeHtml(\App\Json::encode($PARAMS['mask']))}" {/if} />
		{/if}
	</div>
	<!-- /tpl-Base-Edit-Field-Phone -->
{/strip}
