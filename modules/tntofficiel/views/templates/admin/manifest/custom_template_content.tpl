{*
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 * 
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
<head>
    <link rel="stylesheet" type="text/css" href="{$smarty.const._PS_MODULE_DIR_|escape:'htmlall':'UTF-8'}tntofficiel/views/css/{TNTOfficiel::MODULE_RELEASE|escape:'html':'UTF-8'}/manifest.css" />
</head>

<div class="body">
    <div class="merchant-table-container">
        <table class="merchant-table bold">
            <tr>
                <td style="width: 15%;">Compte exp.</td>
                <td style="width: 2%;">:</td>
                <td class="uppercase" style="width: 23%;">{$manifestData['carrierAccount']|escape:'htmlall':'UTF-8'}</td>
                <td style="width: 60%;">&nbsp;</td>
            </tr>
            <tr>
                <td>Nom exp.</td>
                <td>:</td>
                <td class="uppercase">{$manifestData['address']['name']|escape:'htmlall':'UTF-8'}</td>
            </tr>
            <tr><td>& Adresse</td>
                <td>:</td>
                <td class="uppercase">
                    {$manifestData['address']['address1']|escape:'htmlall':'UTF-8'}<br>
                    {if ($manifestData['address']['address2'] != null)}
                        {$manifestData['address']['address2']|escape:'htmlall':'UTF-8'}<br>
                    {/if}
                    {$manifestData['address']['city']|escape:'htmlall':'UTF-8'}{if ($manifestData['address']['city'] != null)},&nbsp;{/if}
                    {$manifestData['address']['postcode']|escape:'htmlall':'UTF-8'}{if ($manifestData['address']['postcode'] != null)},&nbsp;{/if}
                    {$manifestData['address']['country']|escape:'htmlall':'UTF-8'}</td>
            </tr>
        </table>
    </div>
    <br />
    <hr />
    <div class="parcels-table-container">
        <table class="parcels-table">
                <tr><td style="width: 20%">Num BT</td><td>Poids (Kgs)</td><td>Destinataire</td><td>Code Postal</td><td>Ville</td><td>Service</td></tr>
                {foreach from=$manifestData['parcelsData'] item=parcel}
                    <tr><td>&nbsp;</td></tr>
                    <tr class="parcels">
                        <td style="width: 20%;">{$parcel['parcel_number']|escape:'htmlall':'UTF-8'}</td>
                        <td>{$parcel['weight']|escape:'htmlall':'UTF-8'}</td>
                        <td>{$parcel['address']->firstname|escape:'htmlall':'UTF-8'} {$parcel['address']->lastname|escape:'htmlall':'UTF-8'}</td>
                        <td>{$parcel['address']->postcode|escape:'htmlall':'UTF-8'}</td>
                        <td>{$parcel['address']->city|escape:'htmlall':'UTF-8'}</td>
                        <td>{$parcel['carrier_label']|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                {/foreach}
        </table>
    </div>

    <hr>
    <div class="total-table-container">
        <table class="total">
            <tr style="margin: 0; padding: 0;"><td style="margin: 0; padding: 0; width: 20%;">Compte&nbsp;{$manifestData['carrierAccount']|escape:'htmlall':'UTF-8'}</td><td style="margin: 0; padding: 0;">Total</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr style="margin: 0; padding: 0;"><td style="margin: 0; padding: 0; width: 20%;">{$manifestData['totalWeight']|escape:'htmlall':'UTF-8'}</td><td style="margin: 0; padding: 0;">{$manifestData['parcelsNumber']|escape:'htmlall':'UTF-8'}</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>&nbsp;</td></tr>
            <hr />
            <tr><td>&nbsp;</td></tr>
            <hr />
        </table>
    </div>

    <div class="signature">
        <div>
            <table>
                <tr><td>&nbsp;</td><td></td><td></td><td></td></tr>
                <tr><td>&nbsp;</td><td></td><td></td><td></td></tr>
                <tr><td>&nbsp;</td><td></td><td></td><td></td></tr>
                <tr><td>&nbsp;</td><td></td><td></td><td></td></tr>
                <tr><td>Signature de l'expéditeur</td><td>_____________________</td><td>Date ___/___/______</td><td></td></tr>
            </table>
        </div>
        <br /><br /><br />
        <br /><br />
        <div>
            <table>
                <tr>
                    <td>Reçu par TNT</td><td>_____________________</td><td>Date ___/___/______</td><td>Heure ____:____</td>
                </tr>
            </table>
        </div>
    </div>
</div>
