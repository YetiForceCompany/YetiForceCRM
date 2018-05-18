{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if !empty($ITEM_DATA['name'])}
		{assign var="REFERENCE_MODULE" value=\App\Record::getType($ITEM_DATA['name'])}
	{elseif $MAIN_PARAMS}
		{assign var="REFERENCE_MODULE" value=$INVENTORY_FIELD->getDefaultModule($MAIN_PARAMS)}
	{/if}
	{if $REFERENCE_MODULE}
		<tr class="inventoryRow" numrow="{$ROW_NO}">
			<td>
				<span class="fas fa-trash-alt deleteRow u-cursor-pointer {if !$IS_OPTIONAL_ITEMS && $KEY == 0 }d-none{/if}" title="{\App\Language::translate('LBL_DELETE',$MODULE)}"></span>
				&nbsp;&nbsp;<a class="dragHandle"><img src="{\App\Layout::getImagePath('drag.png')}" border="0" alt="{\App\Language::translate('LBL_DRAG',$MODULE)}" /></a>
				<input name="seq{$ROW_NO}" type="hidden" value="{$ROW_NO}" class="sequence" />
				{if $COUNT_FIELDS2 > 0}
					<br /><br />
					<span class="btn btn-light btn-sm toggleVisibility" data-status="0" href="#">
						<span class="fas fa-angle-down"></span>	
					</span>
				{/if}
			</td>
			{foreach item=FIELD from=$FIELDS[1]}
				<td class="col{$FIELD->getName()}{if !$FIELD->isEditable()} d-none{/if} text-right fieldValue">
					{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('EditView',$MODULE)}
					{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE) ITEM_VALUE=$ITEM_DATA[$FIELD->get('columnname')]}
				</td>
			{/foreach}
		</tr>
		{if $FIELDS[2] neq 0}
			<tr class="inventoryRowExpanded numRow{$ROW_NO} d-none" numrowex="{$ROW_NO}">
				<td class="colExpanded" colspan="{$COUNT_FIELDS1+1}">
					{foreach item=FIELD from=$FIELDS[2]}
						{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('EditView',$MODULE)}
						{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE) ITEM_VALUE=$ITEM_DATA[$FIELD->get('columnname')]}
					{/foreach}
				</td>
			</tr>
		{/if}
	{/if}
{/strip}
