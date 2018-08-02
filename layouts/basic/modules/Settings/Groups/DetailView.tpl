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
	<div class="widget_header row">
		<div class="col-md-8">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			{if isset($SELECTED_PAGE)}
				{\App\Language::translate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
			{/if}
		</div>
		<div class="col-md-4 ">
			<a href="{$RECORD_MODEL->getEditViewUrl()}" class="btn btn-primary float-right mt-3">
				<span class="fas fa-edit mr-1"></span><strong>{\App\Language::translate('LBL_EDIT_RECORD', $MODULE)}</strong>
			</a>
		</div>
	</div>
	<div class="detailViewInfo" id="groupsDetailContainer">
		<form id="detailView" class="form-horizontal" method="POST">
			<div class="form-group row">
				<div class="col-md-2 col-form-label text-right">
					<span class="redColor">*</span>{\App\Language::translate('LBL_GROUP_NAME', $QUALIFIED_MODULE)} 
				</div>
				<div class="py-2">
					<strong>{$RECORD_MODEL->getName()}</strong>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2 col-form-label text-right">
					{\App\Language::translate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}  
				</div>
				<div class="py-2">
					<strong>{$RECORD_MODEL->getDescription()}</strong>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2 col-form-label text-right">
					<span class="redColor">*</span>{\App\Language::translate('LBL_MODULES', $QUALIFIED_MODULE)} 
				</div>
				<div class="py-2">
					<div class="row">
						<div class="col-md-9">
							{foreach key=TABID item=MODULE from=$RECORD_MODEL->getModules() name=modules}
								{if  $smarty.foreach.modules.last}
									<strong>{\App\Language::translate($MODULE,$MODULE)} </strong>
								{else}
									<strong>{\App\Language::translate($MODULE,$MODULE)}, </strong>
								{/if} 
							{/foreach}
						</div>
					</div>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2 col-form-label text-right">
					<span class="redColor">*</span>{\App\Language::translate('LBL_GROUP_MEMBERS', $QUALIFIED_MODULE)} 
				</div>
				<div class="col-md-5 controls pushDown">
					<div class="row">
						<div class="collectiveGroupMembers">
							<ul class="nav list-group">
								{assign var="GROUPS" value=$RECORD_MODEL->getMembers()}
								{foreach key=GROUP_LABEL item=GROUP_MEMBERS from=$GROUPS}
									{if !empty($GROUP_MEMBERS)}
										<li class="row groupLabel nav-header">
											{\App\Language::translate($GROUP_LABEL,$QUALIFIED_MODULE)}
										</li>
										{foreach item=GROUP_MEMBER_INFO from=$GROUP_MEMBERS}
											<li class="ml-1">
												<a href="{$GROUP_MEMBER_INFO->getDetailViewUrl()}">{\App\Language::translate($GROUP_MEMBER_INFO->get('name'), $QUALIFIED_MODULE)}</a>
											</li>
										{/foreach}
									{/if}
								{/foreach}
							</ul>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
{/strip}
