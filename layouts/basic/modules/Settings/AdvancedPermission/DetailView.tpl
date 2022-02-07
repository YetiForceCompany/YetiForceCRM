{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-AdvancedPermission-DetailView">
		<div class="o-breadcrumb widget_header row">
			<div class="col-md-8">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
			<div class="col-md-4">
				<a href="{$RECORD_MODEL->getEditViewUrl()}" class="btn btn-info float-right mt-1">
					<span class="fa fa-edit u-mr-5px"></span>
					<strong>{\App\Language::translate('LBL_EDIT_RECORD', $QUALIFIED_MODULE)}</strong>
				</a>
			</div>
		</div>
		<div class="detailViewInfo" id="groupsDetailContainer">
			<table class="table table-bordered">
				<thead class="thead-light">
					<tr>
						<th colspan="2" class="medium">
							<strong>{\App\Language::translate('LBL_ADVANCED_PERMISSION', $QUALIFIED_MODULE)}</strong>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="medium w-25">
							<label class="float-right">
								{\App\Language::translate('LBL_NAME', $QUALIFIED_MODULE)}
							</label>
						</td>
						<td class="medium w-75">
							{$RECORD_MODEL->getName()}
						</td>
					</tr>
					<tr>
						<td class="medium w-25">
							<label class="float-right">
								{\App\Language::translate('LBL_ACTION', $QUALIFIED_MODULE)}
							</label>
						</td>
						<td class="medium w-75">
							{\App\Language::translate($RECORD_MODEL->getDisplayValue('action'), $QUALIFIED_MODULE)}
						</td>
					</tr>
					<tr>
						<td class="medium w-25">
							<label class="float-right">
								{\App\Language::translate('LBL_STATUS', $QUALIFIED_MODULE)}
							</label>
						</td>
						<td class="medium w-75">
							{\App\Language::translate($RECORD_MODEL->getDisplayValue('status'), $QUALIFIED_MODULE)}
						</td>
					</tr>
					<tr>
						<td class="medium w-25">
							<label class="float-right">
								{\App\Language::translate('LBL_PRIORITY', $QUALIFIED_MODULE)}
							</label>
						</td>
						<td class="medium w-75">
							{\App\Language::translate($RECORD_MODEL->getDisplayValue('priority'))}
						</td>
					</tr>
					<tr>
						<td class="medium w-25">
							<label class="float-right">
								{\App\Language::translate('LBL_MODULE', $QUALIFIED_MODULE)}
							</label>
						</td>
						<td class="medium w-75">
							{\App\Language::translate($RECORD_MODEL->getDisplayValue('tabid'), $RECORD_MODEL->getDisplayValue('tabid'))}
						</td>
					</tr>
					<tr>
						<td class="medium w-25">
							<label class="float-right">
								{\App\Language::translate('LBL_MEMBERS', $QUALIFIED_MODULE)}
							</label>
						</td>
						<td class="medium w-75">
							{$RECORD_MODEL->getDisplayValue('members')}
						</td>
					</tr>
					<tr>
						<td class="medium w-25">
							<label class="float-right">
								{\App\Language::translate('LBL_USERS', $QUALIFIED_MODULE)}
							</label>
						</td>
						<td class="medium w-75">
							{foreach from=$RECORD_MODEL->getUserByMember() item=NAME}
								<div>{$NAME}</div>
							{/foreach}
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
{/strip}
