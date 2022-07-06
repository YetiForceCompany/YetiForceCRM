{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Users-Groups -->
	<form name="VisitPurpose" class="form-horizontal validateForm">
		<input type="hidden" name="module" value="{$MODULE_NAME}" />
		<input type="hidden" name="action" value="Groups" />
		<div class="modal-body">
			<div class="form-group row">
				<label class=" col-lg-2 font-weight-bold textAlignRight align-self-center">{\App\Language::translate('LBL_GROUP', $MODULE_NAME)}:</label>
				<div class="col-lg-8">
					<select class="form-control select2" id="groupID" name="groupID">
						{foreach item=GROUP_ID from=$GROUPS}
							<option value="{$GROUP_ID}" {if $GROUP_ID === $GROUP_SELECTED} selected="selected" {/if}>
								{\App\Language::translate(\App\Fields\Owner::getGroupName($GROUP_ID))}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="card bg-light">
				<div class="card-body">
					<table id="dataTableExamplePermissions" class="table table-sm table-striped display text-center mt-2 js-data-table table-bordered" data-url="index.php?module=Groups&action=Groups&mode=getData">
						<thead>
							<tr>
								<th data-name="member" data-orderable="true">{\App\Language::translate('LBL_MEMBER', $MODULE_NAME)}</th>
								<th data-name="member" data-orderable="false" style="width:1%">
									<button class="btn btn-success btn-sm js-member-add" type="button" title="{\App\Language::translate('LBL_GROUP_MEMBERS_ADD_VIEW', $MODULE_NAME)}" data-url="index.php?module={$MODULE_NAME}&view=MemberList">
										<span class="fas fa-plus"></span>
									</button>
								</th>
							</tr>
						</thead>
					</table>
				</div>
			</div>
		</div>
	</form>
	<!-- /tpl-Users-Groups -->
{/strip}
