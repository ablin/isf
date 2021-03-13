{*
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 * 
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
{if isset($item)}
    {if $method_name == 'relay-point'}
        {assign var='method_code' value='xett'}
        {assign var='method_name_item' value='relay-point'}
    {else}
        {assign var='method_code' value='pex'}
        {assign var='method_name_item' value='repository'}
    {/if}

    {assign var='id' value=$item.$method_code|lower}
    {assign var='schedules' value=$item.schedule}
    <div class="shipping-method-info">
        <div class="shipping-method-info-address">
            {if $method_code == 'xett'}
                <div class="shipping-method-info-code">Code: <b>{$item.xett|escape:'htmlall':'UTF-8'}</b></div>
            {/if}
            <div class="shipping-method-info-name">{$item.name|escape:'htmlall':'UTF-8'}</div>
            {* For relay points *}
            {if $method_code == 'xett'}
                <div class="shipping-method-info-street">{$item.address|escape:'htmlall':'UTF-8'}</div>
            {else}
                {* For repositories *}
                <div class="shipping-method-info-street">{$item.address1|escape:'htmlall':'UTF-8'}</div>
                <div class="shipping-method-info-street">{$item.address2|escape:'htmlall':'UTF-8'}</div>
            {/if}
            <div class="shipping-method-info-city">{$item.postcode|escape:'htmlall':'UTF-8'} {$item.city|escape:'htmlall':'UTF-8'}</div>
        </div>
        <div class="shipping-method-info-details">
            <p class="{$method_name_item|escape:'htmlall':'UTF-8'}-time-title"><strong>{l s='Schedules' mod='tntofficiel'} :</strong></p>
            {foreach from=$schedules key=day item=schedule}
                <div class="{$method_name_item|escape:'htmlall':'UTF-8'}-time">
                    <p class="{$method_name_item|escape:'htmlall':'UTF-8'}-time-label">{l s=$day mod='tntofficiel'}:</p>
                    {*{l s='Monday' mod='tntofficiel'}
                    {l s='Tuesday' mod='tntofficiel'}
                    {l s='Wednesday' mod='tntofficiel'}
                    {l s='Thursday' mod='tntofficiel'}
                    {l s='Friday' mod='tntofficiel'}
                    {l s='Saturday' mod='tntofficiel'}
                    {l s='Sunday' mod='tntofficiel'}*}

                    <p class="{$method_name_item|escape:'htmlall':'UTF-8'}-time-value">
                        {if !empty($schedule)}
                            {assign var='i' value=0}
                            {foreach from=$schedule item=part}
                                <span>{' - '|implode:$part|escape:'htmlall':'UTF-8'}</span>
                                {if ($schedule|@count) > 1 and $i < (($schedule|@count) -1)}
                                    <span>{l s='and' mod='tntofficiel'}</span>
                                {/if}
                                {assign var='i' value=$i+1}
                            {/foreach}
                        {else}
                            <span>{l s='Closed' mod='tntofficiel'}</span>
                        {/if}
                    </p>
                </div>
            {/foreach}
        </div>
    </div>
{/if}