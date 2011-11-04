<div class="buynow">
<form style="display:inline;" action="{$SITEURL}/cart/add/{$prodcode}/" class="buynowbutton">
{if $OPTIONS.buy_now_image}<input type="image" src="{$OPTIONS.buy_now_image}"/>
{else}<input type="submit" value="Add to cart" id="add[{$prodcode}]" name="add[{$prodcode}]"  class="button" onclick="document.activeElement=this"/>{/if}
</form>
</div>