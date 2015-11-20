{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<input type="hidden" id="treePopupValues" value="{Vtiger_Util_Helper::toSafeHTML($TREE)}">
	<div class="row padding0">
		<div class="col-md-8 rowContent">
			<br/>
			<div class="alert alert-info" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
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
						<select class="chzn-select form-control" id="moduleFilter" >
							{foreach item=NAME from=$MODULES}
								<option value="{$NAME}" {if $NAME eq $SELECTED_MODULE_NAME} selected {/if} >{vtranslate($NAME,$NAME)}</option>
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
