<?php

$table = 'product';
$query = "
    CREATE TABLE {product} (
        `productid` int(11) NOT NULL auto_increment,
        `pr_name` VARCHAR(255) NOT NULL default '',
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
        `pr_date` date default NULL,
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
    	`pr_quantity_fixed` enum('yes','no') NOT NULL default 'no',
         PRIMARY KEY  (`productid`),
         FULLTEXT KEY `title` (`pr_name`),
         FULLTEXT KEY `body` (`pr_name`,`pr_desc`,`pr_body`, `pr_variety`, `pr_vintage`)
    ) TYPE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci  AUTO_INCREMENT=1000;";

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
      `pc_url` varchar(255) NOT NULL default '',
      `pc_display_order` int(4) NOT NULL default '0',
      PRIMARY KEY  (`productcategoryid`)
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

