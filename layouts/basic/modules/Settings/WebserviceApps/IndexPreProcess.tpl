{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="widget_header row align-items-center mb-2">
		<div class="col-md-6">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
		<div class="col-md-6 d-flex justify-content-lg-end pr-3">
			<button class="btn btn-primary createKey"><span class="fas fa-plus mr-1"></span>{\App\Language::translate('LBL_ADD_APPLICATION',$QUALIFIED_MODULE)}</button>
		</div>
	</div>
	<div class="configContainer">
	{/strip}
