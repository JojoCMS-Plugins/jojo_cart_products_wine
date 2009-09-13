<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2007-2008 Harvey Kane <code@ragepank.com>
 * Copyright 2007-2008 Michael Holt <code@gardyneholt.co.nz>
 * Copyright 2007 Melanie Schulz <mel@gardyneholt.co.nz>
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Harvey Kane <code@ragepank.com>
 * @author  Michael Cochrane <mikec@jojocms.org>
 * @author  Melanie Schulz <mel@gardyneholt.co.nz>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */

$arg1 = Util::getFormData('arg1','');
$arg2 = Util::getFormData('arg2','');
$arg3 = Util::getFormData('arg3','');
$arg4 = Util::getFormData('arg4','');

$frajax = new frajax();
$frajax->title = 'FRAJAX Action - ' . _SITETITLE; //Edit this to something mildly descriptive
$frajax->sendHeader();

$frajax->alert('This is a test FRAJAX action');

$frajax->sendFooter();
