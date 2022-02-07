{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div id="VTEmailReportContainer tpl-Settings-Workflows-Tasks-VTEmailReport">
		<div class="">
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('EmailTempleteList', $QUALIFIED_MODULE)}</span>
				<div class="col-md-4">
					<select class="select2 form-control" name="template" data-validation-engine="validate[required]"
						data-select="allowClear"
						data-placeholder="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}">
						<optgroup class="p-0">
							<option value="">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
						</optgroup>
						{foreach from=App\Mail::getTemplateList($SOURCE_MODULE,'PLL_RECORD') key=key item=item}
							<option {if isset($TASK_OBJECT->template) && $TASK_OBJECT->template eq $item['id']}selected="" {/if}
								value="{$item['id']}">{\App\Language::translate($item['name'], $QUALIFIED_MODULE)}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="row pb-2">
				<span class="col-md-4"></span>
				<span class="col-md-4">
					<label>
						<input type="checkbox" class="align-text-bottom" value="true" name="emailoptout" {if !isset($TASK_OBJECT->emailoptout) || $TASK_OBJECT->emailoptout}checked{/if}>
						&nbsp;{\App\Language::translate('LBL_CHECK_EMAIL_OPTOUT', $QUALIFIED_MODULE)}
					</label>
				</span>
			</div>
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">
					{\App\Language::translate('LBL_SELECT_USERS', $QUALIFIED_MODULE)}
					<span class="js-popover-tooltip ml-1 delay0" data-js="popover" data-placement="top"
						data-content="{\App\Language::translate('LBL_SELECT_USERS_INFO',$QUALIFIED_MODULE)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</span>
				<div class="col-md-4">
					<select class="select2 form-control" name="members[]" multiple="multiple">
						{foreach from=\App\PrivilegeUtil::getMembers() key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
							<optgroup label="{\App\Language::translate($GROUP_LABEL)}">
								{foreach from=$ALL_GROUP_MEMBERS key=MEMBER_ID item=MEMBER}
									<option class="{$MEMBER['type']}" value="{$MEMBER_ID}" {if isset($TASK_OBJECT->members) && is_array($TASK_OBJECT->members) && in_array($MEMBER_ID, $TASK_OBJECT->members)}selected="true" {/if}>
										{\App\Language::translate($MEMBER['name'])}
									</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
	</div>
{/strip}
