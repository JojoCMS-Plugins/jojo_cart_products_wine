{if $wine}<div class="product">
    <div class="row">
        <div class="col-sm-8">
            {jojoHook hook="productBeforeBody"}
            {if $wine.pr_body}{$wine.pr_body}{/if}
            {if $wine.tasting}<h3>Tasting Notes</h3>
            <p>{$wine.tasting}</p>
            {/if}{if $wine.winemaking}<h3>Winemaker's Notes</h3>
            <p>{$wine.winemaking}</p>
            {/if}{if $wine.viticulture}<h3>Viticulture Notes</h3>
            <p>{$wine.viticulture}</p>
            {/if}
            <div class="data">
                <p>{if $wine.pr_region}<span>Region: </span>{$wine.pr_region}<br />
                {/if}{if $wine.winemaker}<span>Winemaker: </span>{$wine.winemaker}<br />
                {/if}{if $wine.alcohol}<span>Alcohol: </span>{$wine.alcohol}% &nbsp;{/if}{if $wine.ph}<span>pH: </span>{$wine.ph} &nbsp;{/if}{if $wine.sugar}<span>RS: </span>{$wine.sugar} &nbsp;{/if}<br />
                {if $wine.ta}<span>TA: </span>{$wine.ta} &nbsp;{/if}{if $wine.brix}<span>Brix: </span>{$wine.brix}{/if}</p>
            </div>
            {if $wine.cellaring}<h3>Cellaring</h3>
            <p>{$wine.cellaring}</p>
            {/if}{if $wine.foodmatch}<h3>Food Match</h3>
            <p>{$wine.foodmatch}</p>
            {/if}
            {if $wine.pr_tastingnote}<p><a href="{$SITEURL}/downloads/products/{$pr_tastingnote}" title="{strip_tags($wine.title)} Tasting Note" class="btn btn-primary pdflink">Tasting Note <span class="note">(PDF)</span></a></p>
            {jojoHook hook="productAfterBody"}
            {if $wine.pr_caseprice > 0 || $wine.pr_na_message}<table class="table prices">
                {if $wine.pr_price > 0}
                <tr>
                    <td>Bottle Price: {$currencysymbol}{$wine.pr_price}</td><td>[[buynowlink: {$wine.pr_code}]]</td>
                <tr>
                {/if}{if $wine.pr_caseprice > 0}
                <tr>
                    <td>Case Price ({$wine.pr_casesize} bottles): {$currencysymbol}{$wine.pr_caseprice}</td><td>[[buynowlink: {$wine.pr_code}_case]]</td>
                <tr>
                {/if}{if $wine.pr_na_message}
                <tr><td colspan="2" class="na"><b>{$wine.pr_na_message}</b></td></tr>
                {/if}
            </table>
            {/if}{/if}{if $wine.awards}
            <h2>Awards &amp; Reviews</h2>
            {foreach from=$wine.awards item=a}
                <h5>{$a.title}{if $a.award} - {$a.award}{/if}</h5>
                {if $a.pa_rating!=0}<div class="rating" style="width:{$a.pa_rating*10}px;"><img src="images/stars-trans.png" /></div>{/if}
               <p>{$a.bodyplain}</p>
            {/foreach}
            {/if}{if $othervintages}
            <h2>Other Vintages</h2>
            <p>{foreach from=$othervintages item=o}<a href="{$o.url}" title="{$o.title} tasting note">{$o.title}</a><br />
            {/foreach}
            </p>
            {/if}
        </div>
        <div class="col-sm-4">
            <figure>
                {if $wine.pr_image}<a href="images/default/{$wine.image}" title="{strip_tags($wine.title)}" class="colorbox"><img src="{$SITEURL}/images/pad188x647/{$wine.image}" alt="" class="img-responsive{if !$ispdf} hidden-xs{/if}" /></a>{if !$ispdf}<br />
                <a href="images/default/{$wine.image}" title="{strip_tags($wine.title)} bottle image" class="btn btn-primary btn-sm">Download bottle image</a>{if $wine.pr_image2}<br />
                <a href="images/default/products/{$wine.pr_image2}" title="{strip_tags($wine.title)} label image" class="btn btn-primary btn-sm">Download label image</a>{/if}{/if}{/if}
            </figure>
        </div>
    </div>
</div>
{/if}