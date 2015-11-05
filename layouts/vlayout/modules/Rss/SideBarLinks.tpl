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
<div class="quickLinksDiv">
    {assign var=SIDEBARLINK value=$QUICK_LINKS['SIDEBARLINK'][0]}
    <div style="margin-bottom: 5px" class="btn-group">
        <button id="rssAddButton" class="btn btn-default addButton col-md-12 rssAddButton" data-href="{$SIDEBARLINK->getUrl()}">
            <img src="layouts/vlayout/skins/images/rss_add.png" alt="{vtranslate(LBL_ADD_FEED_SOURCE, $MODULE)}" />
            <strong>&nbsp;&nbsp; {vtranslate($SIDEBARLINK->getLabel(), $MODULE)}</strong>
        </button>
    </div>
    <div class="rssAddFormContainer hide">
    </div>
</div>
{/strip}