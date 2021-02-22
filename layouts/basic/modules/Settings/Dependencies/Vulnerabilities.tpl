{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Dependencies-Vulnerabilities -->
	<div class="pt-md-0 pt-1">
		<div class="o-breadcrumb widget_header mb-2 d-flex flex-nowrap flex-md-wrap justify-content-between px-2 row">
			<div class="o-breadcrumb__container">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="mb-2">
			{if !\App\YetiForce\Register::verify(false)}
				<div class="alert alert-danger" role="alert">
					<h4 class="alert-heading">
						<span class="yfi yfi-yeti-register-alert mr-2"></span>
						{\App\Language::translate('LBL_YETIFORCE_NOT_REGISTRATION_TITLE')}
					</h4>
					<p>{\App\Language::translate('LBL_YETIFORCE_NOT_REGISTRATION_DESC')}</p>
					{if \App\Security\AdminAccess::isPermitted('Companies')}
						<a href="index.php?module=Companies&parent=Settings&view=List&displayModal=online" class="btn btn-success mr-1">
							<span class="adminIcon-company-detlis mr-2"></span>
							{App\Language::translate('LBL_YETIFORCE_REGISTRATION')}
						</a>
					{/if}
				</div>
			{else if !empty($VULNERABILITIES)}
				<div class="w-100 mb-2">
					<div class="alert alert-danger" role="alert">
						<h4 class="alert-heading">
							<span class="fas fa-skull-crossbones mr-3 u-fs-4x float-left"></span>
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
									<td><label>{\App\Purifier::encodeHtml($LIB_NAME)} ({\App\Purifier::encodeHtml($LIB['version'])})</label></td>
									<td><label>{\App\Purifier::encodeHtml($ADVISORIE['title'])}</label></td>
									<td><label><a title="{$ADVISORIE['cve']}" target="_blank" rel="noreferrer noopener" href="{\App\Purifier::encodeHtml($ADVISORIE['link'])}">{\App\Purifier::encodeHtml($ADVISORIE['link'])}</a></label></td>
									<td><label>{\App\Purifier::encodeHtml($ADVISORIE['cve'])}</label></td>
								</tr>
							{/foreach}
						{/foreach}
					</tbody>
				</table>
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
