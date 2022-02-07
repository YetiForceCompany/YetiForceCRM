{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Documents-RelatedListLeftSide -->
	<div class="btn btn-sm">
		{assign var=IMAGE_CLASS value=Documents_Record_Model::getFileIconByFileType($RELATED_RECORD->get('filetype'))}
		<span class="{$IMAGE_CLASS} fa-lg" title="{$RELATED_RECORD->get('filetype')}">{if $IMAGE_CLASS neq 'yfm-Documents'}&nbsp;{/if}</span>
	</div>
	{if !empty($IS_FAVORITES)}
		{assign var=RECORD_IS_FAVORITE value=(int)in_array($RELATED_RECORD->getId(),$FAVORITES)}
		<div>
			<a class="favorites btn btn-light btn-sm" role="button" data-state="{$RECORD_IS_FAVORITE}">
				<span title="{\App\Language::translate('LBL_REMOVE_FROM_FAVORITES', $MODULE)}" class="fas fa-star {if !$RECORD_IS_FAVORITE}d-none{/if}"></span>
				<span title="{\App\Language::translate('LBL_ADD_TO_FAVORITES', $MODULE)}" class="far fa-star {if $RECORD_IS_FAVORITE}d-none{/if}"></span>
			</a>
		</div>
	{/if}
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
	{if App\Config::module('ModTracker', 'UNREVIEWED_COUNT') && $RELATED_MODULE->isPermitted('ReviewingUpdates') && $RELATED_MODULE->isTrackingEnabled() && $RELATED_RECORD->isViewable()}
		<div>
			<a href="{$RELATED_RECORD->getUpdatesUrl()}" class="unreviewed d-none">
				<span class="badge badge-danger c-badge--md all" title="{\App\Language::translate('LBL_NUMBER_UNREAD_CHANGES', 'ModTracker')}"
					aria-label="{\App\Language::translate('LBL_NUMBER_UNREAD_CHANGES', 'ModTracker')}"></span>
				<span class="badge badge-primary c-badge--md mail" title="{\App\Language::translate('LBL_NUMBER_UNREAD_MAILS', 'ModTracker')}"
					aria-label="{\App\Language::translate('LBL_NUMBER_UNREAD_MAILS', 'ModTracker')}"></span>
			</a>
		</div>
	{/if}
	<!-- /tpl-Documents-RelatedListLeftSide -->
{/strip}
