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
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="dateFormat" value=$USER_MODEL->get('date_format')}
	{assign var="PARAMS" value=$FIELD_MODEL->getFieldParams()}
	<div class="input-group date">
		{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
		<input name="{$FIELD_MODEL->getFieldName()}" class="{if !$FIELD_MODEL->isEditableReadOnly()}dateField{/if} form-control" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" {if $PARAMS && $PARAMS['onChangeCopyValue']}data-copy-to-field="{$PARAMS['onChangeCopyValue']}"{/if} data-date-format="{$dateFormat}" type="text" value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"   {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} data-fieldinfo='{$FIELD_INFO}'
			   {if $MODE eq 'edit' && $FIELD_NAME eq 'due_date'} data-user-changed-time="true" {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} />
		<span class="input-group-append">
			<span class="input-group-text u-cursor-pointer" id="basic-addon2">
				<span class="fas fa-calendar-alt"></span>
			</span>
		</span>
	</div>

{/strip}
