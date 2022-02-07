{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if count($NEW_ACCOUNTS) > 0}
		{if $PAGING_MODEL->getCurrentPage() eq 1}
			<div class="row">
				<div class="col-4">
					<h6><b>{\App\Language::translate('Account Name' ,$MODULE_NAME)}</b></h6>
				</div>
				<div class="col-4">
					<h6><b>{\App\Language::translate('LBL_ASSIGNED_TO' ,$MODULE_NAME)}</b></h6>
				</div>
				<div class="col-4">
					<h6><b>{\App\Language::translate('Created Time' ,$MODULE_NAME)}</b></h6>
				</div>
			</div>
			<hr>
		{/if}
		{foreach from=$NEW_ACCOUNTS key=RECORD_ID item=ACCOUNTS_MODEL}
			<div class="row paddingLRZero">
				<div class="col-4">
					{if \App\Privilege::isPermitted($MODULE_NAME, 'DetailView', $RECORD_ID)}
						<a href="index.php?module=Accounts&view=Detail&record={$RECORD_ID}">
							<b>{\App\Purifier::encodeHtml($ACCOUNTS_MODEL['accountname'])}</b>
						</a>
					{else}
						{\App\Purifier::encodeHtml($ACCOUNTS_MODEL['accountname'])}
					{/if}
				</div>
				<div class="col-4">
					{$ACCOUNTS_MODEL['userModel']->getName()}
				</div>
				<div class="col-4">
					<span>
						{\App\Fields\DateTime::formatToViewDate($ACCOUNTS_MODEL['createdtime'])}
					</span>
				</div>
			</div>
		{/foreach}
		{if count($NEW_ACCOUNTS) eq $PAGING_MODEL->getPageLimit()}
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
