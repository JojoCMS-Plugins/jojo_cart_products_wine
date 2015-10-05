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
    static function getProductDetails($code)
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
        $product = Jojo::selectRow("SELECT productid FROM {product} WHERE pr_code = ? and status = 1", $pcode);

        /* attempt to match by id if code didn't work */
        if(!$product){
            $pcode = str_replace('product', '', $pcode);
            $product = Jojo::selectRow("SELECT productid FROM {product} WHERE productid = ? and status = 1", $pcode);
        }
         
        if(!$product){
            return false;
        }
        
        $product = self::getItemsById($product['productid']);
        
        if(!$product){
            return false;
        }

        $nameformat = isset($product['nameformat_cart']) && $product['nameformat_cart'] ? $product['nameformat_cart'] : '[brand] [region] [variety] [vintage]';
        $formattedname = self::formatname($nameformat, $product);

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

        /* prepare return array - key names are important */
        $data = array('id'             => $product['pr_code'] . ( $case ? '_case' : '' ),
                      'name'           => $formattedname . ( $case ? ' (case of ' . $product['pr_casesize'] . ')'  : '' ),
                      'brand'          => $product['name'],
                      'variant'        => (isset($product['pr_region']) && $product['region'] ? $product['region'] . ' ' : '') . $product['variety'] . ' '. $product['vintage'],
		              'productorder'   => $product['pr_display_order'],
                      'description'    => $product['pr_desc'],
                      'image'          => $image,
                      'price'          => ( $case ? $product['pr_caseprice'] : $product['pr_price'] ),
                      'case'           => $case,
                      'caseprice'      => $product['pr_caseprice'],
                      'casesize'       => $product['pr_casesize'],
		              'currency'       => $product['pr_currency'],
		              'category'       => $product['pagetitle'],
		              'categoryurl'    => $product['pageurl'],
		              'categoryorder'  => $product['pc_display_order'],
                      'code'           => $product['pr_code'],
                      'quantity_fixed' => $fixed,
                      'freight'        => $freight->export(),
                      'url'            => self::getProductUrl($product['productid'])
                      );

        return $data;
     }

    /* a  filter for sorting items in the shopping cart */
    static function sort_cart_items($items) {
        uasort($items, array('jojo_plugin_jojo_cart_products_wine', '_compare'));
        return $items;
    }

    private static function _compare($a, $b)
    {
        if ($a['categoryorder'] != $b['categoryorder']) {
            return ($a['categoryorder'] < $b['categoryorder']) ? -1 : 1;
        } elseif ($a['productorder'] != $b['productorder']) {
            return ($a['productorder'] < $b['productorder']) ? -1 : 1;
        } elseif ($a['name'] != $b['name']) {
            return ($a['name'] < $b['name']) ? -1 : 1;
        } elseif ($a['case'] != $b['case']) {
            return (!$a['case']) ? -1 : 1;
        }
        return 0;
    }

    /* a content filter for inserting buy now buttons */
    static function buyNow($content)
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

  
/*
* Core
*/

    /* Get products  */
    static function getProducts($num=false, $start = 0, $categoryid='all', $sortby='pr_display_order, pr_name, pr_variety', $exclude=false, $include=false) {
        global $page;
        if ($categoryid == 'all' && $include != 'alllanguages') {
            $categoryid = array();
            $sectionpages = self::getPluginPages('', $page->page['root']);
            foreach ($sectionpages as $s) {
                $categoryid[] = $s['productcategoryid'];
            }
        }
        if (is_array($categoryid)) {
             $categoryquery = " AND pr_category IN ('" . implode("','", $categoryid) . "')";
        } else {
            $categoryquery = is_numeric($categoryid) ? " AND pr_category = '$categoryid'" : '';
        }
        /* if calling page is an product, Get current product, exclude from the list and up the limit by one */
        $exclude = ($exclude && Jojo::getOption('product_side_exclude_current', 'no')=='yes' && $page->page['pg_link']=='jojo_plugin_jojo_cart_products_wine' && (Jojo::getFormData('id') || Jojo::getFormData('url'))) ? (Jojo::getFormData('url') ? Jojo::getFormData('url') : Jojo::getFormData('id')) : '';
        if ($num && $exclude) $num++;
        $shownumcomments = (boolean)(class_exists('Jojo_Plugin_Jojo_comment') && Jojo::getOption('comment_show_num', 'no') == 'yes');
        $query  = "SELECT pr.*, c.*, p.pageid, pg_menutitle, pg_title, pg_url, pg_status, pg_livedate, pg_expirydate";
        $query .= $shownumcomments ? ", COUNT(com.itemid) AS numcomments" : '';
        $query .= " FROM {product} pr";
        $query .= " LEFT JOIN {productcategory} c ON (pr.pr_category=c.productcategoryid) LEFT JOIN {page} p ON (c.pageid=p.pageid)";
        $query .= $shownumcomments ? " LEFT JOIN {comment} com ON (com.itemid = pr.productid AND com.plugin = 'jojo_cart_products_wine')" : '';
        $query .= " WHERE pr.status=1" . $categoryquery;
        $query .= $shownumcomments ? " GROUP BY productid" : '';
        $query .= $num ? " ORDER BY $sortby LIMIT $start,$num" : '';
        $products = Jojo::selectQuery($query);
        $products = self::cleanItems($products, $exclude, $include);
        if (!$num)  $products = self::sortItems($products, $sortby);
        return $products;
    }

     /* get items by id - accepts either an array of ids returning a results array, or a single id returning a single result  */
    static function getItemsById($ids = false, $sortby='pr_display_order, pr_name, pr_variety', $include=false) {
        $query  = "SELECT pr.*, c.*, p.pageid, pg_menutitle, pg_title, pg_url, pg_status, pg_livedate, pg_expirydate";
        $query .= " FROM {product} pr";
        $query .= " LEFT JOIN {productcategory} c ON (pr.pr_category=c.productcategoryid) LEFT JOIN {page} p ON (c.pageid=p.pageid)";
        $query .=  is_array($ids) ? " WHERE productid IN ('". implode("',' ", $ids) . "')" : " WHERE productid='" . $ids . "'";
        $items = Jojo::selectQuery($query);
        $items = self::cleanItems($items, '', $include);
        if ($items) {
            $items = is_array($ids) ? self::sortItems($items, $sortby) : $items[0];
            return $items;
        } else {
            return false;
        }
    }

    /* clean items for output */
    static function cleanItems($items, $exclude=false, $include=false) {
        global $_USERID;
        $now    = time();
        if ($_USERID) {
            /* Get user pricing */
            $userPricing = Jojo::selectAssoc('SELECT productid as id, p.* FROM {product_user_price} p WHERE userid = ?', array($_USERID));
        }
        foreach ($items as $k=>&$i){
            $pagedata = Jojo_Plugin_Core::cleanItems(array($i), $include);
            if (!$pagedata || $i['pr_livedate']>$now || (!empty($i['pr_expirydate']) && $i['pr_expirydate']<$now) || (!empty($i['productid']) && $i['productid']==$exclude)  || (!empty($i['pr_url']) && $i['pr_url']==$exclude)) {
                unset($items[$k]);
                continue;
            }
            $productid = $i['productid'];
             /* Logged in user? */
            if ($_USERID && isset($userPricing[$productid])) {
                /* Look for per user availability */
                if ($userPricing[$productid]['case_price'] == 'NA') {
                    /* User can't purchase this varient */
                    unset($items[$k]);
                    continue;
                } elseif ($userPricing) {
                  $i['pr_caseprice'] = $userPricing[$productid]['case_price'] ? $userPricing[$productid]['case_price'] : $i['pr_caseprice'];
                  $i['pr_price'] = $userPricing[$productid]['bottle_price'] ? $userPricing[$productid]['bottle_price'] : $i['pr_price'];
                }
            } else {
                if ($i['pr_caseprice'] == 'NA' && Jojo::getOption('product_show_NA_products', 'no')=='no') {
                    /* Casual users can't purchase this product */
                    unset($items[$k]);
                    continue;
                }
            }
            $i['pagetitle'] = $pagedata[0]['title'];
            $i['pageurl']   = $pagedata[0]['url'];
            $i['id']        = $productid;
            $i['pr_code']   = $i['pr_code'] ? $i['pr_code'] : 'product' . $productid;
            $i['name']      = htmlspecialchars(trim($i['pr_name']), ENT_COMPAT, 'UTF-8', false);
            $i['region']    = isset($i['pr_region']) ? htmlspecialchars(trim($i['pr_region']), ENT_COMPAT, 'UTF-8', false) : '';
            $i['variety']   = htmlspecialchars(htmlentities(trim($i['pr_variety'])), ENT_COMPAT, 'UTF-8', false);
            $i['vintage']   = htmlspecialchars(trim($i['pr_vintage']), ENT_COMPAT, 'UTF-8', false);
            $i['designation']   = htmlspecialchars(trim($i['pr_designation']), ENT_COMPAT, 'UTF-8', false);
            $i['seotitle']        = $i['name'] . ' ' . ($i['region'] ? $i['region'] . ' ' : '') . $i['variety'] . ' ' . $i['vintage'];
            $nameformat = isset($i['nameformat_index']) && $i['nameformat_index'] ? $i['nameformat_index'] : '[brand] [region] [variety] [vintage]';
            $i['title']  = self::formatname($nameformat, $i);
            $i['title_novintage']  = self::formatname($nameformat, $i, true);
            // Snip for the index description
            $i['bodysnip'] = Jojo::iExplode('[[snip]]', $i['pr_body']);
            $i['bodysnip'] = array_shift($i['bodysnip']);
            /* Strip all tags and template include code ie [[ ]] */
            $i['bodysnip'] = strpos($i['bodysnip'], '[[')!==false ? preg_replace('/\[\[.*?\]\]/', '',  $i['bodysnip']) : $i['bodysnip'];
            $i['bodyplain'] = trim(strip_tags($i['bodysnip']));
            $i['description'] = $i['pr_desc'] ? htmlspecialchars($i['pr_desc'], ENT_COMPAT, 'UTF-8', false) : (strlen($i['bodyplain']) >400 ?  substr($mbody=wordwrap($i['bodyplain'], 400, '$$'), 0, strpos($mbody,'$$')) : $i['bodyplain']);
            $i['snippet']       = isset($i['snippet']) ? $i['snippet'] : '400';
            $i['thumbnail']       = isset($i['thumbnail']) ? $i['thumbnail'] : 's150';
            $i['mainimage']       = isset($i['mainimage']) ? $i['mainimage'] : 'v60000';
            $i['readmore'] = isset($i['readmore']) ? str_replace(' ', '&nbsp;', htmlspecialchars($i['readmore'], ENT_COMPAT, 'UTF-8', false)) : '&gt;&nbsp;read&nbsp;more';
            $i['date']       = $i['pr_date'];
            $i['datefriendly'] = isset($i['dateformat']) && !empty($i['dateformat']) ? strftime($i['dateformat'], $i['pr_date']) :  Jojo::formatTimestamp($i['pr_date'], "medium");
            $i['image'] = !empty($i['pr_image']) ? 'products/' . urlencode($i['pr_image']) : '';
            $i['url']          = self::getProductUrl($i['productid'], $i['pr_url'], $i['title'], $i['pageid'], $i['pr_category']);
            $i['plugin']     = 'jojo_cart_products_wine';
            /* Get Awards if used */
            if (class_exists('Jojo_Plugin_Jojo_cart_product_award')) {
               $i['awards'] = Jojo_Plugin_Jojo_cart_product_award::getProductAwards('', '', $i['id'], '', false);
            }
            unset($items[$k]['pr_bbbody']);
        }
        $items = array_values($items);
        return $items;
    }

    /* sort items for output */
    static function sortItems($items, $sortby=false) {
        if ($sortby) {
            $order = "name";
            $reverse = false;
            switch ($sortby) {
              case "name":
                $order="name";
                break;
              case "order":
                $order="order";
                usort($items, array('Jojo_Plugin_Jojo_cart_products_wine','namesort'));
                break;
            }
            usort($items, array('Jojo_Plugin_Jojo_cart_products_wine', $order . 'sort'));
            $items = $reverse ? array_reverse($items) : $items;
        }
        return $items;
    }

    private static function namesort($a, $b)
    {
         if ($a['title']) {
            if ($a['title_novintage']==$b['title_novintage']) {
                return ($a['pr_vintage'] > $b['pr_vintage']) ? -1 : 1;
            }
            return strcmp($a['title'],$b['title']);
        }
    }

    private static function ordersort($a, $b)
    {
        if ($a['pr_display_order'] == $b['pr_display_order']) {
            return 0;
        }
        return ($a['pr_display_order'] < $b['pr_display_order']) ? -1 : 1;
    }

    private static function formatname($format='[brand] [region] [variety] [vintage]', $item=false, $novintage=false)
    {
        if(!$item) return false;
        $namefilters = array(
                '[brand]',
                '[region]',
                '[variety]',
                '[vintage]',
                '[designation]'
               );
        $replace = array(
                $item['name'],
                $item['region'],
                $item['variety'],
                ($novintage ? '' : $item['vintage']),
                $item['designation']
                );
        $formattedname = str_replace($namefilters, $replace, $format);
        return $formattedname;
    }

    /*
     * calculates the URL for the product - requires the product ID, but works without a query if given the URL or title from a previous query
     *
     */
    static function getProductUrl($id=false, $url=false, $title=false, $pageid=false, $category=false )
    {
        $pageprefix = Jojo::getPageUrlPrefix($pageid);

        /* URL specified */
        if (!empty($url)) {
            return $pageprefix . self::_getPrefix($category) . '/' . $url . '/';
         }
        /* ID + title specified */
        if ($id && !empty($title)) {
            return $pageprefix . self::_getPrefix($category) . '/' . $id . '/' .  Jojo::cleanURL($title) . '/';
        }
        /* use the ID to find either the URL or title */
        if ($id) {
            $product = Jojo::selectRow("SELECT pr_url, pr_name, pr_variety, pr_vintage, pr_category, p.pageid FROM {product} pr LEFT JOIN {productcategory} c ON (pr.pr_category=c.productcategoryid) LEFT JOIN {page} p ON (c.pageid=p.pageid) WHERE productid = ?", array($id));
             if ($product) {
                $title = $product['pr_name'] . ' ' . $product['pr_variety'] . ' ' . $product['pr_vintage'];
                return self::getProductUrl($id, $product['pr_url'], $title, $product['pageid'], $product['pr_category']);
            }
         }
        /* No product matching the ID supplied or no ID supplied */
        return false;
    }


    function _getContent()
    {
        global $smarty;
        $content = array();
        $pageid = $this->page['pageid'];
        $pageprefix = Jojo::getPageUrlPrefix($pageid);
        $smarty->assign('multilangstring', $pageprefix);

        if (class_exists('Jojo_Plugin_Jojo_comment') && Jojo::getOption('comment_subscriptions', 'no') == 'yes') {
            Jojo_Plugin_Jojo_comment::processSubscriptionEmails();
        }

        /* Are we looking at an product or the index? */
        $productid = Jojo::getFormData('id',        0);
        $url       = Jojo::getFormData('url',      '');
        $action    = Jojo::getFormData('action',   '');
        $categorydata =  Jojo::selectRow("SELECT * FROM {productcategory} WHERE pageid = ?", $pageid);
        $categorydata['type'] = isset($categorydata['type']) ? $categorydata['type'] : 'normal';
        if ($categorydata['type']=='index') {
            $categoryid = 'all';
        } elseif ($categorydata['type']=='parent') {
            $childcategories = Jojo::selectQuery("SELECT * FROM {page} p  LEFT JOIN {productcategory} c ON (c.pageid=p.pageid) WHERE pg_parent = ? AND pg_link = 'jojo_plugin_jojo_cart_products_wine'", $pageid);
            foreach ($childcategories as $c) {
                $categoryid[] = $c['productcategoryid'];
            }
            $categoryid[] = $categorydata['productcategoryid'];
            $smarty->assign('childcategories', $childcategories);
        } else {
            $categoryid = $categorydata['productcategoryid'];
        }
        $sortby = $categorydata ? $categorydata['sortby'] : '';

        /* handle unsubscribes */
        if ($action == 'unsubscribe') {
            $code      = Jojo::getFormData('code',      '');
            $productid = Jojo::getFormData('productid', '');
            if (Jojo_Plugin_Jojo_comment::removeSubscriptionByCode($code, $productid, 'jojo_cart_products_wine')) {
                $content['content'] = 'Subscription removed.<br />';
            } else {
                $content['content'] = 'This unsubscribe link is inactive, or you have already been unsubscribed.<br />';
            }
            $content['content'] .= 'Return to <a href="' . self::getProductUrl($productid) . '">product</a>.';
            return $content;
        }

        $products = self::getProducts('', '', $categoryid, $sortby, $exclude=false, $include='showhidden');

        if ($productid || !empty($url)) {
            /* find the current, next and previous items */
            $product = array();
            $prevproduct = array();
            $nextproduct = array();
            $next = false;
            foreach ($products as $a) {
                if (!empty($url) && $url==$a['pr_url']) {
                    $product = $a;
                    $next = true;
               } elseif ($productid==$a['productid']) {
                    $product = $a;
                    $next = true;
                } elseif ($next==true) {
                    $nextproduct = $a;
                     break;
                } else {
                    $prevproduct = $a;
                }
            }

            /* If the item can't be found, return a 404 */
            if (!$product) {
                include(_BASEPLUGINDIR . '/jojo_core/404.php');
                exit;
            }

            if ($modproduct = Jojo::runHook('modify_product', array($product))) {
                $product = $modproduct;
            }
            /* Get the specific product */
            $productid = $product['productid'];
            $product['pr_datefriendly'] = Jojo::mysql2date($product['pr_date'], "long");
            $nameformat = isset($product['nameformat']) && $product['nameformat'] ? $product['nameformat'] : '[brand] [region] [variety] [vintage]';
            $product['title'] = self::formatname($nameformat, $product);

            /* calculate the next and previous products */
            if (Jojo::getOption('product_next_prev') == 'yes') {
                if (!empty($nextproduct)) {
                    $smarty->assign('nextproduct', $nextproduct);
                }
                if (!empty($prevproduct)) {
                    $smarty->assign('prevproduct', $prevproduct);
                }
            }

            /* Get tags if used */
            if (class_exists('Jojo_Plugin_Jojo_Tags')) {
                /* Split up tags for display */
                $tags = Jojo_Plugin_Jojo_Tags::getTags('jojo_cart_products_wine', $productid);
                $smarty->assign('tags', $tags);

                /* generate tag cloud of tags belonging to this product */
                $product_tag_cloud_minimum = Jojo::getOption('product_tag_cloud_minimum');
                if (!empty($product_tag_cloud_minimum) && ($product_tag_cloud_minimum < count($tags))) {
                    $itemcloud = Jojo_Plugin_Jojo_Tags::getTagCloud('', $tags);
                    $smarty->assign('itemcloud', $itemcloud);
                }
               /* get related products if tags plugin installed and option enabled */
                $numrelated = Jojo::getOption('product_num_related');
                if ($numrelated) {
                    $related = Jojo_Plugin_Jojo_Tags::getRelated('jojo_cart_products_wine', $productid, $numrelated, 'jojo_cart_products_wine'); //set the last argument to 'jojo_cart_products_wine' to restrict results to only products
                    $smarty->assign('related', $related);
                }
            }

            /* Get Comments if used */
            if (class_exists('Jojo_Plugin_Jojo_comment') && (!isset($product['comments']) || $product['comments']) ) {
                /* Was a comment submitted? */
                if (Jojo::getFormData('comment', false)) {
                    Jojo_Plugin_Jojo_comment::postComment($product);
                }
               $productcommentsenabled = (boolean)(isset($product['pr_comments']) && $product['pr_comments']=='yes');
               $commenthtml = Jojo_Plugin_Jojo_comment::getComments($product['id'], $product['plugin'], $product['pageid'], $productcommentsenabled);
               $smarty->assign('commenthtml', $commenthtml);
            }

            /* Get other Vintages */
            $othervintages = Jojo::selectQuery("SELECT productid FROM {product} WHERE pr_name = ? AND pr_variety = ? AND productid != ? AND pr_category=? ORDER BY pr_display_order", array($product['pr_name'], $product['pr_variety'], $productid, $product['pr_category']));
            if ($othervintages) {
                foreach ($othervintages as &$o) {
                    $o = self::getItemsById($o['productid']);
                }
            }
            $smarty->assign('othervintages', $othervintages);

           /* Add breadcrumb */
            $breadcrumbs                      = $this->_getBreadCrumbs();
            $breadcrumb                       = array();
            $breadcrumb['name']               = $product['title'];
            $breadcrumb['rollover']           = $product['description'];
            $breadcrumb['url']                = $product['url'];
            $breadcrumbs[count($breadcrumbs)] = $breadcrumb;

            /* Assign product content to Smarty */
            $smarty->assign('wine', $product);

            /* Prepare fields for display */
            if (isset($product['pr_htmllang'])) {
                // Override the language setting on this page if necessary.
                $content['pg_htmllang'] = $product['pr_htmllang'];
                $smarty->assign('pg_htmllang', $product['pr_htmllang']);
            }
 
            $content['title']            = $product['title'];
            $content['seotitle']         = Jojo::either($product['seotitle'], $product['title']);
            $content['breadcrumbs']      = $breadcrumbs;

            $meta_description_template = Jojo::getOption('product_meta_description', '[title] - [body]... ');
            $metafilters = array(
                    '[title]',
                    '[site]',
                    '[body]'
                   );
            $metafilterreplace = array(
                    $product['title'],
                    _SITETITLE,
                    $product['description'],
                    );
                    $content['meta_description'] = str_replace($metafilters, $metafilterreplace, $meta_description_template);

            $content['metadescription']  = $content['meta_description'];
            if ((boolean)(Jojo::getOption('ogdata', 'no')=='yes')) {
                $content['ogtags']['description'] = $product['description'];
                $content['ogtags']['image'] = $product['image'] ? _SITEURL .  '/images/' . ($product['thumbnail'] ? $product['thumbnail'] : 's150') . '/' . $product['image'] : '';
                $content['ogtags']['title'] = $product['seotitle'];
            }
            $content['content'] = $smarty->fetch('jojo_cart_products_wine.tpl');

        } else {

            /* Product index section */
            $pagenum = Jojo::getFormData('pagenum', 1);
            if ($pagenum[0] == 'p') {
                $pagenum = substr($pagenum, 1);
            }

            /* get number of products for pagination */
            $productsperpage = Jojo::getOption('productsperpage', 40);
            $start = ($productsperpage * ($pagenum-1));
            $numproducts = count($products);
            $numpages = ceil($numproducts / $productsperpage);
            /* calculate pagination */
            if ($numpages == 1) {
                $pagination = '';
            } elseif ($numpages == 2 && $pagenum == 2) {
                $pagination = sprintf('<a href="%s/p1/">previous...</a>', $pageprefix . self::_getPrefix($categorydata['productcategoryid']) );
            } elseif ($numpages == 2 && $pagenum == 1) {
                $pagination = sprintf('<a href="%s/p2/">more...</a>', $pageprefix . self::_getPrefix($categorydata['productcategoryid']) );
            } else {
                $pagination = '<ul>';
                for ($p=1;$p<=$numpages;$p++) {
                    $url = $pageprefix . self::_getPrefix($categorydata['productcategoryid']) . '/';
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
            $smarty->assign('pagination', $pagination);
            $smarty->assign('pagenum', $pagenum);

            /* clear the meta description to avoid duplicate content issues */
            $content['metadescription'] = '';

            /* get product content and assign to Smarty */
            $products = array_slice($products, $start, $productsperpage);
            
            /* Get Awards if used */
            foreach ($products as &$p) {
                if (class_exists('Jojo_Plugin_Jojo_cart_product_award')) {
                   $p['awards'] = Jojo_Plugin_Jojo_cart_product_award::getProductAwards('', '', $p['id']);
                }
            }

            $smarty->assign('wines', $products);
            $content['content'] = $smarty->fetch('jojo_cart_products_wine_index.tpl');

       }
        return $content;
    }

    static function getPluginPages($for='', $section=0)
    {
        global $sectiondata;
        $cacheKey = 'products';
        /* Have we got a cached result? */
        static $_pluginpages;
        if (isset($_pluginpages[$cacheKey])) {
            return $_pluginpages[$cacheKey];
        }
        /* Cache some stuff */
        $items =  Jojo::selectAssoc("SELECT p.pageid AS id, c.*, p.*  FROM {productcategory} c LEFT JOIN {page} p ON (c.pageid=p.pageid) ORDER BY pg_parent, pg_order");
        // use core function to clean out any pages based on permission, status, expiry etc
        $items =  Jojo_Plugin_Core::cleanItems($items, $for);
        foreach ($items as $k=>$i){
            if ($section && $section != $i['root']) {
                unset($items[$k]);
                continue;
            }
        }
        if ($items) {
            $_pluginpages[$cacheKey] = $items;
        } else {
            $_pluginpages[$cacheKey] = array();
        }
        return $_pluginpages[$cacheKey];
    }

    public static function sitemap($sitemap)
    {
        global $page;
        /* See if we have any product sections to display and find all of them */
        $indexes =  self::getPluginPages('sitemap');
        if (!count($indexes)) {
            return $sitemap;
        }

        if (Jojo::getOption('product_inplacesitemap', 'separate') == 'separate') {
            /* Remove any existing links to the products section from the page listing on the sitemap */
            foreach($sitemap as $j => $section) {
                $sitemap[$j]['tree'] = self::_sitemapRemoveSelf($section['tree']);
            }
            $_INPLACE = false;
        } else {
            $_INPLACE = true;
        }

        $now = strtotime('now');
        $limit = 15;
        $productsperpage = Jojo::getOption('productsperpage', 40);
         /* Make sitemap trees for each products instance found */
        foreach($indexes as $k => $i){
            $categoryid = $i['productcategoryid'];
            $sortby = $i['sortby'];

            /* Create tree and add index and feed links at the top */
            $producttree = new hktree();
            $indexurl = $i['url'];
            if ($_INPLACE) {
                $parent = 0;
            } else {
               $producttree->addNode('index', 0, $i['title'], $indexurl);
               $parent = 'index';
            }

            $products = self::getProducts('', '', $categoryid, $sortby);
            $n = count($products);

            /* Trim items down to first page and add to tree*/
            $products = array_slice($products, 0, $productsperpage);
            foreach ($products as $a) {
                $a['title'] = self::formatname($a['nameformat'], $a);
                $producttree->addNode($a['id'], $parent, $a['title'], $a['url']);
            }

            /* Get number of pages for pagination */
            $numpages = ceil($n / $productsperpage);
            /* calculate pagination */
            if ($numpages > 1) {
                for ($p=2; $p <= $numpages; $p++) {
                    $url = $indexurl .'p' . $p .'/';
                    $nodetitle = $i['title'] . ' (p.' . $p . ')';
                    $producttree->addNode('p' . $p, $parent, $nodetitle, $url);
                }
            }

            /* Add to the sitemap array */
            if ($_INPLACE) {
                /* Add inplace */
                $url = $i['url'];
                $sitemap['pages']['tree'] = self::_sitemapAddInplace($sitemap['pages']['tree'], $producttree->asArray(), $url);
            } else {
                $mldata = Jojo::getMultiLanguageData();
                /* Add to the end */
                $sitemap["products$k"] = array(
                    'title' => $i['title'] . ( _MULTILANGUAGE ? ' (' . ucfirst($mldata['sectiondata'][$i['root']]['name']) . ')' : ''),
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
                $sitemap[$k]['children'] = isset($sitemap[$k]['children']) ? array_merge($toadd, $sitemap[$k]['children']): $toadd;
            } elseif (isset($sitemap[$k]['children'])) {
                $sitemap[$k]['children'] = self::_sitemapAddInplace($t['children'], $toadd, $url);
            }
        }
        return $sitemap;
    }

    static function _sitemapRemoveSelf($tree)
    {
        static $urls;

        if (!is_array($urls)) {
            $urls = array();
            $indexes =  self::getPluginPages('sitemap');
            if (count($indexes)==0) {
               return $tree;
            }
            foreach($indexes as $key => $i){
                $urls[] = $i['url'];
            }
        }

        foreach ($tree as $k =>$t) {
            if (in_array($t['url'], $urls)) {
                unset($tree[$k]);
            } else {
                $tree[$k]['children'] = self::_sitemapRemoveSelf($t['children']);
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
        $products = self::getProducts('', '', 'all', '', '', 'alllanguages');
        $now = time();
        $indexes =  self::getPluginPages('xmlsitemap');
        $ids=array();
        foreach ($indexes as $i) {
            $ids[$i['productcategoryid']] = true;
        }
        /* Add products to sitemap */
        foreach($products as $k => $a) {
            // strip out products from expired pages
            if (!isset($ids[$a['pr_category']])) {
                unset($products[$k]);
                continue;
            }
            $url = _SITEURL . '/'. $a['url'];
            $lastmod = $a['date'];
            $priority = 0.6;
            $changefreq = '';
            $sitemap[$url] = array($url, $lastmod, $changefreq, $priority);
        }
        /* Return sitemap */
        return $sitemap;
    }

    /**
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
    static function _getPrefix($categoryid=false) {
        $for='product';
        $cacheKey = $for;
        $cacheKey .= ($categoryid) ? $categoryid : 'false';

        /* Have we got a cached result? */
        static $_cache;
        if (isset($_cache[$cacheKey])) {
            return $_cache[$cacheKey];
        }

        /* Cache some stuff */
        $res = Jojo::selectRow("SELECT p.pageid, pg_title, pg_url FROM {page} p LEFT JOIN {productcategory} c ON (c.pageid=p.pageid) WHERE `productcategoryid` = '$categoryid'");
        if ($res) {
            $_cache[$cacheKey] = !empty($res['pg_url']) ? $res['pg_url'] : $res['pageid'] . '/' . $res['pg_title'];
        } else {
            $_cache[$cacheKey] = '';
        }
        return $_cache[$cacheKey];
    }

    static function getPrefixById($id=false) {
        if ($id) {
            $data = Jojo::selectRow("SELECT productcategoryid, pageid FROM {product} LEFT JOIN {productcategory} ON (pr_category=productcategoryid) WHERE productid = ?", array($id));
            if ($data) {
                $fullprefix = Jojo::getPageUrlPrefix($data['pageid']) . self::_getPrefix($data['productcategoryid']);
                return $fullprefix;
            }
        }
        return false;
    }

    function getCorrectUrl()
    {
        global $page;
        $pageid  = $page->page['pageid'];
        $id = Jojo::getFormData('id',     0);
        $url       = Jojo::getFormData('url',    '');
        $action    = Jojo::getFormData('action', '');
        $pagenum   = Jojo::getFormData('pagenum', 1);

        $data = Jojo::selectRow("SELECT productcategoryid FROM {productcategory} WHERE pageid=?", $pageid);
        $categoryid = !empty($data['productcategoryid']) ? $data['productcategoryid'] : '';

        if ($pagenum[0] == 'p') {
            $pagenum = substr($pagenum, 1);
        }

        /* unsubscribing */
        if ($action == 'unsubscribe') {
            return _PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        $correcturl = self::getProductUrl($id, $url, null, $pageid, $categoryid);

        if ($correcturl) {
            return _SITEURL . '/' . $correcturl;
        }

        /* index with pagination */
        if ($pagenum > 1) return parent::getCorrectUrl() . 'p' . $pagenum . '/';

        /* index - default */
        return parent::getCorrectUrl();
    }

    static public function isUrl($uri)
    {
        $prefix = false;
        $getvars = array();
        /* Check the suffix matches and extract the prefix */
        if (preg_match('#^(.+)/unsubscribe/([0-9]+)/([a-zA-Z0-9]{16})$#', $uri, $matches)) {
            /* "$prefix/[action:unsubscribe]/[productid:integer]/[code:[a-zA-Z0-9]{16}]" eg "products/unsubscribe/34/7MztlFyWDEKiSoB1/" */
            $prefix = $matches[1];
            $getvars = array(
                        'action' => 'unsubscribe',
                        'productid' => $matches[2],
                        'code' => $matches[3]
                        );
        /* Check for standard plugin url format matches */
        } elseif ($uribits = parent::isPluginUrl($uri)) {
            $prefix = $uribits['prefix'];
            $getvars = $uribits['getvars'];
        } else {
            return false;
        }
        /* Check the prefix matches */
        if ($res = self::checkPrefix($prefix)) {
            /* If full uri matches a prefix it's an index page so ignore it and let the page plugin handle it */
            if (self::checkPrefix(trim($uri, '/'))) {
                return false;
            }

            /* The prefix is good, pass through uri parts */
            foreach($getvars as $k => $v) {
                $_GET[$k] = $v;
            }
            return true;
        }
        return false;
    }

    /**
     * Check if a prefix is an product prefix
     */
    static public function checkPrefix($prefix)
    {
        static $_prefixes, $categories;
        if (!isset($categories)) {
            /* Initialise cache */
            $categories = array(false);
            $categories = array_merge($categories, Jojo::selectAssoc("SELECT productcategoryid, productcategoryid as productcategoryid2 FROM {productcategory}"));
            $_prefixes = array();
        }
        /* Check if it's in the cache */
        if (isset($_prefixes[$prefix])) {
            return $_prefixes[$prefix];
        }
        /* Check everything */
        foreach($categories as $category) {
            $testPrefix = self::_getPrefix($category);
            $_prefixes[$testPrefix] = true;
            if ($testPrefix == $prefix) {
                /* The prefix is good */
                return true;
            }
        }
        /* Didn't match */
        $_prefixes[$testPrefix] = false;
        return false;
    }

    static function getNavItems($pageid, $selected=false)
    {
        $nav = array();
        $section = Jojo::getSectionRoot($pageid);
        $productpages = self::getPluginPages('', $section);
        if (!$productpages) return $nav;
        $categoryid = $productpages[$pageid]['productcategoryid'];
        $sortby = $productpages[$pageid]['sortby'];
        $items = isset($productpages[$pageid]['addtonav']) && $productpages[$pageid]['addtonav'] ? self::getProducts('', '', $categoryid, $sortby) : '';
        if (!$items) return $nav;
        //if the page is currently selected, check to see if an item has been called
        if ($selected) {
            $id = Jojo::getFormData('id', 0);
            $url = Jojo::getFormData('url', '');
        }
        $nameformat = isset($productpages[$pageid]['nameformat_menu']) && $productpages[$pageid]['nameformat_menu'] ? $productpages[$pageid]['nameformat_menu'] : '[brand] [region] [variety] [vintage]';
        $dupenamecheck = '';
        foreach ($items as $i) {
            $formattedname = self::formatname($nameformat, $i);
            if ($formattedname == $dupenamecheck) continue;
            $dupenamecheck = $formattedname;
            $nav[$i['id']]['url'] = _SITEURL . '/' . $i['url'];
            $nav[$i['id']]['title'] = ($i['seotitle'] ? $i['seotitle'] : ($i['pr_desc'] ? htmlspecialchars($i['pr_desc'], ENT_COMPAT,'UTF-8',false) : $i['title']));
            $nav[$i['id']]['label'] = $formattedname;
            $nav[$i['id']]['selected'] = (boolean)($selected && (($id && $id== $i['id']) ||(!empty($url) && $i['url'] == $url)));
        }
        return $nav;
    }
    static function admin_action_after_save_product($id)
    {
        $product = self::getItemsById($id);
        if (empty($product['pr_htmllang'])) {
            $mldata = Jojo::getMultiLanguageData();
            $htmllanguage =  $mldata['sectiondata'][Jojo::getSectionRoot($product['pageid'])]['lc_defaultlang'];
            Jojo::updateQuery("UPDATE {product} SET `pr_htmllang`=? WHERE `productid`=?", array($htmllanguage, $id));
        }
        if (empty($product['pr_url'])) {
            $url = Jojo::cleanURL(str_replace('ü', 'u', $product['title']));
            Jojo::updateQuery("UPDATE {product} SET `pr_url`=? WHERE `productid`=?", array($url, $id));
        }
        
        Jojo::updateQuery("UPDATE {option} SET `op_value`=? WHERE `op_name`='product_last_updated'", time());
        return true;
    }

    // Sync the articategory data over to the page table
    static function admin_action_after_save_productcategory($id) {
        if (!Jojo::getFormData('fm_pageid', 0)) {
            // no pageid set for this category (either it's a new category or maybe the original page was deleted)
            self::sync_category_to_page($id);
       }
    }

    // Sync the category data over from the page table
    static function admin_action_after_save_page($id) {
        if (strtolower(Jojo::getFormData('fm_pg_link',    ''))=='jojo_plugin_jojo_cart_products_wine') {
           self::sync_page_to_category($id);
       }
    }

    static function sync_category_to_page($catid) {
        // add a new hidden page for this category and make up a title
            $newpageid = Jojo::insertQuery(
            "INSERT INTO {page} SET pg_title = ?, pg_link = ?, pg_url = ?, pg_parent = ?, pg_status = ?",
            array(
                'Orphaned Products',  // Title
                'jojo_plugin_jojo_cart_products_wine',  // Link
                'orphaned-products',  // URL
                0,  // Parent - don't do anything smart, just put it at the top level for now
                'hidden' // hide new page so it doesn't show up on the live site until it's been given a proper title and url
            )
        );
        // If we successfully added the page, update the category with the new pageid
        if ($newpageid) {
            jojo::updateQuery(
                "UPDATE {productcategory} SET pageid = ? WHERE productcategoryid = ?",
                array(
                    $newpageid,
                    $catid
                )
            );
       }
       return true;
    }

    static function sync_page_to_category($pageid) {
        // Get the list of categories by page id
        $categories = jojo::selectAssoc("SELECT pageid AS id, pageid FROM {productcategory}");
        // no category for this page id
        if (!count($categories) || !isset($categories[$pageid])) {
            jojo::insertQuery("INSERT INTO {productcategory} (pageid) VALUES ('$pageid')");
        }
        return true;
    }

    /**
     * Site Search
     */
    static function search($results, $keywords, $language, $booleankeyword_str=false)
    {
        $searchfields = array(
            'plugin' => 'jojo_cart_products_wine',
            'table' => 'product',
            'idfield' => 'productid',
            'languagefield' => 'pr_htmllang',
            'primaryfields' => 'pr_name, pr_variety, pr_vintage',
            'secondaryfields' => 'pr_name, pr_variety, pr_vintage, pr_desc, pr_body',
        );
        $rawresults =  Jojo_Plugin_Jojo_search::searchPlugin($searchfields, $keywords, $language, $booleankeyword_str);
        $data = $rawresults ? self::getItemsById(array_keys($rawresults)) : '';
        if ($data) {
            foreach ($data as $result) {
                $result['relevance'] = $rawresults[$result['id']]['relevance'];
                $result['type'] = $result['pagetitle'];
                $result['tags'] = isset($rawresults[$result['id']]['tags']) ? $rawresults[$result['id']]['tags'] : '';
                $results[] = $result;
            }
        }
        /* Return results */
        return $results;
    }

    /**
     * Newsletter content
     */
    static function newslettercontent($contentarray, $newletterid=false)
    {
        /* Get all the products for this newsletter */
        if ($newletterid) {
            $productids = Jojo::selectAssoc('SELECT n.order, a.productid FROM {product} a, {newsletter_product} n WHERE a.productid = n.productid AND n.newsletterid = ? ORDER BY n.order', $newletterid);
            if ($productids) {
                $products = self::getItemsById($productids, '', 'showhidden');
                foreach($products as &$a) {
                    $a['title'] = mb_convert_encoding($a['pr_title'], 'HTML-ENTITIES', 'UTF-8');
                    $a['bodyplain'] = mb_convert_encoding($a['bodyplain'], 'HTML-ENTITIES', 'UTF-8');
                    $a['body'] = mb_convert_encoding($a['pr_body'], 'HTML-ENTITIES', 'UTF-8');
                    $a['imageurl'] = rawurlencode($a['image']);
                    foreach ($productids as $k => $i) {
                        if ($i==$a['productid']) {
                            $contentarray['products'][$k] = $a;
                        }
                    }
                }
                ksort($contentarray['products']);
            }
        }
        /* Return results */
        return $contentarray;
    }

/*
* Tags
*/
    static function getTagSnippets($ids)
    {
        $snippets = self::getItemsById($ids);
        return $snippets;
    }
}
