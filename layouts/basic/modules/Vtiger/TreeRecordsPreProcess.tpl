{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
		<div class="treeviewViewContainer rowContent js-sitebar--active">
			<div class="o-breadcrumb widget_header mb-2 d-flex justify-content-between px-2">
				<div class="col-md-10">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
				</div>
			</div>
			<div class="c-list__buttons d-flex flex-wrap flex-sm-nowrap u-w-sm-down-100">
				{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS='buttonTextHolder mr-sm-1 mb-1 mb-sm-0 c-btn-block-sm-down'}
				{foreach item=LINK from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
					{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='listView' CLASS='mr-sm-1 mb-1 c-btn-block-sm-down'}
				{/foreach}
			</div>
			<div class="row overflow-auto">
				<div class="col-md-12" id="recordsListContents">
{/strip}
