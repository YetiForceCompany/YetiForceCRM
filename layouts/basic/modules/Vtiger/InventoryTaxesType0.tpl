{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if count($GLOBAL_TAXS) > 0}
		<div class="panel panel-default">
			<div class="panel-heading">
				<strong>{vtranslate('LBL_GLOBAL_TAXS', $MODULE)}</strong>
				<div class="pull-right">
					<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="global" class="activeCheckbox">
				</div>
			</div>
			<div class="panel-body" style="display: none;">
				<select class="select2 globalTax" name="globalTax">
					{foreach item=VALUE key=NAME from=$GLOBAL_TAXS}
						<option value="{CurrencyField::convertToUserFormat($VALUE, null, true)}">
							{$VALUE}% - {vtranslate($NAME, $MODULE)}
						</option>
					{/foreach}
				</select>
			</div>
		</div>
	{/if}
{/strip}
