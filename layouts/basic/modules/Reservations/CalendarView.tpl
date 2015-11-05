{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
<input type="hidden" id="currentView" value="{$VIEW}" />
<input type="hidden" id="activity_view" value="{$CURRENT_USER->get('activity_view')}" />
<input type="hidden" id="time_format" value="{$CURRENT_USER->get('hour_format')}" />
<input type="hidden" id="start_hour" value="{$CURRENT_USER->get('start_hour')}" />
<input type="hidden" id="end_hour" value="{$CURRENT_USER->get('end_hour')}" />
<input type="hidden" id="date_format" value="{$CURRENT_USER->get('date_format')}" />
<input type="hidden" id="eventLimit" value="{$EVENT_LIMIT}" />
<input type="hidden" id="weekView" value="{$WEEK_VIEW}" />
<input type="hidden" id="dayView" value="{$DAY_VIEW}" />
<style>
{foreach from=Settings_Calendar_Module_Model::getUserColors('colors') item=ITEM}
	.userCol_{$ITEM.id}{ background: {$ITEM.color}!important;}
{/foreach}
</style>
<div>
	<div class="row bottom_margin">
		<div class="">
			<p><!-- Divider --></p>
			<div id="calendarview"></div>
		</div>
	</div>
</div>
<div class="btn-group listViewMassActions hide">
	{if count($QUICK_LINKS['SIDEBARLINK']) gt 0}
		<button class="btn btn-default fc-button fc-state-default dropdown-toggle" data-toggle="dropdown">
			<span class="glyphicon glyphicon-list" aria-hidden="true"></span>
			&nbsp;&nbsp;<span class="caret"></span>
		</button>
		<ul class="dropdown-menu">
			{foreach item=SIDEBARLINK from=$QUICK_LINKS['SIDEBARLINK']}
				<li>
					<a class="quickLinks" href="{$SIDEBARLINK->getUrl()}">
						{vtranslate($SIDEBARLINK->getLabel(), $MODULE_NAME)}
					</a>
				</li>
			{/foreach}
		</ul>
	{/if}
</div>
{/strip}
