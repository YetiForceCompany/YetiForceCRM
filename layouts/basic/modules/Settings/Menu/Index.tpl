{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="tpl-Settings-Menu-Index menuConfigContainer">
	<div class="o-breadcrumb widget_header row">
		<div class="col-md-7">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
		</div>
		<div class="col-md-5 row mt-2">
			<div class="col-6 px-0">
				<button class="btn btn-outline-secondary addMenu float-right">
					<strong>
						<span class="fa fa-plus u-mr-5px"></span>
						{\App\Language::translate('LBL_ADD_MENU', $QUALIFIED_MODULE)}
					</strong>
				</button>
			</div>
			<div class="col-6 float-right">
				<select class="select2 form-control" name="roleMenu">
					<option value="0" {if $ROLEID eq 0} selected="" {/if}>{\App\Language::translate('LBL_DEFAULT_MENU', $QUALIFIED_MODULE)}</option>
					<optgroup label="{\App\Language::translate('LBL_ROLES', $QUALIFIED_MODULE)}">
						{foreach item=ROLE key=KEY from=Settings_Roles_Record_Model::getAll()}
							<option value="{$KEY}" {if $ROLEID === $KEY} selected="" {/if}>
								{\App\Language::translate($ROLE->getName())}
							</option>
						{/foreach}
					</optgroup>
					<optgroup label="{\App\Language::translate('WebserviceApps', 'Settings:WebserviceApps')}">
						{foreach item=SERVER key=KEY from=Settings_WebserviceApps_Module_Model::getServers()}
							<option value="{$KEY}" {if $ROLEID eq $KEY} selected="" {/if}>
								{App\Purifier::encodeHtml($SERVER['name'])}
							</option>
						{/foreach}
					</optgroup>
				</select>
			</div>
		</div>
	</div>
	<input type="hidden" class="js-source" value="{$SOURCE}" data-js="val">
	<hr>
	{if !$DATA}
		<button class="btn btn-success copyMenu">
			<strong>{\App\Language::translate('LBL_COPY_MENU', $QUALIFIED_MODULE)}</strong></button>
	{/if}
	<div class="treeMenuContainer">
		<input type="hidden" id="treeLastID" value="{$LASTID}" />
		<input type="hidden" name="tree" id="treeValues" value='{\App\Purifier::encodeHtml(\App\Json::encode($DATA))}' />
		<div id="treeContent"></div>
	</div>
	<div class="modal fade copyMenuModal">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<form>
					<div class="modal-header">
						<h5 class="modal-title">{\App\Language::translate('LBL_COPY_MENU', $QUALIFIED_MODULE)}</h5>
						<button type="button" class="close" data-dismiss="modal"
							aria-label="{\App\Language::translate('LBL_CLOSE')}">
							<span aria-hidden="true" title="{\App\Language::translate('LBL_CLOSE')}">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<select id="roleList" class="form-control" name="roles"
							data-validation-engine="validate[required]">
							<option value="0">
								{\App\Language::translate('LBL_DEFAULT_MENU', $QUALIFIED_MODULE)}
							</option>
							{foreach item=ROLE key=KEY from=$ROLES_CONTAIN_MENU}
								<option value="{$ROLE['roleId']}">
									{\App\Language::translate($ROLE['roleName'])}
								</option>
							{/foreach}
						</select>
					</div>
					<div class="modal-footer">
						<button type="submit"
							class="btn btn-success saveButton">
							{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
						</button>
						<button type="button" class="btn btn-warning dismiss"
							data-dismiss="modal">
							{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<div class="modal deleteAlert fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{\App\Language::translate('LBL_REMOVE_TITLE', $QUALIFIED_MODULE)}</h5>
				<button type="button" class="close" data-dismiss="modal"
					aria-label="{\App\Language::translate('LBL_CLOSE')}">
					<span aria-hidden="true" title="{\App\Language::translate('LBL_CLOSE')}">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p>{\App\Language::translate('LBL_REMOVE_DESC', $QUALIFIED_MODULE)}</p>
			</div>
			<div class="modal-footer">
				<div class="float-right">
					<button class="btn btn-warning cancelLink" type="reset" data-dismiss="modal">
						{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
					</button>
				</div>
				<div class="float-right">
					<button class="btn btn-danger" data-dismiss="modal">
						{\App\Language::translate('LBL_REMOVE', $QUALIFIED_MODULE)}
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
