{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="FIELD_NAME" value=$FIELD_MODEL->getName()}
	{assign var="FIELD_VALUE" value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	{if empty($VALIDATE_STRINGS)}
		{assign var="VALIDATE_STRINGS" value=""}
	{/if}
	<div class="tpl-Detail-Field-Base input-group">
		<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text"
			   class="form-control {if $FIELD_MODEL->isNameField()}nameField{/if}"
			   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			   name="{$FIELD_MODEL->getFieldName()}"
				{if $FIELD_NAME eq 'password' }
					value="{if $RECORD && $RECORD->getId() neq ''}{str_repeat('*', 10)}{/if}"
					{if $VIEW eq 'Edit' || $VIEW eq 'QuickCreateAjax'}
						onkeyup="PasswordHelper.passwordStrength('', '{$VALIDATE_STRINGS}')"
						onchange="PasswordHelper.passwordStrength('', '{$VALIDATE_STRINGS}')"
					{/if}
				{else}
					value="{$FIELD_VALUE}"
				{/if}
				{if ($FIELD_MODEL->getUIType() eq '106' && $MODE neq '') || $FIELD_MODEL->getUIType() eq '3'
				|| $FIELD_MODEL->getUIType() eq '4'|| $FIELD_MODEL->isReadOnly()}
					readonly
				{/if}
			   data-fieldinfo='{$FIELD_INFO}'
				{if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if}/>
		{if $FIELD_NAME eq 'password' && ($VIEW eq 'Edit'  || $VIEW eq 'QuickCreateAjax')}
			<div class="input-group-append">
				{if $RECORD && $RECORD->getId() neq ''}
					<button class="btn btn-warning btn-md"
							onclick="PasswordHelper.showPassword('{$RECORD->getId()}'); return false;"
							id="show-btn">
						{\App\Language::translate('LBL_ShowPassword', $MODULE)}
					</button>
					{* button for copying password to clipboard *}
					<button type="button" class="btn btn-success btn-md d-none"
							data-copy-target="{$MODULE}_editView_fieldName_{$FIELD_NAME}" id="copy-button"
							title="{\App\Language::translate('LBL_CopyToClipboardTitle', $MODULE)}">
						<span class="fas fa-download"></span>
					</button>
				{/if}
				{if $FIELD_VALUE eq ''}
					<span class="strength0 input-group-text" id="passwordStrength"><strong><span
									id="passwordDescription">{\App\Language::translate('Enter the password', $MODULE)}</span></strong></span>
				{else}
					<span class="strength0 input-group-text" id="passwordStrength"><strong><span
									id="passwordDescription">{\App\Language::translate('Password is hidden', $MODULE)}</span></strong></span>
				{/if}
			</div>
		{/if}


	</div>
{/strip}
