{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-ProjectTask-Dashboard-UpcomingProjectTasksContents">
		{foreach from=$PROJECTTASKS key=INDEX item=TASK}
			<div class="row">
				<div class="p-0 text-center col-sm-3">
					<a href="{$TASK->getDetailViewUrl()}">
						{\App\TextUtils::textTruncate($TASK->getDisplayName(), $NAMELENGTH)}
					</a>
				</div>
				<div class="p-0 text-center col-sm-1">
					{$TASK->getDisplayValue('projecttaskprogress')}
				</div>
				<div class="p-0 text-center col-sm-3">
					{$TASK->getDisplayValue('projectid')}
				</div>
				<div class="p-0 text-center col-sm-3">
					{$TASK->getDisplayValue('projectmilestoneid')}
				</div>
				<div class="p-0 text-center col-sm-2">
					{\App\Fields\DateTime::formatToViewDate($TASK->get('enddate')|cat:" 23:59:00")}
				</div>
			</div>
			<hr>
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
