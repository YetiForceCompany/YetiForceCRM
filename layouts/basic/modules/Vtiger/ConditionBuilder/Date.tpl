{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-ConditionBuilder-Date input-group date">
		<input class="js-date-field dateField form-control js-condition-builder-value"
			   data-js="datepicker|val"
			   title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}"
			   value="{\App\Purifier::encodeHtml(DateTimeField::convertToUserFormat($VALUE))}"
			   autocomplete="off"/>
		<div class="input-group-append">
			<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
				<span class="fas fa-calendar-alt"></span>
			</span>
		</div>
	</div>
{/strip}
