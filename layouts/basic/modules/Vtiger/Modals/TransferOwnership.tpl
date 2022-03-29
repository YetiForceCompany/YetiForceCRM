{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Modals-TransferOwnership -->
	<div class="modal-body mb-0">
		<form id="changeOwner" name="changeOwner" method="post" action="index.php">
			<div class="modal-body">
				<div class="form-group row">
					<label class="col col-form-label col-form-label-sm text-right" for="transferOwnerId">{\App\Language::translate('LBL_ASSIGNED_TO', $MODULE_NAME)}</label>
					<div class="col-md-8">
						<select name="transferOwnerId" id="transferOwnerId" class="select2 form-control" data-validation-engine="validate[ required]"
							title="{\App\Language::translate('LBL_TRANSFER_OWNERSHIP', $MODULE_NAME)}"
							{if App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
								data-ajax-search="1" data-ajax-url="index.php?module={$MODULE_NAME}&action=Fields&mode=getOwners&fieldName=assigned_user_id" data-minimum-input="{App\Config::performance('OWNER_MINIMUM_INPUT_LENGTH')}"
							{/if}>
							{if App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
								<option value="{$USER_MODEL->get('id')}" data-picklistvalue="{$USER_MODEL->getName()}">{$USER_MODEL->getName()}</option>
							{else}
								{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance()->getAccessibleUsers('', 'owner')}
								{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance()->getAccessibleGroups('', 'owner', true)}
								{assign var=CURRENT_USER_ID value=$USER_MODEL->get('id')}
								<optgroup label="{\App\Language::translate('LBL_USERS')}">
									{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
										<option value="{$OWNER_ID}" data-picklistvalue="{$OWNER_NAME}" data-userId="{$CURRENT_USER_ID}">{$OWNER_NAME}</option>
									{/foreach}
								</optgroup>
								<optgroup label="{\App\Language::translate('LBL_GROUPS')}">
									{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
										<option value="{$OWNER_ID}" data-picklistvalue="{$OWNER_NAME}">{$OWNER_NAME}</option>
									{/foreach}
								</optgroup>
							{/if}
						</select>
					</div>
				</div>
				<div class="form-group row">
					<label class="col col-form-label-sm text-right">{\App\Language::translate('LBL_SELECT_RELATED_MODULES',$MODULE_NAME)}</label>
					<div class="col-md-8">
						<select name="related_modules[]" multiple="" class="select2-container form-control columnsSelect" id="related_modules"
							title="{\App\Language::translate('LBL_SELECT_RELATED_MODULES',$MODULE_NAME)}"
							data-placeholder="{\App\Language::translate('--None--',$MODULE_NAME)}">
							{if $REL_BY_FIELDS}
								<optgroup label="{\App\Language::translate('LBL_RELATIONSHIPS_BASED_ON_FIELDS')}">
									{foreach item=RELATED from=$REL_BY_FIELDS}
										{if !in_array($RELATED, $SKIP_MODULES)}
											<option value="{$RELATED.name}::0::{$RELATED.field}">
												{\App\Language::translate($RELATED.name, $RELATED.name)} - {\App\Language::translate($RELATED.field)} [M:1]
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
											<option value="{$RELATED.name}::{$RELATED.type}">
												{\App\Language::translate($RELATED.name, $RELATED.name)} [{if $RELATED.type == 1}1:M{else}M:M{/if}]
											</option>
										{/if}
									{/foreach}
								</optgroup>
							{/if}
						</select>
					</div>
					</br>
				</div>
				<div class="alert alert-info" role="alert">{\App\Language::translate('LBL_TRANSFER_OWNERSHIP_DESC',$MODULE_NAME)}</div>
			</div>
			{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE_NAME) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
		</form>
	</div>
	<!-- /tpl-Base-Modals-TransferOwnership -->
{/strip}
