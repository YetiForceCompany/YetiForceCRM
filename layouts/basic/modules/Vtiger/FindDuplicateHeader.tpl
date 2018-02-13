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
	<div class='listViewPageDiv'>
		<div class="widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			</div>
		</div>
		<div class="row  listViewActionsDiv pushDown">
			<div class="btn-toolbar col-4">
				<span class="btn-group listViewMassActions">
					{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS}
						<span class="btn-group">
							<button id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_BASICACTION->getLabel())}" class="btn btn-danger" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0} onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick='window.location.href = "{$LISTVIEW_BASICACTION->getUrl()}"'{/if}><strong>{\App\Language::translate($LISTVIEW_BASICACTION->getLabel(), $MODULE)}</strong></button>
						</span>
					{/foreach}
				</span>
			</div>
			<div class='col-4'><div class="textAlignCenter"><h3 style='margin-top:2px'>{\App\Language::translate('LBL_DUPLICATE')}  {\App\Language::translate($MODULE, $MODULE)}</h3></div></div>
			<div class="col-12 col-sm-4 btn-toolbar">
				{include file=\App\Layout::getTemplatePath('ListViewActions.tpl')}
			</div>
		</div>
		<div id="listViewContents" class="listViewContentDiv">
		{/strip}
