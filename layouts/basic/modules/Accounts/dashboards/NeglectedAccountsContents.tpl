{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if count($ACCOUNTS) > 0}
		{if $PAGING_MODEL->getCurrentPage() eq 1}
			<div class="row">
				<div class="col-4">
					<h6><b>{\App\Language::translate('Account Name' ,$MODULE_NAME)}</b></h6>
				</div>
				<div class="col-4">
					<h6><b>{\App\Language::translate('LBL_ASSIGNED_TO' ,$MODULE_NAME)}</b></h6>
				</div>
				<div class="col-4">
					<h6><b>{\App\Language::translate('LBL_CRMACTIVITY' ,$MODULE_NAME)}</b></h6>
				</div>
			</div>
			<hr>
		{/if}
		{foreach from=$ACCOUNTS key=RECORD_ID item=ACCOUNTS_MODEL}
			<div class="row px-0">
				<div class="col-4 u-text-ellipsis">
					{if \App\Privilege::isPermitted($MODULE_NAME, 'DetailView', $RECORD_ID)}
						<a href="index.php?module=Accounts&view=Detail&record={$RECORD_ID}">
							<b>{$ACCOUNTS_MODEL->getDisplayValue('accountname')}</b>
						</a>
					{else}
						{$ACCOUNTS_MODEL->getDisplayValue('accountname')}
					{/if}
				</div>
				<div class="col-4 u-text-ellipsis">
					{$ACCOUNTS_MODEL->getDisplayValue('assigned_user_id')}
				</div>
				<div class="col-4 u-text-ellipsis">
					{if is_null($ACCOUNTS_MODEL->get('crmactivity'))}
						-
					{else}
						{$ACCOUNTS_MODEL->getDisplayValue('crmactivity')}
					{/if}
				</div>
			</div>
		{/foreach}
		{if count($ACCOUNTS) eq $PAGING_MODEL->getPageLimit()}
			<div class="float-right padding5">
				<button type="button" class="btn btn-sm btn-primary showMoreHistory" data-url="{$WIDGET->getUrl()}&page={$PAGING_MODEL->getNextPage()}">{\App\Language::translate('LBL_MORE', $MODULE_NAME)}</button>
			</div>
		{/if}
	{else}
		{if $PAGING_MODEL->getCurrentPage() eq 1}
			<span class="noDataMsg">
				{\App\Language::translate('LBL_NO_RECORDS_MATCHED_THIS_CRITERIA')}
			</span>
		{/if}
	{/if}
{/strip}
