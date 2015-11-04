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
	<div class="listViewPageDiv">
		<div class="listViewTopMenuDiv noprint">
			<div class="listViewActionsDiv row">
				<div class="btn-toolbar col-md-4">
					<div class="btn-group listViewMassActions">
						{if count($QUICK_LINKS['SIDEBARLINK']) gt 0}
							<button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								<span class="glyphicon glyphicon-list" aria-hidden="true"></span>
								&nbsp;&nbsp;<span class="caret"></span>
							</button>
							<ul class="dropdown-menu">
								{foreach item=SIDEBARLINK from=$QUICK_LINKS['SIDEBARLINK']}
									{assign var=SIDE_LINK_URL value=decode_html($SIDEBARLINK->getUrl())}
									{assign var="EXPLODED_PARSE_URL" value=explode('?',$SIDE_LINK_URL)}
									{assign var="COUNT_OF_EXPLODED_URL" value=count($EXPLODED_PARSE_URL)}
									{if $COUNT_OF_EXPLODED_URL gt 1}
										{assign var="EXPLODED_URL" value=$EXPLODED_PARSE_URL[$COUNT_OF_EXPLODED_URL-1]}
									{/if}
									{assign var="PARSE_URL" value=explode('&',$EXPLODED_URL)}
									{assign var="CURRENT_LINK_VIEW" value='view='|cat:$CURRENT_VIEW}
									{assign var="LINK_LIST_VIEW" value=in_array($CURRENT_LINK_VIEW,$PARSE_URL)}
									{assign var="CURRENT_MODULE_NAME" value='module='|cat:$MODULE}
									{assign var="IS_LINK_MODULE_NAME" value=in_array($CURRENT_MODULE_NAME,$PARSE_URL)}
									<li>
										<a class="quickLinks" href="{$SIDEBARLINK->getUrl()}">
											{vtranslate($SIDEBARLINK->getLabel(), $MODULE)}
										</a>
									</li>
									{/foreach}
							</ul>
						{/if}
					</div>
					<div class="btn-group listViewMassActions">
						{if count($LISTVIEW_MASSACTIONS) gt 0 || $LISTVIEW_LINKS['LISTVIEW']|@count gt 0}
							<button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><strong>{vtranslate('LBL_ACTIONS', $MODULE)}</strong>&nbsp;&nbsp;<span class="caret"></span></button>
							<ul class="dropdown-menu">
								{foreach item="LISTVIEW_MASSACTION" from=$LISTVIEW_MASSACTIONS name=actionCount}
									<li id="{$MODULE}_listView_massAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_MASSACTION->getLabel())}"><a href="javascript:void(0);" {if stripos($LISTVIEW_MASSACTION->getUrl(), 'javascript:')===0}onclick='{$LISTVIEW_MASSACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick="Vtiger_List_Js.triggerMassAction('{$LISTVIEW_MASSACTION->getUrl()}')"{/if} >{vtranslate($LISTVIEW_MASSACTION->getLabel(), $MODULE)}</a></li>
										{if $smarty.foreach.actionCount.last eq true}
										<li class="divider"></li>
										{/if}
									{/foreach}
									{if $LISTVIEW_LINKS['LISTVIEW']|@count gt 0}
										{foreach item=LISTVIEW_ADVANCEDACTIONS from=$LISTVIEW_LINKS['LISTVIEW']}
										<li id="{$MODULE}_listView_advancedAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_ADVANCEDACTIONS->getLabel())}">
											<a {if stripos($LISTVIEW_ADVANCEDACTIONS->getUrl(), 'javascript:')===0}
													href="javascript:void(0);" onclick='{$LISTVIEW_ADVANCEDACTIONS->getUrl()|substr:strlen("javascript:")};'
												{else} 
													href='{$LISTVIEW_ADVANCEDACTIONS->getUrl()}'
												{/if}
												{if $LISTVIEW_ADVANCEDACTIONS->get('linkclass') neq ''}
													class="{$LISTVIEW_ADVANCEDACTIONS->get('linkclass')}"
												{/if}
												{if count($LISTVIEW_ADVANCEDACTIONS->get('linkdata')) gt 0}
													{foreach from=$LISTVIEW_ADVANCEDACTIONS->get('linkdata') key=NAME item=DATA}
														data-{$NAME}="{$DATA}" 
													{/foreach}
												{/if}
											>{vtranslate($LISTVIEW_ADVANCEDACTIONS->getLabel(), $MODULE)}</a>
										</li>
										{/foreach}
									{/if}
							</ul>
						{/if}
					</div>
					{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
						<div class="btn-group">
							<button id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_BASICACTION->getLabel())}" class="btn btn-default {if $LISTVIEW_BASICACTION->linkclass neq ''}{$LISTVIEW_BASICACTION->linkclass}{/if} moduleColor_{$MODULE}" 
								{if $LISTVIEW_BASICACTION->get('linkdata') neq ''}
									{foreach from=$LISTVIEW_BASICACTION->get('linkdata') key=NAME item=DATA}
										data-{$NAME}="{$DATA}" 
									{/foreach}
								{else}
									{if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0}
										onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'
									{else}
										onclick='window.location.href = "{$LISTVIEW_BASICACTION->getUrl()}"'
									{/if}
								{/if}>
								{if $LISTVIEW_BASICACTION->linkicon eq ''}
									<span class="glyphicon glyphicon-plus"></span>
								{else}
									<span class="{$LISTVIEW_BASICACTION->linkicon}"></span>
								{/if}&nbsp;<strong>{vtranslate($LISTVIEW_BASICACTION->getLabel(), $MODULE)}</strong></button>
						</div>
					{/foreach}
				</div>
				<div class="btn-toolbar col-md-3">
					<div class="customFilterMainSpan btn-group">
						{if $CUSTOM_VIEWS|@count gt 0}
							<select id="customFilter" title="{vtranslate('LBL_CUSTOM_FILTER')}">
								{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
									<optgroup label='{vtranslate('LBL_CV_GROUP_'|cat:strtoupper($GROUP_LABEL))}' >
										{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS} 
											<option  data-editurl="{$CUSTOM_VIEW->getEditUrl()}" data-deleteurl="{$CUSTOM_VIEW->getDeleteUrl()}" data-approveurl="{$CUSTOM_VIEW->getApproveUrl()}" data-denyurl="{$CUSTOM_VIEW->getDenyUrl()}" data-duplicateurl="{$CUSTOM_VIEW->getDuplicateUrl()}" 
													 data-editable="{$CUSTOM_VIEW->isEditable()}" data-deletable="{$CUSTOM_VIEW->isDeletable()}" 
													 data-pending="{$CUSTOM_VIEW->isPending()}" 
													 data-public="{$CUSTOM_VIEW->isPublic() && $CURRENT_USER_MODEL->isAdminUser()}" id="filterOptionId_{$CUSTOM_VIEW->get('cvid')}" 
													 value="{$CUSTOM_VIEW->get('cvid')}" 
													 data-id="{$CUSTOM_VIEW->get('cvid')}" {if $VIEWID neq '' && $VIEWID neq '0'  && $VIEWID == $CUSTOM_VIEW->getId()} selected="selected" {elseif ($VIEWID == '' or $VIEWID == '0')&& $CUSTOM_VIEW->isDefault() eq 'true'} selected="selected" {/if} class="filterOptionId_{$CUSTOM_VIEW->get('cvid')}">{vtranslate($CUSTOM_VIEW->get('viewname'), $MODULE)}{if $GROUP_LABEL neq 'Mine' && $GROUP_LABEL neq 'System'} [ {$CUSTOM_VIEW->getOwnerName()} ]  {/if}</option>
										{/foreach}
									</optgroup>
								{/foreach}
								{if $FOLDERS neq ''}
									<optgroup id="foldersBlock" label='{vtranslate('LBL_FOLDERS', $MODULE)}' >
										{foreach item=FOLDER from=$FOLDERS}
											<option data-foldername="{$FOLDER->getName()}" {if decode_html($FOLDER->getName()) eq $FOLDER_NAME} selected=""{/if} data-folderid="{$FOLDER->get('folderid')}" data-deletable="{!($FOLDER->hasDocuments())}" class="filterOptionId_folder{$FOLDER->get('folderid')} folderOption{if $FOLDER->getName() eq 'Default'} defaultFolder {/if}" id="filterOptionId_folder{$FOLDER->get('folderid')}" data-id="{$DEFAULT_CUSTOM_FILTER_ID}">{vtranslate($FOLDER->getName(),$MODULE)}</option>
										{/foreach}
									</optgroup>
								{/if}
							</select>
							{if Users_Privileges_Model::isPermitted($MODULE, 'CreateCustomFilter')}
								<div class="filterActionsDiv hide">
									<hr>
									<ul class="filterActions">
										<li data-value="create" id="createFilter" data-createurl="{$CUSTOM_VIEW->getCreateUrl()}"><span class="glyphicon glyphicon-plus-sign"></span> {vtranslate('LBL_CREATE_NEW_FILTER')}</li>
									</ul>
								</div>
							{/if}
							<img class="filterImage" alt="{vtranslate('LBL_FILTER')}" src="{'filter.png'|vimage_path}" style="display:none;height:13px;margin-right:2px;vertical-align: middle;">
						{else}
							<input type="hidden" value="0" id="customFilter" />
						{/if}
					</div>
				</div>
				<div class="col-md-5 btn-toolbar paddingRightZero">
					{include file='ListViewActions.tpl'|@vtemplate_path}
				</div>
				<span class="hide filterActionImages pull-right">
					<span title="{vtranslate('LBL_DENY', $MODULE)}" data-value="deny" class="icon-ban-circle alignMiddle denyFilter filterActionImage pull-right"></span>
					<span title="{vtranslate('LBL_APPROVE', $MODULE)}" data-value="approve" class="glyphicon glyphicon-ok alignMiddle approveFilter filterActionImage pull-right"></span>
					<span title="{vtranslate('LBL_DELETE', $MODULE)}" data-value="delete" class="glyphicon glyphicon-trash alignMiddle deleteFilter filterActionImage pull-right"></span>
					<span title="{vtranslate('LBL_EDIT', $MODULE)}" data-value="edit" class="glyphicon glyphicon-pencil alignMiddle editFilter filterActionImage pull-right"></span>
					<span title="{vtranslate('LBL_DUPLICATE', $MODULE)}" data-value="duplicate" class="glyphicon glyphicon-retweet alignMiddle duplicateFilter filterActionImage pull-right"></span>
				</span>
			</div>
		</div>
		<div class="listViewContentDiv" id="listViewContents">
		{/strip}
