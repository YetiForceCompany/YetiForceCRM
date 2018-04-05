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
	<div class='widget_header row '>
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
		</div>
	</div>
	<div class="listViewPageDiv" id="portalListViewPage">
		<div class="listViewTopMenuDiv noprint">
			<div class="listViewActionsDiv row">
				<div class="btn-toolbar col-md-6">
					<span class="btn-group listViewMassActions">
                        <button class="btn btn-light dropdown-toggle" data-toggle="dropdown"><strong>{\App\Language::translate('LBL_ACTIONS', $MODULE)}</strong>&nbsp;&nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li id="massDelete"><a class="dropdown-item" href="javascript:void(0);" onclick="Portal_List_Js.massDeleteRecords();">{\App\Language::translate('LBL_DELETE', $MODULE)}</a></li>
                        </ul>
					</span>
                    <span class="btn-group">
                        <button class="btn btn-light addButton addBookmark"><span class="fas fa-plus"></span>&nbsp;<strong>{\App\Language::translate('LBL_ADD_BOOKMARK', $MODULE)}</strong></button>
                    </span>
				</div>
				<div class="col-md-6 btn-toolbar row">
					<div class="listViewActions float-right">
						<div class="paginationDiv float-right">
							{include file=\App\Layout::getTemplatePath('Pagination.tpl', $MODULE)}
						</div>
					</div>
				</div>
            </div>
		</div>
        <div class="listViewContentDiv" id="listViewContents">
		{/strip}
