{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-YetiForce-Shop-BuyModal -->
<div class="modal-body">
	<form action="{$PAYPAL_URL}" method="POST" target="_blank">
		<div class="row no-gutters" >
			<div class="col-sm-18 col-md-12">
				<div class="row">
					<div class="col-sm-4 col-md-3">
						{if $PRODUCT->getImage()}
							<img src="{$PRODUCT->getImage()}" class="grow thumbnail-image card-img-top intrinsic-item p-3"
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
					<div class="col-sm-11 col-md-7">
						<div class="card-body">
							<h5 class="card-title text-primary">{$PRODUCT->getLabel()}</h5>
							<p class="card-text truncate">{$PRODUCT->getDescription()}</p>
								<div class="bg-dark text-white rounded-0 d-flex flex-nowrap text-nowrap align-items-center justify-content-center p-3" title="{\App\Language::translate('LBL_BUY', $QUALIFIED_MODULE)}">
									{if 'manual'===$PRODUCT->getPriceType()}
										<input name="a3" class="form-control" style="max-width: 80px;" type="text" value="{$PRODUCT->getPrice()}" aria-label="price">
									{else}
									{$PRODUCT->getPrice()}
									{/if}
									<span class="ml-1">
										{$PRODUCT->currencyCode} / {\App\Language::translate($PRODUCT->getPeriodLabel(), $QUALIFIED_MODULE)}
									</span>
								</div>
						</div>
					</div>
					<div class="col-sm-3 col-md-2 d-flex align-items-center">
						{foreach key=NAME_OF_KEY item=VARIABLE_FORM from=$VARIABLE_PAYMENTS}
								<input name="{$NAME_OF_KEY}" type="hidden" value="{$VARIABLE_FORM}" />
						{/foreach}
						{foreach key=NAME_OF_KEY item=VARIABLE_PRODUCT from=$PRODUCT->getVariable()}
							{if !('manual'===$PRODUCT->getPriceType() && $NAME_OF_KEY==='a3')}
								<input name="{$NAME_OF_KEY}" type="hidden" value="{$VARIABLE_PRODUCT}" />
							{/if}
						{/foreach}
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<!-- /tpl-Settings-YetiForce-Shop-BuyModal -->
{/strip}
