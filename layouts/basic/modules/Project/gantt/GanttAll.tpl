{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Project-gantt-GanttAll">
		<div class="noprint mb-2">
			<div class="row">
				<div class="col-12 d-flex">
					{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS=buttonTextHolder}
					<div class="js-hide-filter customFilterMainSpan ml-1 ml-lg-auto mr-auto" data-js="class: d-none">
						{if $CUSTOM_VIEWS|@count gt 0}
							<select id="customFilter" class="form-control select2"
								title="{\App\Language::translate('LBL_CUSTOM_FILTER')}">
								{foreach item="CUSTOM_VIEW" from=$CUSTOM_VIEWS}
								<option value="{$CUSTOM_VIEW->get('cvid')}" {/strip}
									{strip}data-id="{$CUSTOM_VIEW->get('cvid')}" 
										{if $VIEWID neq '' && $VIEWID neq '0'  && $VIEWID == $CUSTOM_VIEW->getId()} selected="selected" 
										{elseif ($VIEWID == '' or $VIEWID == '0')&& $CUSTOM_VIEW->isDefault() eq 'true'} selected="selected" 
										{/if}
										class="filterOptionId_{$CUSTOM_VIEW->get('cvid')}">{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $MODULE)}</option>
								{/foreach}
							</select>
							<span class="fas fa-filter filterImage mr-2" style="display:none;"></span>
						{else}
							<input type="hidden" value="0" id="customFilter" />
						{/if}
					</div>
				</div>
			</div>
		</div>
		{include file=\App\Layout::getTemplatePath('gantt/GanttContents.tpl', $MODULE)}
		<table class="js-show-add-record d-none emptyRecordsDiv" data-js="class: d-none">
			<tbody>
				<tr>
					<td>
						{\App\Language::translate('LBL_RECORDS_NO_FOUND')} <a
							href="{$MODULE_MODEL->getCreateRecordUrl()}">{\App\Language::translate('LBL_CREATE_SINGLE_RECORD')}</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
{/strip}
