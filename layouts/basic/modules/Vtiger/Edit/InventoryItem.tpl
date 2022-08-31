{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-InventoryItem -->
	{if !empty($ITEM_DATA['name'])}
		{assign var="REFERENCE_MODULE" value=\App\Record::getType($ITEM_DATA['name'])}
	{else}
		{assign var="REFERENCE_MODULE" value=current($INVENTORY_MODEL->getField('name')->getModules())}
	{/if}
	<tr class="inventoryRow" numrow="{$ROW_NO}">
		<td class="u-white-space-nowrap u-w-1per-45px">
			{if $INVENTORY_MODEL->isField('seq')}
				<a class="dragHandle mx-1 mr-2">
					<img src="{\App\Layout::getImagePath('drag.png')}" border="0"
						alt="{\App\Language::translate('LBL_DRAG', $MODULE_NAME)}" />
				</a>
				<input name="inventory[{$ROW_NO}][seq]" type="hidden" value="{$ROW_NO}" class="sequence" />
			{/if}
			<button type="button" class="btn btn-sm btn-danger fas fa-trash-alt deleteRow"
				title="{\App\Language::translate('LBL_DELETE',$MODULE_NAME)}"></button>
			{if $IS_VISIBLE_COMMENTS}
				<button type="button" class="btn btn-sm btn-light toggleVisibility ml-1 js-toggle-icon__container" data-status="{$IS_OPENED_COMMENTS}"
					href="#" data-js="click">
					<span class="js-toggle-icon fas fa-angle-{if $IS_OPENED_COMMENTS}up{else}down{/if}" data-active="fa-angle-up" data-inactive="fa-angle-down" data-js="click"></span>
				</button>
			{/if}
			{if isset($ITEM_DATA['id'])}
				<input name="inventory[{$ROW_NO}][id]" type="hidden" value="{$ITEM_DATA['id']}" />
			{/if}
			{foreach item=FIELD from=$INVENTORY_MODEL->getFieldsToSync()}
				<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" value="" type="hidden" class="js-sync" data-sync-id="{$FIELD->getColumnName()}" data-js="container|data" />
				{foreach key=CUSTOM_FIELD_NAME item=item from=$FIELD->getCustomColumn()}
					<input name="inventory[{$ROW_NO}][{$CUSTOM_FIELD_NAME}]" value="" type="hidden" class="js-sync" data-sync-id="{$CUSTOM_FIELD_NAME}" data-js="container|data" />
				{/foreach}
			{/foreach}
		</td>
		{assign var=FIELDS value=$INVENTORY_MODEL->getFieldsByBlock(1)}
		{foreach item=FIELD from=$FIELDS}
			<td {if !$FIELD->isEditable()}colspan="0" {/if}
				class="col{$FIELD->getType()}{if !$FIELD->isEditable()} d-none{/if} text-right fieldValue">
				{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('EditView',$MODULE_NAME)}
				{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE_NAME)}
			</td>
		{/foreach}
	</tr>
	{if $IS_VISIBLE_COMMENTS}
		<tr class="inventoryRowExpanded numRow{$ROW_NO} {if !$IS_OPENED_COMMENTS}d-none{/if}" numrowex="{$ROW_NO}">
			<td class="colExpanded" colspan="{count($FIELDS)+1}">
				{foreach item=FIELD from=$INVENTORY_MODEL->getFieldsByType('Comment')}
					{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('EditView',$MODULE_NAME)}
					{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE_NAME)}
				{/foreach}
			</td>
		</tr>
	{/if}
	<!-- /tpl-Base-Edit-InventoryItem -->
{/strip}
