{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-ProjectTask-Dashboard-UpcomingProjectTasksContents">
		{foreach from=$PROJECTTASKS key=INDEX item=TASK}
			<div class="u-cursor-pointer mt-1">
				<div>
					<div class="d-flex mb-1">
						<i class="userIcon-ProjectTask"></i>
						<div class="w-100 mx-1">
							<a href="index.php?module=ProjectTask&view=Edit&record={$TASK['projecttaskid']}">
								{\App\TextParser::textTruncate($TASK['projecttaskname'], $NAMELENGTH)}
							</a>
						</div>
					</div>
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
