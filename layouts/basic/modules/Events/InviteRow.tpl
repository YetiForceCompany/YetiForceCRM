{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=LABEL value=''}
	{assign var=TITLE value=''}
	<div class="inviteRow" data-crmid="{$INVITIE['crmid']}" data-ivid="{$INVITIE['inviteesid']}" data-email="{$INVITIE['email']}">
		<div class="input-group input-group-sm">
			<span class="input-group-addon inviteIcon">
				{if $INVITIE['crmid']}
					{assign var=INVITIE_RECORD value=vtlib\Functions::getCRMRecordMetadata($INVITIE['crmid'])}
					{assign var=LABEL value=$INVITIE_RECORD['label']}
					{assign var=TITLE value=\App\Language::translateSingularModuleName($INVITIE_RECORD['setype'])|cat:': '|cat:$LABEL|cat:' - '|cat:$INVITIE['email']}
					<span class="userIcon-{$INVITIE_RECORD['setype']}"></span>
				{else}
					{assign var=LABEL value=$INVITIE['email']}
					<span class="c-badge__icon fas fa-envelope"></span>
				{/if}
			</span>
			<span class="input-group-addon inviteName {if $TITLE}popoverTooltip{/if}" data-content="{$TITLE}" style="width: 100px;">{$LABEL}</span>
			<span class="input-group-addon inviteStatus">
				{assign var=STATUS_LABEL value=Events_Record_Model::getInvitionStatus($INVITIE['status'])}
				{if $INVITIE['status'] == '1'}
					<span class="fas fa-check-circle popoverTooltip" data-placement="top" data-content="{\App\Language::translate($STATUS_LABEL,$MODULE)} {if $INVITIE['time']}({DateTimeField::convertToUserFormat($INVITIE['time'])}){/if}"></span>
				{elseif $INVITIE['status'] == '2'}
					<span class="fas fa-minus-circle popoverTooltip" data-placement="top" data-content="{\App\Language::translate($STATUS_LABEL,$MODULE)} {if $INVITIE['time']}({DateTimeField::convertToUserFormat($INVITIE['time'])}){/if}"></span>
				{else}
					{assign var=LABEL value=$INVITIE['email']}
					<span class="fas fa-question-circle popoverTooltip" data-placement="top" data-content="{\App\Language::translate($STATUS_LABEL,$MODULE)}"></span>
				{/if}
			</span>
			<span class="input-group-btn">
				<button class="btn btn-light inviteRemove" type="button">
					<span class="fas fa-times"></span>
				</button>
			</span>
		</div>
	</div>
{/strip}
