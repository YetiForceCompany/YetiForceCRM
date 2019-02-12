{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
<div class='widget_header row mb-2'>
	<div class="col-12">
		{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
	</div>
</div>
<div class="listViewPageDiv" id="portalListViewPage">
	<div class="listViewTopMenuDiv noprint">
		<div class="listViewActionsDiv d-inline-flex w-100 flex-wrap justify-content-between mb-2">
			<div class="btn-toolbar u-w-sm-down-100 d-flex flex-wrap flex-sm-nowrap mb-1 mb-md-0">
				<div class="btn-group listViewMassActions mr-sm-1 c-btn-block-sm-down mb-1 mb-sm-0">
					<button class="btn btn-light dropdown-toggle" data-toggle="dropdown">
						<span class="fa fa-list mr-1"></span>
						{\App\Language::translate('LBL_ACTIONS', $MODULE)}
					</button>
					<ul class="dropdown-menu">
						<li id="massDelete"><a class="dropdown-item" href="javascript:void(0);"
											   onclick="Portal_List_Js.massDeleteRecords();">{\App\Language::translate('LBL_DELETE', $MODULE)}</a>
						</li>
					</ul>
				</div>
				<div class="btn-group mr-md-1 c-btn-block-sm-down">
					<button class="btn btn-light addButton addBookmark">
						<span class="fas fa-plus mr-1"></span>
						{\App\Language::translate('LBL_ADD_BOOKMARK', $MODULE)}
					</button>
				</div>
			</div>
			<div class="paginationDiv">
				{include file=\App\Layout::getTemplatePath('Pagination.tpl', $MODULE)}
			</div>
		</div>
	</div>
	<div class="listViewContentDiv" id="listViewContents">
		{/strip}
