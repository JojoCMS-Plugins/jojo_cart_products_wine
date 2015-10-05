{if $error}<div class="error">{$error}</div>{/if}

{if $wine}

    {jojoHook hook="productBeforeBody"}
        {if $wine.pr_image}<a href="images/default/{$wine.image}" rel="shadowbox" title="{$wine.title}"><img src="images/v15000/{$wine.image}" alt="{$wine.title}" class="right-image" /></a>{/if}
        {$wine.pr_body}
    {jojoHook hook="productAfterBody"}
    {if $wine.pr_price > 0}
        <p>[[buynowlink: {$wine.pr_code}]] Bottle Price: {$currencysymbol}{$wine.pr_price}</p>
    {/if}
    {if $wine.pr_caseprice > 0}
        <p>[[buynowlink: {$wine.pr_code}_case]] Case Price ({$wine.pr_casesize} bottles): {$currencysymbol}{$wine.pr_caseprice}</p>
    {/if}
 {if $wine.pr_na_message}<p><strong>{$wine.pr_na_message}</strong></p>{/if}

    {if $related}
        <h4>Related Wines</h4>
        <ul>
        {foreach from=$related item=r}
        <li>{if $r.url}<a href="{$r.url}">{/if}{$r.title}{if $r.url}</a>{/if}</li>
        {/foreach}
        </ul>
    {/if}

    <p class="links">&lt;&lt; <a href="{$wine.pageurl}" title="{$wine.pagetitle}">{$wine.pagetitle}</a>&nbsp; {if $prevproduct}&lt; <a href="{$prevproduct.url}" title="Previous">{$prevproduct.title}</a>{/if}{if $nextproduct} | <a href="{$nextproduct.url}" title="Next">{$nextproduct.title}</a> &gt;{/if}</p>
