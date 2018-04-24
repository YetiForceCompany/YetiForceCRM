{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}

{strip}
	<div class="">
		<div class="row widget_header">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			</div>
		</div>
		<div class="form-row my-3 widget_header py-2 align-items-center">
			<div class="col-md-2 float-left">
				<select class="chzn-select form-control" id="usersFilter" >
					<option value="">{\App\Language::translate('LBL_ALL', $QUALIFIED_MODULE)}</option>
					<option value="other" name="other" value="">{\App\Language::translate('LBL_OTHER', $QUALIFIED_MODULE)}</option>
					{foreach item=USERNAME key=USER from=$USERSLIST}
						<option value="{$USER}" name="{$USERNAME}" {if $USERNAME eq $SELECTED_USER} selected {/if}>{$USERNAME}</option>
					{/foreach}
				</select>
			</div>
			<div class="col-md-10 float-right">
				{include file=\App\Layout::getTemplatePath('ListViewActions.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="listViewContentDiv" id="listViewContents">
		{/strip}
