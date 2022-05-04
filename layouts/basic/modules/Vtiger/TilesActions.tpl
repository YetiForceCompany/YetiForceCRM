{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-TilesActions -->
	<div class="d-flex c-tiles-actions-container">
		<div>
			<input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox mt-1 ml-1" title="{\App\Language::translate('LBL_SELECT_SINGLE_ROW')}" />
		</div>
		{assign var=LINKS value=$LISTVIEW_ENTRY->getRecordListViewLinksLeftSide()}
		{if count($LINKS) > 0}
			{assign var=ONLY_ONE value=count($LINKS) eq 1}
			<div class="actions">
				{if $ONLY_ONE}
					{foreach from=$LINKS item=LINK}
						{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='listViewBasic'}
					{/foreach}
				{else}
					<div class="dropright u-remove-dropdown-icon">
						<button class="btn btn-xs  toolsAction dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<span class="fas fa-wrench" title="{\App\Language::translate('LBL_ACTIONS')}"></span>
						</button>
						<div class="dropdown-menu p-1" aria-label="{\App\Language::translate('LBL_ACTIONS')}">
							{foreach from=$LINKS item=LINK}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='listViewBasic' BTN_CLASS='btn-xs'}
							{/foreach}
						</div>
					</div>
				{/if}
			</div>
		{/if}

	</div>
	<!-- /tpl-Base-TilesActions -->
{/strip}
