{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Log-Filter-Boolean -->
	<div class="input-group input-group-sm js-log-filter px-0 mr-2" data-type-filter="{$TYPE_FIELD}" data-js="container">
		<select name="{$NAME_FIELD}" data-type-filter="{$TYPE_FIELD}" class="form-control select2"
			data-allow-clear="true">
			<option value="">{\App\Language::translate('LBL_SELECT_OPTION','Vtiger')}</option>
			<option value="1">{\App\Language::translate('LBL_YES',$MODULE)}</option>
			<option value="0">{\App\Language::translate('LBL_NO',$MODULE)}</option>
		</select>
	</div>
	<!-- /tpl-Settings-Log-Filter-Boolean -->
{/strip}
