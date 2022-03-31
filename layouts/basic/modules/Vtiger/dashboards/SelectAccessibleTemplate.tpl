{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-dashboards-SelectAccessibleTemplate -->
	{assign var=ACCESS_OPTIONS value=\App\Json::decode(html_entity_decode($WIDGET->get('owners')))}
	{if !is_array($ACCESS_OPTIONS.available)}
		{$ACCESS_OPTIONS.available = array($ACCESS_OPTIONS.available)}
	{/if}
	{if !isset($OWNER)}
		{assign var=OWNER value=''}
	{/if}
	{if empty($SOURCE_MODULE) && $MODULE_NAME != 'Home'}
		{assign var=SOURCE_MODULE value=$MODULE_NAME}
	{/if}
	{if !empty($SOURCE_MODULE) && App\Config::performance('SEARCH_SHOW_OWNER_ONLY_IN_LIST') && !\App\Config::module($SOURCE_MODULE, 'DISABLED_SHOW_OWNER_ONLY_IN_LIST', false)}
		{if !empty($USER_CONDITIONS)}
			{assign var=USERS_GROUP_LIST value=\App\Fields\Owner::getInstance($SOURCE_MODULE)->getUsersAndGroupForModuleList(false,$USER_CONDITIONS)}
		{/if}
		{if !empty($USERS_GROUP_LIST['users'])}
			{assign var=ACCESSIBLE_USERS value=$USERS_GROUP_LIST['users']}
		{/if}
		{if !empty($USERS_GROUP_LIST['group'])}
			{assign var=ACCESSIBLE_GROUPS value=$USERS_GROUP_LIST['group']}
		{/if}
	{/if}
	<div class="input-group input-group-sm">
		<span class="input-group-prepend">
			<span class="input-group-text">
				<span class="fas fa-user iconMiddle" title="{\App\Language::translate('Assigned To', $MODULE_NAME)}"></span>
			</span>
		</span>
		<select class="widgetFilter select2 owner form-control" aria-label="{\App\Language::translate('LBL_OWNER')}"
			name="owner" title="{\App\Language::translate('LBL_OWNER')}"
			{if App\Config::performance('SEARCH_OWNERS_BY_AJAX') && (in_array('groups', $ACCESS_OPTIONS['available']) || in_array('users', $ACCESS_OPTIONS['available']))}
				{assign var=AJAX_URL value="index.php?module={$SOURCE_MODULE}&action=Fields&mode=getOwners&fieldName=assigned_user_id"}
				{if in_array('groups', $ACCESS_OPTIONS['available'])}
					{assign var=AJAX_URL value=$AJAX_URL|cat:"&result[]=groups"}
				{/if}
				{if in_array('users', $ACCESS_OPTIONS['available'])}
					{assign var=AJAX_URL value=$AJAX_URL|cat:"&result[]=users"}
				{/if}
				data-ajax-search="1" data-ajax-url="{$AJAX_URL}" data-minimum-input="{App\Config::performance('OWNER_MINIMUM_INPUT_LENGTH')}"
			{/if}>

			{if in_array('mine', $ACCESS_OPTIONS.available)}
				<option value="{$USER_MODEL->getId()}" data-name="{$USER_MODEL->getName()}"
					title="{\App\Language::translate('LBL_MINE')}" {if $OWNER eq $USER_MODEL->getId()} selected {/if}>{\App\Language::translate('LBL_MINE')}</option>
			{/if}
			{if in_array('all', $ACCESS_OPTIONS.available)}
				<option value="all" {if $OWNER eq 'all' || $ACCESS_OPTIONS['default'] eq 'all'} data-name="" title="{\App\Language::translate('LBL_ALL')}" selected {/if}>{\App\Language::translate('LBL_ALL')}</option>
			{/if}
			{if !App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
				{if !empty($ACCESSIBLE_USERS)}
					{assign var=ACCESSIBLE_USERS value=array_diff_key($ACCESSIBLE_USERS, array_flip([$USER_MODEL->getId()]))}
					{if !empty($ACCESSIBLE_USERS) && in_array('users', $ACCESS_OPTIONS['available'])}
						<optgroup label="{\App\Language::translate('LBL_USERS')}">
							{foreach key=OWNER_ID item=OWNER_NAME from=$ACCESSIBLE_USERS}
								<option title="{$OWNER_NAME}" data-name="{$OWNER_NAME}"
									value="{$OWNER_ID}" {if $OWNER eq $OWNER_ID} selected{/if}>{$OWNER_NAME}</option>
							{/foreach}
						</optgroup>
					{/if}
				{/if}
				{if !empty($ACCESSIBLE_GROUPS) && in_array('groups', $ACCESS_OPTIONS['available'])}
					<optgroup label="{\App\Language::translate('LBL_GROUPS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ACCESSIBLE_GROUPS}
							<option title="{$OWNER_NAME}" data-name="{$OWNER_NAME}"
								value="{$OWNER_ID}" {if $OWNER eq $OWNER_ID} selected{/if}>{$OWNER_NAME}</option>
						{/foreach}
					</optgroup>
				{/if}
				{if in_array('groupUsers', $ACCESS_OPTIONS['available'])}
					{assign var=ALL_GROUPS value=\App\Fields\Owner::getInstance($SOURCE_MODULE)->getGroups(false)}
					{if $ALL_GROUPS}
						<optgroup label="{\App\Language::translate('LBL_GROUP_USERS')}">
							{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_GROUPS}
								{assign var="MEMBER_ID" value="{\App\PrivilegeUtil::MEMBER_TYPE_GROUPS}:{$OWNER_ID}"}
								<option class="{\App\PrivilegeUtil::MEMBER_TYPE_GROUPS}" value="{$MEMBER_ID}"
									data-member-type="{\App\PrivilegeUtil::MEMBER_TYPE_GROUPS}"
									{if $OWNER eq $MEMBER_ID} selected {/if}>
									{$OWNER_NAME}
								</option>
							{/foreach}
						</optgroup>
					{/if}
				{/if}
				{if array_intersect(['roleUsers', 'rsUsers'], $ACCESS_OPTIONS['available'])}
					{assign var=ALL_ROLES value=\Settings_Roles_Record_Model::getAll()}
					{if in_array('roleUsers', $ACCESS_OPTIONS['available'])}
						<optgroup label="{\App\Language::translate('LBL_ROLE_USERS', $QUALIFIED_MODULE)}">
							{foreach from=$ALL_ROLES item=MEMBER}
								{assign var="MEMBER_ID" value="{\App\PrivilegeUtil::MEMBER_TYPE_ROLES}:{$MEMBER->getId()}"}
								<option class="{\App\PrivilegeUtil::MEMBER_TYPE_ROLES}" value="{$MEMBER_ID}"
									data-member-type="{\App\PrivilegeUtil::MEMBER_TYPE_ROLES}"
									{if $OWNER eq $MEMBER_ID} selected {/if}>
									{\App\Language::translate($MEMBER->getName(), $QUALIFIED_MODULE)}
								</option>
							{/foreach}
						</optgroup>
					{/if}
					{if in_array('rsUsers', $ACCESS_OPTIONS['available'])}
						<optgroup label="{\App\Language::translate('LBL_ROLE_AND_SUBORDINATES_USERS', $QUALIFIED_MODULE)}">
							{foreach from=$ALL_ROLES item=MEMBER}
								{assign var="MEMBER_ID" value="{\App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES}:{$MEMBER->getId()}"}
								<option class="{\App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES}" value="{$MEMBER_ID}"
									data-member-type="{\App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES}"
									{if $OWNER eq $MEMBER_ID} selected {/if}>
									{\App\Language::translate($MEMBER->getName(), $QUALIFIED_MODULE)}
								</option>
							{/foreach}
						</optgroup>
					{/if}
				{/if}
			{/if}
		</select>
	</div>
	<!-- /tpl-Base-dashboards-SelectAccessibleTemplate -->
{/strip}
