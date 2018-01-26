<?php
/**
 *
 * @category        module
 * @package         wbs_portal
 * @author          Konstantin Polyakov
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.0
 * @requirements    PHP 5.2.2 and higher
 *
 */

if(!defined('WB_PATH')) die(header('Location: index.php'));  

include(WB_PATH.'/modules/wbs_portal/lib.class.portal.php');
$clsModPortal = new ModPortal($page_id, $section_id);

$r = $clsModPortal->section_delete();
if ($r !== true) {
    $admin->print_header();
    $admin->print_error($r);
    $admin->print_footer();
    die();
}

?> 
