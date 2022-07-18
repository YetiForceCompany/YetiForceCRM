{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Users-Edit-Field-Password -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_NAME value=$FIELD_MODEL->getFieldName()}
	<div class="input-group {$WIDTHTYPE_GROUP}">
		<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="password" tabindex="{$FIELD_MODEL->getTabIndex()}" class="form-control {if $FIELD_MODEL->isNameField()}nameField{/if}"
			data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			name="{$FIELD_NAME}" value="" data-fieldinfo='{$FIELD_INFO}'
			{if !empty($SPECIAL_VALIDATOR)} data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}>
		<span class="input-group-append">
			{if $FIELD_NAME === 'user_password'}
				<button class="btn btn-light js-popover-tooltip js-validate-password" data-content="{\App\Language::translate('LBL_VALIDATE_PASSWORD',$MODULE)}" type="button" data-field="{$FIELD_NAME}" data-js="popover|click">
					<span class="mdi mdi-lock-question"></span>
				</button>
			{/if}
			<button class="btn btn-light js-popover-tooltip" data-content="{\App\Language::translate('LBL_SHOW_PASSWORD',$MODULE)}" type="button" onmousedown="{$FIELD_NAME}.type = 'text';" onmouseup="{$FIELD_NAME}.type = 'password';" onmouseout="{$FIELD_NAME}.type = 'password';" data-js="popover">
				<span class="fas fa-eye"></span>
			</button>
		</span>
	</div>
	<!-- /tpl-Users-Edit-Field-Password -->
{/strip}
