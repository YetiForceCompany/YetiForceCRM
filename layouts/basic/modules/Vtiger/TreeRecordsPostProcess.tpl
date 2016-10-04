{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
</div>
</div>
</div>
<div class="{if $USER_MODEL->get('leftpanelhide')}leftPanelOpen {/if}siteBarRight">
	<div class="btn btn-block toggleSiteBarRightButton" title="{vtranslate('LBL_RIGHT_PANEL_SHOW_HIDE', $MODULE)}">
		<span class="glyphicon glyphicon-chevron-right"></span>
	</div>
	<div class="siteBarContent">
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
		<input type="hidden" id="treeListValues" value="{Vtiger_Util_Helper::toSafeHTML($TREE_LIST)}">
		<input type="hidden" id="isActiveCategory" value="{$SELECTABLE_CATEGORY}" />
		<div id="treeListContents"></div>
	</div>
</div>
</div>
</div>
{/strip}
