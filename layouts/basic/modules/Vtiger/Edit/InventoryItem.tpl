{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-InventoryItem -->
	{if !empty($ITEM_DATA['name'])}
		{assign var="REFERENCE_MODULE" value=\App\Record::getType($ITEM_DATA['name'])}
	{elseif $MAIN_PARAMS}
		{assign var="REFERENCE_MODULE" value=$REFERENCE_MODULE_DEFAULT}
	{/if}
	<tr class="inventoryRow" numrow="{$ROW_NO}">
		<td class="u-white-space-nowrap u-w-1per-45px">
			{if $INVENTORY_MODEL->isField('seq')}
				<a class="dragHandle mx-1">
					<img src="{\App\Layout::getImagePath('drag.png')}" border="0"
						alt="{\App\Language::translate('LBL_DRAG', $MODULE_NAME)}" />
				</a>
				<input name="inventory[{$ROW_NO}][seq]" type="hidden" value="{$ROW_NO}" class="sequence" />
			{/if}
			<button type="button" class="btn btn-sm btn-danger fas fa-trash-alt deleteRow"
				title="{\App\Language::translate('LBL_DELETE',$MODULE_NAME)}"></button>
			{if $COUNT_FIELDS2 > 0 && $IS_VISIBLE_COMMENTS}
				<button type="button" class="btn btn-sm btn-light toggleVisibility ml-1 js-toggle-icon__container" data-status="{$IS_OPENED_COMMENTS}"
					href="#" data-js="click">
					<span class="js-toggle-icon fas fa-angle-{if $IS_OPENED_COMMENTS}up{else}down{/if}" data-active="fa-angle-up" data-inactive="fa-angle-down" data-js="click"></span>
				</button>
			{/if}
			{if isset($ITEM_DATA['id'])}
				<input name="inventory[{$ROW_NO}][id]" type="hidden" value="{$ITEM_DATA['id']}" />
			{/if}
			{if isset($FIELDS[0])}
				{foreach item=FIELD from=$FIELDS[0]}
					<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" value="" type="hidden" class="js-sync" data-sync-id="{$FIELD->getColumnName()}" data-js="container|data" />
					{foreach key=CUSTOM_FIELD_NAME item from=$FIELD->getCustomColumn()}
						<input name="inventory[{$ROW_NO}][{$CUSTOM_FIELD_NAME}]" value="" type="hidden" class="js-sync" data-sync-id="{$CUSTOM_FIELD_NAME}" data-js="container|data" />
					{/foreach}
				{/foreach}
			{/if}
		</td>
		{foreach item=FIELD from=$FIELDS[1]}
			<td {if !$FIELD->isEditable()}colspan="0" {/if}
				class="col{$FIELD->getType()}{if !$FIELD->isEditable()} d-none{/if} text-right fieldValue">
				{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('EditView',$MODULE)}
				{assign var="COLUMN_NAME" value=$FIELD->get('columnName')}
				{if isset($ITEM_DATA[$COLUMN_NAME])}
					{assign var="ITEM_VALUE" value=$ITEM_DATA[$COLUMN_NAME]}
				{elseif isset($DEFAULT_INVENTORY_ROW[$COLUMN_NAME])}
					{assign var="ITEM_VALUE" value=$DEFAULT_INVENTORY_ROW[$COLUMN_NAME]}
				{else}
					{assign var="ITEM_VALUE" value=NULL}
				{/if}
				{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE)}
			</td>
		{/foreach}
	</tr>
	{if $IS_VISIBLE_COMMENTS}
		<tr class="inventoryRowExpanded numRow{$ROW_NO} {if !$IS_OPENED_COMMENTS}d-none{/if}" numrowex="{$ROW_NO}">
			<td class="colExpanded" colspan="{$COUNT_FIELDS1+1}">
				{foreach item=FIELD from=$FIELDS[2]}
					{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('EditView',$MODULE)}
					{assign var="COLUMN_NAME" value=$FIELD->get('columnName')}
					{if isset($ITEM_DATA[$COLUMN_NAME])}
						{assign var="ITEM_VALUE" value=$ITEM_DATA[$COLUMN_NAME]}
					{elseif isset($DEFAULT_INVENTORY_ROW[$COLUMN_NAME])}
						{assign var="ITEM_VALUE" value=$DEFAULT_INVENTORY_ROW[$COLUMN_NAME]}
					{else}
						{assign var="ITEM_VALUE" value=NULL}
					{/if}
					{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE)}
				{/foreach}
			</td>
		</tr>
	{/if}
	<!-- /tpl-Base-Edit-InventoryItem -->
{/strip}
