{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getModulesListValues()}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	{assign var=PLACE_HOLDER value=($FIELD_MODEL->isEmptyPicklistOptionAllowed() && !($FIELD_MODEL->isMandatory() eq true && $FIELD_VALUE neq ''))}
	<select class="select2form-control" name="{$FIELD_MODEL->getFieldName()}"
			data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			data-fieldinfo='{$FIELD_INFO|escape}'
			{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if}
			data-selected-value="{$FIELD_VALUE}"{if $PLACE_HOLDER} data-placeholder="{\App\Language::translate('LBL_SELECT_OPTION','Vtiger')}" data-select="allowClear"{/if}{if $FIELD_MODEL->isEditableReadOnly()} readonly="readonly"{/if}>
		{if $PLACE_HOLDER}
			<optgroup class="p-0">
				<option value="">{\App\Language::translate('LBL_SELECT_OPTION','Vtiger')}</option>
			</optgroup>
		{/if}
		{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
			{if $FIELD_MODEL->getFieldName() eq 'target_module'}
				{if $PICKLIST_VALUE.name neq 'SSingleOrders'}{continue}{/if}
				<option value="{$PICKLIST_VALUE.name}" {if trim($FIELD_VALUE) eq trim($PICKLIST_VALUE.name)} selected {/if}>{\App\Purifier::encodeHtml($PICKLIST_VALUE.label)}</option>
			{else}
				<option value="{$PICKLIST_VALUE.name}" {if trim($FIELD_VALUE) eq trim($PICKLIST_VALUE.name)} selected {/if}>{\App\Purifier::encodeHtml($PICKLIST_VALUE.label)}</option>
			{/if}
		{/foreach}
	</select>
{/strip}
