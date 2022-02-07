{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-Double -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var="PARAMS" value=$FIELD_MODEL->getFieldParams()}
	<input name="{$FIELD_MODEL->getFieldName()}" id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" type="text" class="tpl-Edit-Field-Double form-control"
		{if isset($PARAMS['mask'])}data-inputmask="'mask': {\App\Purifier::encodeHtml(\App\Json::encode($PARAMS['mask']))}" {/if} tabindex="{$FIELD_MODEL->getTabIndex()}"
		value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {else} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {/if} />
	<!-- /tpl-Base-Edit-Field-Double -->
{/strip}
