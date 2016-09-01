{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
<input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox" title="{vtranslate('LBL_SELECT_SINGLE_ROW')}" />
{if AppConfig::module('ModTracker', 'UNREVIEWED_COUNT') && $MODULE_MODEL->isPermitted('ReviewingUpdates') && $MODULE_MODEL->isTrackingEnabled() && $LISTVIEW_ENTRY->isViewable()}
	<a href="{$LISTVIEW_ENTRY->getUpdatesUrl()}" class="unreviewed">
		<span class="badge bgDanger"></span>&nbsp;
	</a>&nbsp;
{/if}
<span class="{Documents_Record_Model::getFileIconByFileType($LISTVIEW_ENTRY->get('filetype'))} fa-lg"> </span>
