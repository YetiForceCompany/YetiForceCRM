{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="FIELD_NAME" value=$FIELD_MODEL->getName()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	<div class="tpl-Detail-Field-Base input-group">
		<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text"
			   class="form-control {if $FIELD_MODEL->isNameField()}nameField{/if} {if $FIELD_NAME eq 'password'} col-3 {/if}"
			   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			   name="{$FIELD_MODEL->getFieldName()}"
				{if $FIELD_NAME eq 'password' }
					value="{if $RECORD->getId() neq ''}{str_repeat('*', 10)}{/if}"
					{if $VIEW eq 'Edit' || $VIEW eq 'QuickCreateAjax'}
						onkeyup="passwordStrength('', '{$VALIDATE_STRINGS}')"
						onchange="passwordStrength('', '{$VALIDATE_STRINGS}')"
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
			<div class="input-group-append col-9 u-p-x-0px">
				{if $RECORD->getId() neq ''}
					<span class="input-group-text col-lg-6 col-sm-9 col-10">
					   <button class="btn btn-warning btn-sm" onclick="showPassword('{$RECORD->getId()}'); return false;" id="show-btn">
						   {\App\Language::translate('LBL_ShowPassword', $MODULE)}
					   </button>
						{* button for copying password to clipboard *}
						<button type="button" class="btn btn-success btn-sm d-none ml-1" data-copy-target="{$MODULE}_editView_fieldName_{$FIELD_NAME}" id="copy-button" title="{\App\Language::translate('LBL_CopyToClipboardTitle', $MODULE)}">
					   		<span class="fas fa-download"></span>
				   		</button>
					</span>
				{/if}
				{if $FIELD_VALUE eq ''}
					<span class="input-group-text col-6">
				   		<div id="passwordDescription">{\App\Language::translate('Enter the password', $MODULE)}</div>
					</span>
				{else}
					<span class="input-group-text col-lg-4 d-none d-lg-block">
				   		<div id="passwordDescription">{\App\Language::translate('Password is hidden', $MODULE)}</div>
					</span>
				{/if}
				<span class="input-group-text {if $FIELD_VALUE eq ''} col-6 {else} col-lg-2 col-sm-3 col-2 {/if}">
			   		<div class="strength0 col-12" id="passwordStrength"></div>
				</span>
			</div>
		{/if}


	</div>
{/strip}
