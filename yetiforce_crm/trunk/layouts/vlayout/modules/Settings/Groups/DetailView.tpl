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
    <div class="detailViewInfo" style="box-shadow:0;margin-top: 0;min-height:500px;">
		<div class="">
			<form id="detailView" class="form-horizontal" style="padding-top: 20px;" method="POST">
				<div class="row-fluid">
					<h3 class="span6 settingsHeader">
						{$RECORD_MODEL->get('groupname')}
					</h3>
					<span class="span6">
						<span class="pull-right">
							<button class="btn" onclick="window.location.href='{$RECORD_MODEL->getEditViewUrl()}'" type="button">
								<strong>{vtranslate('LBL_EDIT_RECORD', $MODULE)}</strong>
							</button>
						</span>
					</span>
				</div><hr>
				<div class="control-group">
					<span class="control-label">
						{vtranslate('LBL_GROUP_NAME', $QUALIFIED_MODULE)} <span class="redColor">*</span>
					</span>
					<div class="controls pushDown">
						<b>{$RECORD_MODEL->getName()}</b>
					</div>
				</div>
				<div class="control-group">
					<span class="control-label">
						{vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}
					</span>
					<div class="controls pushDown">
						<b>{$RECORD_MODEL->getDescription()}</b>
					</div>
				</div>
				<div class="control-group">
					<span class="control-label">
						{vtranslate('LBL_GROUP_MEMBERS', $QUALIFIED_MODULE)}
					</span>
					<div class="controls pushDown">
						<div class="row-fluid">
						<span class="span3 collectiveGroupMembers">
							<ul class="nav nav-list">
							{assign var="GROUPS" value=$RECORD_MODEL->getMembers()}
							{foreach key=GROUP_LABEL item=GROUP_MEMBERS from=$GROUPS}
								{if !empty($GROUP_MEMBERS)}
									<li class="row-fluid groupLabel nav-header">
											{vtranslate($GROUP_LABEL,$QUALIFIED_MODULE)}
									</li>
									{foreach item=GROUP_MEMBER_INFO from=$GROUP_MEMBERS}
										<li class="row-fluid">
											<a href="{$GROUP_MEMBER_INFO->getDetailViewUrl()}">{$GROUP_MEMBER_INFO->get('name')}</a>
										</li>
									{/foreach}
								{/if}
							{/foreach}
							</ul>
						</span>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
{strip}