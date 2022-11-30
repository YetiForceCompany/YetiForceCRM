{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewTaxMode -->
	{assign var=VALUE value=$INVENTORY_MODEL->getEditValue($ITEM_DATA, $FIELD->getColumnName())}
	<select class="select2 js-taxmode" title="{\App\Language::translate('LBL_TAX_MODE', $MODULE)}" {if $ROW_NO} name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" {/if}{if $FIELD->isReadOnly()}readonly="readonly" {/if} data-js="change|val">
		{foreach from=$FIELD->getModes() item=LABEL key=KEY}
			<option value="{$KEY}" {if $VALUE == $KEY}selected{/if}>{\App\Language::translate($LABEL, $MODULE)}</option>
		{/foreach}
	</select>
	<!-- /tpl-Base-inventoryfields-EditViewTaxMode -->
{/strip}
