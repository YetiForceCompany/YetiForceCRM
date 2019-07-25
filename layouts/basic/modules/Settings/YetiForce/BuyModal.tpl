{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-YetiForce-Shop-BuyModal -->
<div class="modal-body px-md-5 pb-0">
	<form  class="js-buy-form" action="{$PAYPAL_URL}" method="POST" target="_blank">
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
				<table class="table table-sm">
					<tbody class="u-word-break-all small">
						<tr>
							<td class="py-2">{\App\Language::translate('LBL_SHOP_PRODUCT_NAME', $QUALIFIED_MODULE)}</td>
							<td class="py-2 w-50">{$PRODUCT->getLabel()}</td>
						</tr>
						<tr>
							<td class="py-2">{\App\Language::translate('LBL_SHOP_AMOUNT', $QUALIFIED_MODULE)}</td>
							<td class="py-2 w-50">
							{if 'manual'=== $PRODUCT->getPriceType()}
									<input name="a3" class="form-control" style="max-width: 80px;" type="text" value="{$PRODUCT->getPrice()}" aria-label="price">
							{else}
								{$PRODUCT->getPrice()} {$PRODUCT->currencyCode}
							{/if}
							</td>
						</tr>
						<tr>
							<td class="py-2">{\App\Language::translate('LBL_SHOP_PACKAGE', $QUALIFIED_MODULE)} </td>
							<td class="py-2 w-50">{$VARIABLE_PRODUCT['os0']}</td>
						</tr>
						<tr>
							<td class="py-2">{\App\Language::translate('LBL_SHOP_SUBSCRIPTIONS_DAY', $QUALIFIED_MODULE)}</td>
							<td class="py-2 w-50">{$VARIABLE_PRODUCT['p3']}</td>
						</tr>
						<tr>
							<td class="py-2">{\App\Language::translate('LBL_SHOP_PAYMENT_FREQUENCY', $QUALIFIED_MODULE)}</td>
							<td class="py-2 w-50">{\App\Language::translate("LBL_SHOP_PAYMENT_FREQUENCY_{$VARIABLE_PRODUCT['t3']}", $QUALIFIED_MODULE)}</td>
						</tr>
					</tbody>
				</table>
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
	<p>{\App\Language::translate('LBL_SHOP_INVOICE_DETAILS_DESC', $QUALIFIED_MODULE)}</p>
	{if $COMPANY_DATA}
		<form class="js-update-company-form" name="EditCompanies" action="index.php" method="post" id="EditView" enctype="multipart/form-data">
			<input type="hidden" name="module" value="Companies">
			<input type="hidden" name="parent" value="Settings"/>
			<input type="hidden" name="action" value="SaveAjax"/>
			<input type="hidden" name="mode" value="updateCompany">
			<input type="hidden" name="record" value="{$COMPANY_DATA['id']}"/>
			<input type="hidden" name="id" value="{$COMPANY_DATA['id']}"/>
			<table class="table table-sm">
				<tbody class="u-word-break-all small">
					{foreach key="FIELD_NAME" item="FIELD" from=$FORM_FIELDS}
						{assign var="FIELD_MODEL" value=$RECORD->getFieldInstanceByName($FIELD_NAME, 'LBL_'|cat:$FIELD_NAME|upper)->set('fieldvalue',$RECORD->get($FIELD_NAME))}
						{if isset($FIELD['paymentData'])}
							<tr>
								<td class="align-middle">{\App\Language::translate('LBL_'|cat:$FIELD_NAME|upper, 'Settings:Companies')}</td>
								<td class="position-relative input-group-sm">
									{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName()) MODULE=$QUALIFIED_MODULE}
								</td>
							</tr>
						{/if}
					{/foreach}
				</tbody>
			</table>
		</form>
	{else}
		<div class="alert alert-info text-danger">
		<span class="fas fa-exclamation-triangle"></span>
			<a href="index.php?parent=Settings&module=Companies&view=List&block=3&fieldid=14">
				{\App\Language::translate('LBL_SHOP_NO_COMPANIES_ALERT', $QUALIFIED_MODULE)}
			</a>
		</div>
	{/if}
</div>
<!-- /tpl-Settings-YetiForce-Shop-BuyModal -->
{/strip}
