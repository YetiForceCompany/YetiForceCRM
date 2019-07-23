{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-YetiForce-Shop-Product row no-gutters u-cursor-pointer js-product-modal" data-js="showProductModal | click" data-product="{$PRODUCT->getName()}">
		<div class="mb-3 col-sm-18 col-md-12 item list-group-item{if empty($PRODUCT->expirationDate)} bg-light{/if}">
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
					<div class="card-body h-100 d-flex flex-column">
						<h5 class="card-title text-primary">{$PRODUCT->getLabel()}</h5>
						<p class="card-text truncate">{$PRODUCT->getIntroduction()}</p>
						{if empty($PRODUCT->expirationDate)}
							<button class="btn-dark btn-block p-3 mt-auto js-buy-modal" data-js="showBuyModal | click" data-product="{$PRODUCT->getName()}">
								{$PRODUCT->getPrice()} {$PRODUCT->currencyCode} / {\App\Language::translate($PRODUCT->getPeriodLabel(), $QUALIFIED_MODULE)}
							</button>
						{elseif $PRODUCT->expirationDate!=$PRODUCT->paidPackage}
							<div class="alert alert-info text-danger">
								<span class="fas fa-exclamation-triangle"></span>
								{\App\Language::translate('LBL_SIZE_OF_YOUR_COMPANY_HAS_CHANGED', $QUALIFIED_MODULE)}
							</div>
						{else}
							<span class="bg-yellow p-3">{\App\Fields\Date::formatToDisplay($PRODUCT->expirationDate)}</span>
						{/if}
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
