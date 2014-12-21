{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{strip}
<div class="container-fluid" id="AsteriskServerDetails">
	<div class="widget_header row-fluid">
		<div class="span8"><h3>{vtranslate('LBL_PBXMANAGER', $QUALIFIED_MODULE)}</h3></div>
                {assign var=MODULE_MODEL value=Settings_PBXManager_Module_Model::getCleanInstance()}
                <div class="span4"><div class="pull-right"><button class="btn editButton" data-url='{$MODULE_MODEL->getEditViewUrl()}&mode=showpopup&id={$RECORD_ID}' type="button" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"><strong>{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}</strong></button></div></div>
	</div>
	<hr>
        
        <div class="contents row-fluid">
		<table class="table table-bordered table-condensed themeTableColor">
			<thead>
				<tr class="blockHeader">
					<th colspan="2" class="mediumWidthType">
						<span class="alignMiddle">{vtranslate('LBL_PBXMANAGER_CONFIG', $QUALIFIED_MODULE)}</span>
					</th>
				</tr>
			</thead>
			<tbody>
                            {assign var=FIELDS value=PBXManager_PBXManager_Connector::getSettingsParameters()}
                            {foreach item=FIELD_TYPE key=FIELD_NAME from=$FIELDS}
                                <tr><td width="25%"><label class="muted pull-right marginRight10px">{vtranslate($FIELD_NAME,$QUALIFIED_MODULE)}</label></td>
					<td style="border-left: none;"><span>{$RECORD_MODEL->get($FIELD_NAME)}</span></td></tr>
                            {/foreach}
                            <input type="hidden" name="module" value="PBXManager"/>
                            <input type="hidden" name="action" value="SaveAjax"/>
                            <input type="hidden" name="parent" value="Settings"/>
                            <input type="hidden" class="recordid" name="id" value="{$RECORD_ID}">
			</tbody>
		</table>
	</div>
</div>
<br>
<div class="span8 alert alert-danger container-fluid">
    {vtranslate('LBL_NOTE', $QUALIFIED_MODULE)}<br>
    {vtranslate('LBL_PBXMANAGER_INFO', $QUALIFIED_MODULE)}
</div>	
{/strip}