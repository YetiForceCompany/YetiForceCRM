{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-MailRbl-SearchForm-->
<form class="js-filter-form form-inline" data-js="container">
	<div class="input-group">
		<div class="input-group-prepend">
			<span class="input-group-text" id="rblInputDate">
				<span class="fas fa-calendar-alt mr-2"></span>
				{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}
			</span>
		</div>
		<input name="date" type="text" class="dateRangeField dateFilter form-control text-center" data-calendar-type="range" value="{$DATE}" aria-describedby="rblInputDate"/>
	</div>
	<div class="input-group ml-3">
		<div class="input-group-prepend">
			<span class="input-group-text" id="rblStatusList">
				<span class="fas fa-stream mr-2"></span>
				{\App\Language::translate('Status', $QUALIFIED_MODULE)}
			</span>
		</div>
		<select id="rblStatus" class="form-control select2" multiple="true" name="status[]" aria-describedby="rblStatusList">
			{foreach from=$STATUS_LIST key=KEY item=STATUS}
				<option value="{$KEY}">
					{\App\Language::translate($STATUS, $QUALIFIED_MODULE)}
				</option>
			{/foreach}
		</select>
	</div>

	<div class="input-group ml-3">
		<div class="input-group-prepend">
			<span class="input-group-text" id="rblTypeList">
				<span class="yfi yfi-field-folders mr-2"></span>
				{\App\Language::translate('LBL_LIST_TYPE', $QUALIFIED_MODULE)}
			</span>
		</div>
		<select id="rblTypeList" class="form-control select2" multiple="true" name="type[]" aria-describedby="rblTypeList">
			{foreach from=$TYPE_LIST key=KEY item=TYPE}
				<option value="{$KEY}">
					{\App\Language::translate($TYPE, $QUALIFIED_MODULE)}
				</option>
			{/foreach}
		</select>
	</div>
	<div class="input-group ml-3">
		<div class="input-group-prepend">
			<span class="input-group-text" id="rblUsersList">
				<span class="yfi yfi-users-2 mr-2"></span>
				{\App\Language::translate('LBL_USER', $QUALIFIED_MODULE)}
			</span>
		</div>
		<select id="rblUsersList" class="form-control select2" multiple="true" name="users[]" aria-describedby="rblUsersList">
			{foreach from=\Users_Record_Model::getAll() key=USER_ID item=USER}
				<option value="{$USER_ID}">
					{$USER->getDisplayName()} ({$USER->getRoleDetail()->get('rolename')})
				</option>
			{/foreach}
		</select>
	</div>
</form>
<!-- /tpl-Settings-MailRbl-SearchForm-->
{/strip}
