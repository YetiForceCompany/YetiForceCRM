{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	{include file='Header.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
    <div class="editContainer">
        
		<div id="step">
			{include file='Step1Content.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
		</div>
		<input type="hidden" name="next_step" value="Step2" />
        <div class="clearfix"></div>
    </div>
{/strip}
