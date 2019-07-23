{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-YetiForce-Shop-SmallProduct -->
	{assign var=PRODUCT_ALERT value=$PRODUCT->showAlert()}
	<form action="{$PAYPAL_URL}" method="POST" target="_blank">
	<div class="pl-2 {if empty($PRODUCT->expirationDate)}bg-light{elseif $PRODUCT->expirationDate!=$PRODUCT->paidPackage}bg-danger{else}bg-yellow{/if}">
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
				{assign var=BUTTON_TEXT value="{$PRODUCT->getPrice()} {$PRODUCT->currencyCode} / {\App\Language::translate($PRODUCT->getPeriodLabel(), $QUALIFIED_MODULE)}"}
					{if 'manual'===$PRODUCT->getPriceType()}
						<div class="input-group flex-nowrap">
							<input name="a3" class="form-control" type="text" value="{$PRODUCT->getPrice()}" aria-label="price" style="min-width: 40px;">
							<div class="input-group-append">
								<button class="btn btn-dark u-w-fill-available" type="submit" title="{\App\Language::translate('LBL_BUY', $QUALIFIED_MODULE)}">
									<div class="js-popover-tooltip--ellipsis-icon d-flex flex-nowrap align-items-center"
									data-content="{$BUTTON_TEXT}" data-toggle="popover" data-js="popover | mouseenter">
										<span class="js-popover-text" data-js="clone">{$PRODUCT->currencyCode} / {\App\Language::translate($PRODUCT->getPeriodLabel(), $QUALIFIED_MODULE)}</span>
								</div>
								{if $PRODUCT_ALERT}
									<span class="text-danger fas fa-exclamation-triangle animated flash infinite slow mr-1"></span>
									<span class="u-cursor-pointer js-popover-tooltip fas fa-xs fa-info-circle"
									data-toggle="popover" data-js="popover | mouseenter"
									data-content="{\App\Language::translate($PRODUCT_ALERT, $QUALIFIED_MODULE)}"></span>
								{/if}
								</button>
							</div>
						</div>
					{else}
						{if $PRODUCT_ALERT}
							<span class="text-danger fas fa-exclamation-triangle animated flash infinite slow mr-1"></span>
							<span class="u-cursor-pointer js-popover-tooltip fas fa-xs fa-info-circle"
							data-toggle="popover" data-js="popover | mouseenter"
							data-content="{\App\Language::translate($PRODUCT_ALERT, $QUALIFIED_MODULE)}"></span>
						{/if}
						{if $PRODUCT->expirationDate}
							<button class="btn btn-block text-truncate bg-yellow" title="{\App\Fields\Date::formatToDisplay($PRODUCT->expirationDate)}">
								{\App\Fields\Date::formatToDisplay($PRODUCT->expirationDate)}
							</button>
						{else}
							<button class="btn btn-dark btn-block text-truncate" type="submit" title="{\App\Language::translate('LBL_BUY', $QUALIFIED_MODULE)}">
								{$BUTTON_TEXT}
							</button>
						{/if}
					{/if}
			</div>
		</div>
	</div>
	{foreach key=NAME_OF_KEY item=VARIABLE_FORM from=\App\YetiForce\Shop::getVariablePayments()}
			<input name="{$NAME_OF_KEY}" type="hidden" value="{$VARIABLE_FORM}" />
	{/foreach}
	{foreach key=NAME_OF_KEY item=VARIABLE_PRODUCT from=$PRODUCT->getVariable()}
		{if !('manual'===$PRODUCT->getPriceType() && $NAME_OF_KEY==='a3')}
			<input name="{$NAME_OF_KEY}" type="hidden" value="{$VARIABLE_PRODUCT}" />
		{/if}
	{/foreach}
	</form>
	<!-- /tpl-Settings-YetiForce-Shop-SmallProduct -->
{/strip}
