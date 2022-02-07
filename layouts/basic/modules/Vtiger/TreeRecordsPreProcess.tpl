{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="o-breadcrumb widget_header px-2 flex-column flex-sm-row">
		<div class="mr-auto row">
			<div class="d-flex align-items-center">
				{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK']}
			</div>
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="rowContent">
		<div class="col-md-12 text-nowrap table-responsive mt-2" id="recordsListContents">
{/strip}
