{*
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 * 
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 *}

{* extends /<ADMIN>/themes/default/template/helpers/form/form.tpl *}
{extends file="helpers/form/form.tpl"}

{*block name="other_input"}
    {if isset($input.name) && $input.type == 'test1'}
    {/if}
{/block*}

{block name="defaultForm" prepend}
    <p class="maxwidth-layout clearfix">
        <img class="img-responsive img-thumbnail pull-left tntofficiel-logo-config"
            src="{$tntofficiel.srcTNTLogoImage|escape:'html':'UTF-8'}"
            alt="{TNTOfficiel::CARRIER_NAME|escape:'html':'UTF-8'}"
        />
    </p>
    <p class="maxwidth-layout clearfix">
        <a class="btn btn-default _blank" href="{$tntofficiel.hrefManualPDF|escape:'html':'UTF-8'}"><i class="icon-book"></i> {$tntofficiel.langManualPDF|escape:'html':'UTF-8'}</a>
        <a class="btn btn-default" href="{$tntofficiel.hrefExportLog|escape:'html':'UTF-8'}"><i class="icon-pencil"></i> {$tntofficiel.langExportLog|escape:'html':'UTF-8'}</a>
        <br /><br />
    </p>
{/block}

{block name="after"}
    <p class="clearfix">
        <br /><br />
    </p>
{/block}

{block name="script"}

    // On DOM Ready.
    $(function () {

        $('#TNTOFFICIEL_ACCOUNT_PASSWORD').val("%p#c`Q9,6GSP?U4]e]Zst");
        var boolSomethingChanged = false;
        $('#configuration_form').on('change', function () {
            boolSomethingChanged = true;
        });
        $('#configuration_form').on('submit', function (objEvent) {
            if (boolSomethingChanged) {
                //element.submit();
            } else {
                objEvent.preventDefault();
            }
        });

        $('#TNTRenewCarriers').on('click', function(objEvent){
            // Prevent form submit and further action.
            objEvent.stopImmediatePropagation();
            objEvent.preventDefault();
            var objJqXHR = TNTOfficiel_AJAX({
                "url": TNTOfficiel.link.back.module.renewCurrentCarriers,
                "method": 'POST',
                "dataType": 'json',
                "cache": false
            });

            objJqXHR
            .always(function (mxdData, strTextStatus, objJqXHR) {
                TNTOfficiel_ShowPageSpinner();
                window.location = window.location;
            });

            return false;
        });

    });

{/block}
