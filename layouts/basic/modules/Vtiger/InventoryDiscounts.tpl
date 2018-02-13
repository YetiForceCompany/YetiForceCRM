{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="AGGREGATION" value=$CONFIG['aggregation']}
	<div class="modelContainer modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{\App\Language::translate('LBL_CLOSE')}">x</button>
					<h3 class="modal-title">{\App\Language::translate('LBL_SELECT_DISCOUNT', $MODULE)} {\App\Language::translate($SINGLE_MODULE, $MODULE)}</h3>
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
					<hr/>
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_PRICE_BEFORE_DISCOUNT', $MODULE)}:</div>
						<div class="col-md-6 text-right"><strong><span class="valueTotalPrice">{CurrencyField::convertToUserFormat($TOTAL_PRICE, null, true)}</span> {$CURRENCY_SYMBOL}</strong></div>
					</div>
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_DISCOUNT_IN_TOTAL', $MODULE)}:</div>
						<div class="col-md-6 text-right"><strong><span class="valueDiscount">0</span> {$CURRENCY_SYMBOL}</strong></div>
					</div>
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_PRICE_AFTER_DISCOUNT', $MODULE)}:</div>
						<div class="col-md-6 text-right"><strong><span class="valuePrices">{CurrencyField::convertToUserFormat($TOTAL_PRICE, null, true)}</span> {$CURRENCY_SYMBOL}</span></strong></div>
					</div>
				</div>
				<div class="modal-footer">
					{if count($GLOBAL_DISCOUNTS) > 0 || $GROUP_DISCOUNT != 0 || $DISCOUNT_TYPE == '0'}
						<button class="btn btn-success saveDiscount" type="submit"><strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong></button>
							{/if}
					<button class="btn btn-warning" type="reset" data-dismiss="modal"><strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong></button>
				</div>
			</div>
		</div>
	</div>
{/strip}
