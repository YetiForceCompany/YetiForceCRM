{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="input-group dateTime">
		<input class="js-date-time-field dateTimePickerField form-control js-condition-builder-value"
			   data-js="datetimepicker"
			   type="text"
			   data-date-format="{$USER_MODEL->get('date_format')}"
			   data-hour-format="{$USER_MODEL->get('hour_format')}"
			   autocomplete="off"/>
		<span class="input-group-text u-cursor-pointer">
			<span class="fas fa-clock"></span>	&nbsp; <span class="far fa-calendar-alt"></span>
		</span>
	</div>
{/strip}
