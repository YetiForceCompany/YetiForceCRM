{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-YetiForce-ProductModal -->
	<div class="modal-body js-data pb-0" data-product="{$PRODUCT->getName()}" data-js="data">
		<div class="row no-gutters">
			<div class="col-sm-18 col-md-12">
				<div class="row">
					<div class="col-sm-4 col-md-3">
						{if $IMAGE}
							<img src="{$IMAGE}" class="grow thumbnail-image card-img-top intrinsic-item" alt="{\App\Purifier::encodeHtml($PRODUCT->getLabel())}" title="{\App\Purifier::encodeHtml($PRODUCT->getLabel())}" />
						{else}
							<div class="product-no-image m-auto">
								<span class="fa-stack fa-2x product-no-image">
									<i class="fas fa-camera fa-stack-1x"></i>
									<i class="fas fa-ban fa-stack-2x"></i>
								</span>
							</div>
						{/if}
						{if 'manual'=== $PRODUCT->getPriceType() || 'selection'=== $PRODUCT->getPriceType()}

						{elseif $PRICE !== false }
							<div class="text-danger h1 mb-3 mt-2 text-center">
								{$PRICE} {$CURRENCY} / {\App\Language::translate($PRODUCT->getPeriodLabel(), $QUALIFIED_MODULE)}
							</div>
						{/if}
					</div>
					<div class="col-sm-11 col-md-9">
						<div class="d-flex flex-column h-100">
							<h5 class="h3">
								{$PRODUCT->getLabel()}
							</h5>
							<div>{$PRODUCT->getDescription()}</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-YetiForce-ProductModal -->
{/strip}
