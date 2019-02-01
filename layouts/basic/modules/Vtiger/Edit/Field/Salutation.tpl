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
	<div class="tpl-Edit-Field-Salutation row">
		{if !empty($SALUTATION_FIELD_MODEL)}
			{assign var=PICKLIST_VALUES value=$SALUTATION_FIELD_MODEL->getPicklistValues()}
			{assign var="SALUTATION_VALIDATOR" value=$SALUTATION_FIELD_MODEL->getValidator()}
			<div class="col-md-5">
				<select class="select2 form-control" name="{$SALUTATION_FIELD_MODEL->getName()}"
						data-validation-engine="validate[{if $SALUTATION_FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]">
					{if $SALUTATION_FIELD_MODEL->isEmptyPicklistOptionAllowed()}
						<option value="">{\App\Language::translate('LBL_NONE', $MODULE)}</option>{/if}
					{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
						<option value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" {if trim($SALUTATION_FIELD_MODEL->get('fieldvalue')) eq trim($PICKLIST_NAME)} selected {/if}>{App\Purifier::encodeHtml($PICKLIST_VALUE)}</option>
					{/foreach}
				</select>
			</div>
		{/if}
		<div class="{if !empty($SALUTATION_FIELD_MODEL)}col-md-7{else}col-md-12{/if}">
			{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
			{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
			{assign var="FIELD_NAME" value=$FIELD_MODEL->getName()}
			<input name="{$FIELD_MODEL->getFieldName()}"
				   class="form-control {if $FIELD_MODEL->isNameField()}nameField{/if}"
				   title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$MODULE)}"
				   id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text"
				   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}{if $FIELD_MODEL->get('maximumlength')} maxSize[{$FIELD_MODEL->get('maximumlength')}],{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
				   value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}" {if $FIELD_MODEL->getUIType() eq '3' || $FIELD_MODEL->getUIType() eq '4'} readonly {/if}
				   data-fieldinfo='{$FIELD_INFO}'
					{if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if}/>
		</div>
	</div>
{/strip}