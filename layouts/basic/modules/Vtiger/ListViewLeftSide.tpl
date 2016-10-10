{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div>
		<input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox" title="{vtranslate('LBL_SELECT_SINGLE_ROW')}" />
	</div>
	<div class="actions">
		<span class="glyphicon glyphicon-wrench toolsAction"></span>
		<span class="actionImages hide">
			{if $MODULE_MODEL->isPermitted('WatchingRecords') && $LISTVIEW_ENTRY->isViewable()}
				{assign var=WATCHING_STATE value=(!$LISTVIEW_ENTRY->isWatchingRecord())|intval}
				<a href="#" onclick="Vtiger_Index_Js.changeWatching(this)" title="{vtranslate('BTN_WATCHING_RECORD', $MODULE)}" data-record="{$LISTVIEW_ENTRY->getId()}" data-value="{$WATCHING_STATE}" class="noLinkBtn{if !$WATCHING_STATE} info-color{/if}" data-on="info-color" data-off="" data-icon-on="glyphicon-eye-open" data-icon-off="glyphicon-eye-close">
					<span class="glyphicon {if $WATCHING_STATE}glyphicon-eye-close{else}glyphicon-eye-open{/if}"></span>
				</a>&nbsp;
			{/if}
			<a href="{$LISTVIEW_ENTRY->getFullDetailViewUrl()}">
				<span title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="glyphicon glyphicon-th-list"></span>
			</a>&nbsp;
			{if $IS_MODULE_EDITABLE && $LISTVIEW_ENTRY->isEditable()}
				<a href='{$LISTVIEW_ENTRY->getEditViewUrl()}'>
					<span title="{vtranslate('LBL_EDIT', $MODULE)}" class="glyphicon glyphicon-pencil"></span>
				</a>&nbsp;
			{/if}
			{if ($IS_MODULE_EDITABLE && $LISTVIEW_ENTRY->isEditable() && $LISTVIEW_ENTRY->editFieldByModalPermission()) || $LISTVIEW_ENTRY->editFieldByModalPermission(true)}
				{assign var=FIELD_BY_EDIT_DATA value=$LISTVIEW_ENTRY->getFieldToEditByModal()}
				<a class="showModal {$FIELD_BY_EDIT_DATA['listViewClass']}" data-url="{$LISTVIEW_ENTRY->getEditFieldByModalUrl()}">
					<span title="{vtranslate({$FIELD_BY_EDIT_DATA['titleTag']}, $MODULE)}" class="glyphicon {$FIELD_BY_EDIT_DATA['iconClass']}"></span>
				</a>&nbsp;
			{/if}
			{if $IS_MODULE_DELETABLE && $LISTVIEW_ENTRY->isDeletable()}
				<a class="deleteRecordButton">
					<span title="{vtranslate('LBL_DELETE', $MODULE)}" class="glyphicon glyphicon-trash"></span>
				</a>
			{/if}
		</span>
	</div>
	<div>
		{if AppConfig::module('ModTracker', 'UNREVIEWED_COUNT') && $MODULE_MODEL->isPermitted('ReviewingUpdates') && $MODULE_MODEL->isTrackingEnabled() && $LISTVIEW_ENTRY->isViewable()}
			<a href="{$LISTVIEW_ENTRY->getUpdatesUrl()}" class="unreviewed">
				<span class="badge bgDanger"></span>&nbsp;
			</a>&nbsp;
		{/if}
	</div>
{/strip}
