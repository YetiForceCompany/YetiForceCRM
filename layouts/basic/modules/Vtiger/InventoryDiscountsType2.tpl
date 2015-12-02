{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if $DISCOUNT_TYPE == '0'}
		<div class="panel panel-default">
			<div class="panel-heading">
				<strong>{vtranslate('LBL_INDIVIDUAL_DISCOUNTS', $MODULE)}</strong>
				<div class="pull-right">
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
									{vtranslate('LBL_PERCENTAGE_DISCOUNTS', $MODULE)}
								</label>
							</div>
						</div>
						<div class="col-md-6">
							<div class="radio">
								<label>
									<input type="radio" name="individualDiscountType" value="amount" class="individualDiscountType" data-symbol="{$CURRENCY_SYMBOL}">
									{vtranslate('LBL_AMOUNT_DISCOUNTS', $MODULE)}
								</label>
							</div>
						</div>
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
	{/if}
{/strip}
