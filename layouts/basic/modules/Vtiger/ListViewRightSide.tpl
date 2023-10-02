{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=LINKS value=$LISTVIEW_ENTRY->getRecordListViewLinksRightSide()}
	{if count($LINKS) > 0}
		{assign var=ONLY_ONE value=count($LINKS) eq 1}
		<div class="actions">
			{if $ONLY_ONE}
				{include file=\App\Layout::getTemplatePath('ButtonLinks.tpl', $MODULE) LINKS=$LINKS BUTTON_VIEW='listViewBasic' MODULE_NAME=$MODULE }
			{else}
				<div class="dropleft u-remove-dropdown-icon">
					<button class="btn btn-sm btn-light toolsAction dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span class="fas fa-wrench" aria-hidden="true"></span>
						<span class="sr-only">{\App\Language::translate('LBL_ACTIONS')}</span>
					</button>
					<div class="dropdown-menu" aria-label="{\App\Language::translate('LBL_ACTIONS')}">
						{include file=\App\Layout::getTemplatePath('ButtonLinks.tpl', $MODULE) LINKS=$LINKS BUTTON_VIEW='listViewBasic' MODULE_NAME=$MODULE }
					</div>
				</div>
			{/if}
		</div>
	{/if}
{/strip}
