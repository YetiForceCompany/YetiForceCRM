{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	<div class="tpl-Base-Edit-Field-Twitter input-group {$WIDTHTYPE_GROUP}">
		<div class=" input-group-append">
			<span class="input-group-text" title="Twitter">@</span>
		</div>
		<input name="{$FIELD_MODEL->getFieldName()}" tabindex="{$FIELD_MODEL->getTabIndex()}" value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}"
			class="form-control" id="{$MODULE_NAME}_editView_fieldName_{$FIELD_MODEL->getName()}"
			data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}maxSize[15],funcCall[Vtiger_Twitter_Validator_Js.invokeValidation]]"
			data-advanced-verification="0" {if !empty($FIELD_INFO)}data-fieldinfo='{$FIELD_INFO}' {/if}
			{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if}
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}">
	</div>
{/strip}
