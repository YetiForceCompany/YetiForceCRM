{*<!--
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
-->*}
{strip}
	{assign var=QEOPEN value=Users_Privileges_Model::isPermitted($MODULE_NAME, 'QEOpen', $RECORD_ID)}
	<div class="actions pull-right">
		<span class="actionImages">
			{if ($IS_MODULE_EDITABLE && $LISTVIEW_ENTRY->lockEditView eq false) || $QEOPEN}
				<a class="showModal" data-url="{$LISTVIEW_ENTRY->getModalUrl()}"><span title="{vtranslate('LBL_SET_RECORD_STATUS', $MODULE)}" class="glyphicon glyphicon-modal-window alignMiddle"></span></a>&nbsp;
			{/if}
			<a href="{$LISTVIEW_ENTRY->getFullDetailViewUrl()}"><span title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="glyphicon glyphicon-th-list alignMiddle"></span></a>&nbsp;
			{if $IS_MODULE_EDITABLE && $LISTVIEW_ENTRY->lockEditView eq false && $LISTVIEW_ENTRY->isPermittedToEditView == 1}
			<a href='{$LISTVIEW_ENTRY->getEditViewUrl()}'><span title="{vtranslate('LBL_EDIT', $MODULE)}" class="glyphicon glyphicon-pencil alignMiddle"></span></a>&nbsp;
			{/if}
			{if $IS_MODULE_DELETABLE && $LISTVIEW_ENTRY->lockEditView eq false && $LISTVIEW_ENTRY->isPermittedToEditView == 1}
			<a class="deleteRecordButton"><span title="{vtranslate('LBL_DELETE', $MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
			{/if}
		</span>
	</div>
{/strip}

