{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="widget_header row">
		<div class="col-md-6">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			{\App\Language::translate('LBL_WEBSERVICE_APPS_DESCRIPTION',$QUALIFIED_MODULE)}

		</div>
		<div class="col-md-6">
			<button class="btn btn-primary float-right createKey float-right">{\App\Language::translate('LBL_ADD_APPLICATION',$QUALIFIED_MODULE)}</button>
		</div>
	</div>
	<div class="configContainer">
	{/strip}
