{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if $GROUP_DISCOUNT != 0}
		<div class="panel panel-default">
			<div class="panel-heading">
				<strong>{\App\Language::translate('LBL_GROUP_DISCOUNTS', $MODULE)}</strong>
				<div class="float-right">
					<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="group" class="activeCheckbox">
				</div>
			</div>
			<div class="panel-body" style="display: none;">
				<div>
					<p>
						{\App\Language::translate('LBL_DISCOUNT_FOR_ACCOUNT', $MODULE)} {$ACCOUNT_NAME}
					</p>
					<div class="input-group">
						<span class="input-group-addon">
							<input type="checkbox" name="groupCheckbox" value="on" class="groupCheckbox">
						</span>
						<input type="text" class="form-control groupValue" name="groupDiscount" value="{CurrencyField::convertToUserFormat($GROUP_DISCOUNT, null, true)}" readonly="true">
						<span class="input-group-addon">%</span>
					</div>
				</div>
			</div>
		</div>
	{/if}
{/strip}
