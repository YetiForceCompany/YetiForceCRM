{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(\includes\utils\Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
{assign var="FIELD_VALUE_LIST" value=explode(',',$FIELD_MODEL->get('fieldvalue'))}
<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value="" />
<select id="{$MODULE}_{$VIEW}_fieldName_{$FIELD_MODEL->get('name')}" title="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}" multiple class="chzn-select form-control col-md-12" name="{$FIELD_MODEL->getFieldName()}[]" data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isMandatory() eq true} {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}>
    {foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
        <option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if in_array(Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME), $FIELD_VALUE_LIST)} selected {/if}>{$PICKLIST_VALUE}</option>
    {/foreach}
</select>
{/strip}
