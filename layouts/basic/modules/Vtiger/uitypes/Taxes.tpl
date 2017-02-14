{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="FIELD_VALUE_LIST" value=$FIELD_MODEL->getUITypeModel()->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}
	<select id="{$MODULE}_{$VIEW}_fieldName_{$FIELD_MODEL->get('name')}" title="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}" multiple class="chzn-select form-control col-md-12" name="{$FIELD_MODEL->getFieldName()}[]" data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isMandatory() eq true} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}>
		{foreach item=PICKLIST_NAME key=PICKLIST_VALUE from=$PICKLIST_VALUES}
			<option value="{$PICKLIST_VALUE}" {if in_array($PICKLIST_VALUE,$FIELD_VALUE_LIST)} selected {/if}>{$PICKLIST_NAME}</option>
		{/foreach}
	</select>
{/strip}
