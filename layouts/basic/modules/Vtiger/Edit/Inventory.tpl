{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Inventory -->
	{assign var="INVENTORY_MODEL" value=Vtiger_Inventory_Model::getInstance($MODULE_NAME)}
	{if $INVENTORY_MODEL->isField('name')}
		{assign var="FIELDS" value=$INVENTORY_MODEL->getFieldsByBlocks()}
		{assign var="BASIC_FIELD" value=$INVENTORY_MODEL->getField('name')}
		{assign var="DISCOUNTS_CONFIG" value=Vtiger_Inventory_Model::getDiscountsConfig()}
		{assign var="TAXS_CONFIG" value=Vtiger_Inventory_Model::getTaxesConfig()}
		{assign var="TAX_DEFAULT" value=Vtiger_Inventory_Model::getDefaultGlobalTax()}
		{assign var="BASE_CURRENCY" value=Vtiger_Util_Helper::getBaseCurrency()}

		{assign var="INVENTORY_ROWS" value=$RECORD->getInventoryData()}
		{assign var="DEFAULT_INVENTORY_ROW" value=\App\Config::module($MODULE_NAME, 'defaultInventoryData', [])}
		{if $INVENTORY_ROWS}
			{assign var="INVENTORY_ROW" value=current($INVENTORY_ROWS)}
		{else}
			{assign var="INVENTORY_ROW" value=[]}
		{/if}
		{assign var="MAIN_PARAMS" value=$BASIC_FIELD->getParamsConfig()}
		{assign var="IS_REQUIRED_INVENTORY" value=$BASIC_FIELD->isRequired()}
		{assign var="COUNT_FIELDS1" value=count($FIELDS[1])}
		{assign var="COUNT_FIELDS2" value=0}
		{assign var="REFERENCE_MODULE_DEFAULT" value=''}
		{assign var="IS_VISIBLE_COMMENTS" value=false}
		{assign var="IS_OPENED_COMMENTS" value=false}
		{if isset($FIELDS[2])}
			{assign var="COUNT_FIELDS2" value=count($FIELDS[2])}
			{foreach item=FIELD from=$FIELDS[2]}
				{if $FIELD->getColumnName() eq 'comment1'}
					{if $FIELD->isVisible()}
						{assign var="IS_VISIBLE_COMMENTS" value=true}
					{/if}
					{if $FIELD->isOpened()}
						{assign var="IS_OPENED_COMMENTS" value=true}
					{/if}
					{break}
				{/if}
			{/foreach}
		{/if}
		{if $INVENTORY_MODEL->isField('currency')}
			{if $INVENTORY_ROW && !empty($INVENTORY_ROW['currency'])}
				{assign var="CURRENCY" value=$INVENTORY_ROW['currency']}
			{else}
				{assign var="CURRENCY" value=$BASE_CURRENCY['id']}
			{/if}
			{assign var="CURRENCY_SYMBOLAND" value=\App\Fields\Currency::getById($CURRENCY)}
		{/if}
		{assign var="INVENTORY_ITEMS_NO" value=count($INVENTORY_ROWS)}
		{assign var="RELATED_FIELD" value=\App\Field::getRelatedFieldForModule($MODULE_NAME, 'Accounts')}
		<input type="hidden" class="js-discount-config" value="{App\Purifier::encodeHtml(\App\Json::encode($DISCOUNTS_CONFIG))}">
		<input type="hidden" class="js-tax-config" value="{App\Purifier::encodeHtml(\App\Json::encode($TAXS_CONFIG))}">
		<input type="hidden" id="inventoryItemsNo" value="{if $INVENTORY_ITEMS_NO}{$INVENTORY_ITEMS_NO}{else}1{/if}" />
		<input type="hidden" id="accountReferenceField" value="{if $RELATED_FIELD}{$RELATED_FIELD['fieldname']}{/if}" />
		<input type="hidden" id="inventoryLimit" value="{$MAIN_PARAMS['limit']}" />
		<input type="hidden" id="isRequiredInventory" value="{$IS_REQUIRED_INVENTORY}" />
		<div class="table-responsive">
			<table class="table inventoryHeader blockContainer mb-0 table-bordered">
				<colgroup>
					<col class="w-25">
					{foreach item=FIELD from=$FIELDS[1]}
						<col class="w-25">
					{/foreach}
				</colgroup>
				<thead>
					<tr data-rownumber="0" class="u-min-w-650pxr">
						<th class="border-bottom-0">
							<span class="inventoryLineItemHeader">{\App\Language::translate('LBL_ADD', $MODULE)}</span>&nbsp;&nbsp;
							<div class="d-flex">
								{foreach item=MAIN_MODULE from=$MAIN_PARAMS['modules'] name=moduleList}
									{if \App\Module::isModuleActive($MAIN_MODULE)}
										{if $smarty.foreach.moduleList.first}
											{assign var=REFERENCE_MODULE_DEFAULT value=$MAIN_MODULE}
										{/if}
										<div class="btn-group btn-group-sm d-flex align-items-center justify-content-center {if !$smarty.foreach.moduleList.first}ml-lg-1{/if}" role="group">
											<button type="button" data-module="{$MAIN_MODULE}"
												title="{\App\Language::translate('LBL_ADD',$MODULE_NAME)} {\App\Language::translate('SINGLE_'|cat:$MAIN_MODULE,$MAIN_MODULE)}"
												class="btn btn-light js-inv-add-item border mb-1 mb-lg-0 text-nowrap"
												data-js="click">
												<span class="moduleIcon yfm-{$MAIN_MODULE} mr-1"></span><strong>{\App\Language::translate('SINGLE_'|cat:$MAIN_MODULE,$MAIN_MODULE)}</strong>
											</button>
											{assign var=MASS_ADD_URL value=$BASIC_FIELD->getUrlForMassSelection($MAIN_MODULE)}
											{if $MASS_ADD_URL}
												<button type="button" data-module="{$MAIN_MODULE}" data-url="{$MASS_ADD_URL}"
													title="{\App\Language::translate($MAIN_MODULE, $MAIN_MODULE)}"
													data-content="{\App\Language::translate('LBL_MASS_ADD_ENTIRIES', $MODULE_NAME)}"
													class="btn btn-light js-mass-add border mb-1 mb-lg-0 mr-2 u-cursor-pointer js-popover-tooltip" data-js="popover"
													data-js="click">
													<span class="fas fa-search-plus"></span>
												</button>
											{/if}
										</div>
									{/if}
								{/foreach}
							</div>
						</th>
						{assign var="ROW_NO" value=0}
						{if isset($FIELDS[0])}
							{foreach item=FIELD from=$FIELDS[0]}
								<th class="{if !$FIELD->isEditable()} d-none {/if} border-bottom-0">
									<span class="inventoryLineItemHeader">{\App\Language::translate($FIELD->get('label'), $FIELD->getModuleName())}</span>&nbsp;&nbsp;
									{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('EditView',$MODULE_NAME)}
									{assign var="COLUMN_NAME" value=$FIELD->get('columnName')}
									{if isset($INVENTORY_ROW[$COLUMN_NAME])}
										{assign var="ITEM_VALUE" value=$INVENTORY_ROW[$COLUMN_NAME]}
									{elseif isset($DEFAULT_INVENTORY_ROW[$COLUMN_NAME])}
										{assign var="ITEM_VALUE" value=$DEFAULT_INVENTORY_ROW[$COLUMN_NAME]}
									{else}
										{assign var="ITEM_VALUE" value=NULL}
									{/if}
									<div class="input-group-sm">
										{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE_NAME) ITEM_DATA=$INVENTORY_ROW}
									</div>
								</th>
							{/foreach}
						{/if}
					</tr>
				</thead>
			</table>
		</div>
		<div class="table-responsive">
			<table class="table table-bordered inventoryItems mb-2">
				{if count($FIELDS[1]) neq 0}
					<thead>
						<tr>
							<th class="text-center u-w-1per-45px"></th>
							{foreach item=FIELD from=$FIELDS[1]}
								<th {if !$FIELD->isEditable()}colspan="0" {/if}
									class="col{$FIELD->getType()}{if !$FIELD->isEditable()} d-none{/if} u-table-column__before-block u-table-column__before-block--inventory{if $FIELD->get('colSpan') neq 0 } u-table-column__vw-{$FIELD->get('colSpan')}{/if} text-center text-nowrap">
									{\App\Language::translate($FIELD->get('label'), $FIELD->getModuleName())}
								</th>
							{/foreach}
						</tr>
					</thead>
				{/if}
				<tbody class="js-inventory-items-body" data-js="container">
					{assign var=ROW_NO value=0}
					{foreach key=KEY item=ITEM_DATA from=$INVENTORY_ROWS}
						{assign var=ROW_NO value=$ROW_NO+1}
						{include file=\App\Layout::getTemplatePath('Edit/InventoryItem.tpl', $MODULE_NAME)}
					{foreachelse}
						{if $IS_REQUIRED_INVENTORY}
							{assign var="ROW_NO" value=1}
							{assign var="ITEM_DATA" value=$RECORD->getInventoryDefaultDataFields()}
							{include file=\App\Layout::getTemplatePath('Edit/InventoryItem.tpl', $MODULE_NAME)}
						{/if}
					{/foreach}
				</tbody>
				<tfoot>
					<tr>
						<td colspan="1" class="hideTd u-w-1per-45px">&nbsp;&nbsp;</td>
						{foreach item=FIELD from=$FIELDS[1]}
							<td {if !$FIELD->isEditable()}colspan="0" {/if}
								class="col{$FIELD->getType()}{if !$FIELD->isEditable()} d-none{/if} text-right text-nowrap
								{if !$FIELD->isSummary()} hideTd{else} wisableTd{/if}"
								data-sumfield="{lcfirst($FIELD->getType())}">
								{if $FIELD->isSummary()}
									{assign var="SUM" value=0}
									{foreach key=KEY item=ITEM_VALUE from=$INVENTORY_ROWS}
										{if isset($ITEM_VALUE[$FIELD->get('columnName')])}
											{assign var="SUM" value=($SUM + $ITEM_VALUE[$FIELD->get('columnName')])}
										{/if}
									{/foreach}
									{CurrencyField::convertToUserFormat($SUM, null, true)}
								{/if}
								{if $FIELD->getType() == 'Name' && $INVENTORY_MODEL->isField('price')}
									{\App\Language::translate('LBL_SUMMARY', $MODULE_NAME)}
								{/if}
							</td>
						{/foreach}
					</tr>
				</tfoot>
			</table>
		</div>
		{include file=\App\Layout::getTemplatePath('Edit/InventorySummary.tpl', $MODULE_NAME)}
		{assign var="ITEM_DATA" value=$RECORD->getInventoryDefaultDataFields()}
		<table id="blackIthemTable" class="noValidate d-none">
			{assign var="INVENTORY_LBLS" value=[]}
			{foreach item=MAIN_MODULE from=$MAIN_PARAMS['modules']}
				{$INVENTORY_LBLS[$MAIN_MODULE]=\App\Language::translateSingularModuleName($MAIN_MODULE)}
			{/foreach}
			<tbody class="js-inventory-base-item" data-module-lbls="{App\Purifier::encodeHtml(\App\Json::encode($INVENTORY_LBLS))}">
				{assign var="ROW_NO" value='_NUM_'}
				{include file=\App\Layout::getTemplatePath('Edit/InventoryItem.tpl', $MODULE_NAME)}
			</tbody>
		</table>
	{/if}
	<!-- /tpl-Base-Edit-Inventory -->
{/strip}
