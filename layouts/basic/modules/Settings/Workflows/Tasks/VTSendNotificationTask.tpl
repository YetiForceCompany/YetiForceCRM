{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="" id="VtVTEmailTemplateTaskContainer">
		<div class="">
			<div class="row">
				<label class="col-form-label col-md-4">{\App\Language::translate('EmailTempleteList', $QUALIFIED_MODULE)}</label>
				<div class="col-md-7">
					<select class="select2 form-control" name="template" data-validation-engine='validate[required]'>
						<option value="">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
						{foreach from=App\Mail::getTemplateList($SOURCE_MODULE,'PLL_RECORD') key=key item=item}
							<option {if isset($TASK_OBJECT->template) && $TASK_OBJECT->template eq $item['id']}selected="" {/if} value="{$item['id']}">{\App\Language::translate($item['name'], $QUALIFIED_MODULE)}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
	</div>
{/strip}
