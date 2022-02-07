{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-InventoryTaxesType1 -->
	{if $TAX_TYPE == '0' && $TAX_FIELD && $RECORD}
		{assign var=RECORD_MODEL value=Vtiger_Record_Model::getInstanceById($RECORD)}
		{assign var=SELECTED_TAXES value=Vtiger_Taxes_UIType::getValues($RECORD_MODEL->get($TAX_FIELD))}
		{if count($SELECTED_TAXES) > 0}
			<div class="card js-panel  mb-2" data-js="class: js-active">
				<div class="card-header py-1">
					<strong>{\App\Language::translate('LBL_GROUP_TAXS', $MODULE_NAME)}</strong>
					<div class="float-right">
						<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="group" class="activeCheckbox">
					</div>
				</div>
				<div class="card-body js-panel__body d-none" data-js="class: d-none">
					<div>
						<p>
							{\App\Language::translate('LBL_TAX_FOR_MODULE', $MODULE_NAME)} {\App\Language::translate($RECORD_MODULE, $RECORD_MODULE)}
							:<br>{$RECORD_MODEL->getDisplayName()}
						</p>
						<select class="select2 groupTax" name="groupTax" data-validation-engine="validate[required]">
							{foreach item=TAX from=$SELECTED_TAXES}
								{assign var=VALUE value=CurrencyField::convertToUserFormat($TAX['value'], null, true)}
								<option value="{$VALUE}">
									{$VALUE}% - {\App\Language::translate({$TAX['name']}, $MODULE_NAME)}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
		{/if}
	{/if}
	<!-- /tpl-Base-InventoryTaxesType1 -->
{/strip}
