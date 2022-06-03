{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<div class="listViewPageDiv">
		<div class="listViewTopMenuDiv noprint">
			<div class="listViewActionsDiv row">
				<div class="col-12 d-inline-flex flex-wrap">
					<div class="c-list__buttons d-flex flex-wrap flex-sm-nowrap u-w-sm-down-100">
						{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS='buttonTextHolder mr-sm-1 mb-1 mb-sm-0 c-btn-block-sm-down'}
						{assign var=LINKS value=[]}
						{if $LISTVIEW_MASSACTIONS}
							{assign var=LINKS value=$LISTVIEW_MASSACTIONS}
						{/if}
						{if isset($LISTVIEW_LINKS['LISTVIEW'])}
							{assign var=LINKS value=array_merge($LINKS,$LISTVIEW_LINKS['LISTVIEW'])}
						{/if}
						{if 'Tiles' eq $VIEW}
							{include file=\App\Layout::getTemplatePath('TilesSize.tpl')}
						{/if}
						{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$LINKS TEXT_HOLDER='LBL_ACTIONS' BTN_ICON='fa fa-list' CLASS='listViewMassActions mr-sm-1 mb-1 mb-sm-0 c-btn-block-sm-down'}
						{foreach item=LINK from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
							{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='listView' CLASS='mr-sm-1 mb-1 c-btn-block-sm-down'}
						{/foreach}
					</div>
					<div class="customFilterMainSpan ml-auto mx-xl-auto">
						{if $CUSTOM_VIEWS|@count gt 0}
							<select id="customFilter" class="form-control"
								title="{\App\Language::translate('LBL_CUSTOM_FILTER')}">
								{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
									<optgroup
										label='{\App\Language::translate('LBL_CV_GROUP_'|cat:strtoupper($GROUP_LABEL))}'>
										{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
											<option
												data-editurl="{$CUSTOM_VIEW->getEditUrl($MID)}"
												data-deleteurl="{$CUSTOM_VIEW->getDeleteUrl($MID)}"
												data-approveurl="{$CUSTOM_VIEW->getApproveUrl()}"
												data-denyurl="{$CUSTOM_VIEW->getDenyUrl()}"
												data-duplicateurl="{$CUSTOM_VIEW->getDuplicateUrl()}" {' '}
												data-editable="{$CUSTOM_VIEW->isEditable()}"
												data-deletable="{$CUSTOM_VIEW->privilegeToDelete()}" {' '}
												{if $CUSTOM_VIEW->isFeaturedEditable()}
													data-featured="{$CUSTOM_VIEW->isFeatured()}"
												{/if}
												data-pending="{$CUSTOM_VIEW->isPending()}" {' '}
												data-public="{$CUSTOM_VIEW->isPublic() && $USER_MODEL->isAdminUser()}"
												{if $GROUP_LABEL neq 'Mine' && $GROUP_LABEL neq 'System'}
													data-option="{\App\Purifier::encodeHtml($CUSTOM_VIEW->getOwnerName())}"
												{/if}
												id="filterOptionId_{$CUSTOM_VIEW->get('cvid')}" {' '}
												value="{$CUSTOM_VIEW->get('cvid')}" {' '}
												data-id="{$CUSTOM_VIEW->get('cvid')}" {if $VIEWID neq '' && $VIEWID neq '0'  && $VIEWID == $CUSTOM_VIEW->getId()} selected="selected" {elseif ($VIEWID == '' or $VIEWID == '0')&& $CUSTOM_VIEW->isDefault() eq 'true'} selected="selected" {/if}
												class="filterOptionId_{$CUSTOM_VIEW->get('cvid')}">
												{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $MODULE)}
											</option>
										{/foreach}
									</optgroup>
								{/foreach}
								{if isset($FOLDERS)}
									<optgroup id="foldersBlock" label='{\App\Language::translate('LBL_FOLDERS', $MODULE)}'>
										{foreach item=FOLDER from=$FOLDERS}
											<option data-foldername="{$FOLDER->getName()}" {if App\Purifier::decodeHtml($FOLDER->getName()) eq $FOLDER_NAME} selected="" {/if}
												data-folderid="{$FOLDER->get('folderid')}"
												data-deletable="{!($FOLDER->hasDocuments())}"
												class="filterOptionId_folder{$FOLDER->get('folderid')} folderOption{if $FOLDER->getName() eq 'Default'} defaultFolder {/if}"
												id="filterOptionId_folder{$FOLDER->get('folderid')}"
												data-id="{$DEFAULT_CUSTOM_FILTER_ID}">{\App\Language::translate($FOLDER->getName(),$MODULE)}</option>
										{/foreach}
									</optgroup>
								{/if}
							</select>
							{if (!$MID || !\App\CustomView::getModuleFiltersByMenuId($MID, $MODULE)) && \App\Privilege::isPermitted($MODULE, 'CreateCustomFilter')}
								<div class="filterActionsDiv d-none">
									<hr>
									<ul class="filterActions list-unstyled m-2">
										<li id="createFilter" data-value="create"
											data-createurl="{$CUSTOM_VIEW->getCreateUrl()}"><a href="#"><span
													class="fas fa-plus-circle"></span> {\App\Language::translate('LBL_CREATE_NEW_FILTER')}
											</a></li>
									</ul>
								</div>
							{/if}
							<span class="fas fa-filter filterImage mr-2" style="display:none;"></span>
						{else}
							<input type="hidden" value="0" id="customFilter" />
						{/if}
					</div>

					<div class="c-list__right-container d-flex flex-nowrap u-overflow-scroll-non-desktop">
						{include file=\App\Layout::getTemplatePath('ListViewActions.tpl')}
					</div>
				</div>
			</div>
			{if $CUSTOM_VIEWS|@count gt 0}
				<ul class="c-tab--border nav nav-tabs" role="list">
					{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
						{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
							{if $CUSTOM_VIEW->isFeatured()}
								<li class="nav-item js-filter-tab c-tab--small font-weight-bold"
									data-cvid="{$CUSTOM_VIEW->getId()}" role="listitem" data-js="click">
									<a class="nav-link{if $VIEWID == $CUSTOM_VIEW->getId()} active{/if}" href="#"
										{if $CUSTOM_VIEW->get('color')}style="color: {$CUSTOM_VIEW->get('color')}; border-color: {$CUSTOM_VIEW->get('color')} {$CUSTOM_VIEW->get('color')} #fff" {/if}
										data-toggle="tab">
										{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $MODULE)}
										{if $CUSTOM_VIEW->get('description')}
											<span class="js-popover-tooltip ml-1" data-toggle="popover"
												data-placement="top"
												data-content="{\App\Purifier::encodeHtml($CUSTOM_VIEW->get('description'))}" data-js="popover">
												<span class="fas fa-info-circle"></span>
											</span>
										{/if}
									</a>
								</li>
							{/if}
						{/foreach}
					{/foreach}
				</ul>
			{/if}
		</div>
		<div id="selectAllMsgDiv" class="alert-block msgDiv noprint">
			<strong><a id="selectAllMsg" href="#">{\App\Language::translate('LBL_SELECT_ALL',$MODULE_NAME)}
					&nbsp;{\App\Language::translate($MODULE_NAME ,$MODULE_NAME)}
					&nbsp;(<span id="totalRecordsCount"></span>)</a></strong>
		</div>
		<div id="deSelectAllMsgDiv" class="alert-block msgDiv noprint">
			<strong><a id="deSelectAllMsg" href="#">{\App\Language::translate('LBL_DESELECT_ALL_RECORDS',$MODULE_NAME)}</a></strong>
		</div>
		<div class="listViewContentDiv" id="listViewContents">
{/strip}
