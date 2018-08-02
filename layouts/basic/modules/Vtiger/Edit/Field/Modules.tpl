{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getModulesListValues()}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	<div class="tpl-Edit-Field-Modules">
	<select class="chzn-select form-control" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} data-selected-value='{$FIELD_VALUE}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}>
		{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="" {if $FIELD_MODEL->isMandatory() eq true && $FIELD_VALUE neq ''} disabled{/if}>{\App\Language::translate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
		{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
			<option value="{\App\Purifier::encodeHtml($PICKLIST_VALUE['name'])}" {if trim($FIELD_VALUE) eq trim($PICKLIST_VALUE['name'])} selected {/if}>
				{\App\Purifier::encodeHtml($PICKLIST_VALUE['label'])}
			</option>
		{/foreach}
	</select>
	</div>
{/strip}
