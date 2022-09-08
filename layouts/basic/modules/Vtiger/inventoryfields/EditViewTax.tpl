{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewTax -->
	{assign var=VALUE value=$INVENTORY_MODEL->getEditValue($ITEM_DATA, $FIELD->getColumnName())}
	<div class="input-group input-group-sm">
		<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" value="{$VALUE|escape}" type="text"
			class="tax form-control form-control-sm js-tax" readonly="readonly" data-js="data-default-tax|value" />
		{assign var=TAXPARAM_VALUE value=$INVENTORY_MODEL->getEditValue($ITEM_DATA, 'taxparam')}
		{assign var=TAX_MODE value=$INVENTORY_MODEL->getEditValue($ITEM_DATA, 'taxmode', Vtiger_Inventory_Model::getTaxesConfig('default_mode'))}
		<input name="inventory[{$ROW_NO}][taxparam]" type="hidden" value="{\App\Purifier::encodeHtml($TAXPARAM_VALUE)}"
			class="taxParam" {if $FIELD->isReadOnly()}readonly="readonly" {/if} />
		<span class="input-group-append u-cursor-pointer changeTax {if $TAX_MODE == 0}d-none{/if}">
			<div class="input-group-text">
				<span class="small">
					<span class="fas fa-long-arrow-alt-up"></span>
					<span class="fas fa-percent"></span>
				</span>
			</div>
		</span>
	</div>
	<!-- /tpl-Base-inventoryfields-EditViewTax -->
{/strip}
