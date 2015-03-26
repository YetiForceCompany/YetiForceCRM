<div class="container-fluid autologinContainer" style="margin-top:10px;">
	<h3>{vtranslate('LBL_AUTOLOGIN', $QUALIFIED_MODULE)}</h3>&nbsp;{vtranslate('LBL_AUTOLOGIN_DESCRIPTION', $QUALIFIED_MODULE)}<hr>
	{assign var=ALL_ACTIVEUSER_LIST value=$USER_MODEL->getAccessibleUsers()}
	<table class="table table-bordered table-condensed themeTableColor userTable">
		<thead>
			<tr class="blockHeader" >
				<th class="mediumWidthType">
					<span>{vtranslate('LBL_RC_USER', $QUALIFIED_MODULE)}</span>
				</th>
				<th class="mediumWidthType">
					<span>{vtranslate('LBL_CRM_USER', $QUALIFIED_MODULE)}</span>
				</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$MODULE_MODEL->getAccountsList() key=KEY item=ITEM}	
				{assign var=USERS value=$MODULE_MODEL->getAutologinUsers($ITEM.user_id)}
				<tr data-id="{$ITEM.user_id}">
					<td><label>{$ITEM.username}</label></td>
					<td>
						<select class="chzn-select users" multiple name="users" style="width: 500px;">
							{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
							<option value="{$OWNER_ID}" {if in_array($OWNER_ID, $USERS)} selected {/if} data-userId="{$CURRENT_USER_ID}">{$OWNER_NAME}</option>
							{/foreach}
						</select>
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>
