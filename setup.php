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

/* Wines */
$data = Jojo::selectQuery("SELECT * FROM {page}  WHERE pg_link='jojo_plugin_jojo_cart_products_wine'");
if (!count($data)) {
    echo "jojo_plugin_jojo_cart_products_wine: Adding <b>Wines</b> Page to menu<br />";
    Jojo::insertQuery("INSERT INTO {page} SET pg_title='Our Wines', pg_link='jojo_plugin_jojo_cart_products_wine', pg_url='wines'");
}


/* Edit Wines */
$data = Jojo::selectQuery("SELECT * FROM {page} WHERE pg_url='admin/edit/product'");
if (!count($data)) {
    echo "jojo_cart_products_wine: Adding <b>Edit Products</b> Page to menu<br />";
    Jojo::insertQuery("INSERT INTO {page} SET pg_title='Edit Wines', pg_link='jojo_plugin_Admin_Edit', pg_url='admin/edit/product', pg_parent=". Jojo::clean($_ADMIN_CONTENT_ID).", pg_order=4, pg_sitemapnav='no', pg_xmlsitemapnav='no', pg_index='no', pg_followto='no', pg_followfrom='yes'");
}

/* Edit Wine Categories */
$data = Jojo::selectQuery("SELECT * FROM {page}  WHERE pg_url='admin/edit/productcategory'");
if (!count($data)) {
    echo "jojo_cart_products_wine: Adding <b>Product Categories</b> Page to Edit Content menu<br />";
    Jojo::insertQuery("INSERT INTO {page} SET pg_title='Wine Categories', pg_link='jojo_plugin_Admin_Edit', pg_url='admin/edit/productcategory', pg_parent=?, pg_order=3", array($_ADMIN_CONTENT_ID));
}

/* Ensure there is a folder for uploading product images */
$res = Jojo::RecursiveMkdir(_DOWNLOADDIR . '/products');
if ($res === true) {
    echo "jojo_cart_products_wine: Created folder: " . _DOWNLOADDIR . '/products';
} elseif($res === false) {
    echo 'jojo_cart_products_wine: Could not automatically create ' .  _DOWNLOADDIR . '/products' . 'folder on the server. Please create this folder and assign 777 permissions.';
}
