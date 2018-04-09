{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="settingsIndexPage Settings-Vtiger-Credits">
		<div class="widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
			<div class="col-12">
				{\App\Language::translate('LBL_CREDITS_DESCRIPTION', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="js-table-container" data-js="container">
			<table class="table table-bordered dataTableWithRecords">
				<thead>
				<th class="p-2">
					{\App\Language::translate('LBL_LIBRARY_NAME', $QUALIFIED_MODULE)}
				</th>
				<th class="p-2">
					{\App\Language::translate('LBL_VERSION', $QUALIFIED_MODULE)}
				</th>
				<th class="p-2">
					{\App\Language::translate('LBL_LICENSE', $QUALIFIED_MODULE)}
				</th>
				<th class="p-2"></th>
				</thead>
				<tbody>
				{foreach from=$LIBRARIES key=TYPE item=ITEMS}
					{if $ITEMS}
						{foreach from=$ITEMS item=ITEM}
							<tr>
								<td>
									{$ITEM['name']}
								</td>
								<td>
									{$ITEM['version']}
								</td>
								<td>
									{$ITEM['license']}
								</td>
								<td>
									<a title="{\App\Language::translate('LBL_HOMEPAGE', $QUALIFIED_MODULE)}"
									   href="{$ITEM['homepage']}"><span class="fas fa-link mr-2"></span></a>
									{if $ITEM['licenseError']}
										<span title="{\App\Language::translate('LBL_LICENSE_ERROR', $QUALIFIED_MODULE)}"
											  class="fas fa-exclamation text-danger mr-2"></span>
									{/if}
									{if $ITEM['packageFileMissing'] }
										<span title="{\App\Language::translate('LBL_MISSING_PACKAGE_FILE', $QUALIFIED_MODULE)}"
											  class="fas fa-file-code text-danger mr-2"></span>
									{else}
										<span title="{\App\Language::translate('LBL_SHOW_MORE', $QUALIFIED_MODULE)}" data-type="{$TYPE}"
											  data-library-name="{$ITEM['name']}"
											  class="fas fa-info-circle text-dark cursorPointer js-show-more mr-2"
											  data-js="click"></span>
									{/if}
								</td>
							</tr>
						{/foreach}
					{else}
						<div class="p-3 mb-2 bg-danger text-white">{\App\Language::translate('LBL_MISSING_FILE')}</div>
					{/if}
				{/foreach}
				</tbody>
			</table>
		</div>
	</div>
{/strip}
