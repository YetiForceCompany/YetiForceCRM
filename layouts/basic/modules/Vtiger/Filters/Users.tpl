{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Filters-Users -->
	{if !empty($FILTER_DATA)}
		{assign var=SELECT_ALL_BY_DEFAULT value=$USER_MODEL->get('calendar_all_users_by_default')}
		{assign var=PIN_USER value=App\Config::module($MODULE_NAME, 'showPinUser')}
		<div class="js-filter__container">
			<div class="form-row d-flex align-items-center m-0">
				<input type="checkbox" class="alignMiddle js-select-all mr-2" title="{\App\Language::translate('LBL_SELECT_ALL', $MODULE_NAME)}"
					autocomplete="off" {if $SELECT_ALL_BY_DEFAULT eq '1' } checked {/if}>
				<h6 class="boxFilterTitle mt-2 mx-auto">{\App\Language::translate('LBL_SELECT_CALENDAR',$MODULE_NAME)}</h6>
			</div>
			{if !App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
				<div class="input-group input-group-sm mb-3">
					<div class="input-group-prepend">
						<span class="input-group-text">
							<span class="fas fa-search fa-fw"></span>
						</span>
					</div>
					<input type="text" class="form-control js-filter__search" placeholder="{\App\Language::translate('LBL_USER_NAME',$MODULE_NAME)}" aria-describedby="search-icon">
				</div>
				<ul class="nav form-row">
					{foreach key=OWNER_ID item=OWNER_NAME from=$FILTER_DATA}
						<li class="js-filter__item__container m-0 p-0 col-12 mb-1" data-js="classs: d-none">
							<div class="mr-0 pr-0 col-12 form-row d-flex align-items-center">
								<div class="mr-2">
									<input value="{$OWNER_ID}" type="checkbox" id="ownerId{$OWNER_ID}" class="js-input-user-owner-id alignMiddle mr-2"
										{if (empty($HISTORY_USERS) && $USER_MODEL->getId() eq $OWNER_ID) ||(	isset($HISTORY_USERS['selectedIds'][0]) && (($HISTORY_USERS['selectedIds'][0] eq 'all' || in_array($OWNER_ID, $HISTORY_USERS['selectedIds'])) && !in_array($OWNER_ID, $HISTORY_USERS['excludedIds']))) || (empty($HISTORY_USERS) && $SELECT_ALL_BY_DEFAULT eq '1')}
											checked
										{/if}>
									{if !empty($PIN_USER)}
										<div class="js-pin-user d-inline-block align-middle text-center" data-elementid="{$OWNER_ID}" data-js="click|data-elementid">
											<span class="{if empty($FAVORITES_USERS[$OWNER_ID])}far{else}fas{/if} fa-star js-pin-icon u-cursor-pointer" data-js="class: fas | far"></span>
										</div>
									{/if}
								</div>
								<label class="m-0 p-0 col-9 col-xxl-10 js-filter__item__value u-text-ellipsis--no-hover"
									for="ownerId{$OWNER_ID}" title="{App\Purifier::decodeHtml($OWNER_NAME)}">
									<div class="ownerCBg_{$OWNER_ID} d-inline-block align-middle mr-1 u-w-1em u-h-1em"></div>{$OWNER_NAME}
								</label>
							</div>
						</li>
					{/foreach}
				</ul>
			{else}
				<select class="js-input-user-owner-id-ajax form-control"
					data-validation-engine="validate[required]"
					title="{\App\Language::translate('LBL_TRANSFER_OWNERSHIP', $MODULE_NAME)}"
					name="transferOwnerId" id="transferOwnerId" multiple="multiple"
					data-ajax-search="1"
					data-ajax-url="index.php?module={$MODULE_NAME}&action=Fields&mode=getOwners&fieldName=assigned_user_id&result[]=users"
					data-minimum-input="{App\Config::performance('OWNER_MINIMUM_INPUT_LENGTH')}">
					<option value="{$USER_MODEL->get('id')}"
						data-picklistvalue="{$USER_MODEL->getName()}">
						{$USER_MODEL->getName()}
					</option>
				</select>
			{/if}
		</div>
	{/if}
	<!-- tpl-Base-Filters-Users -->
{/strip}
