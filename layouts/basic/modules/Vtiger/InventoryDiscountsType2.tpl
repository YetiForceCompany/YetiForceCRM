{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="panel panel-default">
		<div class="panel-heading">
			<strong>{\App\Language::translate('LBL_INDIVIDUAL_DISCOUNTS', $MODULE)}</strong>
			<div class="float-right">
				<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="individual" class="activeCheckbox">
			</div>
		</div>
		<div class="panel-body" style="display: none;">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-6">
						<div class="radio">
							<label>
								<input type="radio" name="individualDiscountType" value="percentage" class="individualDiscountType" data-symbol="%" checked>
								{\App\Language::translate('LBL_PERCENTAGE_DISCOUNTS', $MODULE)}
							</label>
						</div>
					</div>
					{if $DISCOUNT_TYPE == '0'}
						<div class="col-md-6">
							<div class="radio">
								<label>
									<input type="radio" name="individualDiscountType" value="amount" class="individualDiscountType" data-symbol="{$CURRENCY_SYMBOL}">
									{\App\Language::translate('LBL_AMOUNT_DISCOUNTS', $MODULE)}
								</label>
							</div>
						</div>
					{/if}
				</div>
				<div class="row">
					<div class="input-group individualDiscountContainer">
						<input type="text" name="individualDiscount" class="form-control individualDiscountValue" value="0">
						<span class="input-group-addon">%</span>
					</div>
				</div>
			</div>
		</div>
	</div>

{/strip}
