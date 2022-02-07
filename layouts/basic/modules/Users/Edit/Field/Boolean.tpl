{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Users-Edit-Field-Boolean -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
	<div class="checkbox">
		<label class="d-flex m-0 mt-1">
			{if !$FIELD_MODEL->isEditableReadOnly()}
				<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value="0" />
			{/if}
			<input name="{$FIELD_MODEL->getFieldName()}" tabindex="{$FIELD_MODEL->getTabIndex()}" {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" disabled="disabled" {/if}
				title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="checkbox" {' '}
				data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {' '}
				{if $FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}checked {/if}
				data-fieldinfo='{$FIELD_INFO}' {' '}
				{if !empty($SPECIAL_VALIDATOR)}data-validator="{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}" {/if} />
		</label>
	</div>
	<!-- /tpl-Users-Edit-Field-Boolean -->
{/strip}
