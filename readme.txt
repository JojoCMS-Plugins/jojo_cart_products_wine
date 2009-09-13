The jojo_cart plugin requires a product plugin - this is a very simple product button that is useful for sites with a small number of products (up to 10).

With this plugin you can add your products to a database in the Jojo backend. You then add buy now buttons anywhere appropriate on the website. Usually, you will want to manually create a 'product information' page, and include the buy now button somewhere on the page.

When the user clicks the buy now button, they will be sent to the shopping cart page, and can continue their purchase from there.

If you need a more comprehensive product plugin, we suggest using this plugin as a starting point for creating your own. Pay careful attention to the setProductHandler function, and how this function is referenced in api.php as this is critical to the integration with jojo_cart. We suggest you contact the Jojo team for more help with this, as the documentation has not yet been created for this process.