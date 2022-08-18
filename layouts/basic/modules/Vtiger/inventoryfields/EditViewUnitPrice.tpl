{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewUnitPrice -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<div class="input-group input-group-sm">
		<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" value="{$FIELD->getEditValue($VALUE)}" title="{$FIELD->getEditValue($VALUE)}" type="text"
			data-maximumlength="{$FIELD->getRangeValues()}"
			data-validation-engine="validate[required,funcCall[Vtiger_Currency_Validator_Js.invokeValidation]]"
			data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD->getFieldInfo()))}"
			class="unitPrice smallInputBox form-control form-control-sm" list-info="" {if $FIELD->isReadOnly()}readonly="readonly" {/if} />

		{assign var=PRICEBOOK_MODULE_MODEL value=Vtiger_Module_Model::getInstance('PriceBooks')}
		{if $PRICEBOOK_MODULE_MODEL->isPermitted('DetailView')}
			<div class="input-group-append">
				<button class="btn btn-light js-price-book-modal js-popover-tooltip" data-js="popover|click" data-content="{\App\Language::translate('PriceBooks',$MODULE)}" type="button">
					<span class="yfm-PriceBooks" data-popup="Popup" data-module-name="PriceBooks" alt="{\App\Language::translate('PriceBooks',$MODULE)}" />
				</button>
				</span>
			</div>
		{/if}
	</div>
	<!-- /tpl-Base-inventoryfields-EditViewUnitPrice -->
{/strip}
