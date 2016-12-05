{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="row">
		{assign var=FIELD_MODEL value=$RECORD_MODEL->getFieldInstanceByName($FIELD_NAME)}
		{assign var=SOURCE_MODULE value=$FIELD_MODEL->getModuleName()}
		{if $FIELD_NAME eq 'value'}
			<div class="col-sm-11 paddingTop20">
				<div class="form-group col-sm-5 paddingLefttZero">
					<label class="col-sm-4 control-label">
						{vtranslate($LABEL, $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-8 controls">
						<div class="input-group fieldContainer" data-name="{$FIELD_MODEL->getName()}" data-dbname="{$FIELD_NAME}">
							{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(), $SOURCE_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$SOURCE_MODULE}
							<div class="input-group-btn" id="basic-addon">
								<button type="button" class="btn btn-success saveValue" id="saveValue" title="{vtranslate('BTN_ADD', $QUALIFIED_MODULE)}">
									<span class="glyphicon glyphicon-plus"></span>
								</button>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-4 form-group">
					<span class="col-sm-12 col-xs-11 btn btn-info ">
						{if $RECORD_MODEL->get($FIELD_NAME)}
							{$FIELD_MODEL->getUITypeModel()->getDisplayValue($RECORD_MODEL->get($FIELD_NAME))}
						{else}
							&nbsp;
						{/if}
					</span>
				</div>
			</div>
		{else}
			<div class="paddingTop20 padding20">
				<div class="table-responsive col-lg-9 col-md-10 col-sm-10 col-xs-12">
					<table class="table table-bordered table-condensed dataTable" data-mode="base">
						<thead>
							<tr>
								<th>
									<strong>{vtranslate($LABEL, $QUALIFIED_MODULE)}</strong>
									<div class="col-xs-8 pull-right controls">
										<div class="input-group col-xs-12 fieldContainer" data-name="{$FIELD_MODEL->getName()}" data-dbname="{$FIELD_NAME}">
											{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getListSearchTemplateName(), $SOURCE_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$SOURCE_MODULE}
											<div class="input-group-btn" id="basic-addon">
												<button type="button" class="btn btn-success saveValue" id="saveValue" title="{vtranslate('BTN_ADD', $QUALIFIED_MODULE)}">
													<span class="glyphicon glyphicon-plus"></span>
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
										<span title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}" class="glyphicon glyphicon-trash pull-right marginIcon marginTop2 delete cursorPointer" data-mode="addOrRemoveMembers"></span>
										{if $FIELD_NAME eq 'roles'}
											<span title="{vtranslate('LBL_CHANGE_ROLE_TYPE', $QUALIFIED_MODULE)}" class="glyphicon glyphicon-transfer pull-right marginIcon marginTop2 changeRoleType cursorPointer" aria-hidden="true"></span>
										{/if}
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
				<div class="col-xs-12 col-sm-2 col-md-2 col-lg-1 groupMembersColors">
					<br/>
					<ul class="nav nav-pills nav-stacked">
						{if $FIELD_NAME eq 'roles'}
							<li class="Roles padding5per textAlignCenter"><strong>{vtranslate('LBL_ROLES', $QUALIFIED_MODULE)}</strong></li>
							<li class="RoleAndSubordinates padding5per textAlignCenter"><strong>{vtranslate('RoleAndSubordinates', $QUALIFIED_MODULE)}</strong></li>
								{else}
							<li class="Users padding5per textAlignCenter"><strong>{vtranslate('LBL_USERS', $QUALIFIED_MODULE)}</strong></li>
							<li class="Groups padding5per textAlignCenter"><strong>{vtranslate('LBL_GROUPS', $QUALIFIED_MODULE)}</strong></li>
								{/if}

					</ul>
				</div>
			</div>
		{/if}
	</div>
{/strip}
