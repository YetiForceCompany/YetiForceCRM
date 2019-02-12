{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="row padding0">
		<div class="col-md-9 rowContent">
			<div class="widget_header row paddingTop10">
				<div class="float-left paddingLeftMd">
					<div class="btn-toolbar">
						{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK']}
					</div>
				</div>
				<div class="col-md-10">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
				</div>
			</div>
			<div class="row">
				<div class="col-md-12" id="recordsListContents">
				{/strip}
