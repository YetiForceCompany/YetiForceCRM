{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Groups-Detail -->
	<div class="o-breadcrumb widget_header row">
		<div class="col-md-12 align-items-center flex-wrap">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			<div class="ml-auto">
				<a href="{$RECORD_MODEL->getEditViewUrl()}" class="btn btn-info float-right ml-2" role="button">
					<span class="yfi yfi-full-editing-view"></span> {App\Language::translate('LBL_EDIT_RECORD', $QUALIFIED_MODULE)}
				</a>
			</div>
		</div>
	</div>
	<div class="detailViewInfo" id="groupsDetailContainer">
		{foreach from=$STRUCTURE item=FIELD_MODEL key=FIELD_NAME name=structre}
			<div class="form-group row">
				<label class="u-font-weight-600 col-lg-2 textAlignRight">
					{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
					{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
					{if $FIELD_MODEL->get('tooltip')}
						<div class="js-popover-tooltip ml-1 d-inline my-auto u-h-fit u-cursor-pointer" data-placement="top" data-content="{\App\Language::translate($FIELD_MODEL->get('tooltip'), $QUALIFIED_MODULE)}">
							<span class="fas fa-info-circle"></span>
						</div>
					{/if}:
				</label>
				<div class="col-lg-8">
					{if $FIELD_NAME eq 'members'}
						<div class="collectiveGroupMembers">
							<ul class="nav list-group">
								{foreach key=GROUP_LABEL item=GROUP_MEMBERS from=$RECORD_MODEL->getMembers()}
									{assign var="MEMBERS_TYPE_MODULE" value=$GROUP_LABEL}
									{if $MEMBERS_TYPE_MODULE eq \App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES}
										{assign var="MEMBERS_TYPE_MODULE" value=\App\PrivilegeUtil::MEMBER_TYPE_ROLES}
									{/if}
									{if !empty($GROUP_MEMBERS)}
										<li class="groupLabel nav-header">
											{\App\Language::translate($GROUP_LABEL,$QUALIFIED_MODULE)}
										</li>
										{foreach item=GROUP_MEMBER_INFO from=$GROUP_MEMBERS}
											<li class="ml-1">
												{if \App\Security\AdminAccess::isPermitted($MEMBERS_TYPE_MODULE)}
													<a href="{$GROUP_MEMBER_INFO->getDetailViewUrl()}">
														{\App\Language::translate(\App\Labels::member($GROUP_MEMBER_INFO->getId()), $QUALIFIED_MODULE)}
													</a>
												{else}
													{\App\Language::translate(\App\Labels::member($GROUP_MEMBER_INFO->getId()), $QUALIFIED_MODULE)}
												{/if}
											</li>
										{/foreach}
									{/if}
								{/foreach}
							</ul>
						</div>
					{else}
						{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE MODULE_NAME=$QUALIFIED_MODULE RECORD=$RECORD_MODEL}
					{/if}
				</div>
			</div>
		{/foreach}
	</div>
	<!-- /tpl-Settings-Groups-Detail -->
{/strip}
