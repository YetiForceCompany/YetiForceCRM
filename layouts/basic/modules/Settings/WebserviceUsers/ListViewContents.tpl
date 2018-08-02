{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}

	<div class="editViewContainer tab-pane active mt-2" id="{$TYPE_API}" data-type="{$TYPE_API}">
		<div class="listViewActionsDiv row">
			<div class="col-md-8 tn-toolbar">
				{foreach item=LINK from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
					{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='listViewBasic'}
				{/foreach}
			</div>
			<div class="col-md-4 ">
				<div class="float-right">
					{include file=\App\Layout::getTemplatePath('ListViewActions.tpl', $QUALIFIED_MODULE)}
				</div>
			</div>
		</div>
		<div class="listViewContentDiv listViewPageDiv mt-2" id="listViewContents">
			{include file=\App\Layout::getTemplatePath('ListViewContents.tpl', 'Settings:Vtiger')}
		</div>
	</div>
</div>
{/strip}

