{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewDate -->
	{assign var=VALUE value=$INVENTORY_MODEL->getEditValue($ITEM_DATA, $FIELD->getColumnName())}
	<div class="input-group input-group-sm date">
		<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" type="text" value="{$FIELD->getDisplayValue($VALUE, $ITEM_DATA, true)|escape}"
			class="form-control {$FIELD->getColumnName()} dateVal {if !$FIELD->isReadOnly()}dateFieldInv{/if}"
			{if $FIELD->isReadOnly()}readonly="readonly" {/if} autocomplete="off" />
		<div class=" input-group-append">
			<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
				<span class="fas fa-calendar-alt"></span>
			</span>
		</div>
	</div>
	<!-- /tpl-Base-inventoryfields-EditViewDate -->
{/strip}
