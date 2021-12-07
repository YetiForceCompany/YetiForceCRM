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
	<!-- tpl-PriceBooks-Edit-Field-Boolean -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
	<div class="checkbox">
		<label>
			<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value="{if !empty($IS_RELATION)}1{else}0{/if}" />
			<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="checkbox" tabindex="{$FIELD_MODEL->getTabIndex()}" {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" disabled="disabled" {/if} title="{if !empty($IS_RELATION)}1{else}0{/if}" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}'
				{if $FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)} checked {/if}
				{if !empty($IS_RELATION)} disabled="disabled" {/if} {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} />
		</label>
	</div>
	<!-- /tpl-PriceBooks-Edit-Field-Boolean -->
{/strip}
