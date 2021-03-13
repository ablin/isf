{*
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
<div id="{$method_id|escape:'htmlall':'UTF-8'}" class="{$method_name|escape:'htmlall':'UTF-8'}">
    <div class="{$method_name|escape:'htmlall':'UTF-8'}-header">
        <h2>
            {if $method_id == 'relay_points'}
                {l s='Choose your package relay point' mod='tntofficiel'}
            {else}
                {l s='Choose your depot' mod='tntofficiel'}
            {/if}
        </h2>
    </div>

    <div class="{$method_name|escape:'htmlall':'UTF-8'}-container location-topbar">

        <form id="{$method_id|escape:'htmlall':'UTF-8'}_form" class="{$method_name|escape:'htmlall':'UTF-8'}-form" action="" method="post">
            <span>{l s='Shipping Address' mod='tntofficiel'}</span>
            <ul class="form-list">
                {* Postcode *}
                <li class="fields">
                    <div class="field">
                        <label for="tnt_postcode" class="required">{l s='Postcode' mod='tntofficiel'}</label>

                        <div class="input-box">
                            <input name="tnt_postcode" id="tnt_postcode"
                                   class=" input-text" type="text"
                                   title="{l s='Postcode' mod='tntofficiel'}"
                                   value="{$current_postcode|escape:'htmlall':'UTF-8'}"
                                   maxlength="8"
                            />
                        </div>
                    </div>

                    {* Cities list *}
                    <div class="field">
                        {* If cities *}
                        {if !empty($cities)}
                            <label for="tnt_city" class="required">{l s='City' mod='tntofficiel'}</label>
                            <div class="input-box">
                                <select name="tnt_city" id="tnt_city" class="is_required validate">
                                    <option value="">{l s='-- Please select a city --' mod='tntofficiel'}</option>
                                    {foreach from=$cities item=city}
                                        <option value="{l s=$city mod='tntofficiel'}" {if $city == $current_city} selected{/if}>{$city|escape:'htmlall':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </div>
                        {else}
                            {* if no results *}
                            <label for="tnt_city" class="required">{l s='City' mod='tntofficiel'}</label>
                            <div class="input-box">
                                <select name="tnt_city" id="tnt_city" disabled>
                                    <option>{l s='No cities available' mod='tntofficiel'}</option>
                                </select>
                            </div>
                        {/if}
                    </div>

                    {* Cities list *}
                    <div class="field">
                        <button type="submit">
                            <span><span>{l s='Change' mod='tntofficiel'}</span></span>
                        </button>
                    </div>
                </li>
            </ul>
        </form>

    </div>

    <div class="{$method_name|escape:'htmlall':'UTF-8'}-container addresses-list">

        <div id="list_scrollbar_container" class="nano">
            <div id="list_scrollbar_content" class="nano-content">
                <ul id="{$method_id|escape:'htmlall':'UTF-8'}_list" class="{$method_name|escape:'htmlall':'UTF-8'}-list">
                    {if !empty($results)}
                        {if $method_name == 'relay-points'}
                            {assign var='method_code' value='xett'}
                            {assign var='method_name_item' value='relay-point'}
                        {else}
                            {assign var='method_code' value='pex'}
                            {assign var='method_name_item' value='repository'}
                        {/if}

                        {foreach from=$results key=index item=item}
                            {assign var='id' value=$item.$method_code|lower}
                            {assign var='schedules' value=$item.schedule}
                            <li id="{$method_id|escape:'htmlall':'UTF-8'}_item_{$index|escape:'htmlall':'UTF-8'}" class="{$method_name_item|escape:'htmlall':'UTF-8'}-item">
                                <div class="{$method_name_item|escape:'htmlall':'UTF-8'}-address">
                                    {if $method_code == 'xett'}
                                        <div class="{$method_name_item|escape:'htmlall':'UTF-8'}-code">Code: <b>{$item.xett|escape:'htmlall':'UTF-8'}</b></div>
                                    {/if}
                                    <div class="{$method_name_item|escape:'htmlall':'UTF-8'}-name">{$item.name|escape:'htmlall':'UTF-8'}</div>
                                    {if $method_code == 'xett'}
                                        {* For relay points *}
                                        <div class="{$method_name_item|escape:'htmlall':'UTF-8'}-street">{$item.address|escape:'htmlall':'UTF-8'}</div>
                                    {else}
                                        {* For repositories *}
                                        <div class="{$method_name_item|escape:'htmlall':'UTF-8'}-street">{$item.address1|escape:'htmlall':'UTF-8'}</div>
                                        <div class="{$method_name_item|escape:'htmlall':'UTF-8'}-street">{$item.address2|escape:'htmlall':'UTF-8'}</div>
                                    {/if}
                                    <div class="{$method_name_item|escape:'htmlall':'UTF-8'}-city">{$item.postcode|escape:'htmlall':'UTF-8'} {$item.city|escape:'htmlall':'UTF-8'}</div>
                                    <div class="{$method_name_item|escape:'htmlall':'UTF-8'}-closing">
                                        {if $method_code == 'xett'}
                                            {* For relay points *}
                                            {if $item.closing && $item.reopening}
                                                <div>{l s='Closing from %s to %s' mod='tntofficiel' sprintf=[$item.closing|escape:'html':'UTF-8', $item.reopening|escape:'html':'UTF-8']}</div>
                                            {elseif $item.closing}
                                                <div>{l s='Closing on the %s' mod='tntofficiel' sprintf=[$item.closing|escape:'html':'UTF-8']}</div>
                                            {/if}
                                        {/if}
                                    </div>
                                </div>
                                <div class="{$method_name_item|escape:'htmlall':'UTF-8'}-details">
                                    <p class="{$method_name_item|escape:'htmlall':'UTF-8'}-time-title"><strong>{l s='Schedules' mod='tntofficiel'} :</strong>
                                    </p>
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
                                <div class="{$method_name_item|escape:'htmlall':'UTF-8'}-action">
                                    <div class="location-code">{$id|escape:'htmlall':'UTF-8'}</div>
                                    <div class="distance-container">
                                        <div><div class="location-nb">{$index|intval + 1}</div></div>
                                        {if isset($item.distance)}
                                            <div class="location-distance">{'%s Km'|sprintf:$item.distance|escape:'htmlall':'UTF-8'|ltrim:0}</div>
                                        {/if}
                                    </div>
                                    <button type="button" class="{$method_name_item|escape:'htmlall':'UTF-8'}-item-select">
                                        <span><span>{l s='Choose' mod='tntofficiel'}</span></span>
                                    </button>
                                </div>
                            </li>
                        {/foreach}
                    {elseif empty($cities)}
                        <li class="no-results">
                            {l s='No matching cities for the requested postal code.' mod='tntofficiel'}
                            <br />{l s='Check the postal code and click' mod='tntofficiel'} <b>{l s='Change' mod='tntofficiel'}</b>.
                        </li>
                    {elseif empty($current_city)}
                        <li class="no-results">{l s='Select a city from the list and click' mod='tntofficiel'} <b>{l s='Change' mod='tntofficiel'}</b>.</li>
                    {else}
                        <li class="no-results">{l s='No delivery point for this city.' mod='tntofficiel'}</li>
                    {/if}
                </ul>
            </div>
        </div>

        <div id="{$method_id|escape:'htmlall':'UTF-8'}_map" class="{$method_name|escape:'htmlall':'UTF-8'}-map"></div>

    </div>
</div>

<script type="text/javascript">

    var objTNTOfficiel_deliveryPointsBox = new TNTOfficiel_deliveryPointsBox(
        '{$carrier_code|escape:'javascript':'UTF-8'}'
    ,   '{$results|json_encode|gzdeflate|base64_encode}'
    );

</script>