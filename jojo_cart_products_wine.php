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

class jojo_plugin_jojo_cart_products_wine extends JOJO_Plugin
{
    function getProductDetails($code)
    {

        preg_match('/(.+)(_case)/', $code, $matches);
        if ($matches) {
            $pcode = $matches[1];
            $case = true;
        } else {
            $pcode = $code;
            $case = false;
        }

        /* attempt to match by product code */
        $product = Jojo::selectRow("SELECT * FROM {product} WHERE pr_code = ?", $pcode);

        /* attempt to match by id if code didn't work */
        if(empty($product)){
            $product = Jojo::selectRow("SELECT * FROM {product} WHERE productid = ?", $code);
         }

        /* decide on an appropriate product image */
        if(!empty($product['pr_image'])){
            $image = "images/" . Jojo::getOption('cart_product_image_size') . "/products/" . $product['pr_image'];
        } else {
            $image = '';
        }

        /* a fixed quantity means the quantity can't be changed by the user (useful for licenses / software etc) */
        $fixed = $product['pr_quantity_fixed'] == 'yes' ? true : false;

        /* include the freight class */
        $freight = new jojo_cart_freight($case ? $product['pr_freightcase'] : $product['pr_freight']);


        $_PCATEGORIES = (Jojo::getOption('product_enable_categories', 'no') == 'yes') ? true : false ;
        $categoryid = $_PCATEGORIES ? $product['pr_category'] : '';
        $categorydata =  ($_PCATEGORIES && !empty($categoryid)) ? Jojo::selectRow("SELECT * FROM {productcategory} WHERE `productcategoryid` = '$categoryid';") : '';
        $categoryurl = ($_PCATEGORIES && !empty($categoryid)) ? $categorydata['pc_url'] : '';
        $categoryorder = ($_PCATEGORIES && !empty($categoryid)) ? $categorydata['pc_display_order'] : '';
        $categorypagedata = ($_PCATEGORIES && !empty($categoryid)) ? Jojo::selectRow("SELECT `pg_title` FROM {page} WHERE `pg_url` = ?;", array($categorydata['pc_url'])) : '';
        $categoryname = ($_PCATEGORIES && !empty($categoryid)) ? $categorypagedata['pg_title'] : '';


	/* prepare return array - key names are important */
        $data = array('id'             => $product['pr_code'] . ( $case ? '_case' : '' ),
                      'name'           => (Jojo::getOption('product_useshortname', 'no') == 'no' ? $product['pr_name'] . ' ' : '') . $product['pr_variety'] . ' '. $product['pr_vintage'] . ( $case ? ' (' . $product['pr_casesize'] . ' bottle case)' : '' ),
                      'brand'          => $product['pr_name'],
                      'variant'        => $product['pr_variety'] . ' '. $product['pr_vintage'],
		              'productorder'   => $product['pr_display_order'],
                      'description'    => $product['pr_desc'],
                      'image'          => $image,
                      'price'          => ( $case ? $product['pr_caseprice'] : $product['pr_price'] ),
                      'case'           => $case,
                      'caseprice'      => $product['pr_caseprice'],
                      'casesize'       => $product['pr_casesize'],
		              'currency'       => $product['pr_currency'],
		              'category'       => $categoryname,
		              'categoryurl'    => $categoryurl,
		              'categoryorder'  => $categoryorder,
                      'code'           => $product['pr_code'],
                      'quantity_fixed' => $fixed,
                      'freight'        => $freight->export(),
                      'url'            => jojo_plugin_Jojo_cart_products_wine::getProductUrl($product['productid'], $product['pr_url'], $product['pr_name'], $product['pr_language'], $product['pr_category'] )
                      );


        /* Logged in user? */
        global $_USERID;
        if (!$_USERID && $product['pr_caseprice'] != 'NA') {
            /* Not logged in and product available, cache and return */
            return $data;
        } elseif (!$_USERID) {
            /* Not logged in but product not available */
                return false;
        }

        /* Look for per user pricing */
        $userPricing = Jojo::selectRow('SELECT * FROM {product_user_price} WHERE productid = ? AND userid = ?', array($product['productid'], $_USERID));
        if ($userPricing) {
            if (!$userPricing || $userPricing['unitprice'] == 'NA') {
                /* User can't purchase this product */
                return false;
            } elseif ($userPricing['unitprice'] !== '') {
                $user = Jojo::selectRow('SELECT * FROM {user} WHERE userid = ?', $_USERID);
                $data['price'] = $userPricing['unitprice'];
                $data['currency'] = $user['user_pricing'];
            }
        }

        return $data;
    }

    /* a  filter for sorting items in the shopping cart */
    function sort_cart_items($items) {
        uasort($items, array('jojo_plugin_jojo_cart_products_wine', '_compare'));
        return $items;
    }

    function _compare($a, $b)
    {
        if ($a['categoryorder'] != $b['categoryorder']) {
            return ($a['categoryorder'] < $b['categoryorder']) ? -1 : 1;
        } elseif ($a['productorder'] != $b['productorder']) {
            return ($a['productorder'] < $b['productorder']) ? -1 : 1;
        } elseif ($a['case'] != $b['case']) {
            return (!$a['case']) ? -1 : 1;
        }
        return 0;
    }

    /* a content filter for inserting buy now buttons */
    function buyNow($content)
    {
        global $smarty;

        /* Find all [[buynow: code]] tags */
        preg_match_all('/\[\[buy ?now: ?([^\]]*)\]\]/', $content, $matches);
        foreach($matches[1] as $id => $code) {

            $smarty->assign('prodcode', $code);

            /* Get the embed html */
            $html = $smarty->fetch('jojo_cart_products_wine_buynow.tpl');
            $content = str_replace($matches[0][$id], $html, $content);

        }

        /* Find all [[buynowlink: code]] tags */
        preg_match_all('/\[\[buy ?now ?link: ?([^\]]*)\]\]/', $content, $matches);
        foreach($matches[1] as $id => $linkcode) {
            $smarty->assign('prodlinkcode', $linkcode);

            /* Get the embed html */
            $html = $smarty->fetch('jojo_cart_products_wine_buynowlink.tpl');
            $content = str_replace($matches[0][$id], $html, $content);
        }

        return $content;
    }
    static function saveTags($record, $tags = array())
    {
        /* Ensure the tags class is available */
        if (!class_exists('jojo_plugin_Jojo_Tags')) {
            return false;
        }

        /* Delete existing tags for this item */
        jojo_plugin_Jojo_Tags::deleteTags('jojo_product', $record['productid']);

        /* Save all the new tages */
        foreach($tags as $tag) {
            jojo_plugin_Jojo_Tags::saveTag($tag, 'jojo_product', $record['productid']);
        }
    }

    static function getTagSnippets($ids)
    {
        /* Convert array of ids to a string */
        $ids = "'" . implode($ids, "', '") . "'";

        /* Get the products */
        $products = Jojo::selectQuery("SELECT *
                                       FROM {product}
                                       WHERE
                                            productid IN ($ids)
                                         AND
                                           pr_livedate < ?
                                         AND
                                           pr_expirydate<=0 OR pr_expirydate > ?
                                       ORDER BY
                                         pr_date DESC",
                                      array(time(), time()));

        /* Create the snippets */
        $snippets = array();
        foreach ($products as $i => $a) {
            $image = !empty($products[$i]['pr_image']) ? 'products/' . $products[$i]['pr_image'] : '';
            $snippets[] = array(
                    'id'    => $products[$i]['productid'],
                    'image' => $image,
                    'title' => Jojo::html2text($products[$i]['pr_name']),
                    'text'  => Jojo::html2text($products[$i]['pr_body']),
                    'url'   => Jojo::urlPrefix(false) . jojo_plugin_Jojo_product::getProductUrl($products[$i]['productid'], $products[$i]['pr_url'], $products[$i]['pr_name'], $products[$i]['pr_language'], $products[$i]['pr_category'])
                );
        }

        /* Return the snippets */
        return $snippets;
    }

    /* Gets $num products sorted by date (desc) for use on homepages and sidebars */
    function getProducts($num=false, $start = 0, $categoryid=false) {
        global $page, $_USERID;
        if (_MULTILANGUAGE) $language = !empty($page->page['pg_language']) ? $page->page['pg_language'] : Jojo::getOption('multilanguage-default', 'en');
        $limit = ($num) ? " LIMIT $start,$num" : '';
        /* Get category url and id if needed */
        $_PCATEGORIES = (Jojo::getOption('product_enable_categories', 'no') == 'yes') ? true : false ;
        $categorydata =  ($_PCATEGORIES && !empty($categoryid) && $categoryid != 'all') ? Jojo::selectRow("SELECT `pc_url` FROM {productcategory} WHERE `productcategoryid` = '$categoryid';") : '';
        $categorypagedata = ($_PCATEGORIES && !empty($categoryid) && $categoryid != 'all') ? Jojo::selectRow("SELECT `pg_title` FROM {page} WHERE `pg_url` = ?;", array($categorydata['pc_url'])) : '';
        $categoryname = ($_PCATEGORIES && !empty($categoryid) && $categoryid != 'all') ? $categorypagedata['pg_title'] : '';
        $categoryurl = ($_PCATEGORIES && !empty($categoryid) && $categoryid != 'all') ? jojo_plugin_Jojo_cart_products_wine::_getPrefix('product', (_MULTILANGUAGE ? $language : ''), (!empty($categoryid) ? $categoryid : '') ) : '';

        $now    = time();
        $query  = "SELECT * FROM {product} WHERE pr_livedate<$now AND (pr_expirydate<=0 OR pr_expirydate>$now)";
        $query .= (_MULTILANGUAGE) ? " AND (pr_language = '$language')" : '';
        $query .= ($_PCATEGORIES && $categoryid != 'all') ? " AND (pr_category = '$categoryid')" : '';
        $query .= " ORDER BY pr_display_order, pr_date DESC $limit";
        $products = Jojo::selectQuery($query);
        foreach ($products as $i => $a){
            if ($_PCATEGORIES && $categoryid == 'all' && !empty($products[$i]['pr_category'])) {
                $categorydata = Jojo::selectRow("SELECT `pc_url` FROM {productcategory} WHERE `productcategoryid` = ?;", array($products[$i]['pr_category']));
                $categorypagedata = Jojo::selectRow("SELECT `pg_title` FROM {page} WHERE `pg_url` = ?;", array($categorydata['pc_url']));
                $categoryname =  $categorypagedata['pg_title'];
                $categoryurl = jojo_plugin_Jojo_cart_products_wine::_getPrefix('product', (_MULTILANGUAGE ? $language : ''), $products[$i]['pr_category'] );
            }
            $products[$i]['id']           = $products[$i]['productid'];
            $products[$i]['title']        = $products[$i]['pr_name'];
            $products[$i]['bodyplain']    = Jojo::html2text($products[$i]['pr_body']);
            $products[$i]['date']         = Jojo::strToTimeUK($products[$i]['pr_date']);
            $products[$i]['datefriendly'] = Jojo::mysql2date($products[$i]['pr_date'], "medium");
            $products[$i]['url']          = jojo_plugin_Jojo_cart_products_wine::getProductUrl($products[$i]['productid'], $products[$i]['pr_url'], $products[$i]['pr_name'], $products[$i]['pr_language'], ($_PCATEGORIES ? $products[$i]['pr_category'] : '') );
            $products[$i]['category']     = $categoryname;
            $products[$i]['categoryurl']  = $categoryurl;

            /* Logged in user? */
            if ($_USERID) {

                /* Look for per user availability */
                $userPricing = Jojo::selectRow('SELECT * FROM {product_user_price} WHERE productid = ? AND userid = ?', array($products[$i]['productid'], $_USERID));
                if ($userPricing && $userPricing['unitprice'] == 'NA') {
                    /* User can't purchase this varient */
                    unset($products[$i]);
                }
		elseif ($userPricing) {
                  $products[$i]['pr_caseprice'] = $userPricing['unitprice'];
                }

            } else {
                if ($products[$i]['pr_caseprice'] == 'NA' && Jojo::getOption('product_show_NA_products', 'no')=='no') {
                    /* Casual users can't purchase this product */
                    unset($products[$i]);
                }
            }

        }
        return $products;
    }

    /*
     * calculates the URL for the product - requires the product ID, but works without a query if given the URL or title from a previous query
     *
     */
    static function getProductUrl($productid=false, $url=false, $title=false, $language=false, $categoryid=false )
    {
        if (_MULTILANGUAGE) {
            $language = !empty($language) ? $language : Jojo::getOption('multilanguage-default', 'en');
            $mldata = Jojo::getMultiLanguageData();
            $lclanguage = $mldata['longcodes'][$language];
        }

        /* URL specified */
        if (!empty($url)) {
            $fullurl = (_MULTILANGUAGE) ? Jojo::getMultiLanguageString ( $language, false ) : '';
            $fullurl .= jojo_plugin_Jojo_cart_products_wine::_getPrefix('product', ((_MULTILANGUAGE) ? $language : ''), ((!empty($categoryid)) ? $categoryid : '') ) . '/' . $url . '/';
            return $fullurl;
         }
        /* ProductID + title specified */
        if ($productid && !empty($title)) {
            $fullurl = (_MULTILANGUAGE) ? Jojo::getMultiLanguageString ( $language, false ) : '';
            $fullurl .= (_MULTILANGUAGE && $language != 'en') ? jojo_plugin_Jojo_cart_products_wine::_getPrefix('product', $language, (!empty($categoryid) ? $categoryid : '') ) . '/' . $productid . '/' . urlencode($title) : Jojo::rewrite(jojo_plugin_Jojo_cart_products_wine::_getPrefix('product', ((_MULTILANGUAGE) ? $language : ''), (!empty($categoryid) ? $categoryid : '')), $productid, $title, '');
            return $fullurl;
        }
        /* use the product ID to find either the URL or title */
        if ($productid) {
            $products = Jojo::selectQuery("SELECT pr_url, pr_name, pr_language, pr_category FROM {product} WHERE productid = ?", $productid);
            if (count($products)) {
                if (_MULTILANGUAGE) {
                    $language = !empty($products[0]['pr_language']) ? $products[0]['pr_language'] : Jojo::getOption('multilanguage-default', 'en');
                    $lclanguage = $mldata['longcodes'][$language];
                }
                if (!empty($products[0]['pr_url'])) {
                    $fullurl = (_MULTILANGUAGE) ? Jojo::getMultiLanguageString ( $language, false ) : '';
                    $fullurl .= jojo_plugin_Jojo_cart_products_wine::_getPrefix('product', ((_MULTILANGUAGE) ? $language : ''), $products[0]['pr_category'] ) . '/' . $products[0]['pr_url'] . '/';
                    return $fullurl;
                } else {
                    $fullurl = (_MULTILANGUAGE) ? Jojo::getMultiLanguageString ( $language, false ) : '';
                    $fullurl .= (_MULTILANGUAGE && $language != 'en') ? jojo_plugin_Jojo_cart_products_wine::_getPrefix('product', $language, $products[0]['pr_category'] ) . '/' . $productid . '/' . urlencode($products[0]['pr_name'])  : Jojo::rewrite(jojo_plugin_Jojo_cart_products_wine::_getPrefix('product', ((_MULTILANGUAGE) ? $language : ''), $products[0]['pr_category'] ), $productid, $products[0]['pr_name'], '');
                    return $fullurl;
                }
            }
         }
        /* No product matching the ID supplied or no ID supplied */
        return false;
    }


    static function forwardToFriend($product)
    {
        global $smarty;
        $content = array();
        $id				= Jojo::getPost('id',0);
        $toaddress 		= $_POST['toaddress'];
        $toname 		= $_POST['toname'];
        $fromaddress 	= $_POST['fromaddress'];
        $fromname		= $_POST['fromname'];
        $pmessage		= $_POST['pmessage'];

        $url 			= _SITEURL . '/' . jojo_plugin_Jojo_cart_products_wine::getProductUrl($id);

        /* send notification */
        $messages		= Jojo::selectQuery('SELECT * FROM {product} WHERE productid = ?', $id);
        $subject		= Jojo::getOption('sitetitle') . ' : ' . $messages[0]['pr_name'];
        $message		= 'Hi ' . $toname.', ' . "\n\n";
        $message		.= $fromname . ' thinks you will be interested in this product on the ' . _SITENAME . " site:\n\n";
        $message		.= $product['pr_name'] . "\n";
        $bodysnippet    = substr(strip_tags($product['pr_body']), 0, 400 ) . '...';
        $message		.= $bodysnippet . "\n\n";
        $message		.= 'Read the full story at: ' . $url . "\n\n";
        $message		.= $fromname . ' said: ' . $pmessage . "\n\n";

        if (Jojo::simpleMail($toname, $toaddress, $subject, $message, $fromname, $fromaddress)){
            $notification = '<p>The story was successfully sent!</p>';
            $smarty->assign('notification', $notification);
        }
        else{
            $notification = '<p>Message delivery failed</p>';
            $smarty->assign('notification', $notification);
        }

    }

    /**
     * Get Previous Product
     *
     * Returns an array containing data about the previous product. An extra query can be saved by inputting the product data as the 2nd argument yyyy-mm-dd (optional)
     */
    static function getPreviousProduct($currentid, $currentdate=false, $language=false, $categoryid=false)
    {
        if (!$currentdate) {
            //get the date from the database (requires an extra query)
            $products = Jojo::selectQuery("SELECT pr_date FROM {product} WHERE productid = ? LIMIT 1", $currentid);
            if (!count($products)) return false;
            $currentdate = $products[0]['pr_date'];
        }
        $now = time();
        $_PCATEGORIES = (Jojo::getOption('product_enable_categories', 'no') == 'yes') ? true : false ;
        $query = "SELECT productid, pr_url, pr_name, pr_variety, pr_vintage, pr_date, pr_language, pr_category FROM {product}";
        $query .= " WHERE ( pr_date < ? OR ( pr_date = ? AND productid < ? ) ) AND productid != ? AND  pr_livedate < ? AND (pr_expirydate <= 0 OR pr_expirydate > ?)";
        $query .= (_MULTILANGUAGE) ? " AND (pr_language = '$language')" : '';
        $query .= ($_PCATEGORIES) ? " AND (pr_category = '$categoryid')" : '';
        $query .= " ORDER BY pr_date DESC, productid DESC LIMIT 1";
        $data = Jojo::selectRow($query, array($currentdate, $currentdate, $currentid, $currentid, $now, $now));
        if (!count($data)) return false;
        $id = $data['productid'];
        $title = (Jojo::getOption('product_useshortname', 'no') == 'no' ? $data['pr_name'] . ' ' : '') . $data['pr_variety'] . ' ' . $data['pr_vintage'];
        $date = $data['pr_date'];
        $url = jojo_plugin_Jojo_cart_products_wine::getProductUrl($data['productid'], $data['pr_url'], $data['pr_name'], $data['pr_language'], $data['pr_category']);
        return array('id'=>$id, 'title'=>$title, 'date'=>$date, 'url'=>$url);
    }

    /**
     * Get Next Product
     *
     * Returns an array containing data about the next product. An extra query can be saved by inputting the product data as the 2nd argument yyyy-mm-dd (optional)
     */
    static function getNextProduct($currentid, $currentdate=false, $language=false, $categoryid=false)
    {
        if (!$currentdate) {
            //get the date from the database (requires an extra query)
            $products = Jojo::selectQuery("SELECT pr_date FROM {product} WHERE productid = ? LIMIT 1", $currentid);
            if (!count($products)) return false;
            $currentdate = $products[0]['pr_date'];
        }
        $now = time();
        $_PCATEGORIES = (Jojo::getOption('product_enable_categories', 'no') == 'yes') ? true : false ;
        $query = "SELECT productid, pr_url, pr_name, pr_variety, pr_vintage, pr_date, pr_language, pr_category FROM {product}";
        $query .= " WHERE ( pr_date > ? OR ( pr_date = ? AND productid > ? ) ) AND productid != ? AND  pr_livedate < ? AND (pr_expirydate <= 0 OR pr_expirydate > ?)";
        $query .= (_MULTILANGUAGE) ? " AND (pr_language = '$language')" : '';
        $query .= ($_PCATEGORIES) ? " AND (pr_category = '$categoryid')" : '';
        $query .= " ORDER BY pr_date, productid LIMIT 1";
        $data = Jojo::selectRow($query, array($currentdate, $currentdate, $currentid, $currentid, $now, $now));
        if (!count($data)) return false;

        $id = $data['productid'];
        $title = (Jojo::getOption('product_useshortname', 'no') == 'no' ? $data['pr_name'] . ' ' : '') . $data['pr_variety'] . ' ' . $data['pr_vintage'];
        $date = $data['pr_date'];
        $url = jojo_plugin_Jojo_cart_products_wine::getProductUrl($data['productid'], $data['pr_url'], $data['pr_name'], $data['pr_language'], $data['pr_category']);
        return array('id'=>$id, 'title'=>$title, 'date'=>$date, 'url'=>$url);
    }


    function _getContent()
    {
        global $smarty, $_USERGROUPS, $_USERID;
        $content = array();
        $language = !empty($this->page['pg_language']) ? $this->page['pg_language'] : Jojo::getOption('multilanguage-default', 'en');
        $mldata = Jojo::getMultiLanguageData();
        $lclanguage = $mldata['longcodes'][$language];

        /* Are we looking at an product or the index? */
        $productid = Jojo::getFormData('id',     0);
        $url       = Jojo::getFormData('url',    '');
        $action    = Jojo::getFormData('action', '');

        if ($productid || !empty($url)) {

            /* The product from the database */
            if (!empty($url)) {
                $product = Jojo::selectRow("SELECT * FROM {product} WHERE pr_url = ?", array($url));
            } else {
                $product = Jojo::selectRow("SELECT * FROM {product} WHERE productid = ?", array($productid));
            }

            /* If the product can't be found, return a 404 */
            if (!count($product)) {
                include(_BASEPLUGINDIR . '/jojo_core/404.php');
                exit;
            }

            /* Get the specific product */
            $productid = $product['productid'];
            $product['pr_datefriendly'] = Jojo::mysql2date($product['pr_date'], "long");


            /* calculate the next and previous products */
            if (Jojo::getOption('product_next_prev') == 'yes') {
                $nextproduct = jojo_plugin_Jojo_cart_products_wine::getNextProduct($productid, $product['pr_date'], $product['pr_language'], $product['pr_category']);
                if ($nextproduct) $smarty->assign('nextproduct', $nextproduct);
                $prevproduct = jojo_plugin_Jojo_cart_products_wine::getPreviousProduct($productid, $product['pr_date'], $product['pr_language'], $product['pr_category']);
                if ($prevproduct) $smarty->assign('prevproduct', $prevproduct);
            }

            /* Ensure the tags class is available */
            if (class_exists('jojo_plugin_Jojo_Tags')) {
                /* Split up tags for display */
                $tags = jojo_plugin_Jojo_Tags::tagstrToArray($product['pr_tags']);
                if (count($tags) > 0) {
                    $smarty->assign('tags', $tags);
                }

                /* generate tag cloud of tags belonging to this product */
                $product_tag_cloud_minimum = Jojo::getOption('product_tag_cloud_minimum');
                if (!empty($product_tag_cloud_minimum) && ($product_tag_cloud_minimum < count($tags))) {
                    $itemcloud = jojo_plugin_Jojo_Tags::getTagCloud('', $tags);
                    $smarty->assign('itemcloud', $itemcloud);
                }
            }

            /* Calculate whether the product has expired or not */
            $now = strtotime('now');
            if (($now < $product['pr_livedate']) || (($now > $product['pr_expirydate']) && ($product['pr_expirydate'] > 0)) ) {
                $this->expired = true;
            }

            /* Logged in user? */
            if ($_USERID) {
                /* Look for per user availability */
                $userPricing = Jojo::selectRow('SELECT * FROM {product_user_price} WHERE productid = ? AND userid = ?', array($productid, $_USERID));
                if ($userPricing && $userPricing['unitprice'] == 'NA') {
                    /* User can't purchase this varient */
                  $product['pr_caseprice'] = 'This wine is not available for order';
                }
		elseif ($userPricing) {
                  $product['pr_caseprice'] = $userPricing['unitprice'];
                }
            } else {
                if ($product['pr_caseprice'] == 'NA') {
                    /* Casual users can't purchase this product */
                  $product['pr_caseprice'] = 'This wine is not available for order';
                }
            }

            /* Foward to friend */
            $smarty->assign('posturl', jojo_plugin_Jojo_cart_products_wine::getCorrectUrl());
            $sent = false;
            $notification = false;
            $smarty->assign('notification', $notification);
            if (Jojo::getFormData('emailsubmit', false)) {
                $sent = jojo_plugin_Jojo_cart_products_wine::forwardToFriend($product);
            }
            /* If a file called send-friend.png exists, use this instead of a text link */
            foreach (Jojo::listPlugins('images/send-friend.png') as $pluginfile) {
                $smarty->assign('friendsendbutton', true);
            }

            /* Get Comments */
            if (Jojo::getOption('productcomments') == 'yes') {
                $productcomments = Jojo::selectQuery("SELECT * FROM {productcomment} WHERE pc_productid = ? ORDER BY pc_timestamp", array($productid));
                $smarty->assign('jojo_cart_products_winecomments', $productcomments);
                $smarty->assign('jojo_cart_products_winecommentsenabled', true);
            }

            /* Calculate URL to POST comments to */
            $smarty->assign('jojo_cart_products_wineposturl', jojo_plugin_Jojo_cart_products_wine::getProductUrl($productid, $product['pr_url'], $product['pr_name'], $product['pr_language'], $product['pr_category']));

            /* Add product breadcrumb */
            $breadcrumbs                      = $this->_getBreadCrumbs();
            $breadcrumb                       = array();
            $breadcrumb['name']               = (Jojo::getOption('product_useshortname', 'no') == 'no' ? $product['pr_name'] . ' ' : '') . $product['pr_variety'] . ' ' . $product['pr_vintage'];
            $breadcrumb['rollover']           = $product['pr_desc'];
            $breadcrumb['url']                = jojo_plugin_Jojo_cart_products_wine::getProductUrl($productid, $product['pr_url'], $product['pr_name'], $product['pr_language'], $product['pr_category']);
            $breadcrumbs[count($breadcrumbs)] = $breadcrumb;

            /* Remember user fields from session */
            if (!empty($_SESSION['name'])) {
                $smarty->assign('name', $_SESSION['name']);
            }
            if (!empty($_SESSION['email'])) {
                $smarty->assign('email', $_SESSION['email']);
            }
            if (!empty($_SESSION['website'])) {
                $smarty->assign('website', $_SESSION['website']);
            }
            if (!empty($_SESSION['anchortext'])) {
                $smarty->assign('anchortext', $_SESSION['anchortext']);
            }

            /* If a file called post-comment.gif exists, use this instead of a text link */
            foreach (Jojo::listPlugins('images/post-comment.gif') as $pluginfile) {
                $smarty->assign('commentbutton', true);
            }

            /* Calculate if user is admin or not. Admins can edit comments */
            if ($this->perms->hasPerm($_USERGROUPS, 'edit')) {
                $smarty->assign('editperms', true);
            }

            /* Assign product content to Smarty */
            $smarty->assign('wine', $product);


            /* Prepare fields for display */
            $content['title']            = (Jojo::getOption('product_useshortname', 'no') == 'no' ? $product['pr_name'] . ' ' : '') . $product['pr_variety'] . ' ' . $product['pr_vintage'];
            $content['seotitle']         = $product['pr_name'] . ' '  . $product['pr_variety'] . ' ' . $product['pr_vintage'];
            $content['breadcrumbs']      = $breadcrumbs;
            $content['meta_description'] = $product['pr_name'].', an product on '._SITETITLE.' - Read all about '.$product['pr_name'].' and other subjects on '._SITETITLE.'. '.Jojo::getOption('linkbody');
            $content['metadescription']  = $content['meta_description'];
            $productsperpage = Jojo::getOption('productsperpage', 40);
	    $pagenum = Jojo::getFormData('pagenum', 1);
            if ($pagenum[0] == 'p') {
                $pagenum = substr($pagenum, 1);
            }
            $start = ($productsperpage * ($pagenum-1));
            $pg_url = $this->page['pg_url'];
            $_PCATEGORIES = (Jojo::getOption('product_enable_categories', 'no') == 'yes') ? true : false ;
            $categorydata =  ($_PCATEGORIES) ? Jojo::selectRow("SELECT productcategoryid FROM {productcategory} WHERE pc_url = '$pg_url'") : '';
            $categoryid = ($_PCATEGORIES && count($categorydata)) ? $categorydata['productcategoryid'] : '';
	    $smarty->assign('winelist', jojo_plugin_Jojo_cart_products_wine::getProducts($productsperpage, $start, $categoryid));
        } else {
            /* Product index section */

            $pagenum = Jojo::getFormData('pagenum', 1);
            if ($pagenum[0] == 'p') {
                $pagenum = substr($pagenum, 1);
            }

            /* Get category url and id if needed */
            $pg_url = $this->page['pg_url'];
            $_PCATEGORIES = (Jojo::getOption('product_enable_categories', 'no') == 'yes') ? true : false ;
            $categorydata =  ($_PCATEGORIES) ? Jojo::selectRow("SELECT productcategoryid FROM {productcategory} WHERE pc_url = '$pg_url'") : '';
            $categoryid = ($_PCATEGORIES && count($categorydata)) ? $categorydata['productcategoryid'] : '';

            $smarty->assign('product','');
            $productsperpage = Jojo::getOption('productsperpage', 40);
            $start = ($productsperpage * ($pagenum-1));

            /* get number of products for pagination */
            $now = strtotime('now');
           /* Get number of products for pagination */
            $countquery =  "SELECT COUNT(*) AS numproducts FROM {product} WHERE pr_livedate<$now AND (pr_expirydate<=0 OR pr_expirydate>$now)";
            $countquery .= (_MULTILANGUAGE) ? " AND (pr_language = '$language')" : '';
            $countquery .= ($_PCATEGORIES) ? " AND (pr_category = '$categoryid')" : '';
            $productscount = Jojo::selectQuery($countquery);
            $numproducts = $productscount[0]['numproducts'];
            $numpages = ceil($numproducts / $productsperpage);
            /* calculate pagination */
            if ($numpages == 1) {
                $pagination = '';
            } elseif ($numpages == 2 && $pagenum == 2) {
                $pagination = sprintf('<a href="%s/p1/">previous...</a>', (_MULTILANGUAGE ? Jojo::getMultiLanguageString ( $language, false ) : '') . jojo_plugin_Jojo_cart_products_wine::_getPrefix('product', (_MULTILANGUAGE ? $language : ''), (!empty($categoryid) ? $categoryid : '')) );
            } elseif ($numpages == 2 && $pagenum == 1) {
                $pagination = sprintf('<a href="%s/p2/">more...</a>', (_MULTILANGUAGE ? Jojo::getMultiLanguageString ( $language, false ) : '') . jojo_plugin_Jojo_cart_products_wine::_getPrefix('product', (_MULTILANGUAGE ? $language : ''), ($_PCATEGORIES ? $categoryid : '')) );
            } else {
                $pagination = '<ul>';
                for ($p=1;$p<=$numpages;$p++) {
                    $url = (_MULTILANGUAGE ? Jojo::getMultiLanguageString ( $language, false ) : '') . jojo_plugin_Jojo_cart_products_wine::_getPrefix('product', (_MULTILANGUAGE ? $language : ''), (!empty($categoryid) ? $categoryid : '')) . '/';
                    if ($p > 1) {
                        $url .= 'p' . $p . '/';
                    }
                    if ($p == $pagenum) {
                        $pagination .= '<li>&gt; Page '.$p.'</li>'. "\n";
                    } else {
                        $pagination .= '<li>&gt; <a href="'.$url.'">Page '.$p.'</a></li>'. "\n";
                    }
                }
                $pagination .= '</ul>';
            }
            $smarty->assign('pagination',$pagination);
            $smarty->assign('pagenum',$pagenum);
            if (_MULTILANGUAGE) {
                $smarty->assign('multilangstring', Jojo::getMultiLanguageString($language));
            }

            /* clear the meta description to avoid duplicate content issues */
             $content['metadescription'] = '';

            /* get product content and assign to Smarty */
            $smarty->assign('wines', jojo_plugin_Jojo_cart_products_wine::getProducts($productsperpage, $start, $categoryid));
            $smarty->assign('winelist', jojo_plugin_Jojo_cart_products_wine::getProducts($productsperpage, $start, $categoryid));

            $content['content'] = $smarty->fetch('jojo_cart_products_wine_index.tpl');
            return $content;

        }


        /* get related products if tags plugin installed and option enabled */
        $numrelated = Jojo::getOption('product_num_related');
        if ($numrelated && class_exists('jojo_plugin_Jojo_Tags')) {
            $related = jojo_plugin_Jojo_Tags::getRelated('jojo_cart_products_wine', $productid, $numrelated, 'jojo_cart_products_wine'); //set the last argument to 'jojo_cart_products_wine' to restrict results to only products
            $smarty->assign('related', $related);
        }

        $content['content'] = $smarty->fetch('jojo_cart_products_wine.tpl');

        return $content;
    }

    static function admin_action_after_save()
    {
        Jojo::updateQuery("UPDATE {option} SET `op_value`=? WHERE `op_name`='product_last_updated'", time());
        return true;
    }

    public static function sitemap($sitemap)
    {
        /* See if we have any product sections to display and find all of them */
        $productindexes = Jojo::selectQuery("SELECT * FROM {page} WHERE pg_link = 'jojo_plugin_Jojo_cart_products_wine' AND pg_sitemapnav = 'yes'");
        if (!count($productindexes)) {
            return $sitemap;
        }

        if (Jojo::getOption('product_inplacesitemap', 'separate') == 'separate') {
            /* Remove any existing links to the products section from the page listing on the sitemap */
            foreach($sitemap as $j => $section) {
                $sitemap[$j]['tree'] = jojo_plugin_Jojo_cart_products_wine::_sitemapRemoveSelf($section['tree']);
            }
            $_INPLACE = false;
        } else {
            $_INPLACE = true;
        }

        $now = strtotime('now');
        $limit = 15;
        $productsperpage = Jojo::getOption('productsperpage', 40);
        $limit = ($productsperpage >= 15) ? 15 : $productsperpage ;
         /* Make sitemap trees for each products instance found */
        foreach($productindexes as $k => $i){
            /* Get language and language longcode if needed */
            if (_MULTILANGUAGE) {
                $language = !empty($i['pg_language']) ? $i['pg_language'] : Jojo::getOption('multilanguage-default', 'en');
                $mldata = Jojo::getMultiLanguageData();
                $lclanguage = $mldata['longcodes'][$language];
            }
            /* Get category url and id if needed */
            $pg_url = $i['pg_url'];
            $_PCATEGORIES = (Jojo::getOption('product_enable_categories', 'no') == 'yes') ? true : false ;
            $categorydata =  ($_PCATEGORIES) ? Jojo::selectRow("SELECT productcategoryid FROM {productcategory} WHERE `pc_url` = '$pg_url'") : '';
            $categoryid = ($_PCATEGORIES && count($categorydata)) ? $categorydata['productcategoryid'] : '';

            /* Create tree and add index and feed links at the top */
            $producttree = new hktree();
            $indexurl = (_MULTILANGUAGE) ? $lclanguage . '/' . jojo_plugin_Jojo_cart_products_wine::_getPrefix('product', $language, $categoryid) . '/' : jojo_plugin_Jojo_cart_products_wine::_getPrefix('product', '', $categoryid) . '/' ;
            if ($_INPLACE) {
                $parent = 0;
            } else {
               $producttree->addNode('index', 0, $i['pg_title'] . ' Index', $indexurl);
               $parent = 'index';
            }

            /* Get the product content from the database */
            $query =  "SELECT * FROM {product} WHERE pr_livedate<$now AND (pr_expirydate<=0 OR pr_expirydate>$now)";
            $query .= (_MULTILANGUAGE) ? " AND (pr_language = '$language')" : '';
            $query .= ($_PCATEGORIES) ? " AND (pr_category = '$categoryid')" : '';
            $query .= " ORDER BY pr_date DESC LIMIT $limit";

            $products = Jojo::selectQuery($query);
            $n = count($products);
            foreach ($products as $a) {
                $producttree->addNode($a['productid'], $parent, $a['pr_name'] . ' ' . $a['pr_variety'] . ' ' . $a['pr_vintage'], jojo_plugin_Jojo_cart_products_wine::getProductUrl($a['productid'], $a['pr_url'], $a['pr_name'], $a['pr_language'], $a['pr_category']));
            }

            /* Get number of products for pagination */
            $countquery =  "SELECT COUNT(*) AS numproducts FROM {product} WHERE pr_livedate<$now AND (pr_expirydate<=0 OR pr_expirydate>$now)";
            $countquery .= (_MULTILANGUAGE) ? " AND (pr_language = '$language')" : '';
            $countquery .= ($_PCATEGORIES) ? " AND (pr_category = '$categoryid')" : '';
            $productscount = Jojo::selectQuery($countquery);
            $numproducts = $productscount[0]['numproducts'];
            $numpages = ceil($numproducts / $productsperpage);

            /* calculate pagination */
            if ($numpages == 1) {
                if ($limit < $numproducts) {
                    $producttree->addNode('p1', $parent, 'More ' . $i['pg_title'] , $indexurl );
                }
            } else {
                for ($p=1; $p <= $numpages; $p++) {
                    if (($limit < $productsperpage) && ($p == 1)) {
                        $producttree->addNode('p1', $parent, '...More' , $indexurl );
                    } elseif ($p != 1) {
                        $url = $indexurl .'p' . $p .'/';
                        $nodetitle = $i['pg_title'] . ' Index - p'. $p;
                        $producttree->addNode('p' . $p, $parent, $nodetitle, $url);
                    }
                }
            }

            /* Add to the sitemap array */
            if ($_INPLACE) {
                /* Add inplace */
                $url = ((_MULTILANGUAGE) ? $lclanguage . '/' : '') . jojo_plugin_Jojo_cart_products_wine::_getPrefix('product', ((_MULTILANGUAGE) ? $language : ''), $categoryid) . '/';
                $sitemap['pages']['tree'] = jojo_plugin_Jojo_cart_products_wine::_sitemapAddInplace($sitemap['pages']['tree'], $producttree->asArray(), $url);
            } else {
                /* Add to the end */
                $sitemap["products$k"] = array(
                    'title' => $i['pg_title'] . ( _MULTILANGUAGE ? ' (' . ucfirst($lclanguage) . ')' : ''),
                    'tree' => $producttree->asArray(),
                    'order' => 3 + $k,
                    'header' => '',
                    'footer' => '',
                    );
            }
        }
        return $sitemap;
    }

    static function _sitemapAddInplace($sitemap, $toadd, $url)
    {
        foreach ($sitemap as $k => $t) {
            if ($t['url'] == $url) {
                $sitemap[$k]['children'] = $toadd;
            } elseif (isset($sitemap[$k]['children'])) {
                $sitemap[$k]['children'] = jojo_plugin_Jojo_cart_products_wine::_sitemapAddInplace($t['children'], $toadd, $url);
            }
        }
        return $sitemap;
    }

    static function _sitemapRemoveSelf($tree)
    {
        static $urls;

        if (!is_array($urls)) {
            $urls = array();
            $productindexes = Jojo::selectQuery("SELECT * FROM {page} WHERE pg_link = 'jojo_plugin_Jojo_cart_products_wine' AND pg_sitemapnav = 'yes'");
            if (count($productindexes)==0) {
               return $tree;
            }

            foreach($productindexes as $key => $i){
                $language = !empty($i['pg_language']) ? $i['pg_language'] : Jojo::getOption('multilanguage-default', 'en');
                $mldata = Jojo::getMultiLanguageData();
                $lclanguage = $mldata['longcodes'][$language];
                $_PCATEGORIES = (Jojo::getOption('product_enable_categories', 'no') == 'yes') ? true : false ;
                $categoryid =  ($_PCATEGORIES) ? Jojo::selectRow("SELECT productcategoryid FROM {productcategory} WHERE pc_url = '" . $i['pg_url'] . "'") : '';
                $urls[] = ((_MULTILANGUAGE) ? $lclanguage . '/' : '') . jojo_plugin_Jojo_cart_products_wine::_getPrefix('product', ((_MULTILANGUAGE) ? $language : ''), (!empty($categoryid) ? $categoryid : '')) . '/';
            }
        }

        foreach ($tree as $k =>$t) {
            if (in_array($t['url'], $urls)) {
                unset($tree[$k]);
            } else {
                $tree[$k]['children'] = jojo_plugin_Jojo_cart_products_wine::_sitemapRemoveSelf($t['children']);
            }
        }
        return $tree;
    }

    /**
    /**
     * XML Sitemap filter
     *
     * Receives existing sitemap and adds product pages
     */
    static function xmlsitemap($sitemap)
    {
        /* Get products from database */
        $products = Jojo::selectQuery("SELECT * FROM {product} WHERE pr_livedate<".time()." AND (pr_expirydate<=0 OR pr_expirydate>".time().")");

        /* Add products to sitemap */
        foreach($products as $a) {
            $url = _SITEURL . '/'. jojo_plugin_Jojo_cart_products_wine::getProductUrl($a['productid'], $a['pr_url'], $a['pr_name'], $a['pr_language'], $a['pr_category']);
            $lastmod = strtotime($a['pr_date']);
            $priority = 0.6;
            $changefreq = '';
            $sitemap[$url] = array($url, $lastmod, $changefreq, $priority);
        }

        /* Return sitemap */
        return $sitemap;
    }

    /**
     * Site Search
     *
     */
    static function search($results, $keywords, $language, $booleankeyword_str=false)
    {
        global $_USERGROUPS;
        $pagePermissions = new JOJO_Permissions();
        $boolean = ($booleankeyword_str) ? true : false;
        $keywords_str = ($boolean) ? $booleankeyword_str :  implode(' ', $keywords);

        $query = "SELECT productid, pr_url, pr_name, pr_variety, pr_vintage, pr_desc, pr_body, pr_language, pr_expirydate, pr_livedate, pr_category, ((MATCH(pr_name, pr_variety, pr_vintage) AGAINST (?" . ($boolean ? ' IN BOOLEAN MODE' : '') . ") * 0.2) + MATCH(pr_name, pr_desc, pr_body, pr_variety, pr_vintage) AGAINST (?" . ($boolean ? ' IN BOOLEAN MODE' : '') . ")) AS relevance ";
        $query .= "FROM {product} AS product ";
        $query .= "LEFT JOIN {language} AS language ON (product.pr_language = languageid) ";
        $query .= "WHERE ( (MATCH(pr_name, pr_variety, pr_vintage) AGAINST (?" . ($boolean ? ' IN BOOLEAN MODE' : '') . ") * 0.2) + MATCH(pr_name, pr_desc, pr_body, pr_variety, pr_vintage) AGAINST (?" . ($boolean ? ' IN BOOLEAN MODE' : '') . ")) > 0 ";
        $query .= ($language) ? "AND pr_language = '$language' " : '';
        $query .= "AND language.active = 'yes' ";
        $query .= "AND pr_livedate<" . time() . " AND (pr_expirydate<=0 OR pr_expirydate>" . time() . ") ";
        $query .= " ORDER BY relevance DESC LIMIT 100";

        $data = Jojo::selectQuery($query, array($keywords_str, $keywords_str, $keywords_str, $keywords_str));

        if (_MULTILANGUAGE) {
            global $page;
            $mldata = Jojo::getMultiLanguageData();
            $homes = $mldata['homes'];
        } else {
            $homes = array(1);
        }

        foreach ($data as $d) {
            $pagePermissions->getPermissions('product', $d['productid']);
            if (!$pagePermissions->hasPerm($_USERGROUPS, 'view')) {
                continue;
            }
            $result = array();
            $result['relevance'] = $d['relevance'];
            $result['title'] = $d['pr_name'] . ' ' . $d['pr_variety'] . ' ' . $d['pr_vintage'];
            $result['body'] = $d['pr_body'];
            $result['url'] = jojo_plugin_Jojo_cart_products_wine::getProductUrl($d['productid'], $d['pr_url'], $d['pr_name'], $d['pr_language'], $d['pr_category']);
            $result['absoluteurl'] = _SITEURL. '/' . $result['url'];
            $result['type'] = 'product';
            $results[] = $result;
        }


        /* Return results */
        return $results;
    }

     /**
     * Site Autotag
     *
     */
    static function autotag($results=false, $tag=false, $save=false)
    {
        /* Get search results for tag if there aren't any */
        if (!$save) {

            $searchtag = '"' . $tag . '"';
            $query = "SELECT productid, pr_url, pr_name, pr_variety, pr_vintage, pr_desc, pr_body, pr_language, pr_category, pr_tags, ( (MATCH(pr_name, pr_variety, pr_vintage) AGAINST (? IN BOOLEAN MODE) * 1.2) + (MATCH(pr_desc) AGAINST (? IN BOOLEAN MODE) * 0.6) + (MATCH(pr_body) AGAINST (? IN BOOLEAN MODE) * 0.6) ) AS relevance ";
            $query .= "FROM {product} AS product ";
            $query .= "WHERE (MATCH(pr_name, pr_variety, pr_vintage, pr_desc, pr_body) AGAINST (? IN BOOLEAN MODE)) > 0 ";
            $query .= "AND (`pr_tags` IS NULL OR `pr_tags` NOT REGEXP ? ) ";
            $query .= "ORDER BY relevance DESC LIMIT 100 ";

            $data = Jojo::selectQuery($query, array($searchtag, $searchtag, $searchtag, $searchtag, $tag));

            if (_MULTILANGUAGE) {
                global $page;
                $mldata = Jojo::getMultiLanguageData();
                $homes = $mldata['homes'];
            } else {
                $homes = array(1);
            }

            foreach ($data as $d) {
                $result = array();
                if ($d['relevance'] <= 1) continue;
                $result['relevance'] = $d['relevance'];
                $result['id'] = $d['productid'];
                $result['title'] = $d['pr_name'] . ' ' . $d['pr_variety'] . ' ' . $d['pr_vintage'];
                $result['body'] = $d['pr_body'];
                $result['tags'] = $d['pr_tags'];
                $result['url'] = jojo_plugin_Jojo_cart_products_wine::getProductUrl($d['productid'], $d['pr_url'], $d['pr_name'], $d['pr_language'], $d['pr_category']);
                $result['absoluteurl'] = _SITEURL. '/' . $result['url'];
                $result['type'] = 'product';
                $result['plugin'] = 'jojo_cart_products_wine';
                $results[] = $result;
            }


            /* Return results */
            return $results;
        /* Save tag into results if there are any */
        } else {
             foreach ($results as $res) {
                if ($res['plugin'] != 'jojo_cart_products_wine') continue;
                $productid = $res['id'];
                $tag = (preg_match_all("#(\w+)\s(\w+)#", $tag, $matches )) ? '"' . $matches[0][0] . '"' : $tag;
                $tags = (!empty($res['tags'])) ? $res['tags'] . " " . $tag : $tag;
                Jojo::insertQuery("UPDATE {product} SET pr_tags = ? WHERE productid = ?", array($tags, $productid));
            }
            return $results;
        }
    }

    /**
     * Remove Snip
     *
     * Removes any [[snip]] tags leftover in the content before outputting
     */
    static function removesnip($data)
    {
        $data = str_ireplace('[[snip]]','',$data);
        return $data;
    }



    /**
     * Get the url prefix for a particular part of this plugin
     */
    static function _getPrefix($for='product', $language=false, $categoryid=false) {
        static $_cache;
        $language = !empty($language) ? $language : Jojo::getOption('multilanguage-default', 'en');
        $_PCATEGORIES = (Jojo::getOption('product_enable_categories', 'no') == 'yes') ? true : false ;
        $categorydata =  ($_PCATEGORIES && !empty($categoryid)) ? Jojo::selectRow("SELECT `pc_url` FROM {productcategory} WHERE `productcategoryid` = '$categoryid';") : '';
        $category = ($_PCATEGORIES && !empty($categoryid)) ? $categorydata['pc_url'] : '';
        if (!isset($_cache[$for.$language.$category])) {
            $query = "SELECT pageid, pg_title, pg_url FROM {page} WHERE pg_link = ?";
            $query .= (_MULTILANGUAGE) ? " AND pg_language = '$language'" : '';
            $query .= (!empty($category)) ? " AND pg_url LIKE '%$category'": '';
            $values = array('jojo_plugin_Jojo_cart_products_wine');

            if ($values) {
                $res = Jojo::selectQuery($query, $values);
                if (isset($res[0])) {
                    $_cache[$for.$language.$category] = !empty($res[0]['pg_url']) ? $res[0]['pg_url'] : $res[0]['pageid'] . '/' . strtolower($res[0]['pg_title']);
                    return $_cache[$for.$language.$category];
                }
            }
            $_cache[$for.$language.$category] = '';

        }

        return $_cache[$for.$language.$category];
    }

    function getCorrectUrl()
    {
        global $page;
        $language  = $page->page['pg_language'];
        $pg_url    = $page->page['pg_url'];
        $productid = Jojo::getFormData('id',     0);
        $url       = Jojo::getFormData('url',    '');
        $action    = Jojo::getFormData('action', '');
        $pagenum   = Jojo::getFormData('pagenum', 1);
        $data = array('pr_category' => '');
        if (!empty($url) && Jojo::getOption('product_enable_categories', 'no') == 'yes') {
            $data = Jojo::selectRow("SELECT pr_category FROM {product} WHERE pr_url=?", $url);
        } elseif (!empty($productid) && Jojo::getOption('product_enable_categories', 'no') == 'yes') {
            $data = Jojo::selectRow("SELECT pr_category FROM {product} WHERE productid=?", $productid);
        }
        $categoryid = !empty($data['pr_category']) ? $data['pr_category'] : '';

        //$categoryid = (Jojo::getOption('product_enable_categories', 'no') == 'yes') ? Jojo::selectRow("SELECT productcategoryid FROM {productcategory} WHERE pc_url = '$pg_url'") : '';
        if ($pagenum[0] == 'p') {
            $pagenum = substr($pagenum, 1);
        }

        $correcturl = jojo_plugin_Jojo_cart_products_wine::getProductUrl($productid, $url, null, $language, $categoryid);
        if ($correcturl) {
            return _SITEURL . '/' . $correcturl;
        }

        /* product index with pagination */
        if ($pagenum > 1) return parent::getCorrectUrl() . 'p' . $pagenum . '/';

        /* product index - default */
        return parent::getCorrectUrl();
    }
}
