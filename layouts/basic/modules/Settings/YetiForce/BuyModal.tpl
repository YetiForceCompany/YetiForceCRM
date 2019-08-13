{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-YetiForce-Shop-BuyModal -->
<div class="modal-body px-md-5 pb-0">
	<form  class="js-buy-form" action="{$PAYPAL_URL}" method="POST" target="_blank">
		<div class="row no-gutters" >
			<div class="col-sm-18 col-md-12">
				<div class="text-center pb-3 pb-md-5">
					{if $PRODUCT->getImage()}
						<img class="o-buy-modal__img" src="{$PRODUCT->getImage()}" alt="{\App\Purifier::encodeHtml($PRODUCT->getLabel())}" title="{\App\Purifier::encodeHtml($PRODUCT->getLabel())}"/>
					{else}
						<div class="product-no-image m-auto">
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
							<td class="py-2 u-font-weight-550">{\App\Language::translate('LBL_SHOP_PRODUCT_NAME', $QUALIFIED_MODULE)}</td>
							<td class="py-2 w-50">{$PRODUCT->getLabel()}</td>
						</tr>
						<tr>
							<td class="py-2 u-font-weight-550 align-middle">{\App\Language::translate('LBL_SHOP_AMOUNT', $QUALIFIED_MODULE)}</td>
							{if 'manual'=== $PRODUCT->getPriceType()}
								<td class="w-50">
									<input name="a3" class="form-control form-control-sm" type="text" value="{$PRODUCT->getPrice()}" aria-label="price">
							{else}
								<td class="py-2 w-50">
								{$PRODUCT->getPrice(true)} {$CURRENCY}
							{/if}
							</td>
						</tr>
						<tr>
							<td class="py-2 u-font-weight-550">{\App\Language::translate('LBL_SHOP_PACKAGE', $QUALIFIED_MODULE)} </td>
							<td class="py-2 w-50">{$VARIABLE_PRODUCT['os0']}</td>
						</tr>
						<tr>
							<td class="py-2 u-font-weight-550">{\App\Language::translate('LBL_SHOP_SUBSCRIPTIONS_DAY', $QUALIFIED_MODULE)}</td>
							<td class="py-2 w-50">{$VARIABLE_PRODUCT['p3']}</td>
						</tr>
						<tr>
							<td class="py-2 u-font-weight-550 border-bottom">{\App\Language::translate('LBL_SHOP_PAYMENT_FREQUENCY', $QUALIFIED_MODULE)}</td>
							<td class="py-2 w-50 border-bottom">{\App\Language::translate("LBL_SHOP_PAYMENT_FREQUENCY_{$VARIABLE_PRODUCT['t3']}", $QUALIFIED_MODULE)}</td>
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
	{if $COMPANY_DATA}
		<p class="small text-truncate my-4 py-1">
		<span class="u-font-weight-550 mr-1">
			{\App\Language::translate('LBL_SHOP_INVOICE_DETAILS', $QUALIFIED_MODULE)}
		</span>
			({\App\Language::translate('LBL_SHOP_INVOICE_DETAILS_DESC', $QUALIFIED_MODULE)})
		</p>
		<form class="js-update-company-form" name="EditCompanies" action="index.php" method="post" id="EditView" enctype="multipart/form-data">
			<input type="hidden" name="module" value="Companies">
			<input type="hidden" name="parent" value="Settings"/>
			<input type="hidden" name="action" value="SaveAjax"/>
			<input type="hidden" name="mode" value="updateCompany">
			<input type="hidden" name="record" value="{$COMPANY_DATA['id']}"/>
			<input type="hidden" name="id" value="{$COMPANY_DATA['id']}"/>
			<table class="table table-sm mb-0">
				<tbody class="u-word-break-all small">
					{foreach key="FIELD_NAME" item="FIELD" from=$FORM_FIELDS name=updateCompanyForm}
						{assign var="FIELD_MODEL" value=$RECORD->getFieldInstanceByName($FIELD_NAME, 'LBL_'|cat:$FIELD_NAME|upper)->set('fieldvalue',$RECORD->get($FIELD_NAME))}
						<tr>
							<td class="align-middle u-font-weight-550{if $smarty.foreach.updateCompanyForm.last} border-bottom{/if}">{\App\Language::translate('LBL_'|cat:$FIELD_NAME|upper, 'Settings:Companies')}</td>
							<td class="w-50 position-relative input-group-sm{if $smarty.foreach.updateCompanyForm.last} border-bottom{/if}">
								{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName()) MODULE=$QUALIFIED_MODULE}
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</form>
	{elseif $INSTALL_MODE}
		<p class="small u-font-weight-550 text-truncate my-4 py-1 text-center">
			{\App\Language::translate('LBL_SHOP_COMPANY_DATA', $QUALIFIED_MODULE)}
		</p>
		<form class="js-company-form" name="Company" action="index.php" method="post"enctype="multipart/form-data">
			<table class="table table-sm mb-0">
				<tbody class="u-word-break-all small">
					{foreach key="FIELD_NAME" item="FIELD" from=$FORM_FIELDS name=updateCompanyForm}
						<tr>
							<td class="align-middle u-font-weight-550{if $smarty.foreach.updateCompanyForm.last} border-bottom{/if}">{\App\Language::translate('LBL_'|cat:$FIELD_NAME|upper, 'Settings:Companies')}</td>
							<td class="w-50 position-relative input-group-sm{if $smarty.foreach.updateCompanyForm.last} border-bottom{/if}">
									<input name="{$FIELD_NAME}" class="form-control form-control-sm" type="text" value="" aria-label="{\App\Language::translate('LBL_'|cat:$FIELD_NAME|upper, 'Settings:Companies')}">
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
	{else}
		<div class="alert alert-danger mb-0">
			<span class="fas fa-exclamation-triangle mr-1"></span>
			{\App\Language::translate('LBL_SHOP_NO_COMPANIES_ALERT', $QUALIFIED_MODULE)}
			<div class="d-flex justify-content-center w-100 pt-1">
				<a class="btn btn-primary" href="index.php?parent=Settings&module=Companies&view=List&block=3&fieldid=14" target="_blank">
					<span class="fas fa-edit mr-1"></span>
					{\App\Language::translate('LBL_SHOP_NO_COMPANIES_BUTTON', $QUALIFIED_MODULE)}
				</a>
			</div>
		</div>
	{/if}
</div>
<!-- /tpl-Settings-YetiForce-Shop-BuyModal -->
{/strip}
