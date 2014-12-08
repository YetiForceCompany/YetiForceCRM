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
						<span class="icon-refresh pull-right totalNumberOfRecords cursorPointer{if !$LISTVIEW_ENTRIES_COUNT} hide{/if}"></span>
					</span>
			</div>
			<div class="btn-group alignTop margin0px">
				<span class="pull-right">
					<span class="btn-group">
						<button class="btn" id="listViewPreviousPageButton" {if !$PAGING_MODEL->isPrevPageExists()} disabled {/if} type="button"><span class="icon-chevron-left"></span></button>
							<button class="btn dropdown-toggle" type="button" id="listViewPageJump" data-toggle="dropdown" {if $PAGE_COUNT eq 1} disabled {/if}>
								<i class="vtGlyph vticon-pageJump" title="{vtranslate('LBL_LISTVIEW_PAGE_JUMP',$moduleName)}"></i>
							</button>
							<ul class="listViewBasicAction dropdown-menu" id="listViewPageJumpDropDown">
								<li>
									<span class="row-fluid">
										<span class="span3 pushUpandDown2per"><span class="pull-right">{vtranslate('LBL_PAGE',$moduleName)}</span></span>
										<span class="span4">
											<input type="text" id="pageToJump" class="listViewPagingInput" value="{$PAGE_NUMBER}"/>
										</span>
										<span class="span2 textAlignCenter pushUpandDown2per">
											{vtranslate('LBL_OF',$moduleName)}&nbsp;
										</span>
										<span class="span3 pushUpandDown2per" id="totalPageCount">{$PAGE_COUNT}</span>
									</span>
								</li>
							</ul>
						<button class="btn" id="listViewNextPageButton" {if (!$PAGING_MODEL->isNextPageExists()) or ($PAGE_COUNT eq 1)} disabled {/if} type="button"><span class="icon-chevron-right"></span></button>
					</span>
				</span>	
			</div>
        {/if}
	{if $LISTVIEW_LINKS['LISTVIEWSETTING']|@count gt 0}
		<div class="settingsIcon">
			<span class="pull-right btn-group">
				<button class="btn dropdown-toggle" href="#" data-toggle="dropdown"><i class="icon-wrench" alt="{vtranslate('LBL_SETTINGS', $MODULE)}" title="{vtranslate('LBL_SETTINGS', $MODULE)}"></i>&nbsp;&nbsp;<i class="caret"></i></button>
				<ul class="listViewSetting dropdown-menu">
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