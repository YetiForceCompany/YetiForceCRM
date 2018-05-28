{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="AGGREGATION" value=$CONFIG['aggregation']}
	<div class="modelContainer modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header contentsBackground align-items-center">
					<span class="fa-layers fa-fw mr-2">
						<i class="fas fa-circle" data-fa-transform="grow-6"></i>
						<i class="fa-inverse fas fa-long-arrow-alt-up text-white" data-fa-transform="shrink-6  left-4"></i>
						<i class="fa-inverse fas fa-percent text-white" data-fa-transform="shrink-8  right-3"></i>
					</span>
					<span class="modal-title h5">{\App\Language::translate('LBL_SELECT_TAX', $MODULE)} {\App\Language::translate($SINGLE_MODULE, $MODULE)}</span>
					<button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{\App\Language::translate('LBL_CLOSE')}">x</button>
				</div>
				<div class="modal-body">
					<input type="hidden" class="taxsType" value="{$AGGREGATION_TYPE}" />
					{foreach item=TAXID from=$CONFIG['taxs']}
						{assign var="TAX_TYPE_TPL" value="InventoryTaxesType"|cat:$TAXID|cat:".tpl"}
						{include file=\App\Layout::getTemplatePath($TAX_TYPE_TPL, $MODULE)}
					{/foreach}
					<hr/>
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_TAX_VALUE', $MODULE)}:</div>
						<div class="col-md-6 text-right"><strong><span class="taxValue js-tax-value">0</span> %</strong></div>
					</div>
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_PRICE_BEFORE_TAX', $MODULE)}:</div>
						<div class="col-md-6 text-right"><strong><span class="valueNetPrice">{CurrencyField::convertToUserFormat($TOTAL_PRICE, null, true)}</span> {$CURRENCY_SYMBOL}</strong></div>
					</div>
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_TAX_IN_TOTAL', $MODULE)}:</div>
						<div class="col-md-6 text-right"><strong><span class="valueTax" data-js="text">0</span> {$CURRENCY_SYMBOL}</strong></div>
					</div>
					<div class="row">
						<div class="col-md-6">{\App\Language::translate('LBL_PRICE_AFTER_TAX', $MODULE)}:</div>
						<div class="col-md-6 text-right"><strong><span class="valuePrices">{CurrencyField::convertToUserFormat($TOTAL_PRICE, null, true)}</span> {$CURRENCY_SYMBOL}</span></strong></div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success saveTaxs" type="submit">
						<strong>
							<span class="fas fa-check mr-1"></span>
							{\App\Language::translate('LBL_SAVE', $MODULE)}
						</strong>
					</button>
					<button class="btn btn-danger" type="reset" data-dismiss="modal">
						<strong>
							<span class="fas fa-times mr-1"></span>
							{\App\Language::translate('LBL_CANCEL', $MODULE)}
						</strong>
					</button>
				</div>
			</div>
		</div>
	</div>
{/strip}
