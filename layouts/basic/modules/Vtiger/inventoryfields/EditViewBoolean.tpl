{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewBoolean -->
	{assign var=VALUE value=$INVENTORY_MODEL->getEditValue($ITEM_DATA, $FIELD->getColumnName())}
	<input type="hidden" name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" value="0" />
	<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" class="form-control form-control-sm {$FIELD->getColumnName()} booleanVal" {' '}
		title="{\App\Language::translate($FIELD->getLabel(), $MODULE_NAME)}" type="checkbox" value="1" {' '}
		{if $FIELD->isReadOnly()}readonly="readonly" {/if}{if $VALUE} checked{/if} />
	<!-- /tpl-Base-inventoryfields-EditViewBoolean -->
{/strip}
