{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if $TAX_TYPE == '0' && $TAX_FIELD && $RECORD}
		{assign var=RECORD_MODEL value=Vtiger_Record_Model::getInstanceById($RECORD)}
		{assign var=SELECTED_TAXES value=Vtiger_Taxes_UIType::getValues($RECORD_MODEL->get($TAX_FIELD))}
		{if count($SELECTED_TAXES) > 0}
			<div class="panel panel-default">
				<div class="panel-heading">
					<strong>{vtranslate('LBL_GROUP_TAXS', $MODULE)}</strong>
					<div class="pull-right">
						<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="group" class="activeCheckbox">
					</div>
				</div>
				<div class="panel-body" style="display: none;">
					<div>
						<p>
							{vtranslate('LBL_TAX_FOR_MODULE', $MODULE)} {vtranslate($RECORD_MODULE, $RECORD_MODULE)}: {$RECORD_MODEL->getDisplayName()}
						</p>
						<select class="select2 groupTax" name="groupTax">
							{foreach item=TAX from=$SELECTED_TAXES}
								{assign var=VALUE value=CurrencyField::convertToUserFormat($TAX['value'], null, true)}
								<option value="{$VALUE}">
									{$VALUE}% - {vtranslate({$TAX['name']}, $MODULE)}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
		{/if}
	{/if}			
{/strip}
