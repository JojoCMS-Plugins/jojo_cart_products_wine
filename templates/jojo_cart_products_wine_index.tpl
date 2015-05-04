{if $pg_body && $pagenum==1}
    {$pg_body}
{/if}
<div class="wines">
    {foreach from=$wines item=wine}<div class="wine">
    <h3 class="clear"><a href="{$wine.url}" title="{$wine.title}">{$wine.title}</a></h3>
        {if $wine.pr_image}<a href="{$wine.url}" title="{$wine.title}"><img src="images/{if $wine.snippet=='full'}{$wine.mainimage}{else}{$wine.thumbnail}{/if}/{$wine.image}" class="index-thumb" alt="{$wine.title}" /></a>{/if}
            {if $wine.snippet=='full'}{$wine.pr_body}{else}<p>{$wine.bodyplain|truncate:$wine.snippet} <a href="{$wine.url}" title="{$wine.title}" class="more">{$wine.readmore}</a></p>{/if}
    {if $wine.pr_price > 0}[[buynow: {$wine.pr_code}]] Bottle Price: {$currencysymbol}{$wine.pr_price}
    {/if}
    {if $wine.pr_caseprice > 0}[[buynow: {$wine.pr_code}_case]] Case Price ({$wine.pr_casesize} bottles): {$currencysymbol}{$wine.pr_caseprice}
    {/if}
     {if $wine.pr_na_message}<p><strong>{$wine.pr_na_message}</strong></p>{/if}
     </div>
    {/foreach}
</div>
<div class="product-pagination">
{$pagination}
</div>
