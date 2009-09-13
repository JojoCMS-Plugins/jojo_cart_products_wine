<p><label for="fm_{$fd_field}">Currency</label> <input type="text" id="fm_{$fd_field}" name="fm_{$fd_field}" value="{if $value}{$value}{else}{$OPTIONS.cart_default_currency}{/if}" onchange="$(this).val($(this).val().toUpperCase()); $('span.currency').html($(this).val());" /></p>

<p>Enter the price for this customer, if this customer should not see a product then enter NA for the price.</p>

<table class="adminZebraTable">
    <tr>
        <th>Product</th>
        <th>Price per case</th>
    </tr>

{assign var=prev value=''}
{foreach from=$userproducts key=productcode item=p}
    <tr class="{cycle values="row1,row2"}">
        <td>{$p.pr_name} {$p.pr_variety} {$p.pr_vintage}</td>
        <td><input style="text-align: right" type="text" size="6" name="fm_{$fd_field}_price[{$p.productid}]" value="{if isset($prices[$p.productid])}{$prices[$p.productid]}{else}NA{/if}" /><span class="currency"> {if $value}{$value}{else}{$OPTIONS.cart_default_currency}{/if}</span></td>
    </tr>
{/foreach}
</table>
