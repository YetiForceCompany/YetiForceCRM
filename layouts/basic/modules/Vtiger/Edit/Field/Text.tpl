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
	<!-- tpl-Base-Edit-Field-Text -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	<div>
		{if $FIELD_MODEL->getUIType() eq '19' || $FIELD_MODEL->getUIType() eq '20' || $FIELD_MODEL->getUIType() eq '300' }
			{assign var="PARAMS" value=$FIELD_MODEL->getFieldParams()}
			{assign var=UNIQUE_ID value=10|mt_rand:20}
			<textarea name="{$FIELD_MODEL->getFieldName()}" tabindex="{$FIELD_MODEL->getTabIndex()}"
				id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_{$UNIQUE_ID}{if $FIELD_MODEL->getUIType() eq '300' && !empty($VIEW) && $VIEW eq 'QuickCreateAjax'}_qc{/if}"
				class="col-md-12 form-control {if $FIELD_MODEL->getUIType() eq '300'}js-editor{/if} {if $FIELD_MODEL->isNameField()}nameField{/if} {if !empty($PARAMS['class'])}{$PARAMS['class']}{/if}"
				title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}"
				data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}{if $FIELD_MODEL->get('maximumlength')}funcCall[Vtiger_MaxSizeInByte_Validator_Js.invokeValidation]{else}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]{/if}"
				data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->getUIType() eq '300'}data-emoji-enabled="true" data-mentions-enabled="true" data-js="ckEditor" {/if}
				{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if}>{$FIELD_VALUE}</textarea>
		{else}
			<textarea name="{$FIELD_MODEL->getFieldName()}" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}"
				class="form-control {if $FIELD_MODEL->isNameField()}nameField{/if}" tabindex="{$FIELD_MODEL->getTabIndex()}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}"
				data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}{if $FIELD_MODEL->get('maximumlength')}funcCall[Vtiger_MaxSizeInByte_Validator_Js.invokeValidation]{else}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]{/if}]]"
				data-fieldinfo='{$FIELD_INFO}'
				{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if}>{$FIELD_VALUE}</textarea>
		{/if}
	</div>
	<!-- /tpl-Base-Edit-Field-Text -->
{/strip}
