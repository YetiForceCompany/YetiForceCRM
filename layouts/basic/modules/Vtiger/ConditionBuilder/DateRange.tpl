{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-ConditionBuilder-Date input-group date">
		{assign var=MODEL value=$FIELD_MODEL}
		<input name="{$MODEL->getFieldName()}"
			   class="dateRangeField js-date-range-field form-control"
			   data-js="daterangepicker"
			   title="{\App\Language::translate($MODEL->getFieldLabel(), $MODEL->getModule()->getName())}"
			   autocomplete="off"/>
		<div class=" input-group-append">
			<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
				<span class="fas fa-calendar-alt"></span>
			</span>
		</div>
	</div>
{/strip}
