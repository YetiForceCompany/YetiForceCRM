{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-YetiForce-Shop-BuyModal -->
<div class="modal-body pb-0">
	{* <form  class="js-buy-form" action="{$PAYPAL_URL}" method="POST" target="_blank"> *}
		<div class="row no-gutters" >
			<div class="col-sm-18 col-md-12">
				<table class="table table-sm mb-0">
					<tbody class="u-word-break-all small">
						{foreach key=FIELD_NAME item=FIELD_DATA from=$PROVIDER->getCustomFields()}
							<tr>
								<td class="{$LABEL_CLASS} border-bottom">{App\Language::translate($FIELD_DATA['label'], $QUALIFIED_MODULE)}</td>
								<td class="py-2 position-relative w-50 border-bottom">
									<div class="input-group-sm position-relative">
										<input type="{$FIELD_DATA['type']}" class="form-control js-custom-field" placeholder="{App\Language::translate($FIELD_DATA['placeholder'], $QUALIFIED_MODULE)}" data-name="{$FIELD_NAME}"
										data-validation-engine="validate[{if isset($FIELD_DATA['validator'])}{$FIELD_DATA['validator']}{else}required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]{/if}]"/>
									</div>
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	{* </form> *}
</div>
<!-- /tpl-Settings-YetiForce-Shop-BuyModal -->
{/strip}
