{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-Taxes -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	<div>
		<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value="" />
		<select id="{$MODULE}_{$VIEW}_fieldName_{$FIELD_MODEL->getName()}" tabindex="{$FIELD_MODEL->getTabIndex()}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
			multiple="multiple" class="select2 form-control col-md-12" name="{$FIELD_MODEL->getFieldName()}[]"
			data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isMandatory() eq true} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if}>
			{foreach item=PICKLIST_NAME key=PICKLIST_VALUE from=$PICKLIST_VALUES}
				<option value="{\App\Purifier::encodeHtml($PICKLIST_VALUE)}" {if in_array($PICKLIST_VALUE,$FIELD_VALUE)} selected {/if}>
					{\App\Purifier::encodeHtml($PICKLIST_NAME)}
				</option>
			{/foreach}
		</select>
	</div>
	<!-- /tpl-Base-Edit-Field-Taxes -->
{/strip}
