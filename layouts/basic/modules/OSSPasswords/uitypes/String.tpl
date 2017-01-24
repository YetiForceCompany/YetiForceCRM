{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{strip}
{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}

<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" 
        class="form-control {if $FIELD_MODEL->isNameField()}nameField{/if}" 
        data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" 
        name="{$FIELD_MODEL->getFieldName()}" 
        {if $FIELD_NAME eq 'password' } 
        value="{if $RECORD->getId() neq ''}{str_repeat('*', 10)}{/if}" 
        {if $VIEW eq 'Edit' || $VIEW eq 'QuickCreateAjax'}
            onkeyup="passwordStrength('','{$VALIDATE_STRINGS}')"  
            onchange="passwordStrength('','{$VALIDATE_STRINGS}')"  
        {/if}
        {else}
        value="{$FIELD_MODEL->get('fieldvalue')}" 
        {/if}
		{if ($FIELD_MODEL->get('uitype') eq '106' && $MODE neq '') || $FIELD_MODEL->get('uitype') eq '3' 
				|| $FIELD_MODEL->get('uitype') eq '4'|| $FIELD_MODEL->isReadOnly()} 
				readonly 
		{/if} 
data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if} />

{if $FIELD_NAME eq 'password' && ($VIEW eq 'Edit'  || $VIEW eq 'QuickCreateAjax')} 
	&nbsp;
	{if $RECORD->getId() neq ''}
		<button class="btn btn-warning btn-xs" 
				onclick="showPassword('{$RECORD->getId()}');return false;" id="show-btn">
			{vtranslate('LBL_ShowPassword', $MODULE)}
		</button>
		&nbsp;
		{* button for copying password to clipboard *}
		<button class="btn btn-success btn-xs hide" data-clipboard-target="{$MODULE}_editView_fieldName_{$FIELD_NAME}" id="copy-button" title="{vtranslate('LBL_CopyToClipboardTitle', $MODULE)}">
			<span class="glyphicon glyphicon-download-alt"></span>
		</button>
	{/if}
	<p>
		{if $FIELD_MODEL->get('fieldvalue') eq ''}
		<div id="passwordDescription">{vtranslate('Enter the password', $MODULE)}</div>
		{else}
		<div id="passwordDescription">{vtranslate('Password is hidden', $MODULE)}</div>
		{/if}
		<div id="passwordStrength" class="strength0"></div>
	</p>
{/if}
{/strip}
