<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2008 Jojo CMS
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Michael Cochrane <mikec@jojocms.org>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */

$table = 'product';
$o = 0;

$default_td[$table] = array(
        'td_name' => "product",
        'td_primarykey' => "productid",
        'td_displayfield' => "CONCAT(pr_name, ' ', if(CHAR_LENGTH(pr_designation) > 0, CONCAT(pr_designation, ' '), '') , pr_variety, ' ', pr_vintage)",
        'td_categorytable' => "productcategory",
        'td_categoryfield' => "pr_category",
        'td_rolloverfield' => "pr_desc",
        'td_filter' => "yes",
        'td_orderbyfields' => "pr_display_order, pr_name, pr_variety, pr_vintage",
        'td_topsubmit' => "yes",
        'td_deleteoption' => "yes",
        'td_menutype' => "tree",
        'td_help' => "News Products are managed from here. Depending on the exact configuration, the most recent 5 products may be shown on the homepage or sidebar, or they may be listed only on the news page. All News Products have their own \"full info\" page, which has a unique URL for the search engines. This is based on the title of the product, so please do not change the title of an product unless absolutely necessary, as the PageRank of the product may suffer. The system will comfortably take many hundreds of products, but you may want to manually delete anything that is no longer relevant, or correct.",
        'td_golivefield' => "pr_livedate",
        'td_expiryfield' => "pr_expirydate",
    );


/* Content Tab */

// Productid Field
$default_fd[$table]['productid'] = array(
        'fd_name' => "Productid",
        'fd_type' => "readonly",
        'fd_help' => "A unique ID, automatically assigned by the system",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "advanced",
    );

// Category Field
$default_fd[$table]['pr_category'] = array(
        'fd_name' => "Category",
        'fd_type' => "dblist",
        'fd_options' => "productcategory",
        'fd_default' => "0",
        'fd_size' => "20",
        'fd_help' => "If categories are used, the category the Product belongs to.",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "advanced",
    );

// Name Fields
$default_fd[$table]['pr_name'] = array(
        'fd_name' => "Brand",
        'fd_type' => "text",
        'fd_required' => "yes",
        'fd_size' => "50",
        'fd_help' => "Product Name (Brand). This will be used for the URL, headings and titles. Because the URL is based on this field, avoid changing this if possible.",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );

$default_fd[$table]['pr_region'] = array(
        'fd_name' => "Region Name",
        'fd_type' => "text",
        'fd_size' => "60",
        'fd_help' => "Variant name (e.g. Marlborough).",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );

$default_fd[$table]['pr_variety'] = array(
        'fd_name' => "Variant Name",
        'fd_type' => "text",
        'fd_size' => "60",
        'fd_help' => "Variant name (e.g. Sauvignon Blanc).",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );

$default_fd[$table]['pr_vintage'] = array(
        'fd_name' => "Vintage",
        'fd_type' => "text",
        'fd_size' => "10",
        'fd_help' => "Vintage year",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );

$default_fd[$table]['pr_designation'] = array(
        'fd_name' => "Designation",
        'fd_type' => "text",
        'fd_size' => "60",
        'fd_help' => "Any extra titles to include with the name (e.g. Single Vineyard).",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );

// Class
$default_fd[$table]['pr_class'] = array(
        'fd_name' => "Wine Type",
        'fd_type' => "radio",
        'fd_options' => "white\nred\ndessert\nrose",
        'fd_default' => "white",
        'fd_help' => "Wine grouping (optionally used for styling, ordering, or separating)",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );
// Display Order Field
$default_fd[$table]['pr_display_order'] = array(
        'fd_name' => "Display Order",
        'fd_type' => "integer",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "advanced",
    );

// Short Description Field
$default_fd[$table]['pr_desc'] = array(
        'fd_name' => "Short Description",
        'fd_type' => "textarea",
        'fd_rows' => "3",
        'fd_help' => "A one sentence description of the product. Used for rollover text on links, which enhances usability",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "advanced",
    );

// URL Field
$default_fd[$table]['pr_url'] = array(
        'fd_name' => "URL",
        'fd_type' => "internalurl",
        'fd_options' => class_exists('Jojo_Plugin_Jojo_cart_products_wine') ? Jojo_Plugin_Jojo_cart_products_wine::_getPrefix() : '', 
        'fd_size' => "20",
        'fd_help' => "A customized URL - leave blank to create a URL from the title of the product",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "standard",
    );

// Image Field
$default_fd[$table]['pr_image'] = array(
        'fd_name' => "Bottle Image",
        'fd_type' => "fileupload",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "standard",
    );

// Image Field
$default_fd[$table]['pr_image2'] = array(
        'fd_name' => "Label Image",
        'fd_type' => "fileupload",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "standard",
    );

// Tasting Note Field
$default_fd[$table]['pr_tastingnote'] = array(
        'fd_name' => "Tasting Note (pdf)",
        'fd_type' => "fileupload",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "standard",
    );

// Full Description Field
$default_fd[$table]['pr_body_code'] = array(
        'fd_name' => "Full Description",
        'fd_type' => "texteditor",
        'fd_options' => "pr_body",
        'fd_rows' => "10",
        'fd_cols' => "50",
        'fd_help' => "The full description of the product. Try to summarise the product in the first paragraph as this will be used for the snippet",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );

// Body Field
$default_fd[$table]['pr_body'] = array(
        'fd_name' => "Body",
        'fd_type' => "hidden",
        'fd_rows' => "10",
        'fd_cols' => "50",
        'fd_help' => "The body of the product. Try to summarise the product in the first paragraph as this will be used for the snippet",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "advanced",
    );

// Date Field
$default_fd[$table]['pr_date'] = array(
        'fd_name' => "Date",
        'fd_type' => "unixdate",
        'fd_default' => "now",
        'fd_help' => "Date the product was published (defaults to Today)",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "standard",
    );

// Language Field
$default_fd[$table]['pr_language'] = array(
        'fd_name' => "Language",
        'fd_type' => "hidden",
        'fd_options' => "language",
        'fd_default' => "en",
        'fd_size' => "20",
        'fd_help' => "The language section this product will appear in. Only used in multilanguage sites.",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "advanced",
    );

// Language Field
$default_fd[$table]['pr_htmllang'] = array(
        'fd_name' => "Language",
        'fd_type' => "dblist",
        'fd_options' => "language",
        'fd_default' => "en",
        'fd_size' => "20",
        'fd_help' => "The language section this product page is written in",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "advanced",
    );


$o=0;

$default_fd[$table]['tasting'] = array(
        'fd_name' => "Tasting Note",
        'fd_type' => "textarea",
        'fd_rows' => "4",
        'fd_help' => "",
        'fd_order' => $o++,
        'fd_tabname' => "Notes",
        'fd_mode' => "advanced",
    );

$default_fd[$table]['foodmatch'] = array(
        'fd_name' => "Food Matching",
        'fd_type' => "textarea",
        'fd_rows' => "4",
        'fd_help' => "",
        'fd_order' => $o++,
        'fd_tabname' => "Notes",
        'fd_mode' => "advanced",
    );

$default_fd[$table]['cellaring'] = array(
        'fd_name' => "Cellaring",
        'fd_type' => "text",
        'fd_size' => "60",
        'fd_order' => $o++,
        'fd_tabname' => "Notes",
        'fd_mode' => "standard",
    );

$default_fd[$table]['winemaker'] = array(
        'fd_name' => "Winemaker",
        'fd_type' => "text",
        'fd_size' => "60",
        'fd_order' => $o++,
        'fd_tabname' => "Notes",
        'fd_mode' => "standard",
    );

$default_fd[$table]['winemaking'] = array(
        'fd_name' => "Winemaking",
        'fd_type' => "textarea",
        'fd_rows' => "4",
        'fd_help' => "",
        'fd_order' => $o++,
        'fd_tabname' => "Notes",
        'fd_mode' => "advanced",
    );

$default_fd[$table]['alcohol'] = array(
        'fd_name' => "Alcohol",
        'fd_type' => "text",
        'fd_size' => "30",
        'fd_unit' => "%",
        'fd_help' => "",
        'fd_order' => $o++,
        'fd_tabname' => "Notes",
    );
$default_fd[$table]['ph'] = array(
        'fd_name' => "pH",
        'fd_type' => "text",
        'fd_size' => "30",
        'fd_help' => "",
        'fd_order' => $o++,
        'fd_tabname' => "Notes",
    );
$default_fd[$table]['ta'] = array(
        'fd_name' => "Total Acidity",
        'fd_type' => "text",
        'fd_size' => "30",
        'fd_unit' => "g/L",
        'fd_help' => "",
        'fd_order' => $o++,
        'fd_tabname' => "Notes",
    );
$default_fd[$table]['sugar'] = array(
        'fd_name' => "Residual Sugar",
        'fd_type' => "text",
        'fd_size' => "30",
        'fd_unit' => "g/L",
        'fd_help' => "",
        'fd_order' => $o++,
        'fd_tabname' => "Notes",
    );

$default_fd[$table]['viticulturist'] = array(
        'fd_name' => "Viticulturist",
        'fd_type' => "text",
        'fd_size' => "60",
        'fd_order' => $o++,
        'fd_tabname' => "Notes",
        'fd_mode' => "standard",
    );

$default_fd[$table]['viticulture'] = array(
        'fd_name' => "Viticulture",
        'fd_type' => "textarea",
        'fd_rows' => "4",
        'fd_help' => "",
        'fd_order' => $o++,
        'fd_tabname' => "Notes",
        'fd_mode' => "advanced",
    );

$default_fd[$table]['harvestdate'] = array(
        'fd_name' => "Harvest Dates",
        'fd_type' => "text",
        'fd_size' => "60",
        'fd_order' => $o++,
        'fd_tabname' => "Notes",
        'fd_mode' => "standard",
    );

$default_fd[$table]['brix'] = array(
        'fd_name' => "Brix",
        'fd_type' => "text",
        'fd_size' => "30",
        'fd_unit' => "%",
        'fd_help' => "Average Brix at Harvest",
        'fd_order' => $o++,
        'fd_tabname' => "Notes",
    );

/* Pricing Tab */

// Bottle Price Field
$default_fd[$table]['pr_price'] = array(
        'fd_name' => "Bottle Price",
        'fd_type' => "decimal",
        'fd_required' => "yes",
        'fd_default' => "0.00",
        'fd_size' => "10",
        'fd_order' => "1",
        'fd_tabname' => "Pricing",
    );

// Case Price Field
$default_fd[$table]['pr_caseprice'] = array(
        'fd_name' => "Case Price",
        'fd_type' => "decimal",
        'fd_required' => "yes",
        'fd_default' => "0.00",
        'fd_size' => "10",
        'fd_order' => "2",
        'fd_tabname' => "Pricing",
    );

// Case Size Field
$default_fd[$table]['pr_casesize'] = array(
        'fd_name' => "Case Size",
        'fd_type' => "integer",
        'fd_default' => "12",
        'fd_size' => "10",
        'fd_help' => "Bottles per case",
        'fd_order' => "3",
        'fd_tabname' => "Pricing",
    );

// Currency Field
$default_fd[$table]['pr_currency'] = array(
        'fd_name' => "Currency",
        'fd_type' => "text",
        'fd_size' => "50",
        'fd_order' => "4",
        'fd_tabname' => "Pricing",
    );

// Code Field
$default_fd[$table]['pr_code'] = array(
        'fd_name' => "Code",
        'fd_type' => "text",
        'fd_size' => "20",
        'fd_order' => "5",
        'fd_tabname' => "Pricing",
    );

// Fixed quantities Field
$default_fd[$table]['pr_quantity_fixed'] = array(
        'fd_name' => "Fixed quantities",
        'fd_type' => "checkbox",
        'fd_options' => "yes\nno",
        'fd_default' => "no",
        'fd_help' => "Prevents the user from changing the quantity of this product, useful for software items etc.",
        'fd_order' => "6",
        'fd_tabname' => "Pricing",
    );

// Short Description Field
$default_fd[$table]['pr_na_message'] = array(
        'fd_name' => "Unavailable Message",
        'fd_type' => "text",
        'fd_size' => "60",
        'fd_help' => "A one sentence description to display when the product is unavailable",
        'fd_order' => $o++,
        'fd_tabname' => "Pricing",
    );


/* Scheduling Tab */

// Go Live Date Field
$default_fd[$table]['pr_livedate'] = array(
        'fd_name' => "Go Live Date",
        'fd_type' => "unixdate",
        'fd_default' => "",
        'fd_help' => "The product will not appear on the site until this date",
        'fd_order' => "1",
        'fd_tabname' => "Scheduling",
        'fd_mode' => "standard",
    );

// Expiry Date Field
$default_fd[$table]['pr_expirydate'] = array(
        'fd_name' => "Expiry Date",
        'fd_type' => "unixdate",
        'fd_default' => "",
        'fd_help' => "The page will be removed from the site after this date",
        'fd_order' => "2",
        'fd_tabname' => "Scheduling",
        'fd_mode' => "standard",
    );

// Status Field
$default_fd[$table]['status'] = array(
        'fd_name' => "Active",
        'fd_type' => "yesno",
        'fd_default' => "1",
        'fd_help' => "Active product",
        'fd_order' => "3",
        'fd_tabname' => "Scheduling",
        'fd_mode' => "standard",
    );


/* Shipping per bottle Tab */

// Freight costs Field
$default_fd[$table]['pr_freight'] = array(
        'fd_name' => "Freight costs",
        'fd_type' => "freight",
        'fd_showlabel' => "no",
        'fd_help' => "Customize the freight charges that apply to this product",
        'fd_order' => "1",
        'fd_tabname' => "Shipping per bottle",
    );

// Freight costs Field
$default_fd[$table]['pr_freightcase'] = array(
        'fd_name' => "Freight costs per case",
        'fd_type' => "freight",
        'fd_showlabel' => "no",
        'fd_help' => "Customize the freight charges that apply to this product",
        'fd_order' => "1",
        'fd_tabname' => "Shipping per case",
    );


/* Tags Tab */

// Tags Field
$default_fd[$table]['pr_tags'] = array(
        'fd_name' => "Tags",
        'fd_type' => "tag",
        'fd_options' => "jojo_product",
        'fd_showlabel' => "no",
        'fd_help' => "A list of words describing the product",
        'fd_order' => "1",
        'fd_tabname' => "Tags",
        'fd_mode' => "standard",
    );



$table = 'productcategory';
$o = 0;

$default_td['productcategory'] = array(
        'td_name' => "productcategory",
        'td_primarykey' => "productcategoryid",
        'td_displayfield' => "pageid",
        'td_filter' => "yes",
        'td_topsubmit' => "yes",
        'td_addsimilar' => "no",
        'td_deleteoption' => "yes",
        'td_menutype' => "list",
        'td_help' => "New Product Categories are managed from here.",
        'td_plugin' => "jojo_cart_products_wine",
    );

$o = 0;

/* Content Tab */

// id Field
$default_fd['productcategory']['productcategoryid'] = array(
        'fd_name' => "Productcategoryid",
        'fd_type' => "integer",
        'fd_readonly' => "1",
        'fd_help' => "A unique ID, automatically assigned by the system",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "advanced",
    );

// Page Field
$default_fd['productcategory']['pageid'] = array(
        'fd_name' => "Page",
        'fd_type' => "dbpluginpagelist",
        'fd_options' => "jojo_plugin_jojo_cart_products_wine",
        'fd_readonly' => "1",
        'fd_default' => "0",
        'fd_help' => "The page on the site used for this category.",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );

// Type Field
$default_fd['productcategory']['type'] = array(
        'fd_name' => "Type",
        'fd_type' => "radio",
        'fd_options' => "normal:Normal\nparent:Parent\nindex:All Products",
        'fd_readonly' => "0",
        'fd_default' => "normal",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );

// Sortby Field
$default_fd['productcategory']['sortby'] = array(
        'fd_name' => "Sortby",
        'fd_type' => "radio",
        'fd_options' => "name:Title\norder:Display Order",
        'fd_readonly' => "0",
        'fd_default' => "ar_date desc",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );

// Add to Nav 
$default_fd['productcategory']['addtonav'] = array(
        'fd_name' => "Show Products in Nav",
        'fd_type' => "yesno",
        'fd_help' => "Add products to navigation as child pages of this one.",
        'fd_default' => "0",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );
    
// Snippet Length Field
$default_fd['productcategory']['snippet'] = array(
        'fd_name' => "Snippet Length",
        'fd_type' => "text",
        'fd_readonly' => "0",
        'fd_default' => "400",
        'fd_help' => "Truncate index snippets to this many characters. Use 'full' for no snipping.",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );

// Read more link text 
$default_fd['productcategory']['readmore'] = array(
        'fd_name' => "Read more link",
        'fd_type' => "text",
        'fd_readonly' => "0",
        'fd_default' => '> Read more',
        'fd_help' => "The link text to read the full product",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );

// Show Date
$default_fd['productcategory']['showdate'] = array(
        'fd_name' => "Show Post Date",
        'fd_type' => "yesno",
        'fd_readonly' => "0",
        'fd_default' => "1",
        'fd_help' => "Show date added on posts",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );

// Date format Field
$default_fd['productcategory']['dateformat'] = array(
        'fd_name' => "Date Format",
        'fd_type' => "text",
        'fd_readonly' => "0",
        'fd_default' => "%e %b %Y",
        'fd_help' => "Format the time and/or date according to locale settings. See php.net/strftime for details",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );

// Thumbnail sizing Field
$default_fd['productcategory']['thumbnail'] = array(
        'fd_name' => "Thumbnail Size",
        'fd_type' => "text",
        'fd_readonly' => "0",
        'fd_default' => "s150",
        'fd_help' => "image thumbnail sizing in index eg: 150x200, h200, v4000",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );

// Main image sizing 
$default_fd['productcategory']['mainimage'] = array(
        'fd_name' => "Main Image",
        'fd_type' => "text",
        'fd_readonly' => "0",
        'fd_default' => "v60000",
        'fd_help' => "image thumbnail sizing in index eg: 150x200, h200, v4000",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );

// Allow Comments
$default_fd['productcategory']['comments'] = array(
        'fd_name' => "Enable comments",
        'fd_type' => "yesno",
        'fd_readonly' => "0",
        'fd_default' => "1",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
    );

// Display Order Field
$default_fd[$table]['pc_display_order'] = array(
        'fd_name' => "Display Order",
        'fd_type' => "order",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "advanced",
    );
    
// Image Field
$default_fd[$table]['pc_image'] = array(
        'fd_name' => "Image",
        'fd_type' => "fileupload",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "standard",
    );

// Name Format fields
$default_fd[$table]['nameformat'] = array(
        'fd_name' => "Name",
        'fd_type' => "text",
        'fd_size' => 80,
        'fd_order' => $o++,
        'fd_help' => "Name format to use on the wine page - choose a combination of [brand], [region], [variety], [vintage], [designation]",
        'fd_tabname' => "Content",
        'fd_mode' => "advanced",
    );

$default_fd[$table]['nameformat_index'] = array(
        'fd_name' => "Name - Index",
        'fd_type' => "text",
        'fd_size' => 80,
        'fd_order' => $o++,
        'fd_help' => "Name format to use on the index page - choose a combination of [brand], [region], [variety], [vintage], [designation]",
        'fd_tabname' => "Content",
        'fd_mode' => "advanced",
    );
    
$default_fd[$table]['nameformat_menu'] = array(
        'fd_name' => "Name - Menu",
        'fd_type' => "text",
        'fd_size' => 80,
        'fd_order' => $o++,
        'fd_help' => "Name format to use in the navigation - choose a combination of [brand], [region], [variety], [vintage], [designation]",
        'fd_tabname' => "Content",
        'fd_mode' => "advanced",
    );
    
$default_fd[$table]['nameformat_cart'] = array(
        'fd_name' => "Name - Cart",
        'fd_type' => "text",
        'fd_size' => 80,
        'fd_order' => $o++,
        'fd_help' => "Name format to use on the cart pages - choose a combination of [brand], [region], [variety], [vintage], [designation]",
        'fd_tabname' => "Content",
        'fd_mode' => "advanced",
    );