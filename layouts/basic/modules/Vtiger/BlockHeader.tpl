{strip}
	<div class="col-md-12 mb-2">
		<label class="text-md-right u-text-small-bold pt-1">
			<span class="copyAddressLabel col-form-label mr-2 mb-1">{\App\Language::translate('COPY_ADRESS_FROM')}</span>
		</label>
		<button class="btn btn-sm btn-primary c-btn-block-sm copyAddressFromAccount mr-2 mb-1" type="button"
				data-label="{$BLOCK_LABEL}">
			<strong>{\App\Language::translate('SINGLE_Accounts', $MODULE)}</strong>
		</button>
		<button class="btn btn-sm btn-primary c-btn-block-sm copyAddressFromLead mr-2 mb-1" type="button" data-label="{$BLOCK_LABEL}">
			<strong>{\App\Language::translate('SINGLE_Leads', 'Contacts')}</strong>
		</button>
		<button class="btn btn-sm btn-primary c-btn-block-sm copyAddressFromVendor mr-2 mb-1" type="button"
				data-label="{$BLOCK_LABEL}">
			<strong>{\App\Language::translate('SINGLE_Vendors', 'Contacts')}</strong>
		</button>
		{if {$MODULE_NAME} neq 'Contacts'}
			<button class="btn btn-sm btn-primary c-btn-block-sm copyAddressFromContact mr-2 mb-1" type="button"
					data-label="{$BLOCK_LABEL}">
				<strong>{\App\Language::translate('SINGLE_Contacts', $MODULE)}</strong>
			</button>
		{/if}
		{if $BLOCK_LABEL neq 'LBL_ADDRESS_INFORMATION' && array_key_exists('LBL_ADDRESS_INFORMATION',$RECORD_STRUCTURE) && $RECORD_STRUCTURE['LBL_ADDRESS_INFORMATION']|@count }
			<button class="btn btn-sm btn-primary c-btn-block-sm copyAddressFromMain mr-2 mb-1" type="button"
					data-label="LBL_ADDRESS_INFORMATION">
				<strong>{\App\Language::translate('LBL_ADDRESS_INFORMATION', $MODULE)}</strong>
			</button>
		{/if}
		{if $BLOCK_LABEL neq 'LBL_ADDRESS_MAILING_INFORMATION' && array_key_exists('LBL_ADDRESS_MAILING_INFORMATION',$RECORD_STRUCTURE) && $RECORD_STRUCTURE['LBL_ADDRESS_MAILING_INFORMATION']|@count}
			<button class="btn btn-sm btn-primary c-btn-block-sm copyAddressFromMailing mr-2 mb-1" type="button"
					data-label="LBL_ADDRESS_MAILING_INFORMATION">
				<strong>{\App\Language::translate('LBL_ADDRESS_MAILING_INFORMATION', $MODULE)}</strong>
			</button>
		{/if}
		{if $BLOCK_LABEL neq 'LBL_ADDRESS_DELIVERY_INFORMATION' && array_key_exists('LBL_ADDRESS_DELIVERY_INFORMATION',$RECORD_STRUCTURE) && $RECORD_STRUCTURE['LBL_ADDRESS_DELIVERY_INFORMATION']|@count}
			<button class="btn btn-sm btn-primary c-btn-block-sm copyAddressFromDelivery mr-2 mb-1" type="button"
					data-label="LBL_ADDRESS_DELIVERY_INFORMATION">
				<strong>{\App\Language::translate('LBL_ADDRESS_DELIVERY_INFORMATION', $MODULE)}</strong>
			</button>
		{/if}
	</div>
	{if $APIADDRESFIELD}
	<div class="col-md-12 d-flex justify-content-center">
		<div class="input-group c-btn-block-sm col-md-4">
			<input value="" title="{\App\Language::translate('LBL_ADDRESS_INFORMATION')}" type="text"
				   class="api_address_autocomplete form-control form-control-sm"
				   placeholder="{\App\Language::translate('LBL_ENTER_SEARCHED_ADDRESS')}"/>
			<div class="input-group-append">
					<span class="input-group-text">
						<span class="fas fa-search fa-fw"></span><span class="sr-only">Szukaj</span>
					</span>
			</div>
		</div>
	</div>
	{/if}
{/strip}

