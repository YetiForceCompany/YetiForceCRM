{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-body tpl-ChangeRelationData">
		<form class="form-horizontal" name="massMerge" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE_NAME}"/>
			<input type="hidden" name="action" value="ChangeRelationData"/>
			<input type="hidden" name="record" value="{$RECORD}"/>
			<input type="hidden" name="fromRecord" value="{$FROM_RECORD}"/>
			<input type="hidden" name="relationId" value="{$RELATION_ID}"/>
			{foreach item=FIELD_MODEL from=$FIELDS}
				<div class="form-group row">
					<label class="u-text-small-bold col-md-3 col-form-label text-right">
						{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
					</label>
					<div class="fieldValue col-md-9">
						{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
					</div>
				</div>
			{/foreach}
		</form>
	</div>
{/strip}
