{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-MailIntegration-Index -->
<div class="o-breadcrumb widget_header row mb-2">
	<div class="col-md-12">
		{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
	</div>
</div>
<div>
	<ul id="tabs" class="nav nav-tabs my-2 mr-0" data-tabs="tabs">
		<li class="nav-item">
			<a class="nav-link {if $ACTIVE_TAB eq 'outlook'}active{/if}" href="#outlook" data-toggle="tab">
				<span class="mdi mdi-microsoft-outlook mr-2 u-fs-lg"></span>{\App\Language::translate('LBL_OUTLOOK', $QUALIFIED_MODULE)}
			</a>
		</li>
	</ul>
</div>
<div id="my-tab-content" class="tab-content">
	<div class="tab-pane {if $ACTIVE_TAB eq 'outlook'}active{/if}" id="outlook">
		{if !\App\YetiForce\Shop::check('YetiForceOutlook')}
			<div class="alert alert-warning">
				<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
				{\App\Language::translate('LBL_PAID_FUNCTIONALITY', $QUALIFIED_MODULE)} <a class="btn btn-primary btn-sm" href="index.php?parent=Settings&module=YetiForce&view=Shop&product=YetiForceOutlook&mode=showProductModal"><span class="yfi yfi-shop mr-2"></span>{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>
			</div>
		{else}
			{if !Settings_MailIntegration_Activate_Model::isActive('outlook')}
				<div class="alert alert-danger">
					<form action='index.php' method="POST" enctype="multipart/form-data">
						<input type="hidden" name="module" value="MailIntegration"/>
						<input type="hidden" name="parent" value="Settings"/>
						<input type="hidden" name="action" value="Activate"/>
						<input type="hidden" name="source" value="outlook"/>
						<span class="mdi mdi-alert-outline mr-3 u-fs-10x float-left"></span>
						{\App\Language::translateArgs('LBL_FUNCTIONALITY_HAS_NOT_YET_BEEN_ACTIVATED', $QUALIFIED_MODULE,'Outlook')}<br />
						{\App\Language::translate('LBL_OUTLOOK_ACTIVATED_ALERT', $QUALIFIED_MODULE)}
						<button type="submit" class="btn btn-primary btn-sm ml-3 float-right">
							<span class="mdi mdi-check mr-2 float-left"></span>
							{\App\Language::translate('LBL_ACTIVATE_FUNCTIONALITY', $QUALIFIED_MODULE)}
						</button>
					</form>
				</div>
			{/if}
			<div class="alert alert-info">
				<span class="mdi mdi-information-outline mr-2 u-fs-lg float-left"></span>
				{\App\Language::translateArgs('LBL_OUTLOOK_ALERT', $QUALIFIED_MODULE, '<a rel="noreferrer noopener" target="_blank" href="https://support.microsoft.com/en-us/office/installed-add-ins-a61762b7-7a82-47bd-b14e-bbc15eaeb70f">support.microsoft.com</a>')}
				<a href="index.php?parent=Settings&module=MailIntegration&action=Download&mode=outlook" class="btn btn-primary btn-sm float-right">
					<span class="fas fa-download mr-2"></span>
					{App\Language::translate('BTN_DOWNLOAD_OUTLOOK_INSTALLATION_FILE', $QUALIFIED_MODULE)}
				</a>
			</div>
			<form class="js-form-single-save js-validate-form" data-js="container|validationEngine">
				<input type="hidden" name="parent" value="Settings">
				<input type="hidden" name="module" value="{$MODULE_NAME}">
				<input type="hidden" name="action" value="SaveConfigForm">
				{include file=\App\Layout::getTemplatePath('ConfigForm.tpl','Vtiger/Utils')}
			</form>
		{/if}
		</table>
	</div>
</div>
<!-- /tpl-Settings-MailIntegration-Index -->
{/strip}
