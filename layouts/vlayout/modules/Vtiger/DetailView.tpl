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
{* TODO: does not look like this is used, need to remove it*}
{strip}
	{assign var="MODULE_NAME" value=$MODULE->get('name')}
	<input id="recordId" type="hidden" value="{$RECORD->getId()}" />
	<div class="detailViewContainer">
		<div class="row-fluid detailViewTitle">
			<div class="span5">
				<span class="span0">
					<h3>{$RECORD->getName()}</h3>
				</span>
				<a class="span0 changeDetailViewMode height20 cursorPointer"><sub>{vtranslate('LBL_SHOW_FULL_DETAILS',$MODULE_NAME)}</sub></a>
				{assign var="FULL_MODE_URL" value={$RECORD->getDetailViewAjaxUrl()|cat:'&mode=showDetailViewByMode&requestMode=full'} }
				{assign var="SUMMARY_MODE_URL" value={$RECORD->getDetailViewAjaxUrl()|cat:'&mode=showDetailViewByMode&requestMode=summary'} }
				<input type="hidden" name="viewMode" value="summary" data-nextviewname="full" data-currentviewlabel="{vtranslate('LBL_SHOW_SUMMARY_DETAILS',{$MODULE_NAME})}"
					  data-summary-url="{$SUMMARY_MODE_URL}" data-full-url="{$FULL_MODE_URL}"  />
			</div>

			<div class="span7">
				<div class="pull-right">
					<div class="btn-toolbar">
						{foreach item=DEVAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWBASIC']}
						<span class="btn-group">
							<button class="btn"
								{if $DEVAIL_VIEW_BASIC_LINK->isPageLoadLink()} onclick="window.location.href='{$DEVAIL_VIEW_BASIC_LINK->getUrl()}'"{/if}>
								<strong>{vtranslate($DEVAIL_VIEW_BASIC_LINK->getLabel(), $MODULE_NAME)}</strong>
							</button>
						</span>
						{/foreach}
						{if $DETAILVIEW_LINKS['DETAILVIEW']|@count gt 0}
						<span class="btn-group">
							<a class="btn dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
								<strong>{vtranslate('LBL_MORE',{$MODULE_NAME})}</strong>
								<span class="caret"></span>
							</a>
							<ul class="dropdown-menu pull-right">
								{foreach item=DETAIL_VIEW_LINK from=$DETAILVIEW_LINKS['DETAILVIEW']}
								<li>
									<a href={$DETAIL_VIEW_LINK->getUrl()} >{vtranslate($DETAIL_VIEW_LINK->getLabel(), $MODULE_NAME)}</a>
								</li>
								{/foreach}

							</ul>
						</span>
						{/if}
					</div>
				</div>
			</div>
		</div>
		<div class="detailViewInfo row-fluid">
			<div class="span10 details">
				<form id="detailView" method="POST">
					<div class="contents">
						{$CONTENTS}
					</div>
				</form>
			</div>
			<div class="related">
				<ul class="nav nav-stacked nav-pills">
					{foreach item=RELATED_LINK from=$DETAILVIEW_LINKS['DETAILVIEWTAB']}
					<li class="" data-url="{$RELATED_LINK->getUrl()}" data-label-key="{$RELATED_LINK->getLabel()}">
						<a>{vtranslate($RELATED_LINK->getLabel(),{$MODULE_NAME})}</a>
					</li>
					{/foreach}
					{foreach item=RELATED_LINK from=$DETAILVIEW_LINKS['DETAILVIEWRELATED']}
					<li class="" data-url="{$RELATED_LINK->getUrl()}" data-label-key="{$RELATED_LINK->getLabel()}" >
							<a>{vtranslate($RELATED_LINK->getLabel(),{$MODULE_NAME})}</a>
						</li>
					{/foreach}
				</ul>
			</div>
		</div>
	</div>
{/strip}