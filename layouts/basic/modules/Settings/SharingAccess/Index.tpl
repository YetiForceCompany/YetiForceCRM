{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="" id="sharingAccessContainer">
		<div class="contents">
			<form name="EditSharingAccess" action="index.php" method="post" class="form-horizontal" id="js-edit-sharing-access" data-js="submit">
				<input type="hidden" name="module" value="SharingAccess" />
				<input type="hidden" name="action" value="SaveAjax" />
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" class="dependentModules" value='{\App\Purifier::encodeHtml(\App\Json::encode($DEPENDENT_MODULES))}' />

				<div class="o-breadcrumb widget_header row align-items-center">
					<div class="col-md-8">
						{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
					</div>
					<div class="col-md-4">
						<button class="btn btn-success float-right d-none" type="submit" name="saveButton"><strong>{\App\Language::translate('LBL_APPLY_NEW_SHARING_RULES', $QUALIFIED_MODULE)}</strong></button>
					</div>
				</div>
				<table class="table tableRWD table-bordered table-sm sharingAccessDetails mt-2">
					<colgroup>
						<col width="20%">
						<col width="15%">
						<col width="15%">
						<col width="20%">
						<col width="10%">
						<col width="20%">
					</colgroup>
					<thead>
						<tr class="blockHeader">
							<th>
								{\App\Language::translate('LBL_MODULE', $QUALIFIED_MODULE)}
							</th>

							{foreach from=$ALL_ACTIONS key=ACTION_ID item=ACTION_MODEL}
								<th>
									{\App\Language::translate($ACTION_MODEL->getName(), $QUALIFIED_MODULE)}
								</th>
							{/foreach}
							<th nowrap="nowrap">{\App\Language::translate('LBL_ADVANCED_SHARING_RULES', $QUALIFIED_MODULE)}</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$ALL_MODULES key=TABID item=MODULE_MODEL}
							<tr data-module-name="{$MODULE_MODEL->get('name')}">
								<td>
									{if $MODULE_MODEL->getName() == 'Accounts'}
										{\App\Language::translate($MODULE_MODEL->get('label'), $QUALIFIED_MODULE)}
									{else}
										{\App\Language::translate($MODULE_MODEL->get('label'), $MODULE_MODEL->getName())}
									{/if}
								</td>
								{foreach from=$ALL_ACTIONS key=ACTION_ID item=ACTION_MODEL}
									<td class="">
										{if $ACTION_MODEL->isModuleEnabled($MODULE_MODEL)}
											<div><input type="radio" name="permissions[{$TABID}]" data-action-state="{$ACTION_MODEL->getName()}" title="{\App\Language::translate($ACTION_MODEL->getName(), $QUALIFIED_MODULE)}" value="{$ACTION_ID}" {if $MODULE_MODEL->getPermissionValue() eq $ACTION_ID}checked="true" {/if}></div>
										{/if}
									</td>
								{/foreach}
								<td class="triggerCustomSharingAccess">
									<div class="row">
										<div class="col-md-3">&nbsp;</div>
										<div class="col-md-6">
											<button type="button" class="btn btn-sm btn-light vtButton arrowDown row" data-handlerfor="fields" data-togglehandler="{$TABID}-rules"><img src="{\App\Layout::getImagePath('Arrow-down.png')}" alt=""></img></button>
											<button type="button" class="btn btn-sm btn-light vtButton arrowUp row d-none" data-handlerfor="fields" data-togglehandler="{$TABID}-rules"><img src="{\App\Layout::getImagePath('Arrow-up.png')}" alt=""></img></button>
										</div>
									</div>
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
				<div>
					<div class="float-right">
						<button class="btn btn-success d-none" type="submit" name="saveButton"><strong>{\App\Language::translate('LBL_APPLY_NEW_SHARING_RULES', $QUALIFIED_MODULE)}</strong></button>
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}
