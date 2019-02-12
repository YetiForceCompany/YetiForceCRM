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
	<div class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<h5 class="modal-title"><i
								class="fa fa-user"></i> {\App\Language::translate('LBL_TRANSFER_OWNERSHIP', $MODULE)}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form id="changeOwner" name="changeOwner" method="post" action="index.php">
					<div class="modal-body">
						<div class="form-group row">
							<label class="col col-form-label col-form-label-sm text-right"
								   for="transferOwnerId">{\App\Language::translate('LBL_ASSIGNED_TO', $MODULE)}</label>
							<div class="col-md-8">
								<select class="select2 form-control" data-validation-engine="validate[ required]"
										title="{\App\Language::translate('LBL_TRANSFER_OWNERSHIP', $MODULE)}"
										name="transferOwnerId" id="transferOwnerId"
										{if AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
									data-ajax-search="1" data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=getOwners&fieldName=assigned_user_id" data-minimum-input="{AppConfig::performance('OWNER_MINIMUM_INPUT_LENGTH')}"
										{/if}>
									{if AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
										<option value="{$USER_MODEL->get('id')}"
												data-picklistvalue="{$USER_MODEL->getName()}">
											{$USER_MODEL->getName()}
										</option>
									{else}
										{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance()->getAccessibleUsers('', 'owner')}
										{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance()->getAccessibleGroups('', 'owner', true)}
										{assign var=CURRENT_USER_ID value=$USER_MODEL->get('id')}
										<optgroup label="{\App\Language::translate('LBL_USERS')}">
											{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
												<option value="{$OWNER_ID}" data-picklistvalue="{$OWNER_NAME}"
														data-userId="{$CURRENT_USER_ID}">
													{$OWNER_NAME}
												</option>
											{/foreach}
										</optgroup>
										<optgroup label="{\App\Language::translate('LBL_GROUPS')}">
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
						<div class="form-group row">
							<label class="col col-form-label-sm text-right">{\App\Language::translate('LBL_SELECT_RELATED_MODULES',$MODULE)}</label>
							<div class="col-md-8">
								<select class="select2-container form-control columnsSelect" id="related_modules"
										title="{\App\Language::translate('LBL_SELECT_RELATED_MODULES',$MODULE)}"
										data-placeholder="{\App\Language::translate('--None--',$MODULE)}" multiple=""
										name="related_modules[]">
									{if $REL_BY_FIELDS}
										<optgroup
												label="{\App\Language::translate('LBL_RELATIONSHIPS_BASED_ON_FIELDS')}">
											{foreach item=RELATED from=$REL_BY_FIELDS}
												{if !in_array($RELATED, $SKIP_MODULES)}
													<option value="{$RELATED.name}::0::{$RELATED.field}">{\App\Language::translate($RELATED.name, $RELATED.name)}
														- {\App\Language::translate($RELATED.field)} [M:1]
													</option>
												{/if}
											{/foreach}
										</optgroup>
									{/if}
									{if $REL_BY_RELATEDLIST}
										<optgroup
												label="{\App\Language::translate('LBL_RELATIONSHIPS_BASED_ON_MODULES')}">
											{foreach item=RELATED from=$REL_BY_RELATEDLIST}
												{if !in_array($RELATED, $SKIP_MODULES)}
													<option value="{$RELATED.name}::{$RELATED.type}">{\App\Language::translate($RELATED.name, $RELATED.name)}
														[{if $RELATED.type == 1}1:M{else}M:M{/if}]
													</option>
												{/if}
											{/foreach}
										</optgroup>
									{/if}
								</select>
							</div>
							</br>
						</div>
						<div class="alert alert-info"
							 role="alert">{\App\Language::translate('LBL_TRANSFER_OWNERSHIP_DESC',$MODULE)}</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
{/strip}
