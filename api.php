<?php

require_once(__DIR__.'/lib.class.portal.php');

$action = $_POST['action'];

$section_id = $clsFilter->f('section_id', [['integer', "Не указана секция!"]], 'fatal');
$page_id = $clsFilter->f('page_id', [['integer', "Не указана страница!"]], 'fatal');

require_once(WB_PATH."/framework/class.admin.php");
$admin = new admin('Start', '', false, false);
$clsModPortal = new ModPortal($page_id, $section_id);

if ($action == 'save') {

    check_auth(); check_all_permission($page_id, ['pages_modify']);

    $section_obj_type = $clsFilter->f('obj_type_id', [['integer', "Не указан тип объекта!"]], 'append', '');
    $section_is_active = $clsFilter->f('section_is_active', [['variants', "Не указана активность!!", ['true', 'false']]], 'append');
    
    if ($clsFilter->is_error()) $clsFilter->print_error();

    $r = update_row(
        $clsModPortal->tbl_section_settings,
        ['section_obj_type'=>$section_obj_type, 'section_is_active'=>$section_is_active === 'true' ? '1' : '0'],
        "`page_id`=".process_value($page_id)." AND `section_id`=".process_value($section_id)
    );
    if ($r === false) print_error('Неизвестная ошибка!');

    print_success('Сохранено!');

} else if ($action == 'replace') {

    check_auth(); check_all_permission($page_id, ['pages_modify']);

    $new_page_id = $clsFilter->f('new_page_id', [['integer', "Не указана страница!"]], 'append', '');

    if ($clsFilter->is_error()) $clsFilter->print_error();

    $r = update_row(
        $clsModPortal->tbl_section_settings,
        ['page_id'=>$new_page_id],
        "`page_id`=".process_value($page_id)." AND `section_id`=".process_value($section_id)
    );
    if ($r === false) print_error('Неизвестная ошибка!');

    $r = update_row(
        "`".TABLE_PREFIX."sections`",
        ['page_id'=>$new_page_id],
        "`page_id`=".process_value($page_id)." AND `section_id`=".process_value($section_id)
    );
    if ($r === false) print_error('Неизвестная ошибка!');

    $r = update_row(
        $clsModPortal->tbl_obj_settings,
        ['page_id'=>$new_page_id],
        "`page_id`=".process_value($page_id)." AND `section_id`=".process_value($section_id)
    );
    if ($r === false) print_error('Неизвестная ошибка!');
    
    print_success('Сохранено!');
    
} else { print_error('Неверный apin name!'); }

?>