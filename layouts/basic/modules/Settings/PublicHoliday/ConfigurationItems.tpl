{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-PublicHoliday-ConfigurationItems -->
	{foreach item=HOLIDAY from=$HOLIDAYS}
		<tr class="holidayElement" data-holiday-id="{$HOLIDAY->getId()}"
			data-holiday-type="{$HOLIDAY->getType()}" data-holiday-name="{$HOLIDAY->getName()}"
			data-holiday-date="{\App\Fields\Date::formatToDisplay($HOLIDAY->getDate())}">
			<td data-label="">
				<div>
					<div>
						<input type="checkbox" class="mass-selector" data-id="{$HOLIDAY->getId()}" />
					</div>
					<div>
						<button data-holiday-id="{$HOLIDAY->getId()}"
							class="editHoliday mr-1 text-white btn btn-xs btn-info">
							<span title="{\App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}"
								class="yfi yfi-full-editing-view"></span>
						</button>
						<button data-holiday-id="{$HOLIDAY->getId()}"
							class="deleteHoliday text-white btn btn-xs btn-danger">
							<span title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"
								class="fas fa-trash-alt"></span>
						</button>
					</div>
				</div>
			</td>
			<td data-label="{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}">
				<span>{$HOLIDAY->getDisplayValue('holidaydate')}</span>
			</td>
			<td data-label="{\App\Language::translate('LBL_DAY', $QUALIFIED_MODULE)}">
				<span>{\App\Language::translate($HOLIDAY->getDayOfWeek(), $QUALIFIED_MODULE)}</span>
			</td>
			<td data-label="{\App\Language::translate('LBL_DAY_NAME', $QUALIFIED_MODULE)}">
				<span>{$HOLIDAY->getDisplayValue('holidayname')}</span>
			</td>
			<td data-label="{\App\Language::translate('LBL_HOLIDAY_TYPE', $QUALIFIED_MODULE)}">
				<span>{$HOLIDAY->getDisplayValue('holidaytype')}</span>
			</td>
			<td data-label="">
				<div class="text-center">
					<button data-holiday-id="{$HOLIDAY->getId()}"
						class="editHoliday mr-1 text-white btn btn-sm btn-info">
						<span title="{\App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}"
							class="yfi yfi-full-editing-view"></span>
					</button>
					<button data-holiday-id="{$HOLIDAY->getId()}"
						class="deleteHoliday text-white btn btn-sm btn-danger">
						<span title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"
							class="fas fa-trash-alt"></span>
					</button>
				</div>
			</td>
		</tr>
	{/foreach}
	<!-- /tpl-Settings-PublicHoliday-ConfigurationItems -->
{/strip}
