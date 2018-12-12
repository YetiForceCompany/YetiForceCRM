{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewDate -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<div class="tpl-Edit-Field-Date input-group date">
		<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" type="text" value="{$FIELD->getEditValue($VALUE)}"
			   class="form-control {$FIELD->getColumnName()} dateVal {if $FIELD->get('displaytype') != 10}dateFieldInv{/if}"
			   {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if} autocomplete="off"/>
		<div class=" input-group-append">
			<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
				<span class="fas fa-calendar-alt"></span>
			</span>
		</div>
	</div>
	<!-- /tpl-Base-inventoryfields-EditViewDate -->
{/strip}
