{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(\includes\utils\Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var=UITYPE_MODEL value=$FIELD_MODEL->getUITypeModel()}
{assign var=PICKLIST_VALUES value=$UITYPE_MODEL->getLimits()}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
<select title="{vtranslate($FIELD_MODEL->get('label'))}" class="chzn-select form-control col-md-12" name="{$FIELD_MODEL->getFieldName()}" data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isMandatory() eq true} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !empty($SPECIAL_VALIDATOR)}data-validator='{\includes\utils\Json::encode($SPECIAL_VALIDATOR)}'{/if} {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}>
	{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="" {if $FIELD_MODEL->isMandatory() eq true && $FIELD_MODEL->get('fieldvalue') neq ''} disabled{/if}>{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
    {foreach item=VALUE key=KEY from=$PICKLIST_VALUES}
        <option value="{Vtiger_Util_Helper::toSafeHTML($KEY)}" {if $KEY == $FIELD_MODEL->get('fieldvalue')} selected {/if}>{$VALUE['value']} - {$VALUE['name']}</option>
    {/foreach}
</select>
{/strip}
