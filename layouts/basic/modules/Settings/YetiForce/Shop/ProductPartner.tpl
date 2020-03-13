{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-YetiForce-Shop-ProductPartner -->
	{assign var=PRODUCT_ALERT value=$PRODUCT->showAlert()}
	<div class="dashboardWidget mt-3 mr-3 flex-grow-1 u-w-max-430px js-product" data-js="showProductModal | click | container" data-product="{$PRODUCT->getName()}">
		{if !empty($PRODUCT->expirationDate) && $PRODUCT_ALERT}
			<span class="text-danger fas fa-exclamation animated flash infinite slow ml-3 mt-1 u-cursor-pointer js-popover-tooltip position-absolute" data-toggle="popover" data-js="popover | mouseenter"
			data-content="{\App\Language::translate($PRODUCT_ALERT, $QUALIFIED_MODULE)}"></span>
		{/if}
		<div class="o-small-product {if empty($PRODUCT->expirationDate)}bg-light u-bg-light-darken{elseif $PRODUCT_ALERT}bg-danger{else}bg-yellow{/if}">
			<div class="o-small-product__container u-min-h-120px-rem no-wrap py-2 px-1 {if !empty($PRODUCT->expirationDate)} bg-white u-bg-white-darken{/if}">
				<div class="o-small-product__partner-img col-12 d-flex justify-content-center mb-2">
					{if $PRODUCT->getImage()}
						<img src="{$PRODUCT->getImage()}" class="my-auto grow thumbnail-image card-img-top intrinsic-item"
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
				<div class="py-0 px-3">
					{include file=\App\Layout::getTemplatePath('DashBoard/WidgetTitle.tpl', $QUALIFIED_MODULE) TITLE=$PRODUCT->getLabel() CLASS="u-cursor-pointer text-center js-text-search"}
					{include file=\App\Layout::getTemplatePath('DashBoard/WidgetDescription.tpl', $QUALIFIED_MODULE) DESCRIPTION=$PRODUCT->getIntroduction() CLASS="mb-2"}
					{if empty($PRODUCT->expirationDate)}
						<button class="btn btn-dark btn-block m-auto js-buy-modal col-6" data-js="showBuyModal | click" data-product="{$PRODUCT->getName()}">
							{if 'manual'===$PRODUCT->getPriceType()}
								{\App\Language::translate("LBL_SUPPORT_US", $QUALIFIED_MODULE)}
							{else}
								{$PRODUCT->getPrice()} {$PRODUCT->currencyCode} / {\App\Language::translate($PRODUCT->getPeriodLabel(), $QUALIFIED_MODULE)}
							{/if}
						</button>
					{else}
						{if $PRODUCT_ALERT}
							<button class="btn btn-dark btn-block m-auto js-buy-modal col-6"
							data-js="showBuyModal | click" data-product="{$PRODUCT->getName()}">
								{\App\Language::translate('LBL_SHOP_RENEW', $QUALIFIED_MODULE)}
							</button>
						{else}
							<button class="btn btn-block bg-yellow m-auto js-buy-modal col-6"
							data-js="showBuyModal | click" data-product="{$PRODUCT->getName()}" disabled>
								{\App\Fields\Date::formatToDisplay($PRODUCT->expirationDate)}
							</button>
						{/if}
					{/if}
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-YetiForce-Shop-ProductPartner -->
{/strip}
