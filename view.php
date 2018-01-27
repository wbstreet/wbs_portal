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

require_once(WB_PATH."/framework/class.admin.php");
$admin = new admin('Start', '', false, false);

// проверяем наличие модулей wbs_portal_obj_*

$r = select_row($clsModPortal->tbl_obj_type, 'COUNT(`obj_type_id`) as tcount');
if ($r === false) {echo "Неизвестная ошибка!"; $count_obj_type = 0; }
else $count_obj_type = $r->fetchRow()['tcount'];
if ($count_obj_type === '0') {$clsModPortal->print_error("Не установлены модули wbs_portal_obj_*");}

// вынимаем настройки секции

$r = select_row(
    [$clsModPortal->tbl_section_settings, $clsModPortal->tbl_obj_type],
    '*',
    glue_fields(['page_id'=>$page_id, 'section_id'=>$section_id], ' AND ')." AND ".$clsModPortal->tbl_section_settings.'.`section_obj_type`='.$clsModPortal->tbl_obj_type.'.`obj_type_id`'
);
if ($r === false) {echo "Неизвестная ошибка!"; $section_settings = null; }
else if ($r === null) { $clsModPortal->print_error(" Настройки секции не найдены. Вероятно, не установлены модули wbs_portal_obj_*"); $section_settings = null; }
else $section_settings = $r->fetchRow();

// вынимаем аргументы

$modPortalArgs = [
    'action' => isset($_GET['action']) ? preg_replace("/[^a-z0-9_]+/", '', $_GET['action']) : 'view',
    'obj_id' => $clsFilter->f2($_GET, 'obj_id', [['integer', '']], 'default', null),
    'category_id' => $clsFilter->f2($_GET, 'category_id', [['integer', '']], 'default', null),
    'page_num' => $clsFilter->f2($_GET, 'page_num', [['integer', '']], 'default', 1),
    'obj_per_page' => $clsFilter->f2($_GET, 'obj_per_page', [['integer', '']], 'default', 10),
    'settlement_id' => $clsFilter->f2($_COOKIE, 'settlement_id', [['integer', '']], 'default', 10),
    's' => $clsFilter->f2($_GET, 's', [['1', '']], 'default', null),
];
if ($modPortalArgs['action'] == '') $modPortalArgs['action'] = 'view';

// подключаем файл отображения контента модуля wbs_portal_obj_*

$latname = preg_replace("[^a-z_]", '', $section_settings['obj_type_latname']);
$path = WB_PATH."/modules/wbs_portal_obj_{$latname}/actions/{$modPortalArgs['action']}.php";
if (file_exists($path)) include($path);
else { $clsModPortal->print_error("Файл не найден: {$latname}/{$modPortalArgs['action']} "); }

?>