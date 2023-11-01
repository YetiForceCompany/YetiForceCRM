{*<!-- {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-YetiForce-BuyModal -->
	{assign var=LABEL_CLASS value='py-2 u-font-weight-550 align-middle'}
	<div class="modal-body pb-0">
		<form class="js-buy-form" action="{$PAYPAL_URL}" method="POST" target="_blank">
			<div class="row no-gutters">
				<div class="col-sm-18 col-md-12">
					<div class="text-center pb-3">
						{if $IMAGE}
							<img class="o-buy-modal__img" src="{$IMAGE|escape}" alt="{App\Purifier::encodeHtml($PRODUCT->getLabel())}" title="{App\Purifier::encodeHtml($PRODUCT->getLabel())}" />
						{else}
							<div class="product-no-image m-auto text-center">
								<span class="fa-stack fa-6x product-no-image">
									<i class="fas fa-camera fa-stack-1x"></i>
									<i class="fas fa-ban fa-stack-2x"></i>
								</span>
							</div>
						{/if}
					</div>
					<table class="table table-sm mb-0">
						<tbody class="u-word-break-all small">
							<tr>
								<td class="{$LABEL_CLASS}">{App\Language::translate('LBL_SHOP_PRODUCT_NAME', $QUALIFIED_MODULE)}</td>
								<td class="py-2 w-50">{$PRODUCT->getLabel()}<br /><small>({$PRODUCT->getName()})</small></td>
							</tr>
							<tr>
								<td class="{$LABEL_CLASS}">{App\Language::translate('LBL_SHOP_PACKAGE', $QUALIFIED_MODULE)}</td>
								<td class="w-50 input-group-sm">
									<select class="select2 form-control js-price-by-size" data-js="container">
										{foreach key=KEY item=PACKAGE from=$PRODUCT->getPackages()}
											<option value="{$PACKAGE->getId()}"
												data-pid="{$PACKAGE->getId()}"
												data-currency_code="{$PACKAGE->getCurrencyCode()}"
												data-pricenet="{$PACKAGE->getPriceNet(true)} {$PACKAGE->getCurrencyCode()}"
												data-pricegross="{$PACKAGE->getPriceGross(true)} {$PACKAGE->getCurrencyCode()}"
												data-a3="{$PACKAGE->getPriceGross()}"
												data-t3="{$PACKAGE->getPaymentFrequencyShort()}"
												data-os0="{$PACKAGE->getName()}"
												data-frequency="{App\Language::translate($PACKAGE->getPaymentFrequencyLabel(), $QUALIFIED_MODULE)}"
												{if $PRODUCT->getFitPackage()->getId() === $PACKAGE->getId()} selected {/if}>
												{$PACKAGE->getLabel()}{if $PACKAGE->getPaymentFrequencyShort() !== 'M'} ({$PACKAGE->getPaymentFrequencyShort()}){/if}
											</option>
										{/foreach}
									</select>
								</td>
							</tr>
							<tr>
								<td class="{$LABEL_CLASS} border-bottom">{App\Language::translate('LBL_SHOP_PRICE_NET', $QUALIFIED_MODULE)}</td>
								<td class="py-2 w-50 border-bottom js-buy-text" data-key="pricenet">{$PRODUCT->getFitPackage()->getPriceNet(true)} {$PRODUCT->getFitPackage()->getCurrencyCode()}</td>
							</tr>
							<tr>
								<td class="{$LABEL_CLASS} border-bottom">{App\Language::translate('LBL_SHOP_PRICE_GROSS', $QUALIFIED_MODULE)}</td>
								<td class="py-2 w-50 border-bottom js-buy-text" data-key="pricegross">{$PRODUCT->getFitPackage()->getPriceGross(true)} {$PRODUCT->getFitPackage()->getCurrencyCode()}</td>
							</tr>
							<tr>
								<td class="{$LABEL_CLASS} border-bottom">{App\Language::translate('LBL_SHOP_PAYMENT_FREQUENCY', $QUALIFIED_MODULE)}</td>
								<td class="py-2 w-50 border-bottom js-buy-text" data-key="frequency">{App\Language::translate($PRODUCT->getFitPackage()->getPaymentFrequencyLabel(), $QUALIFIED_MODULE)}</td>
							</tr>
						</tbody>
					</table>
					{foreach key=NAME_OF_KEY item=VALUE from=$VARIABLE}
						<input name="{$NAME_OF_KEY}" class="js-buy-value" type="hidden" value="{$VALUE}" />
					{/foreach}
				</div>
			</div>
		</form>
		<form class="js-update-company-form" name="EditCompanies" action="index.php" method="post" id="EditView" enctype="multipart/form-data">
			<input type="hidden" name="module" value="YetiForce">
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="Buy" />
			<input type="hidden" name="packageId" class="js-buy-value" data-key="pid" value="{$PRODUCT->getFitPackage()->getId()}" />
			<p class="small text-truncate my-4 py-1 text-center">
				<span class="u-font-weight-550 mr-1">
					{App\Language::translate('LBL_SHOP_INVOICE_DETAILS', $QUALIFIED_MODULE)}
				</span>
			</p>
			<table class="table table-sm mb-0">
				<tbody class="u-word-break-all small">
					{foreach key="FIELD_NAME" item="FIELD_MODEL" from=$FORM_FIELDS name=companyForm}
						<tr>
							<td class="align-middle u-font-weight-550{if $smarty.foreach.companyForm.last} border-bottom{/if}">
								{App\Language::translate($FIELD_MODEL->getLabel(), 'Settings:Companies')}
							</td>
							<td class="w-50 position-relative input-group-sm{if $smarty.foreach.companyForm.last} border-bottom{/if}">
								{include file=App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName()) MODULE=$QUALIFIED_MODULE}
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</form>
	</div>
	<!-- /tpl-Settings-YetiForce-Shop-BuyModal -->
{/strip}
