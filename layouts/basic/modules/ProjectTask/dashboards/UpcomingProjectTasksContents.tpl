{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-ProjectTask-Dashboard-UpcomingProjectTasksContents">
		<div class="row">
			<h6 class="p-0 pr-2 col-sm-3 u-font-size-100per mb-0"><strong>{\App\Language::translate('Project Task Name', $MODULE_NAME)}</strong></h6>
			<h6 class="p-0 pr-2 col-sm-1 u-font-size-100per mb-0"><strong>{\App\Language::translate('Progress', $MODULE_NAME)}</strong></h6>
			<h6 class="p-0 pr-2 col-sm-3 u-font-size-100per mb-0"><strong>{\App\Language::translate('Projekt brak tłumaczeni', $MODULE_NAME)}</strong></h6>
			<h6 class="p-0 pr-2 col-sm-3 u-font-size-100per mb-0"><strong>{\App\Language::translate('Project Milestones', $MODULE_NAME)}</strong></h6>
			<h6 class="p-0 pr-2 col-sm-2 u-font-size-100per mb-0"><strong>{\App\Language::translate('Target End Date', $MODULE_NAME)}</strong></h6>
		</div>
		{foreach from=$PROJECTTASKS key=INDEX item=TASK}
			<div class="row">
				<div class="p-0 col-sm-3">
					<a href="index.php?module=ProjectTask&view=Detail&record={$TASK['projecttaskid']}">
								{\App\TextParser::textTruncate($TASK['projecttaskname'], $NAMELENGTH)}
							</a>
				</div>
				<div class="p-0 col-sm-1">
					{$TASK['projecttaskprogress']} %
				</div>
					<div class="p-0 col-sm-3">
					link do projektu
				</div>
				<div class="p-0 col-sm-3">
					<a href="index.php?module=ProjectMilestone&view=Detail&record={$TASK['projectmilestoneid']}">
						{\App\TextParser::textTruncate(\App\Record::getLabel($TASK['projectmilestoneid']), $NAMELENGTH)}
					</a>
				</div>
				<div class="p-0 col-sm-2">
					{\App\Fields\DateTime::formatToViewDate($TASK['targetenddate']|cat:" 23:59:00")}
				</div>
			</div>
		{foreachelse}
			<span class="noDataMsg">
				{\App\Language::translate($NODATAMSGLABLE, $MODULE_NAME)}
			</span>
		{/foreach}
		{if $PAGING_MODEL->get('nextPageExists') eq 'true'}
			<div class="float-right padding5">
				<button type="button" class="btn btn-sm btn-primary showMoreHistory" data-url="{$WIDGET->getUrl()}&page={$PAGING_MODEL->getNextPage()}">
					{\App\Language::translate('LBL_MORE')}
				</button>
			</div>
		{/if}
	</div>
{/strip}
