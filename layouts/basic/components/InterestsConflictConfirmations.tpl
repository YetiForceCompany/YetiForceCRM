{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-AppComponents-InterestsConflictConfirmations-->
	<form class="js-filter-form form-inline" data-js="container">
		<div class="input-group">
			<div class="input-group-prepend">
				<span class="input-group-text" id="confirmInputDate">
					<span class="fas fa-calendar-alt mr-2"></span>
					{\App\Language::translate('LBL_DATE')}
				</span>
			</div>
			<input name="date" type="text" class="dateRangeField dateFilter form-control text-center" data-calendar-type="range" value="{$DATE}" aria-describedby="confirmInputDate" />
		</div>
		<div class="input-group ml-3">
			<div class="input-group-prepend">
				<span class="input-group-text" id="confirmStatusListDESC">
					<span class="fas fa-stream mr-2"></span>
					{\App\Language::translate('Status')}
				</span>
			</div>
			<select id="confirmStatusList" class="form-control select2" multiple="true" name="status[]" aria-describedby="confirmStatusListDESC">
				{foreach from=$CONFIRM_STATUS_LIST key=KEY item=STATUS}
					<option value="{$KEY}">{$STATUS}</option>
				{/foreach}
			</select>
		</div>
		<div class="input-group ml-3">
			<div class="input-group-prepend">
				<span class="input-group-text" id="confirmRelatedDesc">
					<span class="fas fa-link mr-2"></span>
					{\App\Language::translate('LBL_RELATED_RECORD')}
				</span>
			</div>
			<input name="related" type="text" class="form-control text-center" placeholder="{\App\Language::translate('LBL_STARTS_WITH')}" value="" aria-describedby="confirmRelatedDesc" />
		</div>
		<div class="input-group ml-3">
			<div class="input-group-prepend">
				<span class="input-group-text" id="confirmUsersListDesc">
					<span class="yfi yfi-users-2 mr-2"></span>
					{\App\Language::translate('LBL_USER')}
				</span>
			</div>
			<select id="confirmUsersList" class="form-control select2" multiple="true" name="users[]" aria-describedby="confirmUsersListDesc">
				{foreach from=$USERS key=USER_ID item=USER}
					<option value="{$USER_ID}">
						{$USER->getDisplayName()} ({$USER->getRoleDetail()->get('rolename')})
					</option>
				{/foreach}
			</select>
		</div>
		<div class="input-group ml-3">
			<div class="input-group-prepend">
				<span class="input-group-text" id="showHistoryDesc">
					<span class="fas fa-history mr-2"></span>
					{\App\Language::translate('LBL_SHOW_HISTORICAL_DATA')}
				</span>
			</div>
			<div class="input-group-text bg-white">
				<input name="showHistory" type="checkbox" aria-describedby="showHistoryDesc" value="true">
			</div>
		</div>
	</form>
	<table id="js-confirm-table" class="table table-sm table-striped display js-confirm-table text-center mt-2">
		<thead>
			<tr>
				<th>{\App\Language::translate('LBL_DATE')}</th>
				<th>{\App\Language::translate('LBL_USER')}</th>
				<th>{\App\Language::translate('Status')}</th>
				<th>{\App\Language::translate('LBL_RELATED_RECORD')}</th>
				<th>{\App\Language::translate('LBL_ACTIONS')}</th>
			</tr>
		</thead>
	</table>
	<!-- /tpl-AppComponents-InterestsConflictConfirmations-->
{/strip}
