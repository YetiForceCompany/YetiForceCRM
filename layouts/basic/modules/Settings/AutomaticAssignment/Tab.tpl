{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-AutomaticAssingment-Tab row padding20">
		{assign var=FIELD_MODEL value=$RECORD_MODEL->getFieldInstanceByName($FIELD_NAME)}
		{if isset($FIELD_MODEL)}
			{assign var=SOURCE_MODULE value=$FIELD_MODEL->getModuleName()}
		{/if}
		<div class="alert alert-info fade show w-100">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<strong>{\App\Language::translate('LBL_NOTE', $QUALIFIED_MODULE)}
				&nbsp;&nbsp;</strong>{\App\Language::translate('LBL_'|cat:$FIELD_NAME|upper|cat:'_INFO', $QUALIFIED_MODULE)}
		</div>
		{if $FIELD_NAME eq 'value'}
			{assign var=FIELD_MODEL value=$FIELD_MODEL->set('fieldvalue',$RECORD_MODEL->get($FIELD_NAME))}
			<form id="formValue" class="col-sm-12">
				<label class="col-sm-2 col-md-2 col-lg-1 col-form-label">
					{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
				</label>
				<div class="col-sm-5 col-md-4 controls">
					<div class="input-group fieldContainer" data-name="{$FIELD_MODEL->getName()}"
						 data-dbname="{$FIELD_NAME}">
						{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $SOURCE_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$SOURCE_MODULE}
						<div class="input-group-append">
							<button type="button" class="btn btn-success saveValue" id="saveValue"
									title="{\App\Language::translate('BTN_ADD', $QUALIFIED_MODULE)}">
								<span>{\App\Language::translate('BTN_SAVE', $QUALIFIED_MODULE)}</span>
							</button>
						</div>
					</div>
				</div>
			</form>
		{elseif $FIELD_NAME eq 'assign' || $FIELD_NAME eq 'roleid'}
			{assign var=FIELD_MODEL value=$FIELD_MODEL->set('fieldvalue',$RECORD_MODEL->get($FIELD_NAME))}
			<form id="formValue" class="col-sm-12">
				<div class="form-group">
					<label class="col-12">
						{if $FIELD_NAME eq 'roleid'}
							{\App\Language::translate('LBL_DEACTIVATE_SYSTEM_MODE', $QUALIFIED_MODULE)}
						{else}
							{\App\Language::translate('LBL_SET_DEFAULT_USER', $QUALIFIED_MODULE)}
						{/if}
					</label>
					<div class="col-12">
						<div class="btn-group btn-group-toggle" data-toggle="buttons">
							<label class="btn btn-outline-primary {if $RECORD_MODEL->get($FIELD_NAME)} active{/if}">
								<input class="js-switch noField" type="radio" name="{$FIELD_NAME}"
									   id="defaultUser1" autocomplete="off" value="1"
									   {if $RECORD_MODEL->get($FIELD_NAME)}checked{/if}
								> {\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}
							</label>
							<label class="btn btn-outline-primary {if !$RECORD_MODEL->get($FIELD_NAME)} active{/if}">
								<input class="js-switch noField" type="radio" name="{$FIELD_NAME}"
									   id="defaultUser2" autocomplete="off" value="0"
									   {if !$RECORD_MODEL->get($FIELD_NAME)}checked{/if}
								> {\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}
							</label>
						</div>
					</div>
				</div>
				<div class="form-group fieldToShowHide{if !$RECORD_MODEL->get($FIELD_NAME)} d-none{/if}">
					<label class="col-12">
						{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}<span class="redColor"> *</span>
					</label>
					<div class="col-md-5 col-lg-4">
						<div class="input-group fieldContainer flex-nowrap" data-name="{$FIELD_MODEL->getName()}"
							 data-dbname="{$FIELD_NAME}">
							{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $SOURCE_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$SOURCE_MODULE}
							<div class="input-group-append">
								<button type="button" class="btn btn-success saveValue" id="saveValue"
										title="{\App\Language::translate('BTN_ADD', $QUALIFIED_MODULE)}">
									<span>{\App\Language::translate('BTN_SAVE', $QUALIFIED_MODULE)}</span>
								</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		{elseif $FIELD_NAME eq 'conditions'}
			<div class="form-horizontal">
				<div class="form-group">
					<div class="row col-md-5">
						<label class="pull-left-lg col-form-label paddingLeftMd">{\App\Language::translate('LBL_INCLUDE_USERS_RECORD_LIMIT', $QUALIFIED_MODULE)}</label>
						<div class="col-md-6">
							&nbsp;<input name="user_limit" class="switchBtn saveValue" type="checkbox"
										 {if $RECORD_MODEL->get('user_limit')}checked{/if} data-size="small"
										 data-label-width="5"
										 data-on-text="{\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}"
										 data-off-text="{\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}"
										 value="1">
						</div>
					</div>
				</div>
				<div class="fieldContainer row col-md-10 col-lg-8" data-dbname="{$FIELD_NAME}">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h5 class="no-margin">{\App\Language::translate('LBL_CHOOSE_FILTER_CONDITIONS', $QUALIFIED_MODULE)}</h5>
						</div>
						<div class="panel-body paddingBottomZero">
							<div class="filterConditionsDiv">
								<div class="row">
									<span class="col-md-12">
										{include file=\App\Layout::getTemplatePath('AdvanceFilter.tpl')}
									</span>
								</div>
							</div>
						</div>
						<div class="panel-footer clearfix">
							<div class="btn-toolbar float-right">
								<button class="btn btn-success saveValue"
										type="button">{\App\Language::translate('BTN_SAVE', $QUALIFIED_MODULE)}</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		{else}
			<div class="table-responsive col-lg-9 col-md-10 col-sm-10 col-12">
				<table class="table table-bordered table-sm dataTable" data-mode="base">
					<thead>
					<tr>
						<th>
							<strong>{\App\Language::translate($LABEL, $QUALIFIED_MODULE)}</strong>
							<div class="col-8 float-right controls">
								<div class="input-group col-12 fieldContainer" data-name="{$FIELD_MODEL->getName()}"
									 data-dbname="{$FIELD_NAME}">
									{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getListSearchTemplateName(), $SOURCE_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$SOURCE_MODULE}
									<div class="input-group-btn">
										<button type="button" class="btn btn-success saveValue" id="saveValue"
												title="{\App\Language::translate('BTN_ADD', $QUALIFIED_MODULE)}">
											<span class="fas fa-plus"></span>
										</button>
									</div>
								</div>
							</div>
						</th>
					</tr>
					</thead>
					<tbody class="dropContainer groupMembersColors">
					{foreach from=$RECORD_MODEL->getEditValue($FIELD_NAME) key=KEY item=MEMBER}
						<tr class="{$MEMBER.type}" data-value="{$MEMBER.id}" data-name="{$FIELD_NAME}">
							<td><strong>{$MEMBER.name}</strong>
								<span title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"
									  class="fas fa-trash-alt float-right marginIcon marginTop2 delete u-cursor-pointer"
									  data-mode="addOrRemoveMembers"></span>
								{if $FIELD_NAME eq 'roles'}
									<span title="{\App\Language::translate('LBL_CHANGE_ROLE_TYPE', $QUALIFIED_MODULE)}"
										  class="fas fa-exchange-alt float-right marginIcon marginTop2 changeRoleType u-cursor-pointer"></span>
								{/if}
							</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
				<div class="col-12 col-sm-2 col-md-2 col-lg-1 groupMembersColors">
					<br/>
					<ul class="nav nav-pills nav-stacked">
						{if $FIELD_NAME eq 'roles'}
							<li class="Roles padding5per textAlignCenter">
								<strong>{\App\Language::translate('LBL_ROLES', $QUALIFIED_MODULE)}</strong></li>
							<li class="RoleAndSubordinates padding5per textAlignCenter">
								<strong>{\App\Language::translate('RoleAndSubordinates', $QUALIFIED_MODULE)}</strong>
							</li>
						{else}
							<li class="Users padding5per textAlignCenter">
								<strong>{\App\Language::translate('LBL_USERS', $QUALIFIED_MODULE)}</strong></li>
							<li class="Groups padding5per textAlignCenter">
								<strong>{\App\Language::translate('LBL_GROUPS', $QUALIFIED_MODULE)}</strong></li>
						{/if}
					</ul>
				</div>
			</div>
		{/if}
	</div>
{/strip}
