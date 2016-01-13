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
			<div class="col-xs-12">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			</div>
		</div>
    	<div class="listViewPageDiv" id="portalListViewPage">
		<div class="listViewTopMenuDiv noprint">
			<div class="listViewActionsDiv row">
				<div class="btn-toolbar col-md-6">
					<span class="btn-group listViewMassActions">
                        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><strong>{vtranslate('LBL_ACTIONS', $MODULE)}</strong>&nbsp;&nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li id="massDelete"><a href="javascript:void(0);" onclick="Portal_List_Js.massDeleteRecords();">{vtranslate('LBL_DELETE', $MODULE)}</a></li>
                        </ul>
					</span>
                    <span class="btn-group">
                        <button class="btn btn-default addButton addBookmark"><span class="glyphicon glyphicon-plus"></span>&nbsp;<strong>{vtranslate('LBL_ADD_BOOKMARK', $MODULE)}</strong></button>
                    </span>
				</div>
				<div class="col-md-6 btn-toolbar row">
					<div class="listViewActions pull-right">
						<div class="paginationDiv pull-right">
							{include file='Pagination.tpl'|@vtemplate_path:$MODULE}
						</div>
					</div>
				</div>
            </div>
		</div>
        <div class="listViewContentDiv" id="listViewContents">
{/strip}
