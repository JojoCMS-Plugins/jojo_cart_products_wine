{if $pg_body && $pagenum==1}
    {$pg_body}
{/if}
{foreach from=$wines item=wine}

<h3 class="clear"><a href="{$wine.url}" title="{$wine.pr_name} {$wine.pr_variety} {$wine.pr_vintage}">{$wine.pr_name} {$wine.pr_variety} {$wine.pr_vintage}</a></h3>
<div>
    {if $wine.pr_image}<a href="{$wine.url}" title="{$wine.pr_name} {$wine.pr_variety} {$wine.pr_vintage}"><img src="images/v6000/products/{$wine.pr_image}" class="right-image" alt="{$wine.pr_name} {$wine.pr_variety}" /></a>{/if}
    <p>{$wine.bodyplain|truncate:350}</p>
    <div align="right" style="padding:1px;"><a href="{$wine.url}" title="View full article" rel="nofollow">more...</a></div>
{if $wine.pr_price > 0}
    <p>[[buynow: {$wine.pr_code}]] Bottle Price: {$currencysymbol}{$wine.pr_price}</p>
{/if}
{if $wine.pr_caseprice > 0}
    <p>[[buynow: {$wine.pr_code}_case]] Case Price ({$wine.pr_casesize} bottles): {$currencysymbol}{$wine.pr_caseprice}</p>
{/if}
 {if $wine.pr_na_message}<p><strong>{$wine.pr_na_message}</strong></p>{/if}

 {if $OPTIONS.article_show_date=='yes'}<div class="article-date">Added: {$wine.datefriendly}</div>{/if}
    <div class="clear"></div>
</div>

{/foreach}

<div class="product-pagination">
{$pagination}
</div>
