{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<style type="text/css">
		small.small-a {
			font-size: 75%;
		}
	</style>
	<div>
		{foreach from=$PROJECTSTASKS key=INDEX item=TASKS}
			<div>
				<div class='float-left'>
					<image style="margin-left: 4px;" alt="{\App\Language::translate('ProjectTask')}" src="{\App\Layout::getImagePath('ProjectTask.png')}" width="24px" />&nbsp;&nbsp;
				</div>
				<div>
					<div class='float-left'>
						{assign var=PROJECT_ID value=$TASKS->get('projectid')}
						{assign var=ACCOUNT value=$TASKS->get('account')}
						<a href="{$TASKS->getDetailViewUrl()}">{$TASKS->get('projecttaskname')|html_entity_decode:$smarty.const.ENT_QUOTES:'utf-8'|truncate:$NAMELENGTH:'...'}</a>
						{if $PROJECT_ID}
							<br /><small class='small-a'><strong>{$TASKS->getDisplayValue('projectid')}</strong></small>
						{/if}
						{if $ACCOUNT}
							- <small class='small-a'><strong>{$TASKS->getDisplayValue('account')}</strong></small>
						{/if}
					</div>
					<p class='float-right muted' style='margin-top:5px;padding-right:5px;'><small title="{\App\Fields\DateTime::formatToDay($TASKS->get('targetenddate'))}">{$TASKS->getDisplayValue('targetenddate')}</small></p>
					<div class='clearfix'></div>
				</div>
				<div class='clearfix'></div>
			</div>
		{foreachelse}
			<span class="noDataMsg">
				{\App\Language::translate($NODATAMSGLABLE, $MODULE_NAME)}
			</span>
		{/foreach}

		{if $PAGING_MODEL->get('nextPageExists') eq 'true'}
			<div class='float-right' style='margin-top:5px;padding-right:5px;'>
				<a href="javascript:;" name="history_more" data-url="{$WIDGET->getUrl()}&page={$PAGING_MODEL->getNextPage()}">{\App\Language::translate('LBL_MORE')}...</a>
				<br />
				<br />
				<br />
				<br />
			</div>
		{else}
			<br />
			<br />
			<br />
			<br />
		{/if}
	</div>
{/strip}
