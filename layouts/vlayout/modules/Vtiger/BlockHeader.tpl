{strip}
    <span class="copyAddressLabel">{vtranslate('COPY_ADRESS_FROM')}</span>
&nbsp;&nbsp;
<span class="btn btn-primary copyAddressFromAccount" data-label="{$BLOCK_LABEL}"><strong>{vtranslate('SINGLE_Accounts', $MODULE)}</strong></span>
&nbsp;&nbsp;
<span class="btn btn-primary copyAddressFromLead" data-label="{$BLOCK_LABEL}"><strong>{vtranslate('SINGLE_Leads', $MODULE)}</strong></span>
&nbsp;&nbsp;
<span class="btn btn-primary copyAddressFromVendor" data-label="{$BLOCK_LABEL}"><strong>{vtranslate('SINGLE_Vendors', $MODULE)}</strong></span>
&nbsp;&nbsp;
{if {$MODULE_NAME} neq 'Contacts'}
	<span class="btn btn-primary copyAddressFromContact" data-label="{$BLOCK_LABEL}"><strong>{vtranslate('SINGLE_Contacts', $MODULE)}</strong></span>
	&nbsp;&nbsp;
{/if}	

{if $BLOCK_LABEL neq 'LBL_ADDRESS_INFORMATION' && array_key_exists('LBL_ADDRESS_INFORMATION',$RECORD_STRUCTURE) }
	<span class="btn btn-primary copyAddressFromMain" data-label="LBL_ADDRESS_INFORMATION">
		<strong>{vtranslate('LBL_ADDRESS_INFORMATION', $MODULE)}</strong>
	</span>
{/if}
&nbsp;&nbsp;
{if $BLOCK_LABEL neq 'LBL_ADDRESS_MAILING_INFORMATION' && array_key_exists('LBL_ADDRESS_MAILING_INFORMATION',$RECORD_STRUCTURE) }
	<span class="btn btn-primary copyAddressFromMailing" data-label="LBL_ADDRESS_MAILING_INFORMATION">
		<strong>{vtranslate('LBL_ADDRESS_MAILING_INFORMATION', $MODULE)}</strong>
	</span>
{/if}
&nbsp;&nbsp;
{if $BLOCK_LABEL neq 'LBL_ADDRESS_DELIVERY_INFORMATION' && array_key_exists('LBL_ADDRESS_DELIVERY_INFORMATION',$RECORD_STRUCTURE)}
	<span class="btn btn-primary copyAddressFromDelivery" data-label="LBL_ADDRESS_DELIVERY_INFORMATION">
		<strong>{vtranslate('LBL_ADDRESS_DELIVERY_INFORMATION', $MODULE)}</strong>
	</span>
{/if}
{/strip}