{*<!-- {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-YetiForce-Shop-ProductPremium -->
	{assign var=PRODUCT_ALERT value=$PRODUCT->getAlertMessage(false)}
	<div class="dashboardWidget marketplace-product mt-3 mr-3 flex-grow-1 js-product position-relative" data-js="showProductModal|click|container" data-category="{$PRODUCT->getCategory()}" data-product="{$PRODUCT->getName()}" data-product-id="{$PRODUCT->getId()}">
		{if $PRODUCT_ALERT}
			<span class="text-danger fas fa-exclamation-circle animate__animated animate__infinite animate__flash animate__slow mr-1 mt-1 u-cursor-pointer js-popover-tooltip position-absolute u-position-r-0" data-toggle="popover" data-js="popover | mouseenter" data-content="{App\Language::translate($PRODUCT_ALERT, $QUALIFIED_MODULE)}"></span>
		{/if}
		<div class="o-small-product pl-2 {if $PRODUCT->isExpired()}bg-color-red-100{elseif $PRODUCT->getStatus()} bg-yellow {else} u-bg-white-darken{/if}">
			<div class="o-small-product__container d-flex u-min-h-120px-rem no-wrap py-2 px-1 {if !$PRODUCT->isExpired()} bg-white u-bg-white-darken{/if}">
				<div class="o-small-product__img d-flex row">
					<div class="col-12">
						{if $PRODUCT->getImage()}
							<img src="{$PRODUCT->getImage()|escape}" class="my-auto grow thumbnail-image card-img-top intrinsic-item" alt="{App\Purifier::encodeHtml($PRODUCT->getLabel())}" title="{App\Purifier::encodeHtml($PRODUCT->getLabel())}" />
						{else}
							<div class="product-no-image m-auto">
								<span class="fa-stack fa-2x product-no-image u-fs-lg">
									<i class="fas fa-camera fa-stack-1x"></i>
									<i class="fas fa-ban fa-stack-2x"></i>
								</span>
							</div>
						{/if}
					</div>
					{assign var=SWITCH_LINK value=$PRODUCT->getSwitchButton()}
					{if $SWITCH_LINK}
						<div class="col-12 m-auto">
							<div class="m-2 js-popover-tooltip js-stop-parent-trigger"
								data-placement="top"
								data-content="{$SWITCH_LINK->getLabel()}"
								data-target="focus hover">
								<input class="c-checkbox-input js-product-switch" type="checkbox" id="c-{$PRODUCT->getName()}" {if $PRODUCT->isConfigured()}checked="checked" {/if}
									{if $SWITCH_LINK->get('linkdata') neq '' && is_array($SWITCH_LINK->get('linkdata'))}
										{foreach from=$SWITCH_LINK->get('linkdata') key=NAME item=DATA}
											{' '}data-{$NAME}="{App\Purifier::encodeHtml($DATA)}"
										{/foreach}
									{/if} />
								<label class="c-checkbox-slider" for="c-{$PRODUCT->getName()}"></label>
							</div>
						</div>
					{/if}
				</div>
				<div class="py-0 pl-2 pr-3 d-flex flex-wrap justify-between align-items-center w-100">
					{include file=App\Layout::getTemplatePath('DashBoard/WidgetTitle.tpl', $QUALIFIED_MODULE) TITLE=$PRODUCT->getLabel() CLASS="u-cursor-pointer js-text-search u-flex-b100"}
					{include file=App\Layout::getTemplatePath('DashBoard/WidgetDescription.tpl', $QUALIFIED_MODULE) DESCRIPTION=$PRODUCT->getIntroduction() CLASS="mb-0"}
					{if $PRODUCT->getStatus()}
						<button class="btn btn-block bg-yellow mt-auto js-buy-modal" data-js="showBuyModal | click" data-product="{$PRODUCT->getName()}" disabled data-product-id="{$PRODUCT->getId()}">
							{App\Fields\Date::formatToDisplay($PRODUCT->getExpirationDate())}
						</button>
					{elseif $PRODUCT->isExpired()}
						<button class="btn btn-dark btn-block m-auto js-buy-modal js-popover-tooltip" data-js="showBuyModal | click" data-product="{$PRODUCT->getName()}" data-content="{App\Language::translate('LBL_SUBSCRIPTION_HAS_EXPIRED', $QUALIFIED_MODULE)}" data-product-id="{$PRODUCT->getId()}" data-js="popover | modal">
							{App\Language::translate('LBL_SHOP_RENEW', $QUALIFIED_MODULE)}
						</button>
					{else}
						<button class="btn btn-dark btn-block mt-auto js-buy-modal" data-js="showBuyModal | click" data-product="{$PRODUCT->getName()}" data-product-id="{$PRODUCT->getId()}">
							{$PRODUCT->getPrice()} {$PRODUCT->getCurrencyCode()} / {$PRODUCT->getPaymentFrequencyShort()}
						</button>
					{/if}
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-YetiForce-Shop-ProductPremium -->
{/strip}
