{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	<div class="tpl-Edit-Field-MultiEmail js-multi-email">
		<input name="{$FIELD_MODEL->getFieldName()}" class="form-control"
			   title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
			   id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}"
			   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_MultiEmail_Validator_Js.invokeValidation]]"
			   value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}" {if !empty($MODE) && $MODE eq 'edit' && $FIELD_MODEL->getUIType() eq '106'} readonly {/if}
			   data-fieldinfo='{$FIELD_INFO}'
			   {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} />
	</div>
{/strip}