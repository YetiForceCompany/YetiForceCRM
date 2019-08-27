{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-YetiForce-Vulnerabilities">
		<div class="o-breadcrumb widget_header mb-2 d-flex flex-nowrap flex-md-wrap justify-content-between px-2 row">
			<div class="o-breadcrumb__container">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="js-Settings-YetiForce-Vulnerabilities-table container">
		<div class="row mb-2">
			{if !empty($VULNERABILITIES)}
			<div class="w-100 mb-2">
				<div class="alert alert-danger" role="alert">
					<h4 class="alert-heading">
						<span class="fas fa-info-circle mr-1"></span>
						{\App\Language::translate('LBL_VULNERABILITIES_WARNING',$QUALIFIED_MODULE)}
					</h4>
					<p>{\App\Language::translate('LBL_VULNERABILITIES_WARNING_DESC',$QUALIFIED_MODULE)}</p>
				</div>
			</div>
			<table class="table tableRWD table-bordered table-sm themeTableColor confTable">
				<thead>
					<tr class="blockHeader">
						<th colspan="4" class="mediumWidthType">
							<span>{App\Language::translate('LBL_SECURITY_ADVISORIES_CHECKER', $QUALIFIED_MODULE)}</span>
						</th>
					</tr>
					<tr class="blockHeader">
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_LIB_NAME', $QUALIFIED_MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_VULNERABILITY_NAME', $QUALIFIED_MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>{App\Language::translate('LBL_VULNERABILITY_URL', $QUALIFIED_MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span>CVE</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$VULNERABILITIES key=LIB_NAME item=LIB}
						{foreach from=$LIB['advisories'] item=ADVISORIE}
							<tr>
								<td><label>{$LIB_NAME} ({$LIB['version']})</label></td>
								<td><label>{$ADVISORIE['title']}</label></td>
								<td><label><a title="{$ADVISORIE['cve']}" target="_blank" rel="noreferrer noopener" href="{$ADVISORIE['link']}">{$ADVISORIE['link']}</a></label></td>
								<td><label>{$ADVISORIE['cve']}</label></td>
							</tr>
						{/foreach}
					{/foreach}
				</tbody>
			</table>
		{else}
			<div class="w-100 mb-2">
				<div class="alert alert-success" role="alert">
					<h4 class="alert-heading">
						<span class="fas fa-info-circle mr-1"></span>
						{\App\Language::translate('LBL_VULNERABILITIES_OK', $QUALIFIED_MODULE)}
					</h4>
					<p>{\App\Language::translate('LBL_VULNERABILITIES_OK_DESC', $QUALIFIED_MODULE)}</p>
				</div>
			</div>
		{/if}
		</div>
	</div>
{/strip}
