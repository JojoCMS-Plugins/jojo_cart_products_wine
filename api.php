<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2008 Harvey Kane <code@ragepank.com>
 * Copyright 2008 Michael Holt <code@gardyneholt.co.nz>
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Harvey Kane <code@ragepank.com>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */

/* Define the class for the cart */
if (!defined('Jojo_Cart_Class')) {
    define('Jojo_Cart_Class', Jojo::getOption('jojo_cart_class', 'jojo_plugin_jojo_cart'));
}

$_provides['pluginClasses'] = array(
        'jojo_plugin_jojo_cart_products_wine' => 'Wines - product Listing and View',
        );

/* add a new field type for admin section */
$_provides['fieldTypes'] = array(
        'userpricing' => 'Products - Per User Pricing',
        );


/* Register URI handlers */
Jojo::registerURI(null, 'jojo_plugin_jojo_cart_products_wine', 'isUrl');


if (class_exists(Jojo_Cart_Class)) {
    call_user_func(array(Jojo_Cart_Class, 'setProductHandler'), 'jojo_plugin_jojo_cart_products_wine');
}

/* Sort cart item order filter */
Jojo::addFilter('jojo_cart_sort', 'sort_cart_items', 'jojo_cart_products_wine');

/* Buy Now Embed filter */
Jojo::addFilter('content', 'buynow', 'jojo_cart_products_wine');
Jojo::addFilter('output', 'buynow', 'jojo_cart_products_wine');

/* add an icon onto the editors for inserting Buy Now buttons */
$vars = array('code'=>array('name'=>'code','description'=>'Please enter the Product code for the button'));
$buynowbtn = array(
                'name'=>'Buy Now button',
                'format'=>'[[buynow: [code]]]',
                'description'=>'',
                'vars'=>$vars,
                'icon'=>'images/buynowicon.gif'
                );
Jojo::addContentVar($buynowbtn);

/* add an icon onto the editors for inserting Buy Now links */
$vars = array('linkcode'=>array('name'=>'linkcode','description'=>'Please enter the Product code for the link'));
$buynowlink = array(
                'name'=>'Buy Now link',
                'format'=>'[[buynowlink: [linkcode]]]',
                'description'=>'',
                'vars'=>$vars,
                'icon'=>'images/buynowlink.gif'
                );

Jojo::addContentVar($buynowlink);

/* Sitemap filter */
Jojo::addFilter('jojo_sitemap', 'sitemap', 'jojo_cart_products_wine');

/* XML Sitemap filter */
Jojo::addFilter('jojo_xml_sitemap', 'xmlsitemap', 'jojo_cart_products_wine');

/* Search Filter */
Jojo::addFilter('jojo_search', 'search', 'jojo_cart_products_wine');

/* Content Filter */
Jojo::addFilter('content', 'removesnip', 'jojo_cart_products_wine');

/* capture the button press in the admin section */
Jojo::addHook('admin_action_after_save_product', 'admin_action_after_save_product', 'jojo_cart_products_wine');
Jojo::addHook('admin_action_after_save_page', 'admin_action_after_save_page', 'jojo_cart_products_wine');
Jojo::addHook('admin_action_after_save_productcategory', 'admin_action_after_save_productcategory', 'jojo_cart_products_wine');

/* Newsletter content Filter */
if (class_exists('Jojo_Plugin_Jojo_Newsletter') && Jojo::tableExists('newsletter_product')) {
    Jojo::addFilter('jojo_newslettercontent', 'newslettercontent', 'jojo_cart_products_wine');
}

$_options[] = array(
    'id'          => 'cart_product_image_size',
    'category'    => 'Cart',
    'label'       => 'Image size',
    'description' => 'This will control the size of the image in the shopping cart. The letter dictates the shape, "s" for square, "w" for overall width, "h" for overall height. The number is the number of pixels for the give shape.',
    'type'        => 'text',
    'default'     => 's50',
    'options'     => '',
    'plugin'      => 'jojo_cart_products_wine'
);

$_options[] = array(
    'id'          => 'buy_now_image',
    'category'    => 'Cart',
    'label'       => 'Buy Now source image',
    'description' => 'This will specify which image you would like to have in place of the standard browser generated button for the buy now button. eg images/buynow.gif',
    'type'        => 'text',
    'default'     => '',
    'options'     => '',
    'plugin'      => 'jojo_cart_products_wine'
);

$_options[] = array(
    'id'          => 'productsperpage',
    'category'    => 'Products',
    'label'       => 'Products per page on index',
    'description' => 'The number of products to show on the Products index page before paginating',
    'type'        => 'integer',
    'default'     => '40',
    'options'     => '',
    'plugin'      => 'jojo_cart_products_wine'
);

$_options[] = array(
    'id'          => 'product_useshortname',
    'category'    => 'Products',
    'label'       => 'Short names',
    'description' => 'Use short names (variety vintage) instead of full name (brand variety vintage) for headings',
    'type'        => 'radio',
    'default'     => 'no',
    'options'     => 'yes,no',
    'plugin'      => 'jojo_cart_products_wine'
);

$_options[] = array(
    'id'          => 'product_next_prev',
    'category'    => 'Products',
    'label'       => 'Show Next / Previous links',
    'description' => 'Show a link to the next and previous product at the top of each product page',
    'type'        => 'radio',
    'default'     => 'yes',
    'options'     => 'yes,no',
    'plugin'      => 'jojo_cart_products_wine'
);

$_options[] = array(
    'id'          => 'article_num_sidebar_articles',
    'category'    => 'Articles',
    'label'       => 'Number of article teasers to show in the sidebar',
    'description' => 'The number of articles to be displayed as snippets in a teaser box on other pages - set to 0 to disable',
    'type'        => 'integer',
    'default'     => '3',
    'options'     => '',
    'plugin'      => 'jojo_article'
);

$_options[] = array(
    'id'          => 'article_sidebar_randomise',
    'category'    => 'Articles',
    'label'       => 'Randmomise selection of teasers out of',
    'description' => 'Pick the sidebar articles from a larger group, shuffle them, and then slice them back to the original number so that sidebar content is more dynamic  - set to 0 to disable',
    'type'        => 'integer',
    'default'     => '0',
    'options'     => '',
    'plugin'      => 'jojo_article'
);

$_options[] = array(
    'id'          => 'article_sidebar_categories',
    'category'    => 'Articles',
    'label'       => 'Article teasers by category',
    'description' => 'Generate sidebar list from all articles and also create a list from each category',
    'type'        => 'radio',
    'default'     => 'no',
    'options'     => 'yes,no',
    'plugin'      => 'jojo_article'
);

$_options[] = array(
    'id'          => 'article_sidebar_exclude_current',
    'category'    => 'Articles',
    'label'       => 'Exclude current article from list',
    'description' => 'Exclude the article from the sidebar list when on that articles page',
    'type'        => 'radio',
    'default'     => 'no',
    'options'     => 'yes,no',
    'plugin'      => 'jojo_article'
);
$_options[] = array(
    'id'          => 'product_inplacesitemap',
    'category'    => 'Products',
    'label'       => 'products sitemap location',
    'description' => 'Show artciles as a separate list on the site map, or in-place on the page list',
    'type'        => 'radio',
    'default'     => 'separate',
    'options'     => 'separate,inplace',
    'plugin'      => 'jojo_cart_products_wine'
);


$_options[] = array(
    'id'          => 'product_show_NA_products',
    'category'    => 'Products',
    'label'       => 'Show unavailable products',
    'description' => 'Display unavailable (price NA) products in index (still not shown in cart)',
    'type'        => 'radio',
    'default'     => 'no',
    'options'     => 'yes,no',
    'plugin'      => 'jojo_cart_products_wine'
);

$_options[] = array(
    'id'          => 'article_meta_description',
    'category'    => 'Articles',
    'label'       => 'Dynamic article meta description',
    'description' => 'A dynamically built meta description template to use for articles, which will assist with SEO. Variables to use are [title], [author], [site], [body].',
    'type'        => 'textarea',
    'default'     => '[title], an article on [site]. [body]...',
    'options'     => '',
    'plugin'      => 'jojo_article'
);