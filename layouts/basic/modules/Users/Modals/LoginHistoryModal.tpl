{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Users-PasswordModal -->
	<div class="modal-body pb-3">
		<table class="table table-bordered u-fs-13px">
			<thead>
				<tr class="listViewEntries">
					{foreach key="LISTVIEW_ENTRY_COLUMN_NAME" item="LISTVIEW_ENTRY_COLUMN" from=$TABLE_COLUMNS}
						<th class="noWrap {$WIDTHTYPE}">
							{\App\Language::translate($LISTVIEW_ENTRY_COLUMN, $MODULE_NAME)}
						</th>
					{/foreach}
				</tr>
			</thead>
			<tbody>
				{foreach item="LISTVIEW_ENTRY" from=$LOGIN_HISTORY_ENTRIES}
					<tr class="listViewEntries">
						{foreach key="LISTVIEW_ENTRY_COLUMN_NAME" item="LISTVIEW_ENTRY_COLUMN" from=$TABLE_COLUMNS}
							<td class="noWrap {$WIDTHTYPE}">
							{if $LISTVIEW_ENTRY_COLUMN_NAME === 'status'}
								{\App\Language::translate($LISTVIEW_ENTRY[$LISTVIEW_ENTRY_COLUMN_NAME])}
							{else}
								{if isset($LISTVIEW_ENTRY[$LISTVIEW_ENTRY_COLUMN_NAME]) && !empty($LISTVIEW_ENTRY[$LISTVIEW_ENTRY_COLUMN_NAME])}
									{$LISTVIEW_ENTRY[$LISTVIEW_ENTRY_COLUMN_NAME]}
								{else}
									--
								{/if}
							{/if}
							</td>
						{/foreach}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	<!-- /tpl-Users-PasswordModal -->
{/strip}
