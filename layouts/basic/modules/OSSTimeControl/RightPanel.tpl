{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if !empty($ALL_ACTIVEGROUP_LIST) || !empty($ALL_ACTIVEUSER_LIST)}
		<div class="row no-margin">
			<div class="col-12 marginTB10">
				<select name="calendarUserList" class="select2 col-12 js-calendar__filter__select" data-cache="calendar-users" multiple data-js="data | value">
					<optgroup label="{\App\Language::translate('LBL_USERS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
							<option value="{$OWNER_ID}" class="ownerCBg_{$OWNER_ID} marginBottom5px"
									{if \App\User::getCurrentUserId() eq $OWNER_ID} selected {/if}>
								{$OWNER_NAME}
							</option>
						{/foreach}
					</optgroup>
					<optgroup label="{\App\Language::translate('LBL_GROUPS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
							<option value="{$OWNER_ID}" class="ownerCBg_{$OWNER_ID} marginBottom5px">
								{$OWNER_NAME}
							</option>
						{/foreach}
					</optgroup>
				</select>
			</div>
		</div>
	{/if}
	{if !empty($ALL_ACTIVETYPES_LIST)}
		<div class="row no-margin">
			<div class="col-12 marginTB10">
				<select name="timecontrolTypes" class="select2 col-12 form-control js-calendar__filter__select" data-cache="calendar-types" multiple data-js="data | value">
					{foreach item=ITEM from=$ALL_ACTIVETYPES_LIST}
						<option value="{$ITEM}"
								class="picklistCBr_OSSTimeControl_timecontrol_type_{$ITEM} marginBottom5px">{\App\Language::translate($ITEM,$MODULE)}</option>
					{/foreach}
				</select>
			</div>
		</div>
	{/if}
{/strip}
