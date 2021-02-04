{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Base-Edit-Field-Password -->
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}
	<div class="input-group {$WIDTHTYPE_GROUP}">
		<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="password" tabindex="{$FIELD_MODEL->getTabIndex()}" class="form-control {if $FIELD_MODEL->isNameField()}nameField{/if}"
			   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			   name="{$FIELD_NAME}" value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}"
			   data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}'{/if}>
		<span class="input-group-append">
			<button class="btn btn-light js-popover-tooltip" data-content="{\App\Language::translate('LBL_SHOW_PASSWORD',$MODULE)}" type="button" onmousedown="{$FIELD_NAME}.type = 'text';" onmouseup="{$FIELD_NAME}.type = 'password';" onmouseout="{$FIELD_NAME}.type = 'password';" data-js="popover">
				<span class="fas fa-eye"></span>
			</button>
		</span>
	</div>
	<!-- /tpl-Base-Edit-Field-Password -->
{/strip}
