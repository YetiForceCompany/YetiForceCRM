{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-YetiForce-BuyModal -->
	{assign var=LABEL_CLASS value='py-2 u-font-weight-550 align-middle'}
	{assign var=PRICE_TYPE value=$PRODUCT->getPriceType()}
	<div class="modal-body pb-0">
		<form class="js-buy-form" action="{$PAYPAL_URL}" method="POST" target="_blank">
			<div class="row no-gutters">
				<div class="col-sm-18 col-md-12">
					<div class="text-center pb-3">
						{if $IMAGE}
							<img class="o-buy-modal__img" src="{$IMAGE}" alt="{\App\Purifier::encodeHtml($PRODUCT->getLabel())}" title="{\App\Purifier::encodeHtml($PRODUCT->getLabel())}" />
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
								<td class="{$LABEL_CLASS}">{\App\Language::translate('LBL_SHOP_PRODUCT_NAME', $QUALIFIED_MODULE)}</td>
								<td class="py-2 w-50">{$PRODUCT->getLabel()}</td>
							</tr>
							<tr>
								<td class="{$LABEL_CLASS}">{\App\Language::translate('LBL_SHOP_AMOUNT', $QUALIFIED_MODULE)}</td>
								{if 'manual'=== $PRICE_TYPE}
									<td class="w-50">
										<div class="input-group input-group-sm">
											<input name="a3" class="form-control" type="text" value="{$PRODUCT->getPrice()}" aria-label="price" aria-describedby="currency">
											<div class="input-group-append">
												<span class="input-group-text" id="currency">{$CURRENCY}</span>
											</div>
										</div>
									{elseif 'selection'=== $PRICE_TYPE}
									<td class="w-50 input-group-sm">
										<input class="js-price-by-size-input" name="os0" type="hidden" value="{key($PRODUCT->prices)}" data-js="val">
										<select class="select2 form-control js-price-by-size" name="a3" data-js="container">
											{foreach key=KEY item=PRICE from=$PRODUCT->prices}
												{if isset($PRODUCT->customPricesLabel[$KEY])}
													<option value="{$PRICE}" data-os0="{$PRODUCT->customPricesLabel[$KEY]}">{$PRODUCT->getPriceLabel($KEY)}: {$PRICE} {$CURRENCY}</option>
												{else}
													<option value="{$PRICE}" data-os0="{$KEY}">{$PRODUCT->getPriceLabel($KEY)}: {$PRICE} {$CURRENCY}</option>
												{/if}
											{/foreach}
										</select>
									{else}
									<td class="py-2 w-50">
										{$PRODUCT->getPrice()} {$CURRENCY}
									{/if}
								</td>
							</tr>
							{if 'selection'!== $PRICE_TYPE}
								<tr>
									<td class="{$LABEL_CLASS}">{\App\Language::translate('LBL_SHOP_PACKAGE', $QUALIFIED_MODULE)} </td>
									<td class="py-2 w-50">{$PRODUCT->getPriceLabel($VARIABLE['os0'])}</td>
								</tr>
							{/if}
							<tr>
								<td class="{$LABEL_CLASS} border-bottom">{\App\Language::translate('LBL_SHOP_PAYMENT_FREQUENCY', $QUALIFIED_MODULE)}</td>
								<td class="py-2 w-50 border-bottom">{\App\Language::translate("LBL_SHOP_PAYMENT_FREQUENCY_{$VARIABLE['t3']}", $QUALIFIED_MODULE)}</td>
							</tr>
							{foreach key=FIELD_NAME item=FIELD_DATA from=$PRODUCT->getCustomFields()}
								<tr>
									<td class="{$LABEL_CLASS} border-bottom">{App\Language::translate($FIELD_DATA['label'], $QUALIFIED_MODULE)}</td>
									<td class="py-2 w-50 border-bottom">
										<div {if isset($FIELD_DATA['info'])}class="js-popover-tooltip" data-toggle="popover" data-trigger="focus" data-content="{App\Language::translate($FIELD_DATA['info'], $QUALIFIED_MODULE)}" {/if}>
											<div class="input-group input-group-sm position-relative">
												<input type="{$FIELD_DATA['type']}" class="form-control js-custom-field" placeholder="{App\Language::translate($FIELD_DATA['label'], $QUALIFIED_MODULE)}" data-name="{$FIELD_NAME}"
													data-validation-engine="validate[{if isset($FIELD_DATA['validator'])}{$FIELD_DATA['validator']}{else}required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]{/if}]" />
												{if isset($FIELD_DATA['append'])}
													<div class="input-group-append">
														<span class="input-group-text">{$FIELD_DATA['append']}</span>
													</div>
												{/if}
											</div>
										</div>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
					{if $IS_CUSTOM}
						<input name="custom" type="hidden" class="js-custom-data" value="" data-js="value">
					{/if}
					{foreach key=NAME_OF_KEY item=VALUE from=$VARIABLE}
						<input name="{$NAME_OF_KEY}" type="hidden" value="{$VALUE}" />
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
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="action" value="SaveAjax" />
				<input type="hidden" name="mode" value="updateCompany">
				<input type="hidden" name="record" value="{$COMPANY_DATA['id']}" />
				<input type="hidden" name="id" value="{$COMPANY_DATA['id']}" />
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
		{elseif !$IS_CUSTOM}
			<div class="alert alert-danger mb-0 mt-3">
				<span class="fas fa-exclamation-triangle mr-1"></span>
				{\App\Language::translate('LBL_SHOP_NO_COMPANIES_ALERT', $QUALIFIED_MODULE)}
				<div class="d-flex justify-content-center w-100 pt-1">
					<a class="btn btn-primary" href="index.php?parent=Settings&module=Companies&view=List&block=3&fieldid=14" target="_blank">
						<span class="yfi yfi-full-editing-view mr-1"></span>
						{\App\Language::translate('LBL_SHOP_NO_COMPANIES_BUTTON', $QUALIFIED_MODULE)}
					</a>
				</div>
			</div>
		{/if}
	</div>
	<!-- /tpl-Settings-YetiForce-Shop-BuyModal -->
{/strip}
