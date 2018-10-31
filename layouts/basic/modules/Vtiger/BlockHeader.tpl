{strip}
	<div class="mb-2 mb-lg-0 mx-2 mx-lg-0">
		<label class="text-md-right u-text-small-bold pt-1 mb-0">
			<span class="copyAddressLabel col-form-label mr-2">{\App\Language::translate('COPY_ADRESS_FROM')}</span>
		</label>
		<button class="btn btn-sm btn-primary c-btn-block-sm-down copyAddressFromAccount mr-2 mb-1 mb-md-0"
				type="button"
				data-label="{$BLOCK_LABEL}">
			<strong>{\App\Language::translate('SINGLE_Accounts', $MODULE)}</strong>
		</button>
		<button class="btn btn-sm btn-primary c-btn-block-sm-down copyAddressFromLead mr-2 mb-1 mb-md-0" type="button"
				data-label="{$BLOCK_LABEL}">
			<strong>{\App\Language::translate('SINGLE_Leads', 'Contacts')}</strong>
		</button>
		<button class="btn btn-sm btn-primary c-btn-block-sm-down copyAddressFromVendor mr-2 mb-1 mb-md-0" type="button"
				data-label="{$BLOCK_LABEL}">
			<strong>{\App\Language::translate('SINGLE_Vendors', 'Contacts')}</strong>
		</button>
		{if {$MODULE_NAME} neq 'Contacts'}
			<button class="btn btn-sm btn-primary c-btn-block-sm-down copyAddressFromContact mr-2 mb-1 mb-md-0"
					type="button"
					data-label="{$BLOCK_LABEL}">
				<strong>{\App\Language::translate('SINGLE_Contacts', $MODULE)}</strong>
			</button>
		{/if}
		{if $BLOCK_LABEL neq 'LBL_ADDRESS_INFORMATION' && array_key_exists('LBL_ADDRESS_INFORMATION',$RECORD_STRUCTURE) && $RECORD_STRUCTURE['LBL_ADDRESS_INFORMATION']|@count }
			<button class="btn btn-sm btn-primary c-btn-block-sm-down copyAddressFromMain mr-2 mb-1 mb-md-0"
					type="button"
					data-label="LBL_ADDRESS_INFORMATION">
				<strong>{\App\Language::translate('LBL_ADDRESS_INFORMATION', $MODULE)}</strong>
			</button>
		{/if}
		{if $BLOCK_LABEL neq 'LBL_ADDRESS_MAILING_INFORMATION' && array_key_exists('LBL_ADDRESS_MAILING_INFORMATION',$RECORD_STRUCTURE) && $RECORD_STRUCTURE['LBL_ADDRESS_MAILING_INFORMATION']|@count}
			<button class="btn btn-sm btn-primary c-btn-block-sm-down copyAddressFromMailing mr-2 mb-1 mb-md-0"
					type="button"
					data-label="LBL_ADDRESS_MAILING_INFORMATION">
				<strong>{\App\Language::translate('LBL_ADDRESS_MAILING_INFORMATION', $MODULE)}</strong>
			</button>
		{/if}
		{if $BLOCK_LABEL neq 'LBL_ADDRESS_DELIVERY_INFORMATION' && array_key_exists('LBL_ADDRESS_DELIVERY_INFORMATION',$RECORD_STRUCTURE) && $RECORD_STRUCTURE['LBL_ADDRESS_DELIVERY_INFORMATION']|@count}
			<button class="btn btn-sm btn-primary c-btn-block-sm-down copyAddressFromDelivery mr-2 mb-1 mb-md-0"
					type="button"
					data-label="LBL_ADDRESS_DELIVERY_INFORMATION">
				<strong>{\App\Language::translate('LBL_ADDRESS_DELIVERY_INFORMATION', $MODULE)}</strong>
			</button>
		{/if}
	</div>
	{assign var=PROVIDER value=\App\Map\Address::getProvider()}
	{if $SEARCH_ADDRESS && $PROVIDER}
		<div class="d-flex justify-content-center col-lg-4 mx-1 mx-lg-0">
			<div class="js-search-address input-group input-group-sm c-btn-block-sm-down" data-js="container">
				{if count($PROVIDER) > 1}
					<div class="input-group-prepend">
						<button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split"
								data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<span class="sr-only">Toggle Dropdown</span>
						</button>
						<div class="dropdown-menu">
							{foreach item=ROW from=$PROVIDER}
								<a class="dropdown-item js-select-operator" href="#" data-js="click"
								   data-type="{$ROW}">{App\Language::translate($ROW)}</a>
							{/foreach}
						</div>
					</div>
				{/if}
				{assign var=ADDRESS_FINDER_CONFIG value=\App\Map\Address::getConfig()}
				<input title="{\App\Language::translate('LBL_ADDRESS_INFORMATION')}" type="text"
					   placeholder="{\App\Language::translate('LBL_ENTER_SEARCHED_ADDRESS')}"
					   data-type="{\App\Map\Address::getDefaultProvider()}"
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
{/strip}

