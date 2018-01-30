{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{foreach from=$DATES item=DATE name="iteration"}
		<div class="width1per7 paddingLRZero" {if $smarty.foreach.iteration.index eq 3}id="cur_events"{/if}>
			<table class="table">
				<tr>
					{if $smarty.foreach.iteration.first}
						<th class="padding5 hidden-xs">
							<button type="button" class="btn btn-xs btn-primary previousDayBtn"><</button>
						</th>
					{/if}
					<th class="text-center taskPrevTwoDaysAgo">
						<span class="cursorPointer dateBtn" data-date="{App\Fields\Date::formatToDisplay($DATE)}">{App\Fields\Date::formatToDisplay($DATE)}&nbsp;({\App\Language::translate('LBL_'|cat:DateTimeField::getDayFromDate($DATE, true), $MODULE_NAME)})</span>
					</th>
					{if $smarty.foreach.iteration.last}
						<th class="padding5 hidden-xs">
							<button type="button" class="btn btn-xs btn-primary nextDayBtn">></button>
						</th>
					{/if}
				</tr>
				{foreach from=$EVENTS[$DATE] item=EVENT}
					<tr class="mode_{$EVENT['set']} addedNearCalendarEvent">
						<td colspan="{if $smarty.foreach.iteration.last || $smarty.foreach.iteration.first}2{else}{/if}">
							<a target="_blank" href="{$EVENT['url']}">
								<div class="cut-string">
									<span class="fas fa-calendar-alt"></span>
									<span class="paddingLR5"><strong>{$EVENT['hour_start']}</strong></span>
									<span>{vtlib\Functions::textLength($EVENT['title'], 16)}</span>
									<span class="HelpInfoPopover" title="" data-placement="top" data-content="
											<div><label class='paddingLR5'>{App\Language::translate('Start Time', $MODULE_NAME)}:</label>{$EVENT['start_display']}</div>
											<div><label class='paddingLR5'>{App\Language::translate('End Time', $MODULE_NAME)}:</label>{$EVENT['end_display']}</div>
											<div><label class='paddingLR5'>{App\Language::translate('Subject', $MODULE_NAME)}:</label>{\App\Purifier::encodeHtml($EVENT['title'])}</div>
											<div><label class='paddingLR5'>{App\Language::translate('LBL_STATE', $MODULE_NAME)}:</label>{$EVENT['labels']['state']}</div>
											<div><label class='paddingLR5'>{App\Language::translate('LBL_STATUS', $MODULE_NAME)}:</label>{$EVENT['labels']['sta']}</div>
											<div><label class='paddingLR5'>{App\Language::translate('Priority', $MODULE_NAME)}:</label>{$EVENT['labels']['pri']}</div>
											{if $EVENT['link'] neq 0}
												<div><label class='paddingLR5'>{App\Language::translate('FL_RELATION', $MODULE_NAME)}:</label>{\App\Purifier::encodeHtml($EVENT['linkl'])}</div>
											{/if}
											{if $EVENT['process'] neq 0}
												<div><label class='paddingLR5'>{App\Language::translate('FL_PROCESS', $MODULE_NAME)}:</label>{\App\Purifier::encodeHtml($EVENT['procl'])}</div>
											{/if}
											{if $EVENT['subprocess'] neq 0}
												<div><label class='paddingLR5'>{App\Language::translate('FL_SUB_PROCESS', $MODULE_NAME)}:</label>{\App\Purifier::encodeHtml($EVENT['subprocl'])}</div>
											{/if}
										">
										<i class="float-right fa fa-info-circle"></i>
									</span>
								</div>
							</a>
							{if $SHOW_COMPANIES}
								<div class="cut-string">
									<span class="calIcon userIcon-{$EVENT['linkm']}"></span> 
									{$EVENT['linkl']}
								</div>
							{/if}
						</td>
					</tr>
				{/foreach}
			</table>
		</div>
	{/foreach}
{/strip}
