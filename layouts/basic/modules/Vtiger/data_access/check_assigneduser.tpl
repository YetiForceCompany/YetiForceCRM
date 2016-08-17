{strip}
{assign var=FIELD value=$SAVED_DATA['field']}
<div class="row">
	<div class="col-md-12 padding-bottom1per">
		<h5 class="padding-bottom1per"><strong>{vtranslate('LBL_SELECT_USER_OR_GROUP', 'DataAccess')}:</strong></h5>
		<select multiple name="field" class="marginLeftZero col-md-6 chzn-select">
			<optgroup label="{vtranslate('LBL_USERS')}">
				<option value="currentUser" {foreach item=USER from=$FIELD}{if $USER eq 'currentUser' } selected {/if}{/foreach}>{vtranslate('LBL_CURRENT_USER', 'DataAccess')}</option>
				{foreach key=OWNER_ID item=OWNER_NAME from=$CONFIG['users']}
						<option value="{$OWNER_ID}" {foreach item=USER from=$FIELD}{if $USER eq $OWNER_ID } selected {/if}{/foreach}>{$OWNER_NAME}</option>
				{/foreach}
			</optgroup>
			<optgroup label="{vtranslate('LBL_GROUPS')}">
				{foreach key=OWNER_ID item=OWNER_NAME from=$CONFIG['groups']}
					<option value="{$OWNER_ID}" {foreach item=USER from=$FIELD}{if $USER eq $OWNER_ID } selected {/if}{/foreach}>{$OWNER_NAME}</option>
				{/foreach}
			</optgroup>
		</select>
	</div>
	<div class="marginLeftZero col-md-12 padding-bottom1per">
		<h5 class="padding-bottom1per"><strong>{vtranslate('Message', 'DataAccess')}:</strong></h5>
		<input type="text" name="info" class="marginLeftZero col-md-6 " value="{$SAVED_DATA['info']}">
	</div>
</div>
{/strip}
