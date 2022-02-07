{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Documents-ListViewLeftSide-->
	<div class="d-flex align-items-center">
		<div>
			<input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox" title="{\App\Language::translate('LBL_SELECT_SINGLE_ROW')}" />
		</div>
		{assign var=IMAGE_CLASS value=Documents_Record_Model::getFileIconByFileType($LISTVIEW_ENTRY->get('filetype'))}
		<span class="{$IMAGE_CLASS} fa-lg middle {if $IMAGE_CLASS eq 'yfm-Documents'}back4RightMargin{/if} ml-1"></span>
		{assign var=LINKS value=$LISTVIEW_ENTRY->getRecordListViewLinksLeftSide()}
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
		<div>
			{if in_array($MODULE_NAME, \App\Config::module('ModTracker', 'SHOW_TIMELINE_IN_LISTVIEW', [])) && $MODULE_MODEL->isPermitted('TimeLineList')}
				<a data-url="{$LISTVIEW_ENTRY->getTimeLineUrl()}" class="c-badge__icon fa-fw timeLineIconList d-none u-cursor-pointer"></a>
			{/if}
			{if \App\Config::module('ModTracker', 'UNREVIEWED_COUNT') && $MODULE_MODEL->isPermitted('ReviewingUpdates') && $MODULE_MODEL->isTrackingEnabled() && $LISTVIEW_ENTRY->isViewable()}
				<a href="{$LISTVIEW_ENTRY->getUpdatesUrl()}" class="unreviewed d-none"
					aria-label="{\App\Language::translate('LBL_NOTIFICATIONS')}">
					<span class="badge bgDanger c-badge--md all"
						title="{\App\Language::translate('LBL_NUMBER_UNREAD_CHANGES', 'ModTracker')}"
						aria-label="{\App\Language::translate('LBL_NUMBER_UNREAD_CHANGES', 'ModTracker')}"></span>
					<span class="badge bgBlue c-badge--md mail noLeftRadius noRightRadius"
						title="{\App\Language::translate('LBL_NUMBER_UNREAD_MAILS', 'ModTracker')}"
						aria-label="{\App\Language::translate('LBL_NUMBER_UNREAD_MAILS', 'ModTracker')}"></span>
				</a>
			{/if}
		</div>
	</div>
	<!-- /tpl-Documents-ListViewLeftSide-->
{/strip}
