{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign 'MEMBERS' Settings_Groups_Member_Model::getAll()}
	{assign 'MEMBERS_DEFAULT' $MODULE_MODEL->getFilterPermissionsView($CVID, $TYPE)}
	<input type="hidden" id="cvid" value="{$CVID}" />
	<input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}" />
	<input type="hidden" id="type" value="{$TYPE}" />
	<div class="modal-header">
		<h5 class="modal-title">{\App\Language::translate($TITLE_LABEL, $MODULE_NAME)}: {\App\Language::translate($RECORD_MODEL->getName(), $SOURCE_MODULE)}</h5>
	</div>
	<div class="modal-body">
		<div class="">
			<div class="form-group">
				<label class="col-form-label">
					{\App\Language::translate('LBL_ALL_GROUP_LIST', $MODULE_NAME)}
				</label>
				<div class="input-group">
					<select class="select2 form-control add" id="allGroups" {if $IS_DEFAULT} disabled="disabled" {/if}>
						{foreach from=$MEMBERS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
							<optgroup label="{\App\Language::translate($GROUP_LABEL, $QUALIFIED_MODULE)}">
								{foreach from=$ALL_GROUP_MEMBERS item=MEMBER key=QUALIFIEDID}
									{if isset($MEMBERS_DEFAULT[$GROUP_LABEL]) && is_array($MEMBERS_DEFAULT[$GROUP_LABEL]) && in_array($QUALIFIEDID,$MEMBERS_DEFAULT[$GROUP_LABEL])}
										{continue}
									{/if}
									<option value="{$MEMBER->get('id')}" data-member-type="{$GROUP_LABEL}">{\App\Language::translate($MEMBER->get('name'), $QUALIFIED_MODULE)}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
					<div class="input-group-append">
						<button type="button" class="btn btn-success moveItem" data-source="add" data-target="remove" data-operator="1" title="{\App\Language::translate('LBL_ADD_PERMISSIONS', $MODULE_NAME)}" {if $IS_DEFAULT} disabled="disabled" {/if}>
							<span class="fas fa-arrow-down"></span>
						</button>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-form-label">
					{\App\Language::translate('LBL_GROUP_MEMBERS', $MODULE_NAME)}
				</label>
				<div class="input-group">
					<select class="select2 form-control remove" id="groups">
						{foreach from=$MEMBERS_DEFAULT key=LABEL item=GROUP}
							<optgroup label="{\App\Language::translate($LABEL, $QUALIFIED_MODULE)}">
								{foreach from=$GROUP item=USER}
									{assign 'MEMBER' $MEMBERS[$LABEL][$USER]}
									{if $MEMBER}
										<option value="{$USER}" data-member-type="{$LABEL}">{\App\Language::translate($MEMBER->get('name'), $QUALIFIED_MODULE)}</option>
									{/if}
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
					<div class="input-group-append">
						<button type="button" class="btn btn-danger moveItem" data-source="remove" data-target="add" data-operator="0" title="{\App\Language::translate('LBL_RECEIVE_PERMISSION', $MODULE_NAME)}"><span class="fas fa-arrow-up"></span></button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-warning dismiss" data-dismiss="modal">{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}</button>
	</div>
{/strip}
