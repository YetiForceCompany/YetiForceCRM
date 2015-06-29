{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
		<div class="listViewActions pull-right">
        {if (method_exists($MODULE_MODEL,'isPagingSupported') && ($MODULE_MODEL->isPagingSupported()  eq true)) || !method_exists($MODULE_MODEL,'isPagingSupported')}
			<div class="pageNumbers alignTop {if $LISTVIEW_LINKS['LISTVIEWSETTING']|@count gt 0}{else}{/if}">
					<span>
						<span class="pageNumbersText" style="padding-right:5px">{if $LISTVIEW_ENTRIES_COUNT}{$PAGING_MODEL->getRecordStartRange()} {vtranslate('LBL_to', $MODULE)} {$PAGING_MODEL->getRecordEndRange()}{else}<span>&nbsp;</span>{/if}</span>
						<span class="glyphicon glyphicon-refresh pull-right totalNumberOfRecords cursorPointer{if !$LISTVIEW_ENTRIES_COUNT} hide{/if}"></span>
					</span>
			</div>
			<div class="btn-group alignTop margin0px">
				<span class="pull-right">
					<span class="btn-group" role="group">
						<button class="btn btn-default" role="group" id="listViewPreviousPageButton" {if !$PAGING_MODEL->isPrevPageExists()} disabled {/if} type="button"><span class="glyphicon glyphicon-chevron-left"></span></button>
							<button class="btn btn-default dropdown-toggle" role="group" type="button" id="listViewPageJump" data-toggle="dropdown" {if $PAGE_COUNT eq 1} disabled {/if}>
								<span class="vtGlyph vticon-pageJump" title="{vtranslate('LBL_LISTVIEW_PAGE_JUMP',$moduleName)}"></span>
							</button>
							<ul class="listViewBasicAction dropdown-menu" id="listViewPageJumpDropDown">
								<li>
									<div>
										<div class="col-md-4 recentComments textAlignCenter pushUpandDown2per"><span>{vtranslate('LBL_PAGE',$moduleName)}</span></div>
										<div class="col-md-3 recentComments">
											<input type="text" id="pageToJump" class="listViewPagingInput textAlignCenter" title="{vtranslate('LBL_LISTVIEW_PAGE_JUMP')}" value="{$PAGE_NUMBER}"/>
										</div>
										<div class="col-md-2 recentComments textAlignCenter pushUpandDown2per">
											{vtranslate('LBL_OF',$moduleName)}
										</div>
										<div class="col-md-2 recentComments pushUpandDown2per textAlignCenter" id="totalPageCount">{$PAGE_COUNT}</div>
									</div>
								</li>
							</ul>
						<button class="btn btn-default" id="listViewNextPageButton" {if (!$PAGING_MODEL->isNextPageExists()) or ($PAGE_COUNT eq 1)} disabled {/if} type="button"><span class="glyphicon glyphicon-chevron-right"></span></button>
					</span>
				</span>	
			</div>
        {/if}
	{if $LISTVIEW_LINKS['LISTVIEWSETTING']|@count gt 0}
		<div class="settingsIcon">
			<span class="pull-right btn-group">
				<button class="btn btn-default dropdown-toggle" href="#" data-toggle="dropdown"><span class="glyphicon glyphicon-wrench" alt="{vtranslate('LBL_SETTINGS', $MODULE)}" title="{vtranslate('LBL_SETTINGS', $MODULE)}"></span>&nbsp;&nbsp;<span class="caret"></span></button>
				<ul class="dropdown-menu">
					{foreach item=LISTVIEW_SETTING from=$LISTVIEW_LINKS['LISTVIEWSETTING']}
						<li><a href={$LISTVIEW_SETTING->getUrl()}>{vtranslate($LISTVIEW_SETTING->getLabel(), $MODULE)}</a></li>
					{/foreach}
				</ul>
			</span>
		</div>
	{/if}
	</div>
	<div class="clearfix"></div>
	<input type="hidden" id="recordsCount" value=""/>
	<input type="hidden" id="selectedIds" name="selectedIds" />
	<input type="hidden" id="excludedIds" name="excludedIds" />
{/strip}
