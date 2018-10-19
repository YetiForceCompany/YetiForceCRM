{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-ConditionBuilder-Time input-group time">
		<input class="clockPicker form-control js-time-field js-condition-builder-value"
			   data-js="timepicker" data-format="{$USER_MODEL->get('hour_format')}"
			   title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModule()->getName())}"
			   autocomplete="off"/>
		<div class=" input-group-append">
			<span class="input-group-text u-cursor-pointer js-clock__btn" data-js="click">
				<span class="far fa-clock"></span>
			</span>
		</div>
	</div>
{/strip}
