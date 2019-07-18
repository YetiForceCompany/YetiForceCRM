{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-YetiForce-Shop-SmallProduct -->
	<form action="{$PAYPAL_URL}" method="POST">
	<div class="{if empty($PRODUCT->expirationDate)} bg-light{/if}">
		<div class="d-flex no-wrap py-2 px-1">
			<div class="w-50">
				{if $PRODUCT->getImage()}
					<img src="{$PRODUCT->getImage()}" class="grow thumbnail-image card-img-top intrinsic-item"
						alt="{\App\Purifier::encodeHtml($PRODUCT->getLabel())}" title="{\App\Purifier::encodeHtml($PRODUCT->getLabel())}" />
				{else}
					<div class="product-no-image m-auto">
							<span class="fa-stack fa-2x product-no-image">
									<i class="fas fa-camera fa-stack-1x"></i>
									<i class="fas fa-ban fa-stack-2x"></i>
							</span>
					</div>
				{/if}
			</div>
			<div class="card-body w-50 py-0 pl-2 pr-3 d-flex flex-wrap justify-between align-items-center">
				<h5 class="card-title u-font-size-13px text-primary">{$PRODUCT->getLabel()}</h5>
				<p class="card-text u-font-size-10px">{$PRODUCT->getDescription()}</p>
				{if empty($PRODUCT->expirationDate)}
					{if 'manual'===$PRODUCT->getPriceType()}
						<div class="input-group mb-3">
							<div class="input-group-prepend">
								<button class="btn btn-dark rounded-0 pull-right" type="submit" tile="{\App\Language::translate('LBL_BUY', $QUALIFIED_MODULE)}">
									{$PRODUCT->getPrice()} {$PRODUCT->currencyCode} / {\App\Language::translate($PRODUCT->getPeriodLabel(), $QUALIFIED_MODULE)}
								</button>
							</div>
							<input class="form-control" type="text" value="{$PRODUCT->getPrice()}" aria-label="price">
						</div>
					{else}
						<button class="btn btn-dark btn-block rounded-0 pull-right" type="submit" tile="{\App\Language::translate('LBL_BUY', $QUALIFIED_MODULE)}">
							{$PRODUCT->getPrice()} {$PRODUCT->currencyCode} / {\App\Language::translate($PRODUCT->getPeriodLabel(), $QUALIFIED_MODULE)}
						</button>
					{/if}
				{elseif $PRODUCT->expirationDate!=$PRODUCT->paidPackage}
					<div class="alert alert-info text-danger">
						<span class="fas fa-exclamation-triangle"></span>
						{\App\Language::translate('LBL_SIZE_OF_YOUR_COMPANY_HAS_CHANGED', $QUALIFIED_MODULE)}
					</div>
				{else}
					<button class="btn btn-block btn-warning rounded-0">{\App\Fields\Date::formatToDisplay($PRODUCT->expirationDate)}</button>
				{/if}
			</div>
		</div>
	</div>
	{foreach key=NAME_OF_KEY item=VARIABLE_FORM from=\App\YetiForce\Shop::getVariablePayments()}
			<input name="{$NAME_OF_KEY}" type="hidden" value="{$VARIABLE_FORM}" />
	{/foreach}
	{foreach key=NAME_OF_KEY item=VARIABLE_PRODUCT from=\App\YetiForce\Shop::getVariableProduct($PRODUCT)}
		{if !('manual'===$PRODUCT->getPriceType() && $NAME_OF_KEY==='a3')}
			<input name="{$NAME_OF_KEY}" type="hidden" value="{$VARIABLE_PRODUCT}" />
		{/if}
	{/foreach}
	</form>
	<!-- /tpl-Settings-YetiForce-Shop-SmallProduct -->
{/strip}
