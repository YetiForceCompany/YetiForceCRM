{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Components-CurrencyConverter-->
	<div class="modal-body">
		<form id="editCurrency" class="form-horizontal" method="POST">
			<div class="form-group">
				<div class="input-group {$WIDTHTYPE_GROUP}">
					<div class="input-group-prepend">
						<button class="btn btn-outline-secondary clipboard js-popover-tooltip" type="button" data-placement="top" data-content="{\App\Language::translate('BTN_COPY_TO_CLIPBOARD')}" data-copy-target="#currency_target_value">
							<span class="fas fa-copy"></span>
						</button>
					</div>
					<input id="currency_target_value" name="currency_target_value" type="text" class="marginLeftZero form-control js-format-numer js-currencyc_value" data-validation-engine="validate[required,funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" value="{$AMOUNT}" />
					<div class="input-group-append u-min-w-150pxr">
						<select class="select2 js-currencyc_list" name="currency_target_id" title="{\App\Language::translate('LBL_RELATED_MODULE_TYPE')}" required="required" readonly="readonly">
							{foreach key=KEY item=CURRENCY from=$CURRENCIES}
								{if $KEY neq $CURRENCY_BASE['id']}{continue}{/if}
								<option value="{$KEY}" data-conversion-rate="{$CURRENCY.conversion|escape}"
									data-conversion-date="{$CURRENCY.date|escape}"
									data-currency-value="{$CURRENCY.value|escape}"
									data-currency-id="{$KEY}">{\App\Language::translate($CURRENCY.currency_code, 'Other.Currency')} ({$CURRENCY.currency_symbol|escape})
								</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
			<div class="form-group row mb-1">
				<div class="col-md-6">
					<label class="mr-1 mb-1">{\App\Language::translate('LBL_EXCHANGE_RATE', $MODULE_NAME)}:</label>
					<input id="base_rate_value" name="base_rate_value" type="hidden" class="js-currency-rate" value="" />
					<input id="base_conv_rate_value" name="base_conv_rate_value" type="hidden" class="js-currency-conv-rate" value="" />
					<span class="js-currency-rate_text"></span>
				</div>
				<div class="col-md-6">
					<label class="mr-1 mb-1">{\App\Language::translate('LBL_EXCHANGE_DATE', $MODULE_NAME)}:</label>
					<input id="base_data_value" name="base_data_value" type="hidden" class="js-currency-date" value="" />
					<span class="js-currency-date_text"></span>
				</div>
			</div>
			<div class="form-group">
				<div class="input-group {$WIDTHTYPE_GROUP}">
					<div class="input-group-prepend">
						<button class="btn btn-outline-secondary clipboard js-popover-tooltip" type="button" data-placement="top" data-content="{\App\Language::translate('BTN_COPY_TO_CLIPBOARD')}" data-copy-target="#currency_base_value">
							<span class="fas fa-copy"></span>
						</button>
					</div>
					<input id="currency_base_value" name="currency_base_value" type="text" class="marginLeftZero form-control js-format-numer js-currencyc_value" data-validation-engine="validate[required,funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" value="0" />
					<div class="input-group-append u-min-w-150pxr">
						<select class="select2 js-currencyc_list" name="currency_base_id" title="{\App\Language::translate('LBL_RELATED_MODULE_TYPE')}" required="required">
							{foreach key=KEY item=CURRENCY from=$CURRENCIES}
								{if $KEY eq $CURRENCY_BASE['id']}{continue}{/if}
								<option value="{$KEY}" {if $KEY eq $CURRENCY_ID} selected {/if}
									data-conversion-rate="{$CURRENCY.conversion|escape}"
									data-conversion-date="{$CURRENCY.date|escape}"
									data-currency-value="{$CURRENCY.value|escape}"
									data-currency-id="{$KEY}">
									{\App\Language::translate($CURRENCY.currency_code, 'Other.Currency')} ({$CURRENCY.currency_symbol|escape})
								</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Components-CurrencyConverter-->
{/strip}
