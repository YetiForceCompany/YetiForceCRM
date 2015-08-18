{strip}
	{if $TAX_FIELD}
		{assign var=RECORD_MODEL value=Vtiger_Record_Model::getInstanceById($RECORD)}
		{assign var=TAXES value=Vtiger_Taxs_UIType::getTaxs()}
		{assign var=SELECTED_TAXES value=Vtiger_Taxs_UIType::getValues($RECORD_MODEL->get($TAX_FIELD))}
		<div class="panel panel-default">
			<div class="panel-heading">
				<strong>{vtranslate('LBL_GROUP_TAXS', $SUPMODULE)}</strong>
				<div class="pull-right">
					<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="group" class="activeCheckbox">
				</div>
			</div>
			<div class="panel-body" style="display: none;">
				<div>
					<p>
						{vtranslate('LBL_TAX_FOR_MODULE', $SUPMODULE)} {vtranslate($RECORD_MODULE, $RECORD_MODULE)}: {$RECORD_MODEL->getDisplayName()}
					</p>
					<select class="select2 groupTax" name="groupTax">
						{foreach item=TAX from=$SELECTED_TAXES}
							<option value="{$TAX['value']}">
								{$TAX['value']}% - {vtranslate({$TAX['name']}, $SUPMODULE)}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
	{/if}			
{/strip}
