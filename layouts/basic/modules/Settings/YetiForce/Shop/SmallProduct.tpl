{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-YetiForce-Shop-SmallProduct -->
	<form action="{$PAYPAL_URL}" method="POST">
	<div class="pl-2 {if empty($PRODUCT->expirationDate)}bg-light{else}bg-yellow{/if}">
		<div class="d-flex u-min-h-120px-rem no-wrap py-2 pr-1{if !empty($PRODUCT->expirationDate)} bg-white{/if}">
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
				<h5 class="card-title u-font-size-13px text-black mb-0  ellipsis-2-lines" title="{$PRODUCT->getLabel()}">{$PRODUCT->getLabel()}</h5>
				<p class="card-text u-font-size-10px u-font-weight-600 ellipsis-2-lines mb-0" title="{$PRODUCT->getDescription()}">{$PRODUCT->getDescription()}</p>
				{assign var=BUTTON_TEXT value="{$PRODUCT->getPrice()} {$PRODUCT->currencyCode} / {\App\Language::translate($PRODUCT->getPeriodLabel(), $QUALIFIED_MODULE)}"}
				{if empty($PRODUCT->expirationDate)}
					{if 'manual'===$PRODUCT->getPriceType()}
						<div class="input-group flex-nowrap">
							<div class="input-group-prepend w-50">
								<button class="btn btn-dark rounded-0 u-w-fill-available" type="submit" tile="{\App\Language::translate('LBL_BUY', $QUALIFIED_MODULE)}">
									<div class="js-popover-tooltip--ellipsis-icon d-flex flex-nowrap align-items-center"
									data-content="{$BUTTON_TEXT}" data-toggle="popover" data-js="popover | mouseenter">
										<span class="fas fa-euro-sign js-popover-icon mr-1"></span>
										<span class="js-popover-text" data-js="clone">{$BUTTON_TEXT}</span>
								</div>
								</button>
							</div>
							<input class="form-control w-50" type="text" value="{$PRODUCT->getPrice()}" aria-label="price">
						</div>
					{else}
						<button class="btn btn-dark btn-block text-truncate rounded-0 pull-right" type="submit" title="{\App\Language::translate('LBL_BUY', $QUALIFIED_MODULE)}">
						<span class="fas fa-euro-sign mr-1"></span>
							{$BUTTON_TEXT}
						</button>
					{/if}
				{elseif $PRODUCT->expirationDate!=$PRODUCT->paidPackage}
					<span class="text-danger fas fa-exclamation-triangle animated flash infinite slow mr-1"></span>
					<span class="u-cursor-pointer js-popover-tooltip fas fa-xs fa-info-circle"
					data-toggle="popover" data-js="popover | mouseenter"
					data-content="{\App\Language::translate('LBL_SIZE_OF_YOUR_COMPANY_HAS_CHANGED', $QUALIFIED_MODULE)}"></span>
				{else}
					<button class="btn btn-block text-truncate bg-yellow rounded-0" title="{\App\Fields\Date::formatToDisplay($PRODUCT->expirationDate)}">{\App\Fields\Date::formatToDisplay($PRODUCT->expirationDate)}</button>
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
