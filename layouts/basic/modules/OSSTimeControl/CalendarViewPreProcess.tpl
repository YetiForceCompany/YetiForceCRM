{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
{include file=\App\Layout::getTemplatePath('Header.tpl', $MODULE)}
<div class="bodyContents">
	<div class="mainContainer">
		<div class="o-breadcrumb js-breadcrumb widget_header d-flex justify-content-between align-items-center px-2 flex-column flex-sm-row"
			 data-js="height">
			<div class="mr-auto">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
			<div class="mb-1 mb-sm-0 ml-sm-1">
				{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS='listViewMassActions btn-group' BTN_CLASS='btn-outline-dark'}
			</div>
		</div>
		<div id="centerPanel" class="contentsDiv">
			{/strip}
