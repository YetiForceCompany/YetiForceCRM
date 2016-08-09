{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{assign var=ACCESS_OPTIONS value=\includes\utils\Json::decode(html_entity_decode($WIDGET->get('owners')))}
{if !is_array($ACCESS_OPTIONS.available)}
	{$ACCESS_OPTIONS.available = array($ACCESS_OPTIONS.available)}
{/if}
{if !isset($OWNER)}
	{assign var=OWNER value=''}
{/if}
{if !isset($SOURCE_MODULE) && $MODULE_NAME != 'Home'}
	{assign var=SOURCE_MODULE value=$MODULE_NAME}
{/if}
{if $SOURCE_MODULE && AppConfig::performance('SEARCH_SHOW_OWNER_ONLY_IN_LIST')}
	{assign var=USERS_GROUP_LIST value=\includes\fields\Owner::getInstance($SOURCE_MODULE)->getUsersAndGroupForModuleList(false,$USER_CONDITIONS)}
	{assign var=ACCESSIBLE_USERS value=$USERS_GROUP_LIST['users']}
	{assign var=ACCESSIBLE_GROUPS value=$USERS_GROUP_LIST['group']}
{/if}
<div class="input-group input-group-sm">
	<span class="input-group-addon"><span class="glyphicon glyphicon-user iconMiddle margintop3" title="{vtranslate('Assigned To', $MODULE_NAME)}"></span></span>
	<select class="widgetFilter width90 owner form-control input-sm select2" name="owner" title="{vtranslate('LBL_OWNER')}">
		{if in_array('mine', $ACCESS_OPTIONS.available)}
			<option value="{$CURRENTUSER->getId()}" data-name="{$CURRENTUSER->getName()}" title="{vtranslate('LBL_MINE')}" {if $OWNER eq $CURRENTUSER->getId()} selected {/if}>{vtranslate('LBL_MINE')}</option>
		{/if}
		{if in_array('all', $ACCESS_OPTIONS.available)}
			<option value="all" {if $OWNER eq 'all' || $ACCESS_OPTIONS['default'] eq 'all'} data-name="" title="{vtranslate('LBL_ALL')}" selected {/if}>{vtranslate('LBL_ALL')}</option>
		{/if}
		{assign var=ACCESSIBLE_USERS value=array_diff_key($ACCESSIBLE_USERS, array_flip([$CURRENTUSER->getId()]))}
		{if !empty($ACCESSIBLE_USERS) && in_array('users', $ACCESS_OPTIONS['available'])}
			<optgroup label="{vtranslate('LBL_USERS')}">
				{foreach key=OWNER_ID item=OWNER_NAME from=$ACCESSIBLE_USERS}
					<option title="{$OWNER_NAME}" data-name="{$OWNER_NAME}" value="{$OWNER_ID}" {if $OWNER eq $OWNER_ID} selected{/if}>{$OWNER_NAME}</option>
				{/foreach}
			</optgroup>
		{/if}
		{if !empty($ACCESSIBLE_GROUPS) && in_array('groups', $ACCESS_OPTIONS['available'])}
			<optgroup label="{vtranslate('LBL_GROUPS')}">
				{foreach key=OWNER_ID item=OWNER_NAME from=$ACCESSIBLE_GROUPS}
					<option title="{$OWNER_NAME}" data-name="{$OWNER_NAME}" value="{$OWNER_ID}" {if $OWNER eq $OWNER_ID} selected{/if}>{$OWNER_NAME}</option>
				{/foreach}
			</optgroup>
		{/if}
	</select>
</div>
