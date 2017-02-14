 {*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
 
{strip}
<div class="">
	<div class="row widget_header">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
	<div class="widget_header row">
		<div class="col-md-2 pull-left">
			<select class="chzn-select form-control" id="usersFilter" >
				<option value="">{vtranslate('LBL_ALL', $QUALIFIED_MODULE)}</option>
				<option value="other" name="other" value="">{vtranslate('LBL_OTHER', $QUALIFIED_MODULE)}</option>
				{foreach item=USERNAME key=USER from=$USERSLIST}
					<option value="{$USER}" name="{$USERNAME}" {if $USERNAME eq $SELECTED_USER} selected {/if}>{$USERNAME}</option>
				{/foreach}
			</select>
		</div>
		<div class="col-md-10 pull-right">
			{include file='ListViewActions.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
		</div>
	</div>
	<div class="clearfix"></div>
	<div class="listViewContentDiv" id="listViewContents">
{/strip}
