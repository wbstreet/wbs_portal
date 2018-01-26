<?php

$path_core = WB_PATH.'/modules/wbs_core/include_all.php';
if (file_exists($path_core )) include($path_core );
else echo "<script>console.log('Модуль wbs_portal требует модуль wbs_core')</script>";

class ModPortal extends Addon {

    function __construct($page_id, $section_id) {
        parent::__construct('wbs_portal', $page_id, $section_id);
        $this->tbl_obj_type = "`".TABLE_PREFIX."mod_wbs_portal_obj_type`";
        $this->tbl_obj_settings = "`".TABLE_PREFIX."mod_wbs_portal_obj_settings`";        
        $this->tbl_section_settings = "`".TABLE_PREFIX."mod_wbs_portal_section_settings`";        
    }

    function uninstall() {
        global $database;

        // проверяем, существует хоть какой-нибудь модуль wbs_portal_obj_*

        $r = select_row($this->tbl_obj_type, 'COUNT(`page_id`) as pcount');
        if ($r === false) return "Неизвестная ошибка!";
        if ($r->fetchRow()['pcount'] > 0) return "Существуют модули wbs_portal_obj_* !";
        
        // проверяем, существует хоть какой-нибудь объект в любой секции

        $r = select_row($this->tbl_obj_settings, 'COUNT(`page_id`) as pcount');
        if ($r === false) return "Неизвестная ошибка!";
        if ($r->fetchRow()['pcount'] > 0) return "У модулей wbs_portal_obj_* есть объекты!";
        
        // проверка, есть секция, сам WebsiteBaker
        
        // удаляем таблицы

        $database->query("DROP TABLE ".$this->tbl_section_settings);
        $database->query("DROP TABLE ".$this->tbl_obj_settings);
        $database->query("DROP TABLE ".$this->tbl_obj_type);
        
        return true;
        
    }
    
    function section_add() {
        global $database;

        // проверяем, есть ли на этой странице секция этого же модлуля
        
        $r = select_row($this->tbl_section_settings, '*', "`page_id`=".process_value($this->page_id));
        if ($r === false) return "Неизвестная ошибка!";
        if ($r !== null && $r->numRows() > 0) return "На одну страницу можно установить одну такую секцию!";

        // проверяем, существует хоть какой-нибудь модуль wbs_portal_obj_*

        $r = select_row($this->tbl_obj_type, 'COUNT(`page_id`) as pcount');
        if ($r === false) return "Неизвестная ошибка!";
        if ($r->fetchRow()['pcount'] === 0) return "Отсутствуют модули wbs_portal_obj_* !";
        
        // добавляем настройки для данной секции

        $r = insert_row($this->tbl_section_settings, [
            "page_id"=>$this->page_id,
            "section_id"=>$this->section_id,
        ]);
        
        return $r;
    }

    function section_delete($page_id, $section_id) {
        global $database;

        // проверяем, существует хоть какой-нибудь объект в данной секции
        
        $r = select_row($this->tbl_obj_settings, 'COUNT(`page_id`) as pcount', "`page_id`=".process_value($this->page_id));
        if ($r === false) return "Неизвестная ошибка!";
        if ($r->fetchRow()['pcount'] > 0) return "У данной секции есть объекты!";

        // если нет, удаляем настройки для данной секции

        $r = delete_row($this->tbl_obj_data, "`page_id`=".process_value($this->page_id));

        return $r;
        
    }
    
    function obj_type_add($obj_name) {
        global $database;

        // проверяем на yаличие дубликата
        
        // если дубликатата нет, то добавляем
    
    }
    
    function obj_type_delete($obj_name) {
        global $database;
    
        // проверяем, используется ли этот объект на секциях
        
        // если не используется, то удаляем объект
    
    }
}

?>