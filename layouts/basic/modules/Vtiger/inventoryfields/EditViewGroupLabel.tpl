{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewGroupLabel -->
	{assign var=VALUE value=$INVENTORY_MODEL->getEditValue($ITEM_DATA, $FIELD->getColumnName())}
	{assign var="INPUT_TYPE" value='text'}
	{if $FIELD->isReadOnly()}
		{assign var="INPUT_TYPE" value='hidden'}
		<span class="{$FIELD->getColumnName()}Text valueText">
			{\App\Purifier::encodeHtml($FIELD->getDisplayValue($VALUE, $ITEM_DATA, true))}
		</span>
	{/if}
	{assign var=GROUP_ID value=1}
	{if isset($ITEM_DATA['groupid'])}
		{assign var=GROUP_ID value=$ITEM_DATA['groupid']}
	{/if}
	<input type="{$INPUT_TYPE}" class="form-control form-control-sm {$FIELD->getColumnName()} js-grouplabel" data-validation-engine="validate[required,maxSize[{$FIELD->getRangeValues()}]]" placeholder="{\App\Language::translate('LBL_INV_ENTER_BLOCK_NAME', $MODULE_NAME)}" value="{\App\Purifier::encodeHtml($FIELD->getDisplayValue($VALUE, $ITEM_DATA, true))}" {if $FIELD->isReadOnly()}readonly="readonly" {/if} />
	<input type="hidden" value="{$GROUP_ID|escape}" class="js-groupid" />
	<!-- /tpl-Base-inventoryfields-EditViewGroupLabel -->
{/strip}
