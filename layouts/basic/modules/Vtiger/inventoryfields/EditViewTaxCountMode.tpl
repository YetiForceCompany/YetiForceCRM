{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewTaxCountMode -->
	<select class="select2 js-taxcountmode" title="{\App\Language::translate('LBL_TAX_COUNT_MODE', $MODULE)}" {if $ROW_NO} name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" {/if}{if $FIELD->get('displaytype') == 10}readonly="readonly"{/if} data-js="change|val">
		<option value="brutto" {if $ITEM_VALUE == 'brutto'}selected{/if}>{\App\Language::translate('LBL_BRUTTO', $MODULE)}</option>
		<option value="netto" {if $ITEM_VALUE == 'netto'}selected{/if}>{\App\Language::translate('LBL_NETTO', $MODULE)}</option>
	</select>
	<!-- /tpl-Base-inventoryfields-EditViewTaxCountMode -->
{/strip}
