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
		<div class="listViewTopMenuDiv noprint mb-2">
			<div class="listViewActionsDiv row">
				<div class="col-lg-5 col-12">
					{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS=buttonTextHolder}
					{assign var=LINKS value=[]}
					{if $LISTVIEW_MASSACTIONS}
						{assign var=LINKS value=$LISTVIEW_MASSACTIONS}
					{/if}
					{if isset($LISTVIEW_LINKS['LISTVIEW'])}
						{assign var=LINKS value=array_merge($LINKS,$LISTVIEW_LINKS['LISTVIEW'])}
					{/if}
					{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$LINKS TEXT_HOLDER='LBL_ACTIONS' BTN_ICON='fa fa-list' CLASS=listViewMassActions}
					{foreach item=LINK from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
						{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='listView'}
					{/foreach}
				</div>
				<div class="col-lg-7 col-12 d-flex flex-wrap flex-sm-nowrap flex-md-nowrap flex-lg-nowrap justify-content-center justify-content-md-end justify-content-lg-end">
					<div class="customFilterMainSpan">
						{if $CUSTOM_VIEWS|@count gt 0}
							<select id="customFilter" class="form-control" title="{\App\Language::translate('LBL_CUSTOM_FILTER')}">
								{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
									<optgroup label='{\App\Language::translate('LBL_CV_GROUP_'|cat:strtoupper($GROUP_LABEL))}' >
										{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
											<option data-orderby="{$CUSTOM_VIEW->getSortOrderBy('orderBy')}" data-sortorder="{$CUSTOM_VIEW->getSortOrderBy('sortOrder')}" data-editurl="{$CUSTOM_VIEW->getEditUrl()}" data-deleteurl="{$CUSTOM_VIEW->getDeleteUrl()}" data-approveurl="{$CUSTOM_VIEW->getApproveUrl()}" data-denyurl="{$CUSTOM_VIEW->getDenyUrl()}" data-duplicateurl="{$CUSTOM_VIEW->getDuplicateUrl()}" {/strip} {strip}
													data-editable="{$CUSTOM_VIEW->isEditable()}" data-deletable="{$CUSTOM_VIEW->privilegeToDelete()}" {/strip} {strip}
													data-pending="{$CUSTOM_VIEW->isPending()}" {/strip} {strip}
													data-public="{$CUSTOM_VIEW->isPublic() && $USER_MODEL->isAdminUser()}" id="filterOptionId_{$CUSTOM_VIEW->get('cvid')}" {/strip} {strip}
													value="{$CUSTOM_VIEW->get('cvid')}" {/strip} {strip}
													data-id="{$CUSTOM_VIEW->get('cvid')}" {if $VIEWID neq '' && $VIEWID neq '0'  && $VIEWID == $CUSTOM_VIEW->getId()} selected="selected" {elseif ($VIEWID == '' or $VIEWID == '0')&& $CUSTOM_VIEW->isDefault() eq 'true'} selected="selected" {/if} class="filterOptionId_{$CUSTOM_VIEW->get('cvid')}">{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $MODULE)}{if $GROUP_LABEL neq 'Mine' && $GROUP_LABEL neq 'System'} [ {$CUSTOM_VIEW->getOwnerName()} ]  {/if}</option>
										{/foreach}
									</optgroup>
								{/foreach}
								{if isset($FOLDERS)}
									<optgroup id="foldersBlock" label='{\App\Language::translate('LBL_FOLDERS', $MODULE)}' >
										{foreach item=FOLDER from=$FOLDERS}
											<option data-foldername="{$FOLDER->getName()}" {if App\Purifier::decodeHtml($FOLDER->getName()) eq $FOLDER_NAME} selected=""{/if} data-folderid="{$FOLDER->get('folderid')}" data-deletable="{!($FOLDER->hasDocuments())}" class="filterOptionId_folder{$FOLDER->get('folderid')} folderOption{if $FOLDER->getName() eq 'Default'} defaultFolder {/if}" id="filterOptionId_folder{$FOLDER->get('folderid')}" data-id="{$DEFAULT_CUSTOM_FILTER_ID}">{\App\Language::translate($FOLDER->getName(),$MODULE)}</option>
										{/foreach}
									</optgroup>
								{/if}
							</select>
							{if \App\Privilege::isPermitted($MODULE, 'CreateCustomFilter')}
								<div class="filterActionsDiv d-none">
									<hr>
									<ul class="filterActions list-unstyled m-2">
										<li id="createFilter" data-value="create" data-createurl="{$CUSTOM_VIEW->getCreateUrl()}"><a href="#"><span class="fas fa-plus-circle"></span> {\App\Language::translate('LBL_CREATE_NEW_FILTER')}</a></li>
									</ul>
								</div>
							{/if}
							<span class="fas fa-filter filterImage mr-2" style="display:none;"></span>
						{else}
							<input type="hidden" value="0" id="customFilter" />
						{/if}
					</div>
					{include file=\App\Layout::getTemplatePath('ListViewActions.tpl')}
				</div>
				<span class="d-none filterActionImages float-right">
					<span title="{\App\Language::translate('LBL_DENY', $MODULE)}" data-value="deny" class="fas fa-exclamation-circle alignMiddle denyFilter filterActionImage float-right"></span>
					<span title="{\App\Language::translate('LBL_APPROVE', $MODULE)}" data-value="approve" class="fas fa-check alignMiddle approveFilter filterActionImage float-right"></span>
					<span title="{\App\Language::translate('LBL_DELETE', $MODULE)}" data-value="delete" class="fas fa-trash-alt alignMiddle deleteFilter filterActionImage float-right"></span>
					<span title="{\App\Language::translate('LBL_EDIT', $MODULE)}" data-value="edit" class="fas fa-pencil-alt alignMiddle editFilter filterActionImage float-right"></span>
					<span title="{\App\Language::translate('LBL_DUPLICATE', $MODULE)}" data-value="duplicate" class="fas fa-retweet alignMiddle duplicateFilter filterActionImage float-right"></span>
				</span>
			</div>
			{if $CUSTOM_VIEWS|@count gt 0}
				<ul class="nav nav-tabs pt-2" role="tablist">
					{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
						{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
							{if $CUSTOM_VIEW->isFeatured()}
								<li class="nav-item featuredLabel c-tab--small font-weight-bold" data-cvid="{$CUSTOM_VIEW->getId()}">
									<a class="nav-link" href="#" {if $CUSTOM_VIEW->get('color')}style="color: {$CUSTOM_VIEW->get('color')};"{/if} data-toggle="tab" role="tab" aria-selected="false">
										{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $MODULE)}
										{if $CUSTOM_VIEW->get('description')}
											&nbsp;<span class="js-popover-tooltip fas fa-info-circle" data-js="popover" data-placement="auto right" data-content="{\App\Purifier::encodeHtml($CUSTOM_VIEW->get('description'))}"></span>
										{/if}
									</a>
								</li>
							{/if}
						{/foreach}
					{/foreach}
				</ul>
			{/if}
		</div>
		<div class="listViewContentDiv" id="listViewContents">
		{/strip}
