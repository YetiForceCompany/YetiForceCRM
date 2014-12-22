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
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}

<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value="{if $IS_RELATION eq true}1{else}0{/if}" />
<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="checkbox" name="{$FIELD_MODEL->getFieldName()}"
data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}'
{if $FIELD_MODEL->get('fieldvalue') eq true} checked {/if}
{if $IS_RELATION eq true} disabled="disabled" {/if}
{if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if}
/>
{/strip}