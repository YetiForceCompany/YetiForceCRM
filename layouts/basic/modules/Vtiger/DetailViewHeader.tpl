{strip}
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
	{assign var="MODULE_NAME" value=$MODULE_MODEL->get('name')}
	<input id="recordId" type="hidden" value="{$RECORD->getId()}" />
	<div class="detailViewContainer">
		<div class="row detailViewTitle p-0">
			{if $SHOW_BREAD_CRUMBS}
				<div class="widget_header col-12 p-0 pl-3">
					<div class="row d-flex align-items-center pr-2">
						<div class="col-lg-12 col-xl-6">
							{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
						</div>
						<div class="col-lg-12 col-xl-6">
							<div class="col-12 detailViewToolbar d-flex justify-content-center justify-content-sm-start justify-content-lg-start justify-content-xl-end px-2">
									<div class="btn-toolbar detailViewActionsBtn d-flex justify-content-center">
										{if $DETAILVIEW_LINKS['DETAIL_VIEW_ADDITIONAL']}
											<span class="btn-group">
												{foreach item=LINK from=$DETAILVIEW_LINKS['DETAIL_VIEW_ADDITIONAL']}	
													{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='detailViewAdditional'}
												{/foreach}
											</span>
										{/if}
										{if $DETAILVIEW_LINKS['DETAIL_VIEW_BASIC']}
											<span class="btn-group">
												{foreach item=LINK from=$DETAILVIEW_LINKS['DETAIL_VIEW_BASIC']}
													{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='detailViewBasic'}
												{/foreach}
											</span>
										{/if}
										{if $DETAILVIEW_LINKS['DETAIL_VIEW_EXTENDED']}
											<span class="btn-group">
												{foreach item=LINK from=$DETAILVIEW_LINKS['DETAIL_VIEW_EXTENDED']}
													{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='detailViewExtended'}
												{/foreach}
											</span>
										{/if}
									</div>
							</div>
						</div>
					</div>
				</div>
			{/if}					
			{if !empty($DETAILVIEW_LINKS['DETAIL_VIEW_HEADER_WIDGET'])}
				{foreach item=WIDGET from=$DETAILVIEW_LINKS['DETAIL_VIEW_HEADER_WIDGET']}
					<div class="col-md-12 px-0">
						{Vtiger_Widget_Model::processWidget($WIDGET, $RECORD)}
					</div>
				{/foreach}
			{/if}
			{include file=\App\Layout::getTemplatePath('DetailViewHeaderTitle.tpl', $MODULE)}
		</div>
		<div class="detailViewInfo row">
			{include file=\App\Layout::getTemplatePath('RelatedListButtons.tpl', $MODULE)}
			<div class="col-md-12 {if !empty($DETAILVIEW_LINKS['DETAILVIEWTAB']) || !empty($DETAILVIEW_LINKS['DETAILVIEWRELATED']) } details {/if}">
				<form id="detailView" data-name-fields='{\App\Json::encode($MODULE_MODEL->getNameFields())}' method="POST">
					{if !empty($PICKLIST_DEPENDENCY_DATASOURCE)} 
						<input type="hidden" name="picklistDependency" value="{\App\Purifier::encodeHtml($PICKLIST_DEPENDENCY_DATASOURCE)}"> 
					{/if} 
					<div class="contents">
					{/strip}

