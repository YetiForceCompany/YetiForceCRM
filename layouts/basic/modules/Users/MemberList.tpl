{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Users-MemberList -->
	<form name="MemberList" class="form-horizontal validateForm">
		<input type="hidden" name="module" value="{$MODULE_NAME}" />
		<input type="hidden" name="action" value="Groups" />
		<input type="hidden" name="mode" value="addMembers" />
		<input type="hidden" name="groupID" value="{$GROUP_ID}" />
		<div class="modal-body">
			<div class="row">
				<div class="col-12">
					<ul class="list-inline groupMembersColors mb-1 d-flex flex-nowrap flex-column flex-sm-row">
						{foreach from=array_keys($GROUPS) item=GROUP_LABEL}
							{if !empty($GROUPS[$GROUP_LABEL])}
								<li class="{$GROUP_LABEL} text-center px-4 m-0 list-inline-item w-100">
									{\App\Language::translate($GROUP_LABEL, $MODULE_NAME)}
								</li>
							{/if}
						{/foreach}
					</ul>
				</div>
				<div class="col-12">
					<select id="{$MODULE_NAME}_{$VIEW}_fieldName_members" tabindex="1" title="{\App\Language::translate('LBL_MEMBERS', $MODULE_NAME)}" multiple="multiple" class="select2 form-control" name="members[]" data-validation-engine="validate[required]">
						{foreach from=$GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
							{if empty($ALL_GROUP_MEMBERS)} {continue} {/if}
							<optgroup label="{\App\Language::translate($GROUP_LABEL)}">
								{foreach from=$ALL_GROUP_MEMBERS key=MEMBER_ID item=MEMBER}
									<option class="{$MEMBER['type']}" value="{$MEMBER_ID}">{\App\Language::translate($MEMBER['name'])}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
	</form>
	<!-- /tpl-Users-MemberList -->
{/strip}
