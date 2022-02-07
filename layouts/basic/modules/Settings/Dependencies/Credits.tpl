{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Dependencies-Credits -->
	<div class="settingsIndexPage">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
			<div class="col-12">
				{\App\Language::translate('LBL_CREDITS_DESCRIPTION', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="js-table-container mt-4 mb-5" data-js="container">
			<table class="table table-sm table-bordered dataTableWithRecords">
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
								<tr data-name="{\App\Purifier::encodeHtml($ITEM['name'])}">
									<td>
										{$ITEM['name']}
										{if !empty($ITEM['description'])}
											&nbsp;({\App\Language::translate($ITEM['description'], $QUALIFIED_MODULE)})
										{/if}
									</td>
									<td>{$ITEM['version']}</td>
									<td>{$ITEM['license']}</td>
									<td>
										{if !empty($ITEM['homepage'])}
											<a title="{\App\Language::translate('LBL_LIBRARY_HOMEPAGE', $QUALIFIED_MODULE)}" href="{$ITEM['homepage']}" target="_blank" rel="noreferrer noopener"><span class="fas fa-link mr-2"></span></a>
										{/if}
										{if !empty($ITEM['licenseError'])}
											<span title="{\App\Language::translate('LBL_LICENSE_ERROR', $QUALIFIED_MODULE)}" class="fas fa-exclamation text-danger mr-2 u-cursor-pointer"></span>
										{/if}
										{if !empty($ITEM['packageFileMissing'])}
											<span title="{\App\Language::translate('LBL_MISSING_PACKAGE_FILE', $QUALIFIED_MODULE)}" class="fas fa-file-code text-danger mr-2 u-cursor-pointer"></span>
										{elseif !empty($ITEM['notPackageFile'])}
										{else}
											<span title="{\App\Language::translate('LBL_SHOW_MORE', $QUALIFIED_MODULE)}" data-type="{$TYPE}" data-library-name="{$ITEM['name']}" class="fas fa-info-circle text-dark u-cursor-pointer js-show-more mr-2" data-js="click"></span>
										{/if}
										{if !empty($ITEM['license']) && !empty($ITEM['showLicenseModal'])}
											<span title="{\App\Language::translate('LBL_LICENSE', $QUALIFIED_MODULE)}" data-license="{if !empty($ITEM['licenseToDisplay'])}{$ITEM['licenseToDisplay']}{else}{$ITEM['license']}{/if}" class="fab fa-wpforms text-dark u-cursor-pointer js-show-license mr-2" data-js="click"></span>
										{/if}
									</td>
								</tr>
							{/foreach}
						{else}
							<div class="p-3 mb-2 bg-danger text-white">
								<span class="fas fa-exclamation-triangles mr-2"></span>
								{\App\Language::translate('LBL_MISSING_FILE',$QUALIFIED_MODULE)}: {$TYPE}
							</div>
						{/if}
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
	<!-- /tpl-Settings-Dependencies-Credits -->
{/strip}
