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
{if $SALUTATION_FIELD_MODEL}
{assign var=PICKLIST_VALUES value=$SALUTATION_FIELD_MODEL->getPicklistValues()}
{assign var="SALUTATION_VALIDATOR" value=$SALUTATION_FIELD_MODEL->getValidator()}
<select class="chzn-select" style="width: 70px" name="{$SALUTATION_FIELD_MODEL->get('name')}" data-validation-engine="validate[{if $SALUTATION_FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" >
    {if $SALUTATION_FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_NONE', $MODULE)}</option>{/if}
    {foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
        <option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if trim(decode_html($SALUTATION_FIELD_MODEL->get('fieldvalue'))) eq trim($PICKLIST_NAME)} selected {/if}>{$PICKLIST_VALUE}</option>
    {/foreach}
</select>&nbsp;
{/if}

{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}
<input {if $SALUTATION_FIELD_MODEL} style="width:130px" {/if} id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" class="input-large {if $FIELD_MODEL->isNameField()}nameField{/if}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name="{$FIELD_MODEL->getFieldName()}" value="{$FIELD_MODEL->get('fieldvalue')}"
{if $FIELD_MODEL->get('uitype') eq '3' || $FIELD_MODEL->get('uitype') eq '4'} readonly {/if} data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if} />
{* TODO - Handler Ticker Symbol field *}
{/strip}