{strip}
	<label class="col-sm-3 text-md-right u-text-small-bold pt-1">
		<span class="copyAddressLabel col-form-label mr-2 mb-1">{\App\Language::translate('COPY_ADRESS_FROM')}</span>
	</label>
		<button class="btn btn-sm btn-primary copyAddressFromAccount mr-2 mb-1 mb-sm-0" type="button"
				data-label="{$BLOCK_LABEL}">
			<strong>{\App\Language::translate('SINGLE_Accounts', $MODULE)}</strong>
		</button>
		<button class="btn btn-sm btn-primary copyAddressFromLead mr-2 mb-1 mb-sm-0" type="button" data-label="{$BLOCK_LABEL}">
			<strong>{\App\Language::translate('SINGLE_Leads', $MODULE)}</strong>
		</button>
		<button class="btn btn-sm btn-primary copyAddressFromVendor mr-2 mb-1 mb-sm-0" type="button"
				data-label="{$BLOCK_LABEL}">
			<strong>{\App\Language::translate('SINGLE_Vendors', $MODULE)}</strong>
		</button>
		{if {$MODULE_NAME} neq 'Contacts'}
			<button class="btn btn-sm btn-primary copyAddressFromContact mr-2 mb-1 mb-sm-0" type="button"
					data-label="{$BLOCK_LABEL}">
				<strong>{\App\Language::translate('SINGLE_Contacts', $MODULE)}</strong>
			</button>
		{/if}
		{if $BLOCK_LABEL neq 'LBL_ADDRESS_INFORMATION' && array_key_exists('LBL_ADDRESS_INFORMATION',$RECORD_STRUCTURE) && $RECORD_STRUCTURE['LBL_ADDRESS_INFORMATION']|@count }
			<button class="btn btn-sm btn-primary copyAddressFromMain mr-2 mb-1 mb-sm-0" type="button"
					data-label="LBL_ADDRESS_INFORMATION">
				<strong>{\App\Language::translate('LBL_ADDRESS_INFORMATION', $MODULE)}</strong>
			</button>
		{/if}
		{if $BLOCK_LABEL neq 'LBL_ADDRESS_MAILING_INFORMATION' && array_key_exists('LBL_ADDRESS_MAILING_INFORMATION',$RECORD_STRUCTURE) && $RECORD_STRUCTURE['LBL_ADDRESS_MAILING_INFORMATION']|@count}
			<button class="btn btn-sm btn-primary copyAddressFromMailing mr-2 mb-1 mb-sm-0" type="button"
					data-label="LBL_ADDRESS_MAILING_INFORMATION">
				<strong>{\App\Language::translate('LBL_ADDRESS_MAILING_INFORMATION', $MODULE)}</strong>
			</button>
		{/if}
		{if $BLOCK_LABEL neq 'LBL_ADDRESS_DELIVERY_INFORMATION' && array_key_exists('LBL_ADDRESS_DELIVERY_INFORMATION',$RECORD_STRUCTURE) && $RECORD_STRUCTURE['LBL_ADDRESS_DELIVERY_INFORMATION']|@count}
			<button class="btn btn-sm btn-primary copyAddressFromDelivery mr-2 mb-1 mb-sm-0" type="button"
					data-label="LBL_ADDRESS_DELIVERY_INFORMATION">
				<strong>{\App\Language::translate('LBL_ADDRESS_DELIVERY_INFORMATION', $MODULE)}</strong>
			</button>
		{/if}
		{if $APIADDRESFIELD}
			<div class="input-group mr-1">
				<input value=""
					   title="{\App\Language::translate('LBL_ADDRESS_INFORMATION')}"
					   type="text"
					   class="api_address_autocomplete form-control form-control-sm"
					   placeholder="{\App\Language::translate('LBL_ENTER_SEARCHED_ADDRESS')}"/>
				<div class="input-group-append">
						<span class="input-group-text">
							<span class="fas fa-search fa-fw"></span><span class="sr-only">Szukaj</span>
						</span>
				</div>
			</div>
		{/if}
{/strip}

