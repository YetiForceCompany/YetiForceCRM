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
{assign var=UITYPE_MODEL value=$FIELD_MODEL->getUITypeModel()}
{assign var=PICKLIST_VALUES value=$UITYPE_MODEL->getTaxes()}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var="FIELD_VALUE_LIST" value=explode(',',$FIELD_MODEL->get('fieldvalue'))}
<select title="{vtranslate($FIELD_MODEL->get('label'))}" multiple class="chzn-select form-control col-md-12" name="{$FIELD_MODEL->getFieldName()}[]" data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isMandatory() eq true} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} {/if} {if $FIELD_MODEL->get('displaytype') == 10}readonly="readonly"{/if}>
    {foreach item=VALUE key=KEY from=$PICKLIST_VALUES}
        <option value="{Vtiger_Util_Helper::toSafeHTML($KEY)}" {if in_array(Vtiger_Util_Helper::toSafeHTML($KEY), $FIELD_VALUE_LIST)} selected {/if}>{$VALUE['value']}% - {$VALUE['name']}</option>
    {/foreach}
</select>
{/strip}
