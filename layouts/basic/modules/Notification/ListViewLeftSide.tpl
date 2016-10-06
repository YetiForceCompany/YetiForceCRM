{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div>
		<input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox" title="{vtranslate('LBL_SELECT_SINGLE_ROW')}" />
	</div>
	<div class="actions">
		<span class="glyphicon glyphicon-wrench toolsAction"></span>
		<span class="actionImages hide">
			<a  href="javascript:Notification_List_Js.setAsMarked({$LISTVIEW_ENTRY->getId()})" >
				<span title="{vtranslate('LBL_MARK_READ', $MODULE)}" class="glyphicon glyphicon-ok"></span>
			</a>
		</span>
	</div>
{/strip}
