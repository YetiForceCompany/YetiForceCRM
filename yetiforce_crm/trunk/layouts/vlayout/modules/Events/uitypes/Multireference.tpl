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
{assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
{assign var="REFERENCE_LIST" value=$FIELD_MODEL->getReferenceList()}
{assign var="REFERENCE_LIST_COUNT" value=count($REFERENCE_LIST)}
{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{if {$REFERENCE_LIST_COUNT} eq 1}
	<input name="popupReferenceModule" type="hidden" value="{$REFERENCE_LIST[0]}" />
{/if}
{if {$REFERENCE_LIST_COUNT} gt 1}
	{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
	{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
	{if !empty($REFERENCED_MODULE_STRUCT)}
		{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
	{/if}
	{if in_array($REFERENCED_MODULE_NAME, $REFERENCE_LIST)}
		<input name="popupReferenceModule" type="hidden" value="{$REFERENCED_MODULE_NAME}" />
	{else}
		<input name="popupReferenceModule" type="hidden" value="{$REFERENCE_LIST[0]}" />
	{/if}
{/if}
<input name="{$FIELD_NAME}" type="hidden" value="{$FIELD_MODEL->get('fieldvalue')}" class="sourceField" data-displayvalue='{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}' data-fieldinfo='{$FIELD_INFO}' data-multiple='true' data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if}>
{assign var="displayId" value=$FIELD_MODEL->get('fieldvalue')}
<div class="row-fluid input-prepend input-append">
<span class="add-on clearReferenceSelection cursorPointer">
	<i class='icon-remove-sign' title="{vtranslate('LBL_CLEAR', $MODULE)}"></i>
</span>
<input id="{$FIELD_NAME}_display" name="{$FIELD_NAME}_display" type="text" class="{if $smarty.request.view eq 'Edit'} span7 {else} span8 {/if}	marginLeftZero autoComplete" {if !empty($displayId)}readonly="true"{/if}
 value="{$FIELD_MODEL->getEditViewDisplayValue($displayId)}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
 data-fieldinfo='{$FIELD_INFO}' placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}"
 {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}/>
<span class="add-on relatedPopup cursorPointer">
	<i class="icon-search relatedPopup" title="{vtranslate('LBL_SELECT', $MODULE)}" ></i>
</span>
<!-- Show the add button only if it is edit view  -->
{if $smarty.request.view eq 'Edit'}
<span class="add-on cursorPointer createReferenceRecord">
	<i class='icon-plus' title="{vtranslate('LBL_CREATE', $MODULE)}"></i>
</span>
{/if}
<input type="hidden" name="relatedContactInfo" data-value='{ZEND_JSON::encode($RELATED_CONTACTS)}' />
</div>
{/strip}
