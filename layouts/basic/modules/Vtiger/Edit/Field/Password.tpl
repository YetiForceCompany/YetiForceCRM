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
	<!-- tpl-Base-Edit-Field-Password -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_NAME value=$FIELD_MODEL->getFieldName()}
	{assign var=UITYPE_MODEL value=$FIELD_MODEL->getUITypeModel()}
	{assign var=PARAMS value=$FIELD_MODEL->getFieldParams()}
	{assign var=DISABLE_FIELD value=$FIELD_MODEL->isEditableReadOnly()}
	{assign var=EDIT_MODE value=!empty($FIELD_MODEL->get('fieldvalue')) || !empty($RECORD_ID) }
	<div class="input-group {$WIDTHTYPE_GROUP} js-pwd-container">
		<input id="{$MODULE_NAME}_editView_fieldName_{$FIELD_NAME}" type="password" tabindex="{$FIELD_MODEL->getTabIndex()}" class="form-control {if $FIELD_MODEL->isNameField()}nameField{/if} js-pwd-field"
			data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}{if $FIELD_MODEL->get('maximumlength')}maxSize[{$FIELD_MODEL->getMaxValue()}],{/if} funcCall[Vtiger_InputMask_Validator_Js.invokeValidation]]"
			name="{$FIELD_NAME}" value="{if $EDIT_MODE}{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD)}{/if}"
			data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}
			data-module="{$FIELD_MODEL->getModuleName()}"
			{if !empty($PARAMS['strengthMeter'])} data-strength-meter="{$PARAMS['strengthMeter']}" {/if}
			{if $EDIT_MODE || $DISABLE_FIELD} disabled="disabled" {/if}>
		<span class="input-group-append">
			{if !$DISABLE_FIELD}
				{if $EDIT_MODE}
					<button class="btn btn-light js-pwd-clear" type="button" title="{\App\Language::translate('LBL_CLEAR', $MODULE_NAME)}" {if $FIELD_MODEL->isEditableReadOnly()}disabled{/if}>
						<span class="fas fa-times-circle"></span>
					</button>
				{/if}
				{assign var=ACTIONS_URL value=$UITYPE_MODEL->getActionsUrl()}
				{if !empty($PARAMS['auto-generate']) && !empty($ACTIONS_URL['generate'])}
					<button class="btn btn-light js-popover-tooltip js-pwd-auto-generate" data-content="{\App\Language::translate('LBL_PWD_AUTO_GENERATE',$MODULE_NAME)}" type="button" data-field="{$FIELD_NAME}" data-url="{$ACTIONS_URL['generate']}" data-placement="bottom" data-js="popover|click">
						<span class="mdi mdi-form-textbox-password"></span>
					</button>
				{/if}
				{if !empty($PARAMS['validate']) && !empty($ACTIONS_URL['validate']) && (in_array('config', $PARAMS['validate']) || (in_array('pwned', $PARAMS['validate']) && \App\Extension\PwnedPassword::getDefaultProvider()->isActive()))}
					<button class="btn btn-light js-popover-tooltip js-pwd-validate" data-content="{\App\Language::translate('LBL_PWD_VALIDATE',$MODULE_NAME)}" type="button" data-field="{$FIELD_NAME}" data-url="{$ACTIONS_URL['validate']}" data-placement="bottom" data-js="popover|click" {if $EDIT_MODE} disabled="disabled" {/if}>
						<span class="mdi mdi-lock-question"></span>
					</button>
				{/if}
				{if $EDIT_MODE && !$FIELD_MODEL->get('fromOutsideList')}
					<button class="btn btn-light js-popover-tooltip js-pwd-get" data-content="{\App\Language::translate('LBL_PWD_GET',$MODULE_NAME)}" type="button"
						data-placement="bottom" data-js="popover">
						<span class="fas fa-eye-slash"></span>
					</button>
				{/if}
				<button class="btn btn-light js-popover-tooltip js-pwd-copy" data-content="{\App\Language::translate('BTN_COPY_TO_CLIPBOARD',$MODULE_NAME)}" type="button" data-placement="bottom" data-js="popover">
					<span class="fas fa-copy"></span>
				</button>
				<button class="btn btn-light js-popover-tooltip js-pwd-show" data-content="{\App\Language::translate('LBL_SHOW_PASSWORD',$MODULE_NAME)}" type="button"
					onmousedown="{$FIELD_NAME}.type = 'text';" onmouseup="{$FIELD_NAME}.type = 'password';" onmouseout="{$FIELD_NAME}.type = 'password';" data-placement="bottom" data-js="popover" {if $EDIT_MODE} disabled="disabled" {/if}>
					<span class="fas fa-eye"></span>
				</button>
			{/if}
		</span>
	</div>
	<!-- /tpl-Base-Edit-Field-Password -->
{/strip}
