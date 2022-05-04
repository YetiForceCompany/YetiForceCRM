{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-ListViewContentsBottom -->
	{if $LISTVIEW_ENTRIES_COUNT eq '0'}
		<table class="emptyRecordsDiv">
			<tbody>
				<tr>
					<td>
						{\App\Language::translate('LBL_RECORDS_NO_FOUND')}. {if $IS_MODULE_EDITABLE}
							<a href="{$MODULE_MODEL->getCreateRecordUrl()}">{\App\Language::translate('LBL_CREATE_SINGLE_RECORD')}</a>
						{/if}
					</td>
				</tr>
			</tbody>
		</table>
	{/if}
	<!-- /tpl-Base-ListViewContentsBottom -->
{/strip}
