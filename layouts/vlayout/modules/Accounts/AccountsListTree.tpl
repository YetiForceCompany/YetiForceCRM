{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<input type="hidden" id="treePopupValues" value="{Vtiger_Util_Helper::toSafeHTML($TREE)}">
	<div class="row padding0">
		<div class="col-md-8 rowContent">
			<br/>
			<div id="accountsListContents"></div>
		</div>
		<div class="col-md-4 siteBarRight">
			<div class="btn btn-block toggleSiteBarRightButton" title="{vtranslate('LBL_RIGHT_PANEL_SHOW_HIDE', $NAME)}">
				<span class="glyphicon glyphicon-chevron-right"></span>
			</div>
			<div class="siteBarContent">
				<div class="row">
					<div class="col-md-4 paddingTop10">
						<h5>{vtranslate('LBL_SELECT_MODULE',$NAME)}</h5>
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
