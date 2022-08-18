{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewPurchase -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" value="{$FIELD->getEditValue($VALUE)}" type="text"
		data-validation-engine="validate[required,funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]"
		data-maximumlength="{$FIELD->getRangeValues()}"
		data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD->getFieldInfo()))}"
		class="purchase form-control form-control-sm" {if $FIELD->isReadOnly()}readonly="readonly" {/if} />
	<!-- /tpl-Base-inventoryfields-EditViewPurchase -->
{/strip}
