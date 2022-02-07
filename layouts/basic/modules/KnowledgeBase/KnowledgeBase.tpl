{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="o-breadcrumb widget_header row">
		<div class="col-sm-8 col-12">
			{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS='u-h-fit my-auto pr-1'}
			<div class="btn-group">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
	</div>
	<div class="quasar-container quasar-reset absolute w-100">
		<div id="KnowledgeBaseContainer"></div>
	</div>
{/strip}
