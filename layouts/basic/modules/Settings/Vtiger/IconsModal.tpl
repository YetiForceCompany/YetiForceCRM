{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-header">
		<h5 class="modal-title">{\App\Language::translate('LBL_SELECT_ICON', $QUALIFIED_MODULE)}</h5>
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	</div>
	<div class="modal-body col-md-12">
		<input type="hidden" id="iconType" value="-"/>
		<input type="hidden" id="iconName" value="-"/>
		<div>
			<select class="form-control" id="iconsList" name="type">
				<option value="">-</option>
				{foreach from=Settings_Vtiger_Icons_Model::getUserIcon() key=NAME item=CLASS}
					<option value="{$CLASS}" data-class="{$CLASS}" data-type="icon" title="{$NAME}">{$NAME}</option>
				{/foreach}
				{foreach from=Settings_Vtiger_Icons_Model::getAdminIcon() key=NAME item=CLASS}
					<option value="{$CLASS}" data-class="{$CLASS}" data-type="icon" title="{$NAME}">{$NAME}</option>
				{/foreach}
				{foreach from=Settings_Vtiger_Icons_Model::getAdditionalIcon() key=NAME item=CLASS}
					<option value="{$CLASS}" data-class="{$CLASS}" data-type="icon" title="{$NAME}">{$NAME}</option>
				{/foreach}
				{foreach from=Settings_Vtiger_Icons_Model::getFontAwesomeIcon() key=NAME item=CLASS}
					<option value="{$CLASS}" data-class="{$CLASS}" data-type="icon" title="{$NAME}">{$NAME}</option>
				{/foreach}
				{foreach from=Settings_Vtiger_Icons_Model::getImageIcon() key=NAME item=URL}
					<option value="{\Vtiger_Theme::getImagePath($URL)}" data-type="image"
							title="{$NAME}">{$NAME}</option>
				{/foreach}
			</select>
		</div>
		<br/>
		<div>
			<div class="row">
				<div class="col-sm-5 d-flex">
					<strong class="ml-sm-auto">{\App\Language::translate('LBL_ICON_NAME', $QUALIFIED_MODULE)}:</strong>

				</div>
				<div class="col-sm-7 d-flex">
					<div class="iconName m-sm-auto"></div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-5 d-flex">
					<strong class="ml-sm-auto my-sm-auto">{\App\Language::translate('LBL_ICON_EXAMPLE', $QUALIFIED_MODULE)}
						:</strong>
				</div>
				<div class="col-sm-7 d-flex">
					<div class="iconExample m-sm-auto u-font-size-38px"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-success" type="submit" name="saveButton" data-dismiss="modal">
			<span class="fas fa-check mr-1"></span><strong>{\App\Language::translate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</strong>
		</button>
		<button class="btn btn-danger" type="reset" data-dismiss="modal">
			<span class="fas fa-times mr-1"></span><strong>{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}</strong>
		</button>
	</div>
{/strip}
