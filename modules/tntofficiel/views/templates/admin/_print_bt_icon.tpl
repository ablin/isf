{*
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 * 
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
<span class="btn-group-action">
    <span class="btn-group">
    {if $objTNTOrderModel}
        {if $objTNTOrderModel->bt_filename}
            <a class="btn btn-default _blank"
               href="{$link->getAdminLink('AdminTNTOfficiel')|escape:'html':'UTF-8'}&amp;action=getBT&amp;id_order={$objTNTOrderModel->id_order|intval}"
               rel="tooltip" title="{l s='BT' mod='tntofficiel'}">
                <i class="icon-AdminTNTOfficiel"></i>
            </a>
        {else}
            <span class="btn btn-default disabled">
                <i class="icon-AdminTNTOfficiel"></i>
            </span>
        {/if}
        <a class="btn btn-default _blank"
           href="{$link->getAdminLink('AdminTNTOfficiel')|escape:'html':'UTF-8'}&amp;action=getManifest&amp;id_order={$objTNTOrderModel->id_order|intval}"
           rel="tooltip" title="{l s='Manifest' mod='tntofficiel'}">
            <i class="icon-file-text"></i>
        </a>
    {/if}
    </span>
</span>
