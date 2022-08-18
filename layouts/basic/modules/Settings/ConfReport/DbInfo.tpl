{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-ConfReport-DbInfo -->
	<div class="modal-body js-modal-content" data-js="click">
		<h5>
			<span class="mr-4 mr-5">{\App\Language::translate('LBL_DB_TOTAL_SIZE', $QUALIFIED_MODULE)}: {\vtlib\Functions::showBytes($DB_INFO['size'])}</span>
			<span class="mr-4 mr-5">{\App\Language::translate('LBL_DATA_TOTAL_SIZE', $QUALIFIED_MODULE)}: {\vtlib\Functions::showBytes($DB_INFO['dataSize'])}</span>
			<span class="mr-4 mr-5">{\App\Language::translate('LBL_INDEX_TOTAL_SIZE', $QUALIFIED_MODULE)}: {\vtlib\Functions::showBytes($DB_INFO['indexSize'])}</span>
			{if $DB_INFO['isFileSize']}
				<span class="mr-4">{\App\Language::translate('LBL_FILE_TOTAL_SIZE', $QUALIFIED_MODULE)}: {\vtlib\Functions::showBytes($DB_INFO['filesSize'])}</span>
			{/if}
		</h5>
		<div class="mt-3 table-responsive">
			<table id="db-crm-table" class="table table-sm table-striped display js-db-info-table" data-js="dataTables">
				<thead>
					<tr>
						<th>{\App\Language::translate('LBL_MODULE_NAME', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_NUMBER_OF_ASSIGNED_RECORDS', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$DB_RECORDS key=MODULE item=COUNT}
						<tr>
							<td>{\App\Language::translate($MODULE, $MODULE)}</td>
							<td>{$COUNT}</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
			<hr class="my-3">
			<table id="db-info-table" class="table table-sm table-striped display js-db-info-table" data-js="dataTables">
				<thead>
					<tr>
						<th>{\App\Language::translate('LBL_TABLE_NAME', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_ROWS', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_DATA_SIZE', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_INDEX_SIZE', $QUALIFIED_MODULE)}</th>
						{if $DB_INFO['isFileSize']}
							<th>{\App\Language::translate('LBL_FILE_SIZE', $QUALIFIED_MODULE)}</th>
						{/if}
						<th>{\App\Language::translate('LBL_FORMAT', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_ENGINE', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_COLLATION', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$DB_INFO['tables'] key=TABLE item=ITEM}
						<tr>
							<td>{\App\Purifier::encodeHtml($TABLE)}</td>
							<td data-order="{$ITEM['rows']}">{App\Fields\Integer::formatToDisplay($ITEM['rows'])}</td>
							<td data-order="{$ITEM['dataSize']}">{\vtlib\Functions::showBytes($ITEM['dataSize'])}</td>
							<td data-order="{$ITEM['indexSize']}">{\vtlib\Functions::showBytes($ITEM['indexSize'])}</td>
							{if $DB_INFO['isFileSize']}
								<td data-order="{if isset($ITEM['fileSize'])}{$ITEM['fileSize']}{/if}">
									{if isset($ITEM['fileSize'])}{\vtlib\Functions::showBytes($ITEM['fileSize'])}{/if}
								</td>
							{/if}
							<td>{$ITEM['format']}</td>
							<td>{$ITEM['engine']}</td>
							<td>{$ITEM['collation']}</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
	<!-- /tpl-Settings-ConfReport-DbInfo -->
{/strip}
