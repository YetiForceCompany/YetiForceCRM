{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="INVENTORY_FIELD" value=Vtiger_InventoryField_Model::getInstance($MODULE)}
	{assign var="FIELDS" value=$INVENTORY_FIELD->getFields(true)}
	{if count($FIELDS) neq 0}
		{assign var="DISCOUNTS_CONFIG" value=Vtiger_Inventory_Model::getDiscountsConfig()}
		{assign var="TAXS_CONFIG" value=Vtiger_Inventory_Model::getTaxesConfig()}
		{assign var="TAX_DEFAULT" value=Vtiger_Inventory_Model::getDefaultGlobalTax()}
		{assign var="BASE_CURRENCY" value=Vtiger_Util_Helper::getBaseCurrency()}

		{assign var="COLUMNS" value=$INVENTORY_FIELD->getColumns()}
		{assign var="INVENTORY_ROWS" value=$RECORD->getInventoryData()}
		{assign var="MAIN_PARAMS" value=$INVENTORY_FIELD->getMainParams($FIELDS[1])}
		{assign var="COUNT_FIELDS0" value=count($FIELDS[0])}
		{assign var="COUNT_FIELDS1" value=count($FIELDS[1])}
		{assign var="COUNT_FIELDS2" value=count($FIELDS[2])}
		{assign var="IS_OPTIONAL_ITEMS" value=AppConfig::module($MODULE, 'INVENTORY_IS_OPTIONAL')}
		{if in_array("currency",$COLUMNS)}
			{if count($INVENTORY_ROWS) > 0}
				{assign var="CURRENCY" value=$INVENTORY_ROWS[0]['currency']}
			{else}
				{assign var="CURRENCY" value=$BASE_CURRENCY['id']}
			{/if}
			{assign var="CURRENCY_SYMBOLAND" value=vtlib\Functions::getCurrencySymbolandRate($CURRENCY)}
		{/if}
		{assign var="INVENTORY_ITEMS_NO" value=count($INVENTORY_ROWS)}
		<input type="hidden" class="aggregationTypeDiscount" value="{$DISCOUNTS_CONFIG['aggregation']}">
		<input type="hidden" class="aggregationTypeTax" value="{$TAXS_CONFIG['aggregation']}">
		<input name="inventoryItemsNo" id="inventoryItemsNo" type="hidden"
			   value="{if $INVENTORY_ITEMS_NO}{$INVENTORY_ITEMS_NO}{else}1{/if}"/>
		<input id="accountReferenceField" type="hidden" value="{$INVENTORY_FIELD->getReferenceField()}"/>
		<input id="inventoryLimit" type="hidden" value="{$MAIN_PARAMS['limit']}"/>
		<div class="table-responsive">
			<table class="table table-bordered inventoryHeader blockContainer">
				<thead>
				<tr data-rownumber="0" class="d-flex u-min-w-650px">
					<th class="btn-toolbar col-3 d-flex justify-content-center mb-0">
						{foreach item=MAIN_MODULE from=$MAIN_PARAMS['modules']}
							{if \App\Module::isModuleActive($MAIN_MODULE)}
								{assign var="CRMENTITY" value=CRMEntity::getInstance($MAIN_MODULE)}
								<span class="btn-group-sm d-flex align-items-center justify-content-center ml-lg-2">
										<button type="button" data-module="{$MAIN_MODULE}"
												data-field="{$CRMENTITY->table_index}"
												data-wysiwyg="{$INVENTORY_FIELD->isWysiwygType($MAIN_MODULE)}"
												class="btn btn-light addItem border mb-1 mb-lg-0">
											<span class="fas fa-plus"></span>&nbsp;<strong>{\App\Language::translate('LBL_ADD',$MODULE)} {\App\Language::translate('SINGLE_'|cat:$MAIN_MODULE,$MAIN_MODULE)}</strong>
										</button>
									</span>
							{/if}
						{/foreach}
					</th>
					{foreach item=FIELD from=$FIELDS[0]}
						<th {if !$FIELD->isEditable()}class="d-none"{/if} class="col-3" >
							<span class="inventoryLineItemHeader">{\App\Language::translate($FIELD->get('label'), $MODULE)}</span>&nbsp;&nbsp;
							{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('EditView',$MODULE)}
							{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE) ITEM_VALUE=$INVENTORY_ROWS[0][$FIELD->get('columnname')]}
						</th>
					{/foreach}
				</tr>
				</thead>
			</table>
		</div>
		<div class="table-responsive">
			<table class="table blockContainer inventoryItems" data-isoptional="{$IS_OPTIONAL_ITEMS}">
				{if count($FIELDS[1]) neq 0}
					<thead>
					<tr>
						<th class="text-center u-w-1per-45px"></th>
						{foreach item=FIELD from=$FIELDS[1]}
							<th  class="col{$FIELD->getName()} {if !$FIELD->isEditable()} d-none{/if} text-center text-nowrap u-w-3per-150px">
								{\App\Language::translate($FIELD->get('label'), $MODULE)}
							</th>
						{/foreach}
					</tr>
					</thead>
				{/if}
				<tbody>
				{foreach key=KEY item=ITEM_DATA from=$INVENTORY_ROWS}
					{assign var="ROW_NO" value=$KEY+1}
					{include file=\App\Layout::getTemplatePath('EditViewInventoryItem.tpl', $MODULE)}
					{foreachelse}
					{if !$IS_OPTIONAL_ITEMS}
						{assign var="ROW_NO" value=1}
						{include file=\App\Layout::getTemplatePath('EditViewInventoryItem.tpl', $MODULE)}
					{/if}
				{/foreach}
				</tbody>
				<tfoot>
				<tr>
					<td colspan="1" class="hideTd">&nbsp;&nbsp;</td>
					{foreach item=FIELD from=$FIELDS[1]}
						<td colspan="1" class="col{$FIELD->getName()}{if !$FIELD->isEditable()} d-none{/if} text-right
								{if !$FIELD->isSummary()} hideTd{else} wisableTd{/if}"
							data-sumfield="{lcfirst($FIELD->get('invtype'))}">
							{if $FIELD->isSummary()}
								{assign var="SUM" value=0}
								{foreach key=KEY item=ITEM_VALUE from=$INVENTORY_ROWS}
									{assign var="SUM" value=($SUM + $ITEM_VALUE[$FIELD->get('columnname')])}
								{/foreach}
								{CurrencyField::convertToUserFormat($SUM, null, true)}
							{/if}
							{if $FIELD->getName() == 'Name' && in_array("price",$COLUMNS)}
								{\App\Language::translate('LBL_SUMMARY', $MODULE)}
							{/if}
						</td>
					{/foreach}
				</tr>
				</tfoot>
			</table>
		</div>
		{include file=\App\Layout::getTemplatePath('EditViewInventorySummary.tpl', $MODULE)}
		{assign var="ITEM_DATA" value=$RECORD->getInventoryDefaultDataFields()}
		<table id="blackIthemTable" class="noValidate d-none">
			<tbody>
			{assign var="ROW_NO" value='_NUM_'}
			{include file=\App\Layout::getTemplatePath('EditViewInventoryItem.tpl', $MODULE)}
			</tbody>
		</table>
	{/if}
{/strip}
