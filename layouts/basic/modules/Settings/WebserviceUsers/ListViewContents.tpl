{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<br>
		<div class="editViewContainer tab-pane active" id="{$TYPE_API}" data-type="{$TYPE_API}">
			<div class="listViewActionsDiv row">
				<div class="col-md-8 tn-toolbar">
					{foreach item=LINK from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
						{include file='ButtonLink.tpl'|@vtemplate_path:$MODULE BUTTON_VIEW='listViewBasic'}
					{/foreach}
				</div>
				<div class="col-md-4">
					{include file='ListViewActions.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
				</div>
			</div>
			<div class="listViewContentDiv listViewPageDiv" id="listViewContents">
				{include file='ListViewContents.tpl'|@vtemplate_path:'Settings:Vtiger'}
			</div>
		</div>
	</div>
{/strip}

