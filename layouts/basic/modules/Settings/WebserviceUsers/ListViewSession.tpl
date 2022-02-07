{*
<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-WebserviceUsers-ListViewSession -->
	<div class="modal-body">
		<table class="table table-bordered u-fs-13px">
			<thead>
				<tr class="listViewEntries">
					{foreach key=LISTVIEW_ENTRY_COLUMN_NAME item=LISTVIEW_ENTRY_COLUMN from=$TABLE_COLUMNS}
						<th class="noWrap {$WIDTHTYPE}">
							{\App\Language::translate($LISTVIEW_ENTRY_COLUMN, $QUALIFIED_MODULE)}
						</th>
					{/foreach}
				</tr>
			</thead>
			<tbody>
				<div class="alert alert-info">
					{\App\Language::translateArgs('LBL_LIMIT_INFO', $QUALIFIED_MODULE, 30)}
				</div>
				{foreach item="LISTVIEW_ENTRY" from=$SESSION_HISTORY_ENTRIES}
					<tr class="listViewEntries">
						{foreach key=LISTVIEW_ENTRY_COLUMN_NAME item=LISTVIEW_ENTRY_COLUMN from=$TABLE_COLUMNS}
							<td class="noWrap {$WIDTHTYPE}">
								{if !empty($LISTVIEW_ENTRY[$LISTVIEW_ENTRY_COLUMN_NAME])}
									{$LISTVIEW_ENTRY[$LISTVIEW_ENTRY_COLUMN_NAME]}
								{else}
									--
								{/if}
							</td>
						{/foreach}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	<!-- /tpl-Settings-WebserviceUsers-ListViewSession -->
{/strip}
