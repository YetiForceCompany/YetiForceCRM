{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-InventoryDiscounts -->
	{assign var=AGGREGATION value=$CONFIG['aggregation']}
	<div class="modelContainer modal fade">
		<div class="modal-dialog">
			<form class="modal-content">
				<div class="modal-header align-items-center">
					<span class="mr-2 small">
						<span class="fas fa-long-arrow-alt-down"></span>
						<span class="fas fa-percent"></span>
					</span>
					<h5 class="modal-title">{\App\Language::translate('LBL_SELECT_DISCOUNT', $MODULE_NAME)} {\App\Language::translate("SINGLE_{$MODULE_NAME}", $MODULE_NAME)}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<input type="hidden" class="discountsType" value="{$AGGREGATION_TYPE}" />
					{foreach item=DISCOUNTID from=$CONFIG['discounts']}
						{assign var="DISCOUNT_TYPE_TPL" value="InventoryDiscountsType"|cat:$DISCOUNTID|cat:".tpl"}
						{include file=\App\Layout::getTemplatePath($DISCOUNT_TYPE_TPL, $MODULE)}
					{/foreach}
					{if count($GLOBAL_DISCOUNTS) == 0 && $GROUP_DISCOUNT == 0 && $DISCOUNT_TYPE != '0'}
						<div class="alert alert-danger" role="alert">
							{\App\Language::translate('LBL_NO_DISCOUNTS')}
						</div>
					{/if}
					<hr />
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_PRICE_BEFORE_DISCOUNT', $MODULE)}:</div>
						<div class="col-md-6 text-right">
							<strong><span class="valueTotalPrice">{CurrencyField::convertToUserFormat($TOTAL_PRICE, null, true)}</span> {$CURRENCY_SYMBOL}</strong>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_DISCOUNT_IN_TOTAL', $MODULE)}:</div>
						<div class="col-md-6 text-right">
							<strong><span class="valueDiscount">0</span> {$CURRENCY_SYMBOL}</strong>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_PRICE_AFTER_DISCOUNT', $MODULE)}:</div>
						<div class="col-md-6 text-right">
							<strong><span class="valuePrices">{CurrencyField::convertToUserFormat($TOTAL_PRICE, null, true)}</span> {$CURRENCY_SYMBOL}</span></strong>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					{if count($GLOBAL_DISCOUNTS) > 0 || $GROUP_DISCOUNT != 0 || $DISCOUNT_TYPE == '0' || ($DISCOUNT_TYPE == '1' && in_array(2, $CONFIG['discounts']))}
						<button class="btn btn-success js-save-discount" type="button" data-js="click">
							<strong>
								<span class="fas fa-check mr-2"></span>
								{\App\Language::translate('LBL_SAVE', $MODULE)}
							</strong>
						</button>
					{/if}
					<button class="btn btn-danger" type="reset" data-dismiss="modal">
						<strong>
							<span class="fas fa-times mr-2"></span>
							{\App\Language::translate('LBL_CANCEL', $MODULE)}
						</strong>
					</button>
				</div>
			</form>
		</div>
	</div>
	<!-- /tpl-Base-InventoryDiscounts -->
{/strip}
