{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	<div class="listViewPageDiv">
		<div class="listViewTopMenuDiv noprint">
			<div class="listViewActionsDiv row">
				<div class="btn-toolbar col-md-4 col-sm-6 col-xs-12">
					{include file='ButtonViewLinks.tpl'|@vtemplate_path LINKS=$QUICK_LINKS['SIDEBARLINK']}
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
					{foreach item=LINK from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
						{include file='ButtonLink.tpl'|@vtemplate_path:$MODULE BUTTON_VIEW='listView'}
					{/foreach}
				</div>
				<div class="btn-toolbar col-md-3 col-sm-5 col-xs-12 pull-right-sm pull-left-xs">
					<div class="customFilterMainSpan btn-group">
						{if $CUSTOM_VIEWS|@count gt 0}
							<select id="customFilter" title="{vtranslate('LBL_CUSTOM_FILTER')}">
								{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
									<optgroup label='{vtranslate('LBL_CV_GROUP_'|cat:strtoupper($GROUP_LABEL))}' >
										{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS} 
											<option data-orderby="{$CUSTOM_VIEW->getSortOrderBy('orderBy')}" data-sortorder="{$CUSTOM_VIEW->getSortOrderBy('sortOrder')}" data-editurl="{$CUSTOM_VIEW->getEditUrl()}" data-deleteurl="{$CUSTOM_VIEW->getDeleteUrl()}" data-approveurl="{$CUSTOM_VIEW->getApproveUrl()}" data-denyurl="{$CUSTOM_VIEW->getDenyUrl()}" data-duplicateurl="{$CUSTOM_VIEW->getDuplicateUrl()}" 
													 data-editable="{$CUSTOM_VIEW->isEditable()}" data-deletable="{$CUSTOM_VIEW->isDeletable()}" 
													 data-pending="{$CUSTOM_VIEW->isPending()}" 
													 data-public="{$CUSTOM_VIEW->isPublic() && $USER_MODEL->isAdminUser()}" id="filterOptionId_{$CUSTOM_VIEW->get('cvid')}" 
													 value="{$CUSTOM_VIEW->get('cvid')}" 
													 data-id="{$CUSTOM_VIEW->get('cvid')}" {if $VIEWID neq '' && $VIEWID neq '0'  && $VIEWID == $CUSTOM_VIEW->getId()} selected="selected" {elseif ($VIEWID == '' or $VIEWID == '0')&& $CUSTOM_VIEW->isDefault() eq 'true'} selected="selected" {/if} class="filterOptionId_{$CUSTOM_VIEW->get('cvid')}">{vtranslate($CUSTOM_VIEW->get('viewname'), $MODULE)}{if $GROUP_LABEL neq 'Mine' && $GROUP_LABEL neq 'System'} [ {$CUSTOM_VIEW->getOwnerName()} ]  {/if}</option>
										{/foreach}
									</optgroup>
								{/foreach}
								{if isset($FOLDERS)}
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
				<div class="col-xs-12 col-md-5 btn-toolbar paddingRightZero">
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
			{if $CUSTOM_VIEWS|@count gt 0}
				<div class="row">
					<div class="col-xs-12 btn-toolbar">
						{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
							{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS} 
								{if $CUSTOM_VIEW->isFeatured()}
									<h5 class="btn-group resetButton cursorPointer">
										<span class="label label-default btn-success featuredLabel" data-cvid="{$CUSTOM_VIEW->getId()}" {if $CUSTOM_VIEW->get('color')}style="background-color: {$CUSTOM_VIEW->get('color')};"{/if}>
											{vtranslate($CUSTOM_VIEW->get('viewname'), $MODULE)}
											{if $CUSTOM_VIEW->get('description')}
												&nbsp;<span class="popoverTooltip glyphicon glyphicon-info-sign"  data-placement="auto right" data-content="{Vtiger_Util_Helper::toSafeHTML($CUSTOM_VIEW->get('description'))}"></span>
											{/if}
										</span>
									</h5>
								{/if}
							{/foreach}
						{/foreach}
					</div>
				</div>
			{/if}
		</div>
		<div class="listViewContentDiv" id="listViewContents">
		{/strip}
