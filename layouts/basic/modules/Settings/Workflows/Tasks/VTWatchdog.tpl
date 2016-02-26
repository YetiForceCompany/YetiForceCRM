{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="well" id="VtVTEmailTemplateTaskContainer">
		<div class="">
			<div class="row padding-bottom1per">
				<span class="col-md-4">{vtranslate('EmailTempleteList', $QUALIFIED_MODULE)}</span>
				<div class="col-md-4">
					<select class="chzn-select form-control" name="type" data-validation-engine='validate[required]'>
						{foreach from=[1,2,3,4] key=KEY item=ITEM}
							<option {if $TASK_OBJECT->type eq $key}selected{/if} value="{$ITEM}">{$ITEM}</option>
						{/foreach}	
					</select>
				</div>
			</div>
			<div class="row padding-bottom1per">
				<span class="col-md-4">{vtranslate('LBL_MESSAGE', $QUALIFIED_MODULE)}</span>
				<div class="col-md-4">
					<input class="form-control" name="message" value="{$TASK_OBJECT->massage}">
				</div>
			</div>
		</div>
	</div>	
{/strip}	
