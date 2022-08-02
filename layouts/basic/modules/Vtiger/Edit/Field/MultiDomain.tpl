{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-MultiDomain -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	<div class="tpl-Edit-Field-MultiDomain">
		<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value="" />
		<select id="{$MODULE_NAME}_{$VIEW}_fieldName_{$FIELD_MODEL->getName()}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}" multiple data-tags="true"
			class="js-multi-domain select2 form-control col-md-12" name="{$FIELD_MODEL->getFieldName()}[]" data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isMandatory() eq true}
			data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {else} data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			{/if}
			data-validator='{\App\Purifier::encodeHtml(\App\Json::encode([['name'=>'MultiDomain']]))}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if} tabindex="{$FIELD_MODEL->getTabIndex()}">
			{foreach item=PICKLIST_VALUE from=$FIELD_VALUE}
				<option value="{\App\Purifier::encodeHtml($PICKLIST_VALUE)}" {if $PICKLIST_VALUE}selected{/if}>{$PICKLIST_VALUE}</option>
			{/foreach}
		</select>
	</div>
	<!-- /tpl-Base-Edit-Field-MultiDomain -->
{/strip}
