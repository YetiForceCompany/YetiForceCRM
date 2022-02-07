{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewDouble -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	{assign var="INPUT_TYPE" value='text'}
	{if $FIELD->isReadOnly()}
		{assign var="INPUT_TYPE" value='hidden'}
		<span class="{$FIELD->getColumnName()}Text integerText">
			{$FIELD->getDisplayValue($VALUE)}
		</span>
	{/if}
	<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" type="{$INPUT_TYPE}" class="form-control form-control-sm {$FIELD->getColumnName()} integerVal" data-validation-engine="validate[funcCall[Vtiger_Double_Validator_Js.invokeValidation],maxSize[{$FIELD->getRangeValues()}]]" value="{$FIELD->getEditValue($VALUE)}" {if $FIELD->isReadOnly()}readonly="readonly" {/if} />
	<!-- /tpl-Base-inventoryfields-EditViewDouble -->
{/strip}
