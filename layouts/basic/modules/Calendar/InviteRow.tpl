{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-InviteRow -->
	{if !isset($IS_VIEW)}
		{assign var=IS_VIEW value=false}
	{/if}
	{if !isset($INVITIE)}
		{assign var=INVITIE value=['crmid'=>'','inviteesid'=>'','email'=>'','status'=>'','time'=>'']}
	{/if}
	{if $INVITIE['crmid']}
		{assign var=LABEL value=$INVITIE['label']}
		{assign var=NAME value=$INVITIE['label']}
		{assign var=TITLE value=\App\Language::translateSingularModuleName($INVITIE['setype'])|cat:': '|cat:$LABEL|cat:' - '|cat:$INVITIE['email']}
		{assign var=ICON value='yfm-'|cat:$INVITIE['setype']}
	{elseif empty($INVITIE['name'])}
		{assign var=LABEL value=$INVITIE['email']}
		{assign var=NAME value=''}
		{assign var=TITLE value=$INVITIE['email']}
		{assign var=ICON value='fas fa-envelope'}
	{else}
		{assign var=LABEL value=$INVITIE['name']}
		{assign var=NAME value=$INVITIE['name']}
		{assign var=TITLE value=$INVITIE['name']|cat:': '|cat:$INVITIE['email']}
		{assign var=ICON value='fas fa-envelope'}
	{/if}
	<div class="inviteRow js-participant-row" data-crmid="{$INVITIE['crmid']}" data-ivid="{$INVITIE['inviteesid']}" data-email="{$INVITIE['email']}" data-name="{\App\Purifier::encodeHtml($NAME)}" data-js="clone|edit">
		<div class="input-group input-group-sm">
			<span class="input-group-prepend js-participant-icon" data-js="change">
				<span class="input-group-text">
					<span class="{$ICON}"></span>
				</span>
				<span class="input-group-text u-w-125px u-max-w-150px text-truncate js-participant-name {if $TITLE}js-popover-tooltip{/if}" data-js="popover" data-content="{$TITLE}">{$LABEL}</span>
				<span class="input-group-text inviteStatus">
					{assign var=STATUS_LABEL value=Calendar_Record_Model::getInvitionStatus($INVITIE['status'])}
					{if $INVITIE['status'] == '1'}
						<span class="fas fa-check-circle color-green-a700 js-popover-tooltip" data-js="popover" data-placement="top" data-content="{\App\Language::translate($STATUS_LABEL, $MODULE_NAME)} {if $INVITIE['time']}({DateTimeField::convertToUserFormat($INVITIE['time'])}){/if}"></span>
					{elseif $INVITIE['status'] == '2'}
						<span class="fas fa-minus-circle color-red-a700 js-popover-tooltip" data-js="popover" data-placement="top" data-content="{\App\Language::translate($STATUS_LABEL, $MODULE_NAME)} {if $INVITIE['time']}({DateTimeField::convertToUserFormat($INVITIE['time'])}){/if}"></span>
					{else}
						{assign var=LABEL value=$INVITIE['email']}
						<span class="fas fa-question-circle color-orange-a700 js-popover-tooltip" data-js="popover" data-placement="top" data-content="{\App\Language::translate($STATUS_LABEL, $MODULE_NAME)}"></span>
					{/if}
				</span>
			</span>
			{if !$IS_VIEW}
				<span class="input-group-append">
					<button class="btn btn-outline-secondary border js-participant-remove" type="button" data-js="click">
						<span class="fas fa-times"></span>
					</button>
				</span>
			{/if}
		</div>
	</div>
	<!-- /tpl-Calendar-InviteRow -->
{/strip}
