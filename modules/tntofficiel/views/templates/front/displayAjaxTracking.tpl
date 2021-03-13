{*
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 * 
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
<html>
    <head>
        <title>{l s='shipping detail' mod='tntofficiel'}</title>
        <link type="text/css" href="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}tntofficiel/views/css/{TNTOfficiel::MODULE_RELEASE|escape:'html':'UTF-8'}/tracking.css" rel="stylesheet" />
        <link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    </head>
    <body>
        <div class="header">
            <div class="tnt-logo"></div>
            <div class="title">{l s='shipping detail' mod='tntofficiel'}</div>
            <a class="close" href="#" onclick="window.close(); window.opener.focus();return false;" title="{l s='Close' mod='tntofficiel'}"> </a>
        </div>
        {foreach from=$parcels item=parcel}
            <div class="header-track">
                <div class="track-label">{l s='tracking number' mod='tntofficiel'}</div>
                <div class="track-number">{$parcel['parcel_number']|escape:'htmlall':'UTF-8'}</div>
                <div class="button">
                    <a href="{$parcel['tracking_url']|escape:'html':'UTF-8'}" onclick="this.target='_blank';">{l s='follow my parcel' mod='tntofficiel'}&nbsp;<div class="tnt-arrow"></div></a>
                </div>
            </div>
            <div class="status-track">
                <p></p>
                {if (isset($parcel['trackingData']['allStatus']) && count($parcel['trackingData']['allStatus']))}
                    <ul>
                        {foreach from=$parcel['trackingData']['allStatus'] item=label key=id}
                            <li class="status {if ($id == $parcel['trackingData']['status'] || ($id == 5 && $parcel['trackingData']['status'] > 5))} current {/if}" style="display: inline-block">{$label|escape:'htmlall':'UTF-8'}</li>
                        {/foreach}
                    </ul>
                {/if}
                {if (isset($parcel['trackingData']['history']))}
                    <div class="history-track">
                        <ul>
                            {foreach from=$parcel['trackingData']['history'] item=info key=idx}
                                <li>
                                    <ul>
                                        <li class="index">{$idx|intval}</li>
                                        <li class="label">{$info['label']|escape:'htmlall':'UTF-8'} :</li>
                                        {if (isset($info['date']) && strlen($info['date']))}
                                            <li class="date">{$info['date']|escape:'htmlall':'UTF-8'|date_format:"%d.%m.%Y - %R"}</li>
                                        {/if}
                                        {if (isset($info['center']) && strlen($info['center']))}
                                            <li class="center"> - {$info['center']|escape:'htmlall':'UTF-8'}</li>
                                        {/if}
                                    </ul>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                    <br /><br />
                {/if}
            </div>
        {/foreach}
        <div class="footer-track">
            <div class="button">
                <a href="#" onclick="window.close(); window.opener.focus();return false;">{l s='Close' mod='tntofficiel'}</a>
            </div>
        </div>
    </body>
</html>