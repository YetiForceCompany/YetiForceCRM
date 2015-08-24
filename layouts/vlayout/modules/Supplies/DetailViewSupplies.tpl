{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="FIELDS" value=Supplies_SupField_Model::getFields($MODULE, true)}
	{if count($FIELDS) neq 0}
		{assign var="SUPMODULE" value='Supplies'}
		{assign var="COLUMNS" value=Supplies_SupField_Model::getColumns($MODULE)}
		{assign var="SUP_RECORD_DATA" value=$RECORD->getSupplieData()}
		{assign var="MAIN_PARAMS" value=Supplies_SupField_Model::getMainParams($FIELDS[1])}
		{assign var="COUNT_FIELDS0" value=count($FIELDS[0])}
		{assign var="COUNT_FIELDS1" value=count($FIELDS[1])}
		{assign var="COUNT_FIELDS2" value=count($FIELDS[2])}
		<table class="table table-bordered suppliesHeaderTable blockContainer">
			<thead>
				<tr>
					<th style="width: 40%;"></th>
						{foreach item=FIELD from=$FIELDS[0]}
						<th colspan="{$FIELD->get('colspan')}">
							<span class="inventoryLineItemHeader">{vtranslate($FIELD->get('label'), $SUPMODULE)}:</span>&nbsp;
							{assign var="FIELD_TPL_NAME" value="fields/"|cat:$FIELD->getTemplateName('DetailView',$MODULE)}
							{include file=$FIELD_TPL_NAME|@vtemplate_path:Supplies_Module_Model::getModuleNameForTpl($FIELD_TPL_NAME,$MODULE) SUP_VALUE=$SUP_RECORD_DATA[0][$FIELD->get('columnname')]}
						</th>
					{/foreach}
				</tr>
			</thead>
		</table>
		{assign var="FIELDS_TEXT_ALIGN_RIGHT" value=['TotalPrice','Tax','MarginP','Margin','Purchase','Discount','NetPrice','GrossPrice','UnitPrice','Quantity']}
		<table class="table blockContainer suppliesItemsTable">
			<thead>
				<tr>
					{foreach item=FIELD from=$FIELDS[1]}
						{if $FIELD->isVisible($SUP_RECORD_DATA)}
							<th colspan="{$FIELD->get('colspan')}" class="textAlignCenter">{vtranslate($FIELD->get('label'), $SUPMODULE)}</th>
							{/if}
						{/foreach}
				</tr>
			</thead>
			<tbody>
				{foreach key=KEY item=SUP_DATA from=$SUP_RECORD_DATA}
					{assign var="ROW_NO" value=$KEY+1}
					<tr>
						{foreach item=FIELD from=$FIELDS[1]}
							{if $FIELD->isVisible($SUP_RECORD_DATA)}
								<td {if in_array($FIELD->getName(), $FIELDS_TEXT_ALIGN_RIGHT)}class="textAlignRight"{/if}>
									{assign var="FIELD_TPL_NAME" value="fields/"|cat:$FIELD->getTemplateName('DetailView',$MODULE)}
									{include file=$FIELD_TPL_NAME|@vtemplate_path:Supplies_Module_Model::getModuleNameForTpl($FIELD_TPL_NAME,$MODULE) SUP_VALUE=$SUP_DATA[$FIELD->get('columnname')]}
								</td>
							{/if}
						{/foreach}
					</tr>
				{/foreach}
			</tbody>
			<tfoot>
				<tr>
					{foreach item=FIELD from=$FIELDS[1]}
						{if $FIELD->isVisible($SUP_RECORD_DATA)}
							<td colspan="{$FIELD->get('colspan')}" class="col{$FIELD->getName()} textAlignRight {if !$FIELD->isSummary()}hideTd{else}wisableTd{/if}" data-sumfield="{lcfirst($FIELD->get('suptype'))}">
								{if $FIELD->isSummary()}
									{assign var="SUM" value=0}
									{foreach key=KEY item=SUP_DATA from=$SUP_RECORD_DATA}
										{assign var="SUM" value=($SUM + $SUP_DATA[$FIELD->get('columnname')])}
									{/foreach}
									{CurrencyField::convertToUserFormat($SUM, null, true)}
								{/if}
							</td>
						{/if}
					{/foreach}
				</tr>
			</tfoot>
		</table>
	{/if}
{/strip}
