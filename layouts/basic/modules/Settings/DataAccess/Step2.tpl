{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	{include file=\App\Layout::getTemplatePath('Header.tpl', $QUALIFIED_MODULE)}
    <div class="editContainer">
        <div id="step">
			{include file=\App\Layout::getTemplatePath('Step2Content.tpl', $QUALIFIED_MODULE)}
        </div>
        <input type="hidden" name="next_step" value="Step3" />
        <div class="clearfix"></div>
    </div>
{/strip}
