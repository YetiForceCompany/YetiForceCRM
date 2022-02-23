{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-ConditionBuilder-Owner">
		{assign var=VALUES value=explode('##', $VALUE)}
		{assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->getName()}
		{assign var=OWNER_INSTANCE value=\App\Fields\Owner::getInstance($FIELD_MODEL->getModuleName())}
		{if !App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
			{assign var=ALL_ACTIVEUSER_LIST value=$OWNER_INSTANCE->getAccessibleUsers()}
			{if $ASSIGNED_USER_ID neq 'modifiedby'}
				{assign var=ALL_ACTIVEGROUP_LIST value=$OWNER_INSTANCE->getAccessibleGroups()}
			{else}
				{assign var=ALL_ACTIVEGROUP_LIST value=[]}
			{/if}
		{/if}
		<select class="select2 form-control js-condition-builder-value"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}"
			multiple="multiple"
			data-js="val"
			data-placeholder="{\App\Language::translate('LBL_SELECT_OPTION')}"
			{if App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
				data-ajax-search="1" data-ajax-url="index.php?module={$MODULE_NAME}&action=Fields&mode=getOwners&fieldName={$ASSIGNED_USER_ID}" data-minimum-input="{App\Config::performance('OWNER_MINIMUM_INPUT_LENGTH')}" {' '}
			{/if}>
			{if App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
				{foreach from=$VALUES item=OWNER_ID}
					{if false !== strpos($OWNER_ID, ':')}
						<option value="{$OWNER_ID}" selected>{\App\Labels::member($OWNER_ID)}</option>
					{else}
						<option value="{$OWNER_ID}" selected>{\App\Fields\Owner::getLabel($OWNER_ID)}</option>
					{/if}
				{/foreach}
			{else}
				{if count($ALL_ACTIVEUSER_LIST) gt 0}
					<optgroup label="{\App\Language::translate('LBL_USERS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
							<option value="{$OWNER_ID}" {if in_array($OWNER_ID, $VALUES)} selected {/if}>
								{$OWNER_NAME}
							</option>
						{/foreach}
					</optgroup>
				{/if}
				{if count($ALL_ACTIVEGROUP_LIST) gt 0}
					<optgroup label="{\App\Language::translate('LBL_GROUPS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
							<option value="{$OWNER_ID}" {if in_array($OWNER_ID, $VALUES)} selected {/if}>
								{\App\Language::translate($OWNER_NAME)}
							</option>
						{/foreach}
					</optgroup>
				{/if}
				{assign var=ALL_ACTIVEGROUP_LIST value=$OWNER_INSTANCE->getGroups(false)}
				{if count($ALL_ACTIVEGROUP_LIST) gt 0}
					<optgroup label="{\App\Language::translate('LBL_GROUP_USERS', $MODULE_NAME)}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
							{assign var="MEMBER_ID" value="{\App\PrivilegeUtil::MEMBER_TYPE_GROUPS}:{$OWNER_ID}"}
							<option class="{\App\PrivilegeUtil::MEMBER_TYPE_GROUPS}" value="{$MEMBER_ID}"
								data-member-type="{\App\PrivilegeUtil::MEMBER_TYPE_GROUPS}"
								{if in_array($MEMBER_ID, $VALUES)} selected {/if}>
								{\App\Language::translate($OWNER_NAME)}
							</option>
						{/foreach}
					</optgroup>
				{/if}
				{assign var=ALL_ROLES value=\Settings_Roles_Record_Model::getAll()}
				<optgroup label="{\App\Language::translate('LBL_ROLE_USERS', $MODULE_NAME)}">
					{foreach from=$ALL_ROLES item=MEMBER}
						{assign var="MEMBER_ID" value="{\App\PrivilegeUtil::MEMBER_TYPE_ROLES}:{$MEMBER->getId()}"}
						<option class="{\App\PrivilegeUtil::MEMBER_TYPE_ROLES}" value="{$MEMBER_ID}"
							data-member-type="{\App\PrivilegeUtil::MEMBER_TYPE_ROLES}"
							{if in_array($MEMBER_ID, $VALUES)} selected {/if}>
							{\App\Language::translate($MEMBER->getName(), $MODULE_NAME)}
						</option>
					{/foreach}
				</optgroup>
				<optgroup label="{\App\Language::translate('LBL_ROLE_AND_SUBORDINATES_USERS', $MODULE_NAME)}">
					{foreach from=$ALL_ROLES item=MEMBER}
						{assign var="MEMBER_ID" value="{\App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES}:{$MEMBER->getId()}"}
						<option class="{\App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES}" value="{$MEMBER_ID}"
							data-member-type="{\App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES}"
							{if in_array($MEMBER_ID, $VALUES)} selected {/if}>
							{\App\Language::translate($MEMBER->getName(), $MODULE_NAME)}
						</option>
					{/foreach}
				</optgroup>
			{/if}
		</select>
	</div>
{/strip}
