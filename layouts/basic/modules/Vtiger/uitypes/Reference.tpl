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
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{if {$REFERENCE_LIST_COUNT} eq 1}
	<input name="popupReferenceModule" type="hidden" data-multi-reference="0" title="{reset($REFERENCE_LIST)}" value="{reset($REFERENCE_LIST)}" />
{/if}
{if {$REFERENCE_LIST_COUNT} gt 1}
	{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
	{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
	{if !empty($REFERENCED_MODULE_STRUCT)}
		{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
	{/if}
	{if in_array($REFERENCED_MODULE_NAME, $REFERENCE_LIST)}
		<input name="popupReferenceModule" type="hidden" data-multi-reference="1" value="{$REFERENCED_MODULE_NAME}" />
	{else}
		<input name="popupReferenceModule" type="hidden" data-multi-reference="1" value="{$REFERENCE_LIST[0]}" />
	{/if}
{/if}
<input name="{$FIELD_MODEL->getFieldName()}" type="hidden" value="{$FIELD_MODEL->get('fieldvalue')}" title="{$FIELD_MODEL->get('fieldvalue')}" class="sourceField" data-displayvalue='{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}' data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->get('displaytype') == 10}readonly="readonly"{/if} />
{assign var="displayId" value=$FIELD_MODEL->get('fieldvalue')}
<div class="input-group">
	{if $FIELD_MODEL->get('displaytype') != 10}
		<span class="input-group-addon clearReferenceSelection cursorPointer">
			<span id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_clear" class='glyphicon glyphicon-remove-sign' title="{vtranslate('LBL_CLEAR', $MODULE)}"></span>
		</span>
	{/if}
	<input id="{$FIELD_NAME}_display" name="{$FIELD_MODEL->getFieldName()}_display" type="text" title="{vtranslate($FIELD_MODEL->get('fieldvalue'))}" class="marginLeftZero form-control autoComplete" {if !empty($displayId)}readonly="true"{/if}
	 value="{Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getEditViewDisplayValue($displayId))}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
	 data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->get('displaytype') != 10}placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}"{/if}
	 {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} {if $FIELD_MODEL->get('displaytype') == 10}readonly="readonly"{/if}/>
	{if $FIELD_MODEL->get('displaytype') != 10}
		<span class="input-group-addon relatedPopup cursorPointer">
			<span id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_select" class="glyphicon glyphicon-search relatedPopup" title="{vtranslate('LBL_SELECT', $MODULE)}" ></span>
		</span>
	{/if}
	{assign var=REFERENCE_MODULE_MODEL value=Vtiger_Module_Model::getInstance($REFERENCE_LIST[0])}
	<!-- Show the add button only if it is edit view  -->
	{if (($VIEW eq 'Edit') or ($MODULE_NAME eq 'Webforms')) && $REFERENCE_MODULE_MODEL->isQuickCreateSupported() && $FIELD_MODEL->get('displaytype') != 10}
	<span class="input-group-addon cursorPointer createReferenceRecord">
		<span id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_create" class='glyphicon glyphicon-plus' title="{vtranslate('LBL_CREATE', $MODULE)}"></span>
	</span>
	{/if}
</div>
{/strip}
