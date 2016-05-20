{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{assign var=ACCESS_OPTIONS value=Zend_Json::decode(html_entity_decode($WIDGET->get('owners')))}
{if !is_array($ACCESS_OPTIONS.available)}
	{$ACCESS_OPTIONS.available = array($ACCESS_OPTIONS.available)}
{/if}
{if !isset($OWNER)}
	{assign var=OWNER value=''}
{/if}
<select class="widgetFilter width90 owner form-control input-sm" name="owner" title="{vtranslate('LBL_OWNER')}">
	{if array_key_exists( $CURRENTUSER->getId(), $ACCESSIBLE_USERS ) && in_array('mine', $ACCESS_OPTIONS.available)}
		<option value="{$CURRENTUSER->getId()}" data-name="{$CURRENTUSER->getName()}" title="{vtranslate('LBL_MINE')}" {if $OWNER eq $CURRENTUSER->getId()} selected {/if}>{vtranslate('LBL_MINE')}</option>
	{/if}
	{if array_key_exists( $CURRENTUSER->getId(), $ACCESSIBLE_USERS ) && in_array('all', $ACCESS_OPTIONS.available)}
		<option value="all" {if $OWNER eq 'all' || $ACCESS_OPTIONS['default'] eq 'all'} data-name="" title="{vtranslate('LBL_ALL')}" selected {/if}>{vtranslate('LBL_ALL')}</option>
	{/if}
	{if !empty($ACCESSIBLE_USERS) && in_array('users', $ACCESS_OPTIONS.available)}
		<optgroup label="{vtranslate('LBL_USERS')}">
			{foreach key=OWNER_ID item=OWNER_NAME from=$ACCESSIBLE_USERS}
				{if $OWNER_ID neq {$CURRENTUSER->getId()}}
					<option title="{$OWNER_NAME}" data-name="{$OWNER_NAME}" value="{$OWNER_ID}" {if $OWNER eq $OWNER_ID} selected{/if}>{$OWNER_NAME}</option>
				{/if}
			{/foreach}
		</optgroup>
	{/if}
	{if !empty($ACCESSIBLE_GROUPS) && in_array('groups', $ACCESS_OPTIONS.available)}
		<optgroup label="{vtranslate('LBL_GROUPS')}">
			{foreach key=OWNER_ID item=OWNER_NAME from=$ACCESSIBLE_GROUPS}
				<option title="{$OWNER_NAME}" data-name="{$OWNER_NAME}" value="{$OWNER_ID}" {if $OWNER eq $OWNER_ID} selected{/if}>{$OWNER_NAME}</option>
			{/foreach}
		</optgroup>
	{/if}
</select>

