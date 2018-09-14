{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if !empty($ALL_ACTIVEUSER_LIST)}
		<div class="js-filter__container">
			<h4 class="boxFilterTitle">{\App\Language::translate('LBL_SELECT_USER_CALENDAR',$MODULE)}</h4>
			<div class="input-group input-group-sm marginBottom5px">
			  <span class="input-group-btn cursorPointer" id="search-icon">
					<button class="btn btn-default "><span class="glyphicon glyphicon-search"></span></button>
			  </span>
				<input type="text" class="form-control js-filter__search" placeholder="Nazwa użytkownika" aria-describedby="search-icon">
			</div>
			<ul class="nav">
				{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST.users}
					<li class="js-filter__item__container">
						<div class="marginRightZero">
							<input type="checkbox" id="ownerId{$OWNER_ID}" class="alignMiddle" value="{$OWNER_ID}"{if $USER_MODEL->getId() eq $OWNER_ID} checked{/if}>
							{foreach key=IMAGE_ID item=IMAGE_INFO from=$ALL_ACTIVEUSER_LIST.images.$OWNER_ID}
								<img src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE_INFO.path))}"
									 alt="{$IMAGE_INFO.orgname}" title="{$IMAGE_INFO.orgname}"
									 data-image-id="{$IMAGE_INFO.id}" class="calendarUserImage alignMiddle marginLeft10">
								{break}
							{/foreach}
							<label class="marginLeft10 js-filter__item__value" for="ownerId{$OWNER_ID}"><span class="ownerCBg_{$OWNER_ID}">&nbsp&nbsp</span>&nbsp{$OWNER_NAME}
							</label>
						</div>
					</li>
				{/foreach}
			</ul>
		</div>
	{/if}
	{if !empty($ALL_ACTIVEGROUP_LIST)}
		<div class="js-filter__container">
			<h4 class="boxFilterTitle">{\App\Language::translate('LBL_SELECT_GROUP_CALENDAR',$MODULE)}</h4>
			<div class="input-group input-group-sm marginBottom5px">
			  <span class="input-group-btn cursorPointer" id="search-icon-group">
					<button class="btn btn-default "><span class="glyphicon glyphicon-search"></span></button>
			  </span>
				<input type="text" class="form-control js-filter__search" placeholder="Nazwa grupy" aria-describedby="search-icon-group">
			</div>
			<ul class="nav">
				{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
					<li class="js-filter__item__container">
						<div class="marginRightZero">
							<input type="checkbox" id="ownerId{$OWNER_ID}" value="{$OWNER_ID}">
							<label class="marginLeft10 js-filter__item__value" for="ownerId{$OWNER_ID}"><span class="ownerCBg_{$OWNER_ID}">&nbsp&nbsp</span>&nbsp{$OWNER_NAME}
							</label>
						</div>
					</li>
				{/foreach}
			</ul>
		</div>
	{/if}
	{if !empty($ACTIVITY_TYPE)}
		<div>
			<ul class="nav">
				<li class="">
					{foreach item=ITEM from=$ACTIVITY_TYPE}
						<input type="checkbox" id="itemId{$ITEM}" class="picklistCBr_Calendar_activitytype_{$ITEM}" selected value="{$ITEM}">
						<label for="itemId{$ITEM}">{\App\Language::translate($ITEM,$MODULE)}</label>
					{/foreach}
				</li>
			</ul>
		</div>
	{/if}
{/strip}
