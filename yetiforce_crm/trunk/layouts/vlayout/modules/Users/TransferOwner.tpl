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
<div id="massEditContainer" class='modelContainer'>
	<div class="modal-header contentsBackground">
		<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
		<h3 id="massEditHeader">{vtranslate('Transfer Ownership to User', $MODULE)}</h3>
	</div>
	<form class="form-horizontal" id="deleteUser" name="transferOwner" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="userid" value="{$USERID}" />
		<div name='massEditContent'>
			<div class="modal-body tabbable">
				<div class="tab-content massEditContent">
					<table class="massEditTable table table-bordered">
						<tr>
							<td class="fieldLabel alignMiddle">{vtranslate('Transfer Ownership to User', $MODULE)}</td>
							<td class="fieldValue">
								<select class="chzn-select {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="tranfer_owner_id" data-validation-engine="validate[ required]" >
									{foreach item=USER_MODEL key=USER_ID from=$USER_LIST}
										<option value="{$USER_ID}" >{$USER_MODEL->getName()}</option>
									{/foreach}
								</select>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
	</form>
</div>
{/strip}
