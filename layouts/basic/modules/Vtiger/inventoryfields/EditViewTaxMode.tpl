{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewTaxMode -->
	{if $ITEM_VALUE === null}
		{assign var=ITEM_VALUE value=$TAXS_CONFIG['default_mode']}
	{/if}
	<select class="select2 js-taxmode" title="{\App\Language::translate('LBL_TAX_MODE', $MODULE)}" {if $ROW_NO} name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" {/if}{if $FIELD->isReadOnly()}readonly="readonly" {/if} data-js="change|val">
		<option value="0" {if $ITEM_VALUE == '0'}selected{/if}>{\App\Language::translate('LBL_GROUP', $MODULE)}</option>
		<option value="1" {if $ITEM_VALUE == '1'}selected{/if}>{\App\Language::translate('LBL_INDIVIDUAL', $MODULE)}</option>
	</select>
	<!-- /tpl-Base-inventoryfields-EditViewTaxMode -->
{/strip}
