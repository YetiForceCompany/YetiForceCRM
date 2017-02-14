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
	<div id="transferOwnershipContainer" class='modelContainer modal fade' tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
					<h3 id="massEditHeader" class="modal-title">{vtranslate('LBL_TRANSFER_OWNERSHIP', $MODULE)}</h3>
				</div>
				<form class="form-horizontal" id="changeOwner" name="changeOwner" method="post" action="index.php">
					<div class="modal-body tabbable">
						<div class="form-group">
							<div class="col-sm-4 control-label">{vtranslate('LBL_ASSIGNED_TO', $MODULE)}</div>
							<div class="col-sm-7 controls">
								<select class="select2 form-control" data-validation-engine="validate[ required]" title="{vtranslate('LBL_TRANSFER_OWNERSHIP', $MODULE)}" name="transferOwnerId" id="transferOwnerId"
									{if AppConfig::performance('SEARCH_OWNERS_BY_AJAX')} 
										data-ajax-search="1" data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=getOwners&type=Edit" data-minimum-input="{AppConfig::performance('OWNER_MINIMUM_INPUT_LENGTH')}"
									{/if}>
									{if AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
										<option value="{$USER_MODEL->get('id')}" data-picklistvalue="{$USER_MODEL->getName()}">
											{$USER_MODEL->getName()}
										</option>
									{else}
										{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance()->getAccessibleUsers('', 'owner')}
										{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance()->getAccessibleGroups('', 'owner', true)}
										{assign var=CURRENT_USER_ID value=$USER_MODEL->get('id')}
										<optgroup label="{vtranslate('LBL_USERS')}">
											{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
												<option value="{$OWNER_ID}" data-picklistvalue="{$OWNER_NAME}" {if $FIELD_VALUE eq $OWNER_ID} selected {/if}
														data-userId="{$CURRENT_USER_ID}">
													{$OWNER_NAME}
												</option>
											{/foreach}
										</optgroup>
										<optgroup label="{vtranslate('LBL_GROUPS')}">
											{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
												<option value="{$OWNER_ID}" data-picklistvalue="{$OWNER_NAME}">
													{$OWNER_NAME}
												</option>
											{/foreach}
										</optgroup>
									{/if}
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-4 control-label">{vtranslate('LBL_SELECT_RELATED_MODULES',$MODULE)}</div>
							<div class="col-sm-7 controls"> 
								<select class="select2-container form-control columnsSelect" id="related_modules" title="{vtranslate('LBL_SELECT_RELATED_MODULES',$MODULE)}" data-placeholder="{vtranslate('--None--',$MODULE)}" multiple="" name="related_modules[]">
									{if $REL_BY_FIELDS}
										<optgroup label="{vtranslate('LBL_RELATIONSHIPS_BASED_ON_FIELDS')}">
											{foreach item=RELATED from=$REL_BY_FIELDS}
												{if !in_array($RELATED, $SKIP_MODULES)}
													<option value="{$RELATED.name}::0::{$RELATED.field}">{vtranslate($RELATED.name, $RELATED.name)} - {vtranslate($RELATED.field)} [M:1]</option>
												{/if}
											{/foreach}
										</optgroup>
									{/if}
									{if $REL_BY_RELATEDLIST}
										<optgroup label="{vtranslate('LBL_RELATIONSHIPS_BASED_ON_MODULES')}">
											{foreach item=RELATED from=$REL_BY_RELATEDLIST}
												{if !in_array($RELATED, $SKIP_MODULES)}
													<option value="{$RELATED.name}::{$RELATED.type}">{vtranslate($RELATED.name, $RELATED.name)} [{if $RELATED.type == 1}1:M{else}M:M{/if}]</option>
												{/if}
											{/foreach}
										</optgroup>
									{/if}
								</select>
							</div></br>
						</div>
						<div class="alert alert-info" role="alert">{vtranslate('LBL_TRANSFER_OWNERSHIP_DESC',$MODULE)}</div>
					</div>
					{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
				</form>
			</div>
		</div>
	</div>
{/strip}
