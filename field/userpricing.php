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

class Jojo_Field_userpricing extends Jojo_Field
{
    var $error;

    function checkvalue()
    {
        return true;
    }

    /*
     * Return the html for editing this field
     */
    function displayedit()
    {
        global $smarty;
        $userproducts = Jojo::selectAssoc("SELECT CONCAT(pr_code, '_case') as productcode, p.* FROM {product} p ORDER BY pr_display_order, pr_name, productid");
        $smarty->assign('userproducts', $userproducts );
        $smarty->assign('value',     $this->value);
        $smarty->assign('prices',    Jojo::selectAssoc("SELECT productid, unitprice FROM {product_user_price} WHERE userid = ?", $this->table->getRecordID()));
        $smarty->assign('fd_field', $this->fd_field);
        $smarty->assign('fd_help',  htmlentities($this->fd_help));
        return $smarty->fetch('admin/fields/userpricing.tpl');
    }

    function aftersave()
    {
        foreach (Jojo::getFormData('fm_' . $this->fd_field . '_price', array()) as $id => $price) {
            Jojo::updateQuery('REPLACE INTO {product_user_price} SET userid = ?, unitprice = ?, productid = ?', array($this->table->getRecordID(), $price, $id));
        }
        return true;
    }
}