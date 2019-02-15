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
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="FIELD_NAME" value=$FIELD_MODEL->getName()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	<input class="tpl-Edit-Field-Base form-control {if $FIELD_MODEL->isNameField()}nameField{/if}"
		   name="{$FIELD_MODEL->getFieldName()}" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text"
		   title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
		   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}{if $FIELD_MODEL->get('maximumlength')}maxSize[{$FIELD_MODEL->get('maximumlength')}],{/if} funcCall[Vtiger_InputMask_Validator_Js.invokeValidation]]"
		   {if $FIELD_MODEL->isNameField()}autocomplete="username"{/if}
		   value="{$FIELD_VALUE}"
			{if $FIELD_MODEL->getUIType() eq '3' || $FIELD_MODEL->getUIType() eq '4'|| $FIELD_MODEL->isReadOnly() || $FIELD_MODEL->isEditableReadOnly()} readonly="readonly" {/if}
		   data-fieldinfo='{$FIELD_INFO}'
			{if !empty($SPECIAL_VALIDATOR)}data-validator="{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}"{/if}
			{if $FIELD_MODEL->get('fieldparams') != ''}data-inputmask="'mask': '{$FIELD_MODEL->get('fieldparams')}'"{/if} />
{/strip}
