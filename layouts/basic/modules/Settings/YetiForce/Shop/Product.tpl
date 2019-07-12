{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<form action="{$PAYPAL_URL}" method="POST">
	<div class="tpl-Settings-YetiForce-Shop-Product row">
			<div class="mb-3 col-sm-18 col-md-12 item list-group-item">
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
							{if 'manual'===$PRODUCT->getPriceType()}
								<input class="form-control" name="a3" type="text" value="{$PRODUCT->getPrice()}" />
							{else}
								<p class="col-6 lead">{$PRODUCT->getPrice()}</p>
							{/if}
						</div>
					</div>
					<div class="col-sm-3 col-md-2 d-flex align-items-center">
						{foreach key=NAME_OF_KEY item=VARIABLE_FORM from=\App\YetiForce\Shop::getVariablePayments()}
								<input name="{$NAME_OF_KEY}" type="hidden" value="{$VARIABLE_FORM}" />
						{/foreach}
						{foreach key=NAME_OF_KEY item=VARIABLE_PRODUCT from=\App\YetiForce\Shop::getVariableProduct($PRODUCT)}
							{if !('manual'===$PRODUCT->getPriceType() && $NAME_OF_KEY==='a3')}
								<input name="{$NAME_OF_KEY}" type="hidden" value="{$VARIABLE_PRODUCT}" />
							{/if}
						{/foreach}
						<button class="btn btn-success pull-right" type="submit">{\App\Language::translate('LBL_BUY', $QUALIFIED_MODULE)}</button>
					</div>
				</div>
			</div>
	</div>
	</form>
{/strip}
