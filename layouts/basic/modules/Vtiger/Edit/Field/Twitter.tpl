{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Edit-Field-Twitter input-group">
		<div class=" input-group-append">
			<span class="input-group-text" title="Twitter">@</span>
		</div>
		<input name="{$FIELD_MODEL->getFieldName()}"
			   value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}"
			   class="form-control" id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}"
			   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}maxSize[15],funcCall[Vtiger_Twitter_Validator_Js.invokeValidation]]"
			   data-advanced-verification="0" {if !empty($FIELD_INFO)}data-fieldinfo='{$FIELD_INFO}'{/if}
			   {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}
			   title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}">
	</div>
{/strip}
