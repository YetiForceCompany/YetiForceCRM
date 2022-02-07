{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewBoolean -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	{assign var='LABEL' value=$FIELD->getDefaultLabel()}
	{if $FIELD->get('label') }
		{assign var='LABEL' value=$FIELD->get('label')}
	{/if}
	<input type="hidden" name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" value="0" />
	<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" class="form-control form-control-sm {$FIELD->getColumnName()} booleanVal" {' '}
		title="{\App\Language::translate($LABEL, $MODULE)}" type="checkbox" value="1" {' '}
		{if $FIELD->isReadOnly()}readonly="readonly" {/if} {if $FIELD->getEditValue($VALUE)}checked{/if} />
	<!-- /tpl-Base-inventoryfields-EditViewBoolean -->
{/strip}
