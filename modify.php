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

$r = select_row($clsModPortal->tbl_obj_settings, 'COUNT(`obj_type_id`) as ocount', glue_fields(['page_id'=>$page_id, 'section_id'=>$section_id], 'AND'));
if ($r === false) {echo "Неизвестная ошибка!"; $count_in_section = 0; }
else $count_in_section = $r->fetchRow()['ocount'];

$r = select_row($clsModPortal->tbl_obj_settings, 'COUNT(`obj_type_id`) as ocount');
if ($r === false) {echo "Неизвестная ошибка!"; $count_total = 0; }
else $count_total = $r->fetchRow()['ocount'];

$r = select_row($clsModPortal->tbl_obj_type, '*');
if ($r === false) {echo "Неизвестная ошибка!"; $obj_types = null; }
else $obj_types = $r;

$r = select_row($clsModPortal->tbl_section_settings, '*', glue_fields(['page_id'=>$page_id, 'section_id'=>$section_id], 'AND'));
if ($r === false) {echo "Неизвестная ошибка!"; $section_settings = null; }
else $section_settings = $r->fetchRow();

?>

<div>
    Кол-во объектов: <?=$count_in_section?> - в секции, <?=$count_total?> - всего <input type="button" value='Подробная статистика' onclick=''>
</div>

<form>
    <table>
        <tr>
            <td>Тип объектов:</td>
            <td>
                <select name='obj_type'>
                    <?
                    while ($obj_types !== null && $obj_type = $obj_types->fetchRow()) {
                        ?> <option value="<?=$obj_type['obj_type_id']?>"<?php if ($obj_type['obj_type_id']===$section_settings['obj_type_id']) echo " selected"; ?>><?=$obj_type['obj_type_name']?></option> <?php
                    }
                ?>
                </select>
                <?php if ($obj_type===null) echo "Не установлены модули wbs_portal_obj_*"; ?>
            </td>
        </tr>
    </table>
    <input type="button" value='Сохранить' onclick=''>
</form>