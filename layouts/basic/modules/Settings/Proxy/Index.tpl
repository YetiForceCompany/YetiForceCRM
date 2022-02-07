{*
<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Proxy-Index -->
	<div class="o-breadcrumb widget_header row mb-2">
		<div class="col-md-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="alert alert-block alert-info mb-2">
		<button button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
		<span>
			{\App\Language::translate('LBL_PROXY_INFORMATION', $QUALIFIED_MODULE)}
		</span>
	</div>
	<form class="js-form-single-save js-validate-form" data-js="container|validationEngine">
		<input type="hidden" name="parent" value="Settings">
		<input type="hidden" name="module" value="{$MODULE_NAME}">
		<input type="hidden" name="action" value="SaveConfigForm">
		{include file=\App\Layout::getTemplatePath('ConfigForm.tpl','Vtiger/Utils')}
	</form>
	<!-- /tpl-Settings-Proxy-Index -->
{/strip}
