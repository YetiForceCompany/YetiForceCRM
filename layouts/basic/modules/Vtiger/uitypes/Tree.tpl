{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
{assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var="ALL_VALUE" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL->getUITypeModel()->getAllValue()))}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
<input name="{$FIELD_MODEL->getFieldName()}" type="hidden" value="{$FIELD_MODEL->get('fieldvalue')}" class="sourceField" data-displayvalue='{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}' data-fieldinfo='{$FIELD_INFO}' data-multiple="{if $FIELD_MODEL->get('uitype') == 309 }1{else}0{/if}" data-treetemplate="{$FIELD_MODEL->getFieldParams()}" data-allvalues='{$ALL_VALUE}'  />
{assign var="displayId" value=$FIELD_MODEL->get('fieldvalue')}
<div class="input-group">
	{if $FIELD_MODEL->get('displaytype') != 10}
		<span class="input-group-addon clearTreeSelection cursorPointer">
			<span id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_clear" class='glyphicon glyphicon-remove-sign' title="{vtranslate('LBL_CLEAR', $MODULE)}"></span>
		</span>
	{/if}
	<input id="{$FIELD_NAME}_display" name="{$FIELD_MODEL->getFieldName()}_display" type="text" class="{if (($VIEW eq 'Edit'))} col-md-7 {else} col-md-8 {/if}	marginLeftZero treeAutoComplete form-control" {if !empty($displayId)}readonly="true"{/if}
	 value="{$FIELD_MODEL->getEditViewDisplayValue($displayId)}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
	 data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->get('displaytype') != 10}placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}"{/if}
	 {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}/>
	{if $FIELD_MODEL->get('displaytype') != 10}
		<span class="input-group-addon treePopup cursorPointer">
			<span id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_select" class="glyphicon glyphicon-search" title="{vtranslate('LBL_SELECT', $MODULE)}" ></span>
		</span>
	{/if}
</div>
{/strip}
