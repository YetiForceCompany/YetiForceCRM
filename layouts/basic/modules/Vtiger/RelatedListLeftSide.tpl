{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-RelatedListLeftSide -->
	{if !empty($IS_FAVORITES)}
		{assign var=RECORD_IS_FAVORITE value=(int)in_array($RELATED_RECORD->getId(),$FAVORITES)}
		<div>
			<a class="favorites btn btn-light btn-sm" data-state="{$RECORD_IS_FAVORITE}">
				<span title="{\App\Language::translate('LBL_REMOVE_FROM_FAVORITES', $MODULE)}"
					class="fas fa-star  {if !$RECORD_IS_FAVORITE}d-none{/if}"></span>
				<span title="{\App\Language::translate('LBL_ADD_TO_FAVORITES', $MODULE)}"
					class="far fa-star  {if $RECORD_IS_FAVORITE}d-none{/if}"></span>
			</a>
		</div>
	{/if}
	{function LINKS_BUTTONS LINKS=[]}
		{if count($LINKS) > 0}
			{assign var=ONLY_ONE value=count($LINKS) eq 1}
			<div class="actions">
				{if $ONLY_ONE}
					{foreach from=$LINKS item=LINK}
						{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='listViewBasic'}
					{/foreach}
				{else}
					<div class="dropright u-remove-dropdown-icon">
						<button class="btn btn-sm btn-light toolsAction dropdown-toggle" type="button"
							data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<span class="fas fa-wrench" aria-hidden="true"></span>
							<span class="sr-only">{\App\Language::translate('LBL_ACTIONS')}</span>
						</button>
						<div class="dropdown-menu" aria-label="{\App\Language::translate('LBL_ACTIONS')}">
							{foreach from=$LINKS item=LINK}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='listViewBasic'}
							{/foreach}
						</div>
					</div>
				{/if}
			</div>
		{/if}
	{/function}
	{assign var=LINKS value=$RELATED_RECORD->getRecordRelatedListViewLinksLeftSide($VIEW_MODEL)}
	{assign var=NEXT_LINKS value=[]}
	{if isset($LINKS['BUTTONS'])}
		{assign var=NEXT_LINKS value=$LINKS['BUTTONS']}
		{assign var=LINKS value=array_diff_key($LINKS,['BUTTONS'=>''])}
	{/if}
	{LINKS_BUTTONS LINKS=$LINKS}
	{foreach from=$NEXT_LINKS item=LINK}
		{LINKS_BUTTONS LINKS=[$LINK]}
	{/foreach}
	{if App\Config::module('ModTracker', 'UNREVIEWED_COUNT') && $RELATED_MODULE->isPermitted('ReviewingUpdates') && $RELATED_MODULE->isTrackingEnabled() && $RELATED_RECORD->isViewable()}
		<div>
			<a href="{$RELATED_RECORD->getUpdatesUrl()}" class="unreviewed alignMiddle d-none"
				aria-label="{\App\Language::translate('LBL_NOTIFICATIONS')}">
				<span class="badge bgDanger c-badge--md all"
					title="{\App\Language::translate('LBL_NUMBER_UNREAD_CHANGES', 'ModTracker')}"
					aria-label="{\App\Language::translate('LBL_NUMBER_UNREAD_CHANGES', 'ModTracker')}"></span>
				<span class="badge bgBlue c-badge--md mail noLeftRadius noRightRadius"
					title="{\App\Language::translate('LBL_NUMBER_UNREAD_MAILS', 'ModTracker')}"
					aria-label="{\App\Language::translate('LBL_NUMBER_UNREAD_MAILS', 'ModTracker')}"></span>
			</a>
		</div>
	{/if}
	<!-- /tpl-Base-RelatedListLeftSide -->
{/strip}
