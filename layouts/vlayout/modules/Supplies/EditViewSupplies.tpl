{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="SUPMODULE" value='Supplies'}
	{assign var="FIELDS" value=$SUPFIELD_MODEL->getFields($MODULE, true)}
	{if count($FIELDS) neq 0}
		{assign var="COLUMNS" value=$SUPFIELD_MODEL->getColumns($MODULE)}
		{assign var="SUP_RECORD_DATA" value=$RECORD->getSupplieData()}
		{assign var="MAIN_PARAMS" value=$SUPFIELD_MODEL->getMainParams($FIELDS[1])}
		{assign var="COUNT_FIELDS0" value=count($FIELDS[0])}
		{assign var="COUNT_FIELDS1" value=count($FIELDS[1])}
		{assign var="COUNT_FIELDS2" value=count($FIELDS[2])}
		{if in_array("currency",$COLUMNS)}
			{if count($SUP_RECORD_DATA) > 0}
				{assign var="CURRENCY" value=$SUP_RECORD_DATA[0]['currency']}
			{else}
				{assign var="CURRENCY" value=$SUP_RECORD_DATA[0]['currency']}
			{/if}
			{assign var="CURRENCY_SYMBOLAND" value=Vtiger_Functions::getCurrencySymbolandRate($CURRENCY)}
		{/if}
		<input name="suppliesRowNo" id="suppliesRowNo" type="hidden" value="{count($SUP_RECORD_DATA)}" />
		<input id="accountReferenceField" type="hidden" value="{$ACCOUNT_REFERENCE_FIELD}" />
		<input id="suppliesLimit" type="hidden" value="{$MAIN_PARAMS['limit']}" />
		<table class="table table-bordered suppliesHeaderTable blockContainer">
			<thead>
				<tr data-rownumber="0">
					<th class="btn-toolbar">
						{foreach item=MAIN_MODULE from=$MAIN_PARAMS['modules']}
							{assign var="CRMEntity" value=CRMEntity::getInstance($MAIN_MODULE)}
							<span class="btn-group">
								<button type="button" data-module="{$MAIN_MODULE}" data-field="{$CRMEntity->table_index}" 
										data-wysiwyg="{Supplies_EditView_Model::isWysiwygType($MAIN_MODULE)}" class="btn btn-default addButton">
									<span class="glyphicon glyphicon-plus"></span>&nbsp;<strong>{vtranslate('LBL_ADD',$SUPMODULE)} {vtranslate('SINGLE_'|cat:$MAIN_MODULE,$MAIN_MODULE)}</strong>
								</button>
							</span>
						{/foreach}
					</th>
					{foreach item=FIELD from=$FIELDS[0]}
						<th colspan="{$FIELD->get('colspan')}">
							<span class="inventoryLineItemHeader">{vtranslate($FIELD->get('label'), $SUPMODULE)}</span>&nbsp;&nbsp;
							{assign var="FIELD_TPL_NAME" value="fields/"|cat:$FIELD->getTemplateName('EditView',$MODULE)}
							{include file=$FIELD_TPL_NAME|@vtemplate_path:Supplies_Module_Model::getModuleNameForTpl($FIELD_TPL_NAME,$MODULE) SUP_VALUE=$SUP_RECORD_DATA[0][$FIELD->get('columnname')]}
						</th>
					{/foreach}
				</tr>
			</thead>
		</table>
		<table class="table blockContainer suppliesItemsTable">
			<thead>
				<tr>
					<th style="min-width: 50px">&nbsp;&nbsp;</th>
						{foreach item=FIELD from=$FIELDS[1]}
						<th colspan="{$FIELD->get('colspan')}" class="col{$FIELD->getName()}{if !$FIELD->isVisible($SUP_RECORD_DATA)} hide{/if} textAlignCenter">{vtranslate($FIELD->get('label'), $SUPMODULE)}</th>
						{/foreach}
				</tr>
			</thead>
			<tbody>
				{foreach key=KEY item=SUP_DATA from=$SUP_RECORD_DATA}
					{assign var="ROW_NO" value=$KEY+1}
					{include file='EditViewSuppliesRow.tpl'|@vtemplate_path:Supplies_Module_Model::getModuleNameForTpl('EditViewSuppliesRow.tpl',$MODULE)}
				{/foreach}
			</tbody>
			<tfoot>
				<tr>
					<td class="hideTd" style="min-width: 50px">&nbsp;&nbsp;</td>
					{foreach item=FIELD from=$FIELDS[1]}
						<td colspan="{$FIELD->get('colspan')}" class="col{$FIELD->getName()}{if !$FIELD->isVisible($SUP_RECORD_DATA)} hide{/if} textAlignRight {if !$FIELD->isSummary()}hideTd{else}wisableTd{/if}" data-sumfield="{lcfirst($FIELD->get('suptype'))}">
							{if $FIELD->isSummary()}
								{assign var="SUM" value=0}
								{foreach key=KEY item=SUP_DATA from=$SUP_RECORD_DATA}
									{assign var="SUM" value=($SUM + $SUP_DATA[$FIELD->get('columnname')])}
								{/foreach}
								{CurrencyField::convertToUserFormat($SUM, null, true)}
							{/if}
							{if $FIELD->getName() == 'Name'}
								{vtranslate('LBL_SUMMARY', $SUPMODULE)}
							{/if}
						</td>
					{/foreach}
				</tr>
			</tfoot>
		</table>
		{include file='EditViewSuppliesSummary.tpl'|@vtemplate_path:Supplies_Module_Model::getModuleNameForTpl('EditViewSuppliesRow.tpl',$MODULE)}
		{assign var="SUP_DATA" value=[]}
		<table id="blackSuppliesTable" class="noValidate" style="display: none;">
			<tbody>
				{assign var="ROW_NO" value='_NUM_'}
				{include file='EditViewSuppliesRow.tpl'|@vtemplate_path:Supplies_Module_Model::getModuleNameForTpl('EditViewSuppliesRow.tpl',$MODULE)}
			</tbody>
		</table>
		<br/>
	{/if}
{/strip}
