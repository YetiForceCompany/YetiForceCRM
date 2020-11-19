
{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-Log-Filter-DateTimeRange -->
<div class="input-group input-group-sm js-log-filter px-0 mr-2" data-type-filter="{$TYPE_FIELD}" data-js="container" >
	<div class="input-group-prepend">
		<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
			<span class="fas fa-calendar-alt"> {\App\Language::translate('LBL_TIME', $QUALIFIED_MODULE)}</span>
		</span>
	</div>
	<input type="text" name="{$NAME_FIELD}" title="{\App\Language::translate('LBL_CHOOSE_DATE')}" class="dateRangeField js-date-filter form-control text-center" data-js="val" data-date-format="{$USER_MODEL->get('date_format')}"  value="{implode(',',\App\Fields\Date::formatRangeToDisplay([date('Y-m-d'),date('Y-m-d')]))}"/>
</div>
<!-- /tpl-Settings-Log-Filter-DateTimeRange -->
{/strip}
