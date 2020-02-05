{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-Extended-RightPanel -->
	{include file=\App\Layout::getTemplatePath('Calendar/RightPanel.tpl', 'Vtiger')}
	{if !empty($ALL_ACTIVETYPES_LIST)}
		<div class="card">
			<div class="card-header p-1 pl-2">{\App\Language::translate('LBL_TYPE', $MODULE_NAME)}</div>
			<div class="card-body row p-1">
				<div class="col-12">
					<select class="select2 form-control col-12 js-calendar__filter__select" name="types" data-cache="calendar-types" data-name="type" multiple="multiple" data-js="data | value">
						{foreach key=ITEM_ID item=ITEM from=$ALL_ACTIVETYPES_LIST}
							<option value="{$ITEM_ID}" class="mb-1">{\App\Language::translate($ITEM, $MODULE_NAME)}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
	{/if}
	<!-- /tpl-Calendar-Extended-RightPanel -->
{/strip}
