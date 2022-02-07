{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Dependencies-Vulnerabilities -->
	<div class="pt-md-0 pt-1">
		<div class="o-breadcrumb widget_header mb-2 d-flex flex-nowrap flex-md-wrap justify-content-between px-2 row">
			<div class="o-breadcrumb__container">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="mb-2">
			{assign var=CHECK_ALERT value=\App\YetiForce\Shop::checkAlert('YetiForceVulnerabilities')}
			{if $CHECK_ALERT}
				<div class="alert alert-warning">
					<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
					{\App\Language::translate($CHECK_ALERT, 'Settings::YetiForce')} <a class="btn btn-primary btn-sm" href="index.php?parent=Settings&module=YetiForce&view=Shop&product=YetiForceVulnerabilities&mode=showProductModal"><span class="yfi yfi-shop mr-2"></span>{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>
				</div>
			{else if !empty($VULNERABILITIES)}
				<div class="w-100 mb-2">
					<div class="alert alert-danger" role="alert">
						<h4 class="alert-heading">
							<span class="fas fa-skull-crossbones mr-3 u-fs-4x float-left"></span>
							{\App\Language::translate('LBL_VULNERABILITIES_WARNING',$QUALIFIED_MODULE)}
						</h4>
						<span>{\App\Language::translate('LBL_VULNERABILITIES_WARNING_DESC',$QUALIFIED_MODULE)}</span>
					</div>
				</div>
				{foreach from=$VULNERABILITIES key=PACKAGE item=ROWS}
					<table class="table tableRWD table-bordered table-sm themeTableColor confTable">
						<thead>
							<tr class="blockHeader">
								<th colspan="4" class="mediumWidthType">
									<span>{App\Language::translate("LBL_SECURITY_{$PACKAGE|upper}", $QUALIFIED_MODULE)}</span>
								</th>
							</tr>
							<tr class="blockHeader">
								<th colspan="1" class="mediumWidthType">
									<span>{App\Language::translate('LBL_LIB_NAME', $QUALIFIED_MODULE)}</span>
								</th>
								<th colspan="1" class="mediumWidthType u-w-3per-150px">
									<span>CVE</span>
								</th>
								<th colspan="1" class="mediumWidthType">
									<span>{App\Language::translate('LBL_VULNERABILITY_NAME', $QUALIFIED_MODULE)}</span>
								</th>
								<th colspan="1" class="mediumWidthType">
									<span>{App\Language::translate('LBL_VULNERABILITY_URL', $QUALIFIED_MODULE)}</span>
								</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$ROWS key=LIB_NAME item=LIB}
								{foreach from=$LIB['advisories'] item=ADVISORIE}
									<tr>
										<td><label>{\App\Purifier::encodeHtml($LIB_NAME)} ({\App\Purifier::encodeHtml($LIB['version'])})</label></td>
										<td><label>{if isset($ADVISORIE['cve'])}{\App\Purifier::encodeHtml($ADVISORIE['cve'])}{/if}</label></td>
										<td><label>{\App\Purifier::encodeHtml($ADVISORIE['title'])}</label></td>
										<td><label>
												{if isset($ADVISORIE['link'])}
													<a target="_blank" rel="noreferrer noopener" href="{\App\Purifier::encodeHtml($ADVISORIE['link'])}">
														{\App\Purifier::encodeHtml($ADVISORIE['link'])}
													</a>
												{/if}
											</label></td>

									</tr>
								{/foreach}
							{/foreach}
						</tbody>
					</table>
				{/foreach}
			{else}
				<div class="w-100 mb-2">
					<div class="alert alert-success" role="alert">
						<h4 class="alert-heading">
							<span class="far fa-thumbs-up mr-3 u-fs-4x float-left"></span>
							{\App\Language::translate('LBL_VULNERABILITIES_OK', $QUALIFIED_MODULE)}
						</h4>
						<p>{\App\Language::translate('LBL_VULNERABILITIES_OK_DESC', $QUALIFIED_MODULE)}</p>
					</div>
				</div>
			{/if}
		</div>
	</div>
	<!-- /tpl-Settings-Dependencies-Vulnerabilities -->
{/strip}
