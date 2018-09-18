{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if !empty($ALL_ACTIVEUSER_LIST)}
		<div class="js-filter__container">
			<h4 class="boxFilterTitle">{\App\Language::translate('LBL_SELECT_USER_CALENDAR',$MODULE)}</h4>
			{if !AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
				<div class="input-group input-group-sm marginBottom5px">
					<div class="input-group-append">
						<span class="input-group-text">
							<span class="fas fa-search fa-fw"></span>
						</span>
					</div>
					<input type="text" class="form-control js-filter__search" placeholder="Nazwa użytkownika"
						   aria-describedby="search-icon">
				</div>
				<ul class="nav">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
						<li class="js-filter__item__container" data-js="classs: d-none">
							<div class="marginRightZero">
								<input value="{$OWNER_ID}" type="checkbox" id="ownerId{$OWNER_ID}"
									   class="js-inputUserOwnerId alignMiddle"
										{if $USER_MODEL->getId() eq $OWNER_ID} checked{/if}>
								<label class="marginLeft10 js-filter__item__value" for="ownerId{$OWNER_ID}">
									<span class="ownerCBg_{$OWNER_ID}">&nbsp&nbsp</span>&nbsp{$OWNER_NAME}
								</label>
							</div>
						</li>
					{/foreach}
				</ul>
			{else}
				<select class="js-inputUserOwnerIdAjax select2 form-control"
						data-validation-engine="validate[required]"
						title="{\App\Language::translate('LBL_TRANSFER_OWNERSHIP', $MODULE)}"
						name="transferOwnerId" id="transferOwnerId" multiple="multiple"
						data-ajax-search="1"
						data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=getOwners&fieldName=assigned_user_id"
						data-minimum-input="{AppConfig::performance('OWNER_MINIMUM_INPUT_LENGTH')}">
					<option value="{$USER_MODEL->get('id')}"
							data-picklistvalue="{$USER_MODEL->getName()}">
						{$USER_MODEL->getName()}
					</option>
				</select>
			{/if}
		</div>
	{/if}
	{if !empty($ALL_ACTIVEGROUP_LIST)}
		<div class="js-filter__container">
			<h4 class="boxFilterTitle">{\App\Language::translate('LBL_SELECT_GROUP_CALENDAR',$MODULE)}</h4>
			{if !AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
				<div class="input-group input-group-sm marginBottom5px">
					<div class="input-group-append">
						<span class="input-group-text">
							<span class="fas fa-search fa-fw"></span>
						</span>
					</div>
					<input type="text" class="form-control js-filter__search" placeholder="Nazwa grupy"
						   aria-describedby="search-icon-group">
				</div>
				<ul class="nav">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
						<li class="js-filter__item__container" data-js="classs: d-none">
							<div class="marginRightZero">
								<input value="{$OWNER_ID}" type="checkbox" id="ownerId{$OWNER_ID}"
									   class="js-inputUserOwnerId alignMiddle"
										{if $USER_MODEL->getId() eq $OWNER_ID} checked{/if}>
								<label class="marginLeft10 js-filter__item__value" for="ownerId{$OWNER_ID}">
									<span class="ownerCBg_{$OWNER_ID}">&nbsp&nbsp</span>&nbsp{$OWNER_NAME}
								</label>
							</div>
						</li>
					{/foreach}
				</ul>
			{else}
				<select class="js-inputUserOwnerIdAjax select2 form-control"
						data-validation-engine="validate[required]"
						title="{\App\Language::translate('LBL_TRANSFER_OWNERSHIP', $MODULE)}"
						name="transferOwnerId" id="transferOwnerId" multiple="multiple"
						data-ajax-search="1"
						data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=getOwners&fieldName=assigned_user_id"
						data-minimum-input="{AppConfig::performance('OWNER_MINIMUM_INPUT_LENGTH')}">
					<option value="{$USER_MODEL->get('id')}"
							data-picklistvalue="{$USER_MODEL->getName()}">
						{$USER_MODEL->getName()}
					</option>
				</select>
			{/if}
		</div>
	{/if}
	{if !empty($ACTIVITY_TYPE)}
		<div>
			<ul class="nav">
				<li>
					{foreach item=ITEM from=$ACTIVITY_TYPE}
						<input value="{$ITEM}" type="checkbox" id="itemId{$ITEM}"
							   class="picklistCBr_Calendar_activitytype_{$ITEM}" selected>
						<label for="itemId{$ITEM}">{\App\Language::translate($ITEM,$MODULE)}</label>
					{/foreach}
				</li>
			</ul>
		</div>
	{/if}
{/strip}
