{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<input type="hidden" id="treePopupValues" value="{Vtiger_Util_Helper::toSafeHTML($TREE)}">
	<div class="row padding0">
		<div class="col-md-8 rowContent">
			<div class="row paddingTop10">
			<div class="col-md-1">
				<div class="btn-toolbar">
					<div class="btn-group">
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
				</div>
			</div>
			<div class="col-md-11">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			</div>
			</div>
			<hr />
			<div class="alert alert-info" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>&nbsp;
				{vtranslate('LBL_ACCOUNTS_LIST_TREE_ALERT', $MODULE)}
			</div>
			<div id="accountsListContents"></div>
		</div>
		<div class="col-md-4 siteBarRight">
			<div class="btn btn-block toggleSiteBarRightButton" title="{vtranslate('LBL_RIGHT_PANEL_SHOW_HIDE', $MODULE)}">
				<span class="glyphicon glyphicon-chevron-right"></span>
			</div>
			<div class="siteBarContent">
				<div class="row">
					<div class="col-md-4 paddingTop10">
						<h5>{vtranslate('LBL_SELECT_MODULE',$MODULE)}</h5>
					</div>
					<div class="col-md-8 paddingTop10">
						<select class="chzn-select form-control" id="moduleList" >
							{foreach item=NAME from=$MODULES}
								<option value="{$NAME}" {if $NAME eq $SELECTED_MODULE_NAME} selected {/if} >
									{vtranslate($NAME,$NAME)}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4 paddingTop10">
						<h5>{vtranslate('LBL_FILTERING',$MODULE)}</h5>
					</div>
					<div class="col-md-8 paddingTop10">
						<select class="chzn-select form-control" id="moduleFilter" >
							{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
								<optgroup label="{vtranslate('LBL_CV_GROUP_'|cat:strtoupper($GROUP_LABEL))}">
									{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
										<option value="{$CUSTOM_VIEW->get('cvid')}" {if $VIEWID neq '' && $VIEWID neq '0'  && $VIEWID == $CUSTOM_VIEW->getId()} selected="selected" {elseif ($VIEWID == '' or $VIEWID == '0')&& $CUSTOM_VIEW->isDefault() eq 'true'} selected="selected" {/if}>
											{vtranslate($CUSTOM_VIEW->get('viewname'), $MODULE)}
											{if $GROUP_LABEL neq 'Mine' && $GROUP_LABEL neq 'System'} [ {$CUSTOM_VIEW->getOwnerName()} ]  {/if}
										</option>
									{/foreach}
								</optgroup>
							{/foreach}
						</select>
					</div>
				</div>
				<hr />
				<div id="treeContents"></div>
			</div>
		</div>
	</div>
{/strip}
