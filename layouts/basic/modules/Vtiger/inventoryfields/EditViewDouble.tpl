{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewDouble -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	{assign var="INPUT_TYPE" value='text'}
	{if $FIELD->get('displaytype') == 10}
		{assign var="INPUT_TYPE" value='hidden'}
		<span class="{$FIELD->getColumnName()}Text integerText">
			{$FIELD->getDisplayValue($VALUE)}
		</span>
	{/if}
	<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" type="{$INPUT_TYPE}" class="form-control {$FIELD->getColumnName()} integerVal" data-validation-engine="validate[funcCall[Vtiger_NumberUserFormat_Validator_Js.invokeValidation],maxSize[{$FIELD->getRangeValues()}]]" value="{$FIELD->getEditValue($VALUE)}" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if}/>
	<!-- /tpl-Base-inventoryfields-EditViewDouble -->
{/strip}
