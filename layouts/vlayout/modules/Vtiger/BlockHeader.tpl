{strip}
<span class="copyAddressLabel pull-left control-label">{vtranslate('COPY_ADRESS_FROM')}</span>	
<div class="btn-toolbar">
	<button class="btn btn-primary copyAddressFromAccount" type="button" data-label="{$BLOCK_LABEL}"><strong>{vtranslate('SINGLE_Accounts', $MODULE)}</strong></button>
	<button class="btn btn-primary copyAddressFromLead" type="button" data-label="{$BLOCK_LABEL}"><strong>{vtranslate('SINGLE_Leads', $MODULE)}</strong></button>
	<button class="btn btn-primary copyAddressFromVendor" type="button" data-label="{$BLOCK_LABEL}"><strong>{vtranslate('SINGLE_Vendors', $MODULE)}</strong></button>
	{if {$MODULE_NAME} neq 'Contacts'}
		<button class="btn btn-primary copyAddressFromContact" type="button" data-label="{$BLOCK_LABEL}"><strong>{vtranslate('SINGLE_Contacts', $MODULE)}</strong></button>
	{/if}
	{if $BLOCK_LABEL neq 'LBL_ADDRESS_INFORMATION' && array_key_exists('LBL_ADDRESS_INFORMATION',$RECORD_STRUCTURE) && $RECORD_STRUCTURE['LBL_ADDRESS_INFORMATION']|@count }
		<button class="btn btn-primary copyAddressFromMain" type="button" data-label="LBL_ADDRESS_INFORMATION">
			<strong>{vtranslate('LBL_ADDRESS_INFORMATION', $MODULE)}</strong>
		</button>
	{/if}
	{if $BLOCK_LABEL neq 'LBL_ADDRESS_MAILING_INFORMATION' && array_key_exists('LBL_ADDRESS_MAILING_INFORMATION',$RECORD_STRUCTURE) && $RECORD_STRUCTURE['LBL_ADDRESS_MAILING_INFORMATION']|@count}
		<button class="btn btn-primary copyAddressFromMailing" type="button" data-label="LBL_ADDRESS_MAILING_INFORMATION">
			<strong>{vtranslate('LBL_ADDRESS_MAILING_INFORMATION', $MODULE)}</strong>
		</button>
	{/if}
	{if $BLOCK_LABEL neq 'LBL_ADDRESS_DELIVERY_INFORMATION' && array_key_exists('LBL_ADDRESS_DELIVERY_INFORMATION',$RECORD_STRUCTURE) && $RECORD_STRUCTURE['LBL_ADDRESS_DELIVERY_INFORMATION']|@count}
		<button class="btn btn-primary copyAddressFromDelivery" type="button" data-label="LBL_ADDRESS_DELIVERY_INFORMATION">
			<strong>{vtranslate('LBL_ADDRESS_DELIVERY_INFORMATION', $MODULE)}</strong>
		</button>
	{/if}
</div>
{/strip}
