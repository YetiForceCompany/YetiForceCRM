{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if !empty($IS_FAVORITES)}
		{assign var=RECORD_IS_FAVORITE value=(int)in_array($RELATED_RECORD->getId(),$FAVORITES)}
		<div>
			<a class="favorites btn btn-light btn-sm" role="button" data-state="{$RECORD_IS_FAVORITE}">
				<span title="{\App\Language::translate('LBL_REMOVE_FROM_FAVORITES', $MODULE)}"
					  class="fas fa-star {if !$RECORD_IS_FAVORITE}d-none{/if}"></span>
				<span title="{\App\Language::translate('LBL_ADD_TO_FAVORITES', $MODULE)}"
					  class="far fa-star {if $RECORD_IS_FAVORITE}d-none{/if}"></span>
			</a>
		</div>
	{/if}
	<div>
		<a href="#" id="copybtn_{$PASS_ID}" data-id="{$PASS_ID}"
		   class="copy_pass d-none btn btn-light btn-sm js-popover-tooltip" data-js="popover"
		   data-content="{\App\Language::translate('LBL_CopyToClipboardTitle', $RELATED_MODULE_NAME)}"><span
					class="fas fa-download"></span></a>&nbsp;
	</div>
	<a href="#" class="show_pass btn btn-sm btn-light js-popover-tooltip" data-js="popover" id="btn_{$PASS_ID}"
	   data-content="{\App\Language::translate('LBL_ShowPassword', $RELATED_MODULE_NAME)}"
	   data-title-show="{\App\Language::translate('LBL_ShowPassword', $RELATED_MODULE_NAME)}"
	   data-title-hide="{\App\Language::translate('LBL_HidePassword', $RELATED_MODULE_NAME)}"><span
				class="adminIcon-passwords-encryption"></span></a>
	{assign var=LINKS value=$RELATED_RECORD->getRecordRelatedListViewLinksLeftSide($VIEW_MODEL)}
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
						<span class="fas fa-wrench" title="{\App\Language::translate('LBL_ACTIONS')}"></span>
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
	{if AppConfig::module('ModTracker', 'UNREVIEWED_COUNT') && $RELATED_MODULE->isPermitted('ReviewingUpdates') && $RELATED_MODULE->isTrackingEnabled() && $RELATED_RECORD->isViewable()}
		<div>
			<a href="{$RELATED_RECORD->getUpdatesUrl()}" class="unreviewed d-none">
				<span class="badge badge-danger c-badge--md all"
					  title="{\App\Language::translate('LBL_NUMBER_UNREAD_CHANGES', 'ModTracker')}"
					  aria-label="{\App\Language::translate('LBL_NUMBER_UNREAD_CHANGES', 'ModTracker')}"></span>
				<span class="badge badge-primary c-badge--md mail"
					  title="{\App\Language::translate('LBL_NUMBER_UNREAD_MAILS', 'ModTracker')}"
					  aria-label="{\App\Language::translate('LBL_NUMBER_UNREAD_MAILS', 'ModTracker')}"></span>
			</a>
		</div>
	{/if}
{/strip}
