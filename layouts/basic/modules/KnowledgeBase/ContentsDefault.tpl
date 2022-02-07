{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="col-12 paddingLRZero">
		<h4>{\App\Language::translate('LBL_LIST_RECORDS',$MODULE_NAME)}</h4>
		<hr>
	</div>
	{if $ENTRIES}
		<table class="table table-striped dataTableWithDocuments">
			<thead>
				<tr>
					{foreach item=HEADER from=$HEADERS}
						<th>
							{\App\Language::translate($HEADER->get('label'), $MODULE_NAME)}
						</th>
					{/foreach}
					<th></th>
				</tr>
			</thead>
			<tbody>
				{foreach item=ENTRY from=$ENTRIES name=listview}
					<tr>
						{foreach item=HEADER from=$HEADERS}
							{assign var=HEADERNAME value=$HEADER->get('name')}
							<td>
								{$ENTRY->getDisplayValue($HEADERNAME)}
							</td>
						{/foreach}
						<td>
							<div class="actions float-right">
								<a href="{$ENTRY->getDetailViewUrl()}">
									<span class="fas fa-th-list alignMiddle" title="{\App\Language::translate('LBL_RECORD_DETAILS', $MODULE_NAME)}"></span>
								</a>
							</div>
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	{else}
		<div class="col-12 paddingLRZero">
			<div class="alert alert-info">
				{\App\Language::translate('LBL_RECORDS_NO_FOUND',$MODULE_NAME)}
			</div>
		</div>
	{/if}
{/strip}
