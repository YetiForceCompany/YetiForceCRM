{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Backup-Index">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="contents mt-2">
			<div class="listViewContentDiv ps ps--active-y">
				{if !empty($CONFIG_ALERT)}
					<div class="alert alert-block alert-danger fade in show">
						<button type="button" class="close" data-dismiss="alert">×</button>
						<h4 class="alert-heading">{\App\Language::translate('ERR_CONFIG_ALERT_TITLE', $QUALIFIED_MODULE)}</h4>
						<p>{$CONFIG_ALERT}</p>
					</div>
				{else}
					<h5>{\App\Language::translate('LBL_BACKUP_LIST',$QUALIFIED_MODULE)}</h5>
					<table class="table tableBorderHeadBody listViewEntriesTable medium">
						{if !empty($STRUCTURE['manage'])}
							<tr class="listViewEntries">
								<td class="border bc-gray-lighter">
									<a href="{$STRUCTURE['manage']}">
										<i class="fas fa-level-up-alt"></i> [..]
									</a>
								</td>
							</tr>
						{/if}
						{if isset($STRUCTURE['catalogs'])}
							{foreach from=$STRUCTURE['catalogs'] item=$catalog}
								<tr class="listViewEntries{if empty($catalog['url'])} u-opacity-muted{/if}">
									<td>
										{if empty($catalog['url'])}
											<span class="fas fa-folder mr-1"></span>
											{\App\Purifier::encodeHtml($catalog['name'])}
										{else}
											<a href="{\App\Purifier::encodeHtml($catalog['url'])}" class="font-weight-bold">
												<span class="fas fa-folder"></span> {\App\Purifier::encodeHtml($catalog['name'])}
											</a>
										{/if}
									</td>
								</tr>
							{/foreach}
						{/if}
					</table>
					<table class="table table-striped table-bordered js-data-table dataTable" data-j="DataTable">
						<thead>
							<tr class="c-tab--border-active listViewHeaders">
								<th class="p-2">
									{\App\Language::translate('LBL_FILE_NAME',$QUALIFIED_MODULE)}
								</th>
								<th class="p-2">
									{\App\Language::translate('LBL_FILE_DATE',$QUALIFIED_MODULE)}
								</th>
								<th class="p-2">
									{\App\Language::translate('LBL_FILE_SIZE',$QUALIFIED_MODULE)}
								</th>
								<th class="noWrap p-2">
									{\App\Language::translate('LBL_DOWNLOAD',$QUALIFIED_MODULE)}
								</th>
							</tr>
						</thead>
						{if !empty($STRUCTURE['files'])}
							{foreach from=$STRUCTURE['files'] item=$file}
								<tr class="listViewEntries">
									<td>{\App\Purifier::encodeHtml($file['name'])}</td>
									<td>{$file['date']}</td>
									<td>{$file['size']}</td>
									<td class="u-w-1em">
										<a href="{$file['url']}" class="btn btn-primary btn-sm js-post-action">
											<span class="fas fa-download mr-1"></span> {\App\Language::translate('LBL_DOWNLOAD',$QUALIFIED_MODULE)}
										</a>
									</td>
								</tr>
							{/foreach}
						{/if}
					</table>
					{if empty($STRUCTURE['files'])}
						<table class="emptyRecordsDiv">
							<tbody>
								<tr>
									<td>{\App\Language::translate('LBL_NO_RECORDS_FOUND')}</td>
								</tr>
							</tbody>
						</table>
					{/if}
				{/if}
			</div>
		</div>
	</div>
{/strip}
