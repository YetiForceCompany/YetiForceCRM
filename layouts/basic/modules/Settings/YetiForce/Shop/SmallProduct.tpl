{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-YetiForce-Shop-SmallProduct -->
	{assign var=PRODUCT_ALERT value=$PRODUCT->showAlert()}
	<div class="pl-2 {if empty($PRODUCT->expirationDate)}bg-light{elseif $PRODUCT_ALERT}bg-danger{else}bg-yellow{/if} u-cursor-pointer js-product-modal"
		data-js="showProductModal | click" data-product="{$PRODUCT->getName()}">
		<div class="d-flex u-min-h-120px-rem no-wrap py-2 pr-1{if !empty($PRODUCT->expirationDate)} bg-white{/if}">
			<div class="d-flex" style="min-width: 30%;">
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
			<div class="py-0 pl-2 pr-3 d-flex flex-wrap justify-between align-items-center">
				{include file=\App\Layout::getTemplatePath('DashBoard/WidgetTitle.tpl', $QUALIFIED_MODULE) TITLE=$PRODUCT->getLabel()}
				{include file=\App\Layout::getTemplatePath('DashBoard/WidgetDescription.tpl', $QUALIFIED_MODULE) DESCRIPTION=$PRODUCT->getIntroduction()}
				{if empty($PRODUCT->expirationDate)}
					<button class="btn btn-dark btn-block mt-auto js-buy-modal" data-js="showBuyModal | click" data-product="{$PRODUCT->getName()}">
						{if 'manual'===$PRODUCT->getPriceType()}
							{\App\Language::translate("LBL_SUPPORT_US", $QUALIFIED_MODULE)}
						{else}
							{$PRODUCT->getPrice()} {$PRODUCT->currencyCode} / {\App\Language::translate($PRODUCT->getPeriodLabel(), $QUALIFIED_MODULE)}
						{/if}
					</button>
				{else}
					{if $PRODUCT_ALERT}
						<span class="text-danger fas fa-exclamation-triangle animated flash infinite slow mr-1"></span>
						<span class="u-cursor-pointer js-popover-tooltip fas fa-xs fa-info-circle"
						data-toggle="popover" data-js="popover | mouseenter"
						data-content="{\App\Language::translate($PRODUCT_ALERT, $QUALIFIED_MODULE)}"></span>
					{/if}
					<button class="btn btn-block bg-yellow mt-auto js-buy-modal"
					data-js="showBuyModal | click" data-product="{$PRODUCT->getName()}"{if !$PRODUCT_ALERT} disabled{/if}>
						{\App\Fields\Date::formatToDisplay($PRODUCT->expirationDate)}
					</button>
				{/if}
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-YetiForce-Shop-SmallProduct -->
{/strip}
