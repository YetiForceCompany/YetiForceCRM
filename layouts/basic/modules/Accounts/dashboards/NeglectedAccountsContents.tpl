{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if count($ACCOUNTS) > 0}
		{if $PAGING_MODEL->getCurrentPage() eq 1}
			<div class="col-xs-4">
				<h6><b>{vtranslate('Account Name' ,$MODULE_NAME)}</b></h6>
			</div>
			<div class="col-xs-4">
				<h6><b>{vtranslate('LBL_ASSIGNED_TO' ,$MODULE_NAME)}</b></h6>
			</div>
			<div class="col-xs-4">
				<h6><b>{vtranslate('LBL_CRMACTIVITY' ,$MODULE_NAME)}</b></h6>
			</div>
			<div class="col-xs-12"><hr></div>
			{/if}
			{foreach from=$ACCOUNTS key=RECORD_ID item=ACCOUNTS_MODEL}
				<div class="col-xs-12 paddingLRZero">
					<div class="col-xs-4 textOverflowEllipsis">
						{if Users_Privileges_Model::isPermitted($MODULE_NAME, 'DetailView', $RECORD_ID)}
							<a href="index.php?module=Accounts&view=Detail&record={$RECORD_ID}">
								<b>{$ACCOUNTS_MODEL['accountname']}</b>
							</a>
						{else}
							{$ACCOUNTS_MODEL['accountname']}
						{/if}
					</div>
					<div class="col-xs-4 textOverflowEllipsis">
						{$ACCOUNTS_MODEL['userModel']->getName()}
					</div>
					<div class="col-xs-4 textOverflowEllipsis">
						{if is_null($ACCOUNTS_MODEL['crmactivity'])}
							-
						{else}
							{$ACCOUNTS_MODEL['crmactivity']}
						{/if}
					</div>
				</div>
			{/foreach}
		{if count($ACCOUNTS) eq $PAGING_MODEL->getPageLimit()}
			<div class="pull-right padding5">
				<button type="button" class="btn btn-xs btn-primary showMoreHistory" data-url="{$WIDGET->getUrl()}&page={$PAGING_MODEL->getNextPage()}">{vtranslate('LBL_MORE', $MODULE_NAME)}</button>
			</div>
		{/if}
	{else}
		{if $PAGING_MODEL->getCurrentPage() eq 1}
			<span class="noDataMsg">
				{vtranslate('LBL_NO_RECORDS_MATCHED_THIS_CRITERIA')}
			</span>
		{/if}
	{/if}
{/strip}
