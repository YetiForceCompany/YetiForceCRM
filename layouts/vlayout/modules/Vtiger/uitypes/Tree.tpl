{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{strip}
{assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var="ALL_VALUE" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getUitypeInstance()->getAllValue()))}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
<input name="{$FIELD_MODEL->getFieldName()}" type="hidden" value="{$FIELD_MODEL->get('fieldvalue')}" class="sourceField" data-displayvalue='{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}' data-fieldinfo='{$FIELD_INFO}' data-treetemplate="{$FIELD_MODEL->getFieldParams()}" data-allvalues='{$ALL_VALUE}' />
{assign var="displayId" value=$FIELD_MODEL->get('fieldvalue')}
<div class="row-fluid input-prepend input-append">
	{if $FIELD_MODEL->get('displaytype') != 10}
		<span class="add-on clearTreeSelection cursorPointer">
			<span id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_clear" class='icon-remove-sign' title="{vtranslate('LBL_CLEAR', $MODULE)}"></span>
		</span>
	{/if}
	<input id="{$FIELD_NAME}_display" name="{$FIELD_MODEL->getFieldName()}_display" type="text" class="{if (($smarty.request.view eq 'Edit') or ($smarty.request.module eq 'Webforms'))} span7 {else} span8 {/if}	marginLeftZero treeAutoComplete" {if !empty($displayId)}readonly="true"{/if}
	 value="{$FIELD_MODEL->getEditViewDisplayValue($displayId)}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
	 data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->get('displaytype') != 10}placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}"{/if}
	 {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} {if $FIELD_MODEL->get('displaytype') == 10}readonly="readonly"{/if}/>
	{if $FIELD_MODEL->get('displaytype') != 10}
		<span class="add-on treePopup cursorPointer">
			<span id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_select" class="icon-search" title="{vtranslate('LBL_SELECT', $MODULE)}" ></span>
		</span>
	{/if}
</div>
{/strip}
