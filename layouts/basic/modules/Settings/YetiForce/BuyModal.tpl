{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-YetiForce-Shop-BuyModal -->
<div class="modal-body">
	<form action="{$PAYPAL_URL}" method="POST" target="_blank">
		<div class="row no-gutters" >
			<div class="col-sm-18 col-md-12">
				<div class="text-center m-2">
					{if $PRODUCT->getImage()}
						<img src="{$PRODUCT->getImage()}" alt="{\App\Purifier::encodeHtml($PRODUCT->getLabel())}" title="{\App\Purifier::encodeHtml($PRODUCT->getLabel())}" />
					{else}
						<div class="product-no-image m-auto">
								<span class="fa-stack fa-2x product-no-image">
										<i class="fas fa-camera fa-stack-1x"></i>
										<i class="fas fa-ban fa-stack-2x"></i>
								</span>
						</div>
					{/if}
				</div>
				<table class="table table-bordered table-sm">
					<tbody class="u-word-break-all small">
						<tr>
							<td>{\App\Language::translate('LBL_SHOP_PRODUCT_NAME', $QUALIFIED_MODULE)}</td>
							<td>{$PRODUCT->getLabel()}</td>
						</tr>
						<tr>
							<td>{\App\Language::translate('LBL_SHOP_AMOUNT', $QUALIFIED_MODULE)}</td>
							<td>
							{if 'manual'=== $PRODUCT->getPriceType()}
									<input name="a3" class="form-control form-control-lg" style="max-width: 80px;" type="text" value="{$PRODUCT->getPrice()}" aria-label="price">
							{else}
								{$PRODUCT->getPrice()} {$PRODUCT->currencyCode}
							{/if}
							</td>
						</tr>
						<tr>
							<td>{\App\Language::translate('LBL_SHOP_PACKAGE', $QUALIFIED_MODULE)} </td>
							<td>{$VARIABLE_PRODUCT['os0']}</td>
						</tr>
						<tr>
							<td>{\App\Language::translate('LBL_SHOP_SUBSCRIPTIONS_DAY', $QUALIFIED_MODULE)}</td>
							<td>{$VARIABLE_PRODUCT['p3']}</td>
						</tr>
						<tr>
							<td>{\App\Language::translate('LBL_SHOP_PAYMENT_FREQUENCY', $QUALIFIED_MODULE)}</td>
							<td>{\App\Language::translate("LBL_SHOP_PAYMENT_FREQUENCY_{$VARIABLE_PRODUCT['t3']}", $QUALIFIED_MODULE)}</td>
						</tr>
					</tbody>
				</table>
				<p>{\App\Language::translate('LBL_SHOP_INVOICE_DETAILS_DESC', $QUALIFIED_MODULE)}</p>
				{if $COMPANY_DATA}
				<table class="table table-bordered table-sm">
					<tbody class="u-word-break-all small">
						<tr>
							<td>{\App\Language::translate('name', $QUALIFIED_MODULE)}</td>
							<td>
								<input name="company_name" class="form-control form-control-lg" type="text" value="{$COMPANY_DATA['name']}" aria-label="price">
							</td>
						</tr>
						<tr>
							<td>{\App\Language::translate('name', $QUALIFIED_MODULE)}</td>
							<td>
								<input name="address1" class="form-control form-control-lg"  type="text" value="{$COMPANY_DATA['name']}" aria-label="price">
							</td>
						</tr>
						<tr>
							<td>{\App\Language::translate('name', $QUALIFIED_MODULE)}</td>
							<td>
								<input name="city" class="form-control form-control-lg" type="text" value="{$COMPANY_DATA['city']}" aria-label="price">
							</td>
						</tr>
						<tr>
							<td>{\App\Language::translate('name', $QUALIFIED_MODULE)}</td>
							<td>
								<input name="zip" class="form-control form-control-lg" type="text" value="{$COMPANY_DATA['city']}" aria-label="price">
							</td>
						</tr>
						<tr>
							<td>{\App\Language::translate('name', $QUALIFIED_MODULE)}</td>
							<td>
								<input name="country" class="form-control form-control-lg" type="text" value="{$COMPANY_DATA['country']}" aria-label="price">
							</td>
						</tr>
						<tr>
							<td>{\App\Language::translate('name', $QUALIFIED_MODULE)}</td>
							<td>
								<input name="company_vat" class="form-control form-control-lg" type="text" value="{$COMPANY_DATA['name']}" aria-label="price">
							</td>
						</tr>
					</tbody>
				</table>
				{else}
					<div class="alert alert-info text-danger">
					<span class="fas fa-exclamation-triangle"></span>
						<a href="index.php?parent=Settings&module=Companies&view=List&block=3&fieldid=14">
							{\App\Language::translate('LBL_SHOP_NO_COMPANIES_ALERT', $QUALIFIED_MODULE)}
						</a>
					</div>
				{/if}
				{foreach key=NAME_OF_KEY item=VARIABLE_FORM from=$VARIABLE_PAYMENTS}
						<input name="{$NAME_OF_KEY}" type="hidden" value="{$VARIABLE_FORM}" />
				{/foreach}
				{foreach key=NAME_OF_KEY item=VALUE from=$VARIABLE_PRODUCT}
					{if !('manual'=== $PRODUCT->getPriceType() && $NAME_OF_KEY === 'a3')}
						<input name="{$NAME_OF_KEY}" type="hidden" value="{$VALUE}" />
					{/if}
				{/foreach}
			</div>
		</div>
	</form>
</div>
<!-- /tpl-Settings-YetiForce-Shop-BuyModal -->
{/strip}
