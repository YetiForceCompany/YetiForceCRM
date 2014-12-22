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
<div id="accountHierarchyContainer" class="modelContainer" style='min-width:750px'>
	<div class="modal-header">
		<button class="close" data-dismiss="modal" title="{vtranslate('LBL_CLOSE')}">x</button>
		<h3>{vtranslate('LBL_SHOW_ACCOUNT_HIERARCHY', $MODULE)}</h3>
	</div>
	<div class="modal-body">
            <div id ="hierarchyScroll" style="margin-right: 8px;">
		<table class="table table-bordered">
			<thead>
				<tr class="blockHeader">
				{foreach item=HEADERNAME from=$ACCOUNT_HIERARCHY['header']}
					<th>{vtranslate($HEADERNAME, $MODULE)}</th>
				{/foreach}
				</tr>
			</thead>
		{foreach item=ENTRIES from=$ACCOUNT_HIERARCHY['entries']}
			<tbody>
				<tr>
				{foreach item=LISTFIELDS from=$ENTRIES}
					<td>{$LISTFIELDS}</td>
				{/foreach}
				</tr>
			</tbody>
		{/foreach}
		</table>
	</div>
        </div>
        <div class="modal-footer">
            <div class=" pull-right cancelLinkContainer">
                <button class="btn btn-primary" type="reset" data-dismiss="modal"><strong>{vtranslate('LBL_CLOSE', $MODULE)}</strong></button>
            </div>
        </div>
	</div>
    {/strip}