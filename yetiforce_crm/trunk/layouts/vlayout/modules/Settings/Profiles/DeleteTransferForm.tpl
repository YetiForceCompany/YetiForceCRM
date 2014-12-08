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
<div class="modelContainer">
	<div class="row-fluid">
		<div class="modal-header">
			<button class="close vtButton" data-dismiss="modal">×</button>
			<h3>{vtranslate('LBL_DELETE_PROFILE', $QUALIFIED_MODULE)} - {$RECORD_MODEL->getName()}</h3>
		</div>
		<form class="form-horizontal" id="DeleteModal" name="AddComment" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="Delete" />
			<input type="hidden" name="record" id="record" value="{$RECORD_MODEL->getId()}" />

			<div class="modal-body">
				<div class="control-group">
					<div class="control-label">{vtranslate('LBL_TRANSFER_ROLES_TO_PROFILE',$QUALIFIED_MODULE)}</div>
					<div class="controls">
						<select id="transfer_record" name="transfer_record" class="chzn-select">
							<optgroup label="{vtranslate('LBL_PROFILES', $QUALIFIED_MODULE)}">
								{foreach from=$ALL_RECORDS item=PROFILE_MODEL}
									{assign var=PROFILE_ID value=$PROFILE_MODEL->get('profileid')}
									{if $PROFILE_ID neq $RECORD_MODEL->getId()}
										<option value="{$PROFILE_ID}">{$PROFILE_MODEL->get('profilename')}</option>
									{/if}
								{/foreach}
							</optgroup>
						</select>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div class=" pull-right cancelLinkContainer">
					<a class="cancelLink" data-dismiss="modal" type="reset">Cancel</a>
				</div>
				<button class="btn btn-success pull-right" type="submit">{vtranslate('LBL_SAVE', $MODULE)}</button>
			</div>
		</form>
	</div>
</div>				
{/strip}