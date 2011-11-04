<?php

$table = 'product';
$query = "
    CREATE TABLE {product} (
        `productid` int(11) NOT NULL auto_increment,
        `pr_name` VARCHAR(255) NOT NULL default '',
        `pr_region` VARCHAR(255) NOT NULL default '',
        `pr_variety` VARCHAR(255) NOT NULL default '',
        `pr_vintage` VARCHAR(10) NOT NULL default '',
        `pr_display_order` int(4) NOT NULL default '0',
        `pr_desc` text NOT NULL,
        `pr_url` VARCHAR(255) NOT NULL default '',
        `pr_body` text NULL,
        `pr_body_code` text NULL,
        `pr_image` VARCHAR(255) NOT NULL,
        `pr_tastingnote` VARCHAR(255) NOT NULL,
        `pr_category` int(11) NOT NULL default '0',
        `pr_language` VARCHAR(100) NOT NULL default 'en',
        `pr_htmllang` VARCHAR(100) NOT NULL default 'en',
      `pr_date` int(11) default '0',
        `pr_livedate` int(11) NOT NULL default '0',
        `pr_expirydate` int(11) NOT NULL default '0',
        `pr_tags` text NULL,
        `pr_code` VARCHAR(255) NOT NULL default '',
	    `pr_price` VARCHAR(10) NOT NULL default '0.00',
	    `pr_caseprice` VARCHAR(10) NOT NULL default '0.00',
        `pr_casesize` int(2) NOT NULL default '12',
        `pr_na_message` VARCHAR(255) NOT NULL default '',
        `pr_currency` VARCHAR(255) NOT NULL default '',
	    `pr_freight` text NOT NULL,
 	    `pr_freightcase` text NOT NULL,
   	`pr_quantity_fixed` enum('yes','no') NOT NULL default 'no',
     	`status` tinyint(1) NOT NULL default '1',
        PRIMARY KEY  (`productid`),
         FULLTEXT KEY `title` (`pr_name`),
         FULLTEXT KEY `body` (`pr_name`,`pr_desc`,`pr_body`, `pr_variety`, `pr_vintage`)
    ) TYPE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci  AUTO_INCREMENT=1000;";

/* Convert mysql date format to unix timestamps */
if (Jojo::tableExists($table) && Jojo::getMySQLType($table, 'pr_date') == 'date') {
    date_default_timezone_set(Jojo::getOption('sitetimezone', 'Pacific/Auckland'));
    $products = Jojo::selectQuery("SELECT productid, pr_date FROM {product}");
    Jojo::structureQuery("ALTER TABLE  {product} CHANGE  `pr_date`  `pr_date` INT(11) NOT NULL DEFAULT '0'");
    foreach ($products as $k => $a) {
        if ($a['pr_date']!='0000-00-00') {
            $timestamp = strtotime($a['pr_date']);
        } else {
            $timestamp = 0;
        }
       Jojo::updateQuery("UPDATE {product} SET pr_date=? WHERE productid=?", array($timestamp, $a['productid']));
    }
}


/* Check table structure */
$result = Jojo::checkTable($table, $query);

/* Output result */
if (isset($result['created'])) {
    echo sprintf("jojo_cart_products_wine: Table <b>%s</b> Does not exist - created empty table.<br />", $table);
}

if (isset($result['added'])) {
    foreach ($result['added'] as $col => $v) {
        echo sprintf("jojo_cart_products_wine: Table <b>%s</b> column <b>%s</b> Does not exist - added.<br />", $table, $col);
    }
}

if (isset($result['different'])) Jojo::printTableDifference($table,$result['different']);

$table = 'productcategory';

$query = "
    CREATE TABLE {productcategory} (
      `productcategoryid` int(11) NOT NULL auto_increment,
      `pageid` int(11) NOT NULL default '0',
      `type` enum('normal','parent','index') NOT NULL default 'normal',
      `pc_display_order` int(4) NOT NULL default '0',
      `sortby` enum('name','order') NOT NULL default 'name',
      `addtonav` tinyint(1) NOT NULL default '0',
      `showdate` tinyint(1) NOT NULL default '1',
      `dateformat` varchar(255) NOT NULL default '%e %b %Y',
      `snippet` varchar(255) NOT NULL default '400',
      `readmore` varchar(255) NOT NULL default '> Read more',
      `thumbnail` varchar(255) NOT NULL default 's150',
      `mainimage` varchar(255) NOT NULL default 'v60000',
      `nameformat` varchar(255) NOT NULL default '[brand] [region] [variety] [vintage]',
      `nameformat_index` varchar(255) NOT NULL default '[brand] [region] [variety] [vintage]',
      `nameformat_menu` varchar(255) NOT NULL default '[brand] [region] [variety] [vintage]',
      `nameformat_cart` varchar(255) NOT NULL default '[brand] [region] [variety] [vintage]',";
if (class_exists('Jojo_Plugin_Jojo_comment')) {
    $query .= "
     `comments` tinyint(1) NOT NULL default '1',";
}
$query .= "
      PRIMARY KEY  (`productcategoryid`),
      KEY `id` (`pageid`)
    ) TYPE=MyISAM ;";

/* Check table structure */
$result = Jojo::checkTable($table, $query);

/* Output result */
if (isset($result['created'])) {
    echo sprintf("jojo_product: Table <b>%s</b> Does not exist - created empty table.<br />", $table);
}

if (isset($result['added'])) {
    foreach ($result['added'] as $col => $v) {
        echo sprintf("jojo_product: Table <b>%s</b> column <b>%s</b> Does not exist - added.<br />", $table, $col);
    }
}

if (isset($result['different'])) Jojo::printTableDifference($table, $result['different']);


/* add relational table for use by newsletter plugin if present */
if (class_exists('Jojo_Plugin_Jojo_Newsletter')) {
    $table = 'newsletter_product';
    $query = "CREATE TABLE {newsletter_product} (
      `newsletterid` int(11) NOT NULL,
      `productid` int(11) NOT NULL,
      `order` int(11) NOT NULL
    );";
    
    /* Check table structure */
    $result = Jojo::checkTable($table, $query);
    
    /* Output result */
    if (isset($result['created'])) {
        echo sprintf("jojo_newsletter_phplist: Table <b>%s</b> Does not exist - created empty table.<br />", $table);
    }
    
    if (isset($result['added'])) {
        foreach ($result['added'] as $col => $v) {
            echo sprintf("jojo_newsletter_phplist: Table <b>%s</b> column <b>%s</b> Does not exist - added.<br />", $table, $col);
        }
    }
    
    if (isset($result['different'])) Jojo::printTableDifference($table,$result['different']);

    /* add the new products field to the newsletter table if it does not exist */
    if (Jojo::tableExists('newsletter') && !Jojo::fieldExists('newsletter', 'products')) {
        Jojo::structureQuery("ALTER TABLE `newsletter` ADD `products` TEXT NOT NULL;");
    }

}

$table = 'product_user_price';
$query = "
    CREATE TABLE {product_user_price} (
      `userid` int(11) NOT NULL default '0',
      `productid` int(11) NOT NULL default '0',
      `unitprice` varchar(7) NOT NULL default '0.00',
      PRIMARY KEY  (`userid`, `productid`)
    ) TYPE=MyISAM ;";

/* Check table structure */
$result = Jojo::checkTable($table, $query);

/* Output result */
if (isset($result['created'])) {
    echo sprintf("jojo_cart_products_wine: Table <b>%s</b> Does not exist - created empty table.<br />", $table);
}

if (isset($result['added'])) {
    foreach ($result['added'] as $col => $v) {
        echo sprintf("jojo_cart_products_wine: Table <b>%s</b> column <b>%s</b> Does not exist - added.<br />", $table, $col);
    }
}

if (isset($result['different'])) Jojo::printTableDifference($table, $result['different']);

