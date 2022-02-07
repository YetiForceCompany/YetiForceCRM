{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Notifications-Configuration widget_header row align-items-center">
		<div class="col-12 col-sm-12 col-md-9">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
		</div>
		<div class="col-12 col-sm-6 offset-sm-6 col-md-3 offset-md-0">
			<select class="select2 form-control" name="supportedModule" id="supportedModule">
				{foreach item=SUPPORTED_MODULE key=TAB_ID from=$SUPPORTED_MODULES}
					<option value="{$TAB_ID}" {if $TAB_ID eq $SELECTED_MODULE} selected {/if}>{\App\Language::translate($SUPPORTED_MODULE->getName(), $SUPPORTED_MODULE->getName())}</option>
				{/foreach}
			</select>
		</div>
	</div>
	{if App\Config::module('ModTracker', 'WATCHDOG')}
		<div class="row mt-2">
			<div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3">
				<div class="table-responsive padding10">
					<table class="table table-bordered table-sm dataTable" data-mode="base">
						<thead>
							<tr>
								<th>
									<strong>{\App\Language::translate('LBL_MEMBERS',$QUALIFIED_MODULE)}</strong>
									<button class="btn btn-sm btn-success float-right addUser mr-2" type="button" data-editurl=""><span class="fas fa-plus"></span> {\App\Language::translate('LBL_ADD',$QUALIFIED_MODULE)}</button>
								</th>
							</tr>
						</thead>
						<tbody class="dropContainer groupMembersColors">
							{foreach from=$WATCHDOG_MODULE->getWatchingMembers(true) key=KEY item=MEMBER}
								<tr class="{$MEMBER.type}" data-value="{$MEMBER.member}" data-lock="{$MEMBER.lock}">
									<td><strong>{$MEMBER.name}</strong>
										<span class="wrapperTrash">
											<span title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}" class="fas fa-trash-alt fa-lg float-right marginIcon marginTop2 delete u-cursor-pointer" data-mode="addOrRemoveMembers"></span>
										</span>
										<span class="wrapperLock">
											<span title="{\App\Language::translate('LBL_LOCK', $QUALIFIED_MODULE)}" class="fas {if $MEMBER.lock}fa-lock{else}fa-unlock-alt{/if} fa-lg float-right marginTB3 lock u-cursor-pointer" data-mode="lock"></span>
										</span>
										<span class="wrapperExceptions" title="{\App\Language::translate('LBL_EXCEPTIONS', $QUALIFIED_MODULE)}" data-mode="exceptions">
											{if $MEMBER.type neq 'Users'}
												<span class="fas fa-exclamation-circle float-right marginIcon marginTop2 exceptions u-cursor-pointer"></span>
											{/if}
										</span>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
			<div class="col-12 col-sm-2 col-md-2 groupMembersColors">
				<br />
				<ul class="nav nav-pills flex-column">
					<li class="Users padding5per textAlignCenter"><strong>{\App\Language::translate('LBL_USERS', $QUALIFIED_MODULE)}</strong></li>
					<li class="Groups padding5per textAlignCenter"><strong>{\App\Language::translate('LBL_GROUPS', $QUALIFIED_MODULE)}</strong></li>
					<li class="Roles padding5per textAlignCenter"><strong>{\App\Language::translate('LBL_ROLES', $QUALIFIED_MODULE)}</strong></li>
					<li class="RoleAndSubordinates padding5per textAlignCenter"><strong>{\App\Language::translate('RoleAndSubordinates', $QUALIFIED_MODULE)}</strong></li>
				</ul>
			</div>
		</div>
	{else}
		<div class="alert alert-danger fade in wa">
			{\App\Language::translate('LBL_NOTICE_CONFIG_WARNING', $QUALIFIED_MODULE)}
		</div>
	{/if}
{/strip}
