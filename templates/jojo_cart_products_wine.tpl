{if $error}<div class="error">{$error}</div>{/if}

{if $wine}

    {jojoHook hook="productBeforeBody"}
        {if $wine.pr_image}<a href="images/default/products/{$wine.pr_image}" rel="shadowbox" title="{$wine.pr_name} {$wine.pr_variety}"><img src="images/v15000/products/{$wine.pr_image}" alt="{$wine.pr_name} {$wine.pr_variety}" class="right-image" /></a>{/if}
        {$wine.pr_body}
    {jojoHook hook="productAfterBody"}
    {if $wine.pr_price > 0}
        <p>[[buynow: {$wine.pr_code}]] Bottle Price: {$currencysymbol}{$wine.pr_price}</p>
    {/if}
    {if $wine.pr_caseprice > 0}
        <p>[[buynow: {$wine.pr_code}_case]] Case Price ({$wine.pr_casesize} bottles): {$currencysymbol}{$wine.pr_caseprice}</p>
    {/if}
 {if $wine.pr_na_message}<p><strong>{$wine.pr_na_message}</strong></p>{/if}

    {if $related}
        <h4>Related Wines</h4>
        <ul>
        {section name=r loop=$related}
        <li>{if $related[r].url}<a href="{$related[r].url}">{/if}{$related[r].title}{if $related[r].url}</a>{/if}</li>
        {/section}
        </ul>
    {/if}

    <p class="links">&lt;&lt; <a href="{if _MULTILANGUAGE}{$multilangstring}{/if}{if $pg_url}{$pg_url}/{else}{$pageid}/{$pg_title|strtolower}{/if}" title="{$pg_title} Index">{$pg_title} index</a>&nbsp; {if $prevproduct}&lt; <a href="{$prevproduct.url}" title="Previous">{$prevproduct.title}</a>{/if}{if $nextproduct} | <a href="{$nextproduct.url}" title="Next">{$nextproduct.title}</a> &gt;{/if}</p>

    {if $tags}
    <p class="tags"><strong>Tags: </strong>
    {if $itemcloud}
    {$itemcloud}
    {else}
    {section name=t loop=$tags}
    <a href="{if _MULTILANGUAGE}{$multilangstring}{/if}tags/{$tags[t]|replace:" ":"-"}/">{$tags[t]}</a>
    {/section}
    </p>
    {/if}

    {/if}

    {if $OPTIONS.product_forwardtofriend=='yes'}
    <a href="#" id="sendtofriend-link" onclick="showregion('sendtofriend'); hideregion('sendtofriend-link'); return false;">{if $friendsendbutton}<img src="images/send-to-friend.gif" alt="Send to a friend" style="border: 0;" />{else}Send to a friend{/if}</a>
    <div id="sendtofriend"  style="clear: both;display: none;">
        <h3>Send To a friend</h3>
        {if $notification}
        <div class="message">{$notification}</div>
        {/if}

        <form name="emailfriend" id="emailfriend" method="post" action="{$posturl}">
        <p>Your Name: <input type="text" name="fromname" id="fromname" size="30" value="" /> </p>
        <p>Your Email: <input type="text" name="fromaddress" id="fromaddress" size="30" value="" /> </p>
        <p>Your personal message here: <br/><textarea rows="3" cols="40" name="pmessage">
        </textarea></p>
        <p>Your Friend's Name: <input type="text" name="toname" id="toname" size="30" value="" /> </p>
        <p>Your Friend's Email: <input type="text" name="toaddress" id="toaddress" size="30" value="" /> </p>
        <input type="hidden" name="id" value="{$wine.wineid}">
        <input type="submit" name="emailsubmit" value="Send to Friend" />
        </form>
    </div>
    <script type="text/javascript">
    {literal}
    /*<![CDATA[*/
    $('#emailfriend').submit(function () {
        var errors=new Array();
        var i=0;
        if ($('#fromname').val() == '') {errors[i++]='Please provide a name';}
        if ($('#fromaddress').val() == '') {errors[i++]='Please provide an email';}
        if ($('#pmessage').val() == '') {errors[i++]='Please provide an address';}
        if ($('#toname').val() == '') {errors[i++]='Please provide an address';}
        if ($('#toaddress').val() == '') {errors[i++]='Please provide a valid post code';}
        if (i) {
            alert(errors.join("\n"));
            return false;
        }
        return true;
    });
    {/literal}
    </script>
    {/if}
{/if}