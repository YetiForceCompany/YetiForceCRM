{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-body js-data" data-product="{$PRODUCT->getName()}" data-js="data">
		<div class="row no-gutters" >
			<div class="col-sm-18 col-md-12">
				<div class="row">
					<div class="col-sm-4 col-md-3">
						{if $IMAGE}
							<img src="{$IMAGE}" class="grow thumbnail-image card-img-top intrinsic-item p-3"
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
						<div class="d-flex flex-column h-100">
							<div class="text-danger h1 mt-1 mb-0">
								{if 'manual'=== $PRODUCT->getPriceType() || 'selection'=== $PRODUCT->getPriceType()}

								{elseif $PRICE !== false }
									{$PRICE} {$CURRENCY} / {\App\Language::translate($PRODUCT->getPeriodLabel(), $QUALIFIED_MODULE)}
								{/if}
							</div>
							<h5 class="h4 my-4">{$PRODUCT->getLabel()}</h5>
							<p>{$PRODUCT->getDescription()}</p>
							{if 'selection'=== $PRODUCT->getPriceType()}
								<p>
										{foreach key=KEY item=PRICE from=$PRODUCT->prices}
											{if isset($PRODUCT->customPricesLabel[$KEY])}
												{$PRODUCT->getPriceLabel($KEY)}: {$PRICE} {$CURRENCY}<br>
											{else}
												{$PRODUCT->getPriceLabel($KEY)}: {$PRICE} {$CURRENCY}<br>
											{/if}
										{/foreach}
								</p>
							{/if}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
