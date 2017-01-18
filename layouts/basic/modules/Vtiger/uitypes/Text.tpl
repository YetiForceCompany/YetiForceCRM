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
{assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}
{assign var=UNIQUE_ID value=10|mt_rand:20}
{if $smarty.post.view neq 'QuickCreateAjax'}
	{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
{/if}
{if $FIELD_MODEL->get('uitype') eq '19' || $FIELD_MODEL->get('uitype') eq '20' || $FIELD_MODEL->get('uitype') eq '300' }
    <textarea id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_{$UNIQUE_ID}{if $FIELD_MODEL->get('uitype') eq '300' && $smarty.post.view eq 'QuickCreateAjax'}_qc{/if}" class="col-md-11 form-control {if $FIELD_MODEL->get('uitype') eq '300'}ckEditorSource{/if} {if $FIELD_MODEL->isNameField()}nameField{/if}" title="{vtranslate($FIELD_MODEL->get('label'))}" name="{$FIELD_MODEL->getFieldName()}" {if $FIELD_NAME eq "notecontent"}id="{$FIELD_NAME}"{/if} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}>
    {$FIELD_MODEL->get('fieldvalue')}</textarea>
{else}
    <textarea id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" class="form-control {if $FIELD_MODEL->isNameField()}nameField{/if}" title="{vtranslate($FIELD_MODEL->get('label'))} " name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}>
    {$FIELD_MODEL->get('fieldvalue')}</textarea>
{/if}
{/strip}
