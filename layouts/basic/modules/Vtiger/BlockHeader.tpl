{strip}
	<!-- tpl-Base-BlockHeader.tpl -->
	<div class="mb-2 mb-lg-0 mx-2 mx-lg-0">
		<label class="text-md-right u-text-small-bold pt-1 mb-0">
			<span class="copyAddressLabel col-form-label mr-2">{\App\Language::translate('COPY_ADRESS_FROM')}</span>
		</label>
		{if $BLOCK_LABEL neq 'LBL_ADDRESS_SHIPPING' && !empty($RECORD_STRUCTURE['LBL_ADDRESS_SHIPPING'])}
			<button class="btn btn-sm btn-primary c-btn-block-sm-down copyAddressFromDelivery mr-2 mb-1 mb-md-0" type="button" data-label="LBL_ADDRESS_SHIPPING" data-js="class: d-none">
				<strong>{\App\Language::translate('LBL_ADDRESS_SHIPPING', $MODULE)}</strong>
			</button>
		{/if}
		{if $BLOCK_LABEL neq 'LBL_ADDRESS_BILLING' && !empty($RECORD_STRUCTURE['LBL_ADDRESS_BILLING'])}
			<button class="btn btn-sm btn-primary c-btn-block-sm-down copyAddressFromDelivery mr-2 mb-1 mb-md-0" type="button" data-label="LBL_ADDRESS_BILLING" data-js="class: d-none">
				<strong>{\App\Language::translate('LBL_ADDRESS_BILLING', $MODULE)}</strong>
			</button>
		{/if}
		{if $BLOCK_LABEL neq 'LBL_ADDRESS_INFORMATION' && !empty($RECORD_STRUCTURE['LBL_ADDRESS_INFORMATION'])}
			<button class="btn btn-sm btn-primary c-btn-block-sm-down copyAddressFromMain mr-2 mb-1 mb-md-0" type="button" data-label="LBL_ADDRESS_INFORMATION" data-js="class: d-none">
				<strong>{\App\Language::translate('LBL_ADDRESS_INFORMATION', $MODULE)}</strong>
			</button>
		{/if}
		{if $BLOCK_LABEL neq 'LBL_ADDRESS_MAILING_INFORMATION' && !empty($RECORD_STRUCTURE['LBL_ADDRESS_MAILING_INFORMATION'])}
			<button class="btn btn-sm btn-primary c-btn-block-sm-down copyAddressFromMailing mr-2 mb-1 mb-md-0" type="button" data-label="LBL_ADDRESS_MAILING_INFORMATION" data-js="class: d-none">
				<strong>{\App\Language::translate('LBL_ADDRESS_MAILING_INFORMATION', $MODULE)}</strong>
			</button>
		{/if}
		{if $BLOCK_LABEL neq 'LBL_ADDRESS_DELIVERY_INFORMATION' && !empty($RECORD_STRUCTURE['LBL_ADDRESS_DELIVERY_INFORMATION'])}
			<button class="btn btn-sm btn-primary c-btn-block-sm-down copyAddressFromDelivery mr-2 mb-1 mb-md-0" type="button" data-label="LBL_ADDRESS_DELIVERY_INFORMATION" data-js="class: d-none">
				<strong>{\App\Language::translate('LBL_ADDRESS_DELIVERY_INFORMATION', $MODULE)}</strong>
			</button>
		{/if}
		<button class="btn btn-sm btn-primary c-btn-block-sm-down copyAddressFromAccount mr-2 mb-1 mb-md-0 d-none" type="button" data-label="{$BLOCK_LABEL}" data-js="class: d-none">
			<strong>{\App\Language::translate('SINGLE_Accounts', $MODULE)}</strong>
		</button>
		<button class="btn btn-sm btn-primary c-btn-block-sm-down copyAddressFromLead mr-2 mb-1 mb-md-0 d-none" type="button" data-label="{$BLOCK_LABEL}" data-js="class: d-none">
			<strong>{\App\Language::translate('SINGLE_Leads', 'Contacts')}</strong>
		</button>
		<button class="btn btn-sm btn-primary c-btn-block-sm-down copyAddressFromVendor mr-2 mb-1 mb-md-0 d-none" type="button" data-label="{$BLOCK_LABEL}" data-js="class: d-none">
			<strong>{\App\Language::translate('SINGLE_Vendors', 'Contacts')}</strong>
		</button>
		{if {$MODULE_NAME} neq 'Contacts'}
			<button class="btn btn-sm btn-primary c-btn-block-sm-down copyAddressFromContact mr-2 mb-1 mb-md-0 d-none" type="button" data-label="{$BLOCK_LABEL}" data-js="class: d-none">
				<strong>{\App\Language::translate('SINGLE_Contacts', $MODULE)}</strong>
			</button>
		{/if}
	</div>
	{if $SEARCH_ADDRESS && $PROVIDER}
		<div class="d-flex justify-content-center col-lg-8 mx-1 mx-lg-0">
			<div class="js-search-address input-group input-group-sm c-btn-block-sm-down" data-js="container">
				<div class="input-group-prepend{if count($PROVIDER) eq 1} d-none{/if}">
					<select class="select2 js-select-operator" data-dropdown-auto-width="true" data-js="value">
						{foreach item=ROW from=$PROVIDER}
							<option value="{$ROW}" {if \App\Map\Address::getDefaultProvider() eq $ROW}selected{/if}>{\App\Language::translate('LBL_PROVIDER_'|cat:$ROW|upper, 'Settings:ApiAddress')}</option>
						{/foreach}
					</select>
				</div>
				{assign var=ADDRESS_FINDER_CONFIG value=\App\Map\Address::getConfig()}
				<input title="{\App\Language::translate('LBL_ADDRESS_INFORMATION')}" type="search"
					   placeholder="{\App\Language::translate('LBL_ENTER_SEARCHED_ADDRESS')}"
					   data-min="{$ADDRESS_FINDER_CONFIG['global']['min_length']}"
					   class="js-autoload-address form-control" data-js="autocomplete"
				/>
				<div class="input-group-append">
					<span class="input-group-text">
						<span class="fas fa-search fa-fw"></span>
						<span class="sr-only">{App\Language::translate('LBL_SEARCH')}</span>
					</span>
				</div>
			</div>
		</div>
	{/if}
	<!-- /tpl-Base-BlockHeader.tpl -->
{/strip}
