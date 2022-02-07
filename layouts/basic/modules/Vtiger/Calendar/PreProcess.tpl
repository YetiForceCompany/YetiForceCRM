{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Calendar-PreProcess -->
	{include file=\App\Layout::getTemplatePath('PageHeader.tpl', $MODULE_NAME)}
	<div class="c-calendar-view js-calendar--scroll h-100">
		<div class="mainContainer pt-md-0 pt-1">
			<div class="o-breadcrumb widget_header d-flex justify-content-between align-items-center px-2 flex-column flex-sm-row"
				data-js="height">
				<div class="mr-auto">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
				</div>
			</div>
			<div id="centerPanel" class="contentsDiv js-contents-div" data-js="css">
				<!-- /tpl-Base-Calendar-PreProcess -->
{/strip}
