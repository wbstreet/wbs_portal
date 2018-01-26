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

if(!defined('WB_PATH')) {
        require_once(dirname(dirname(__FILE__)).'/framework/globalExceptionHandler.php');
        throw new IllegalFileException();
}
/* -------------------------------------------------------- */
$module_directory = 'wbs_portal';
$module_name = 'WBS Portal v 1.0';
$module_function = 'page';
$module_version = '1.0';
$module_platform = '2.10.0';
$module_author = 'Konstantin Polyakov';
$module_license = 'GNU General Public License';
$module_description = 'Managing your objects (pages, apartment, services) on any page.';

//$links = ['windows', 'media_efects']; // зависимости

?>