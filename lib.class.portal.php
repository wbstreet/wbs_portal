<?php

$path_core = __DIR__.'/../wbs_core/include_all.php';
if (file_exists($path_core )) include($path_core );
else echo "<script>console.log('Модуль wbs_portal требует модуль wbs_core')</script>";

if (!class_exists('_ModPortal')) {
class _ModPortal extends Addon {

    function __construct($name, $page_id, $section_id) {
        parent::__construct($name, $page_id, $section_id);
        $this->tbl_obj_type = "`".TABLE_PREFIX."mod_wbs_portal_obj_type`";
        $this->tbl_obj_settings = "`".TABLE_PREFIX."mod_wbs_portal_obj_settings`";        
        $this->tbl_section_settings = "`".TABLE_PREFIX."mod_wbs_portal_section_settings`";        
    }

    function _get_obj() {
        if (isset($_GET['obj_id']) && $_GET['obj_id']) {
            $r = select_row($this->tbl_obj_settings, '*', '`obj_id`='.process_value($_GET['obj_id']));
            if (gettype($r) === 'string') return null;
            if ($r === null) return null;
            
            return $r->fetchRow();
        } else return null;
    }
}
}

/* Используется этим модулем */
if (!class_exists('ModPortal')) {
class ModPortal extends _ModPortal {

    function __construct($page_id, $section_id) {
        parent::__construct('wbs_portal', $page_id, $section_id);
    }

    /* В дочернем классе должен быть переопределён */
    function uninstall() {
        global $database;

        // проверяем, существует хоть какой-нибудь модуль wbs_portal_obj_*

        $r = select_row($this->tbl_obj_type, 'COUNT(`obj_type_id`) as pcount');
        if (gettype($r) === 'string') return "Неизвестная ошибка!";
        if ($r->fetchRow()['pcount'] > 0) return "Существуют модули wbs_portal_obj_* !";
        
        // проверяем, существует хоть какой-нибудь объект в любой секции

        $r = select_row($this->tbl_obj_settings, 'COUNT(`page_id`) as pcount');
        if (gettype($r) === 'string') return "Неизвестная ошибка!";
        if ($r->fetchRow()['pcount'] > 0) return "У модулей wbs_portal_obj_* есть объекты!";
        
        // проверяет, есть секция, сам WebsiteBaker
        
        // удаляем таблицы

        $database->query("DROP TABLE ".$this->tbl_section_settings);
        $database->query("DROP TABLE ".$this->tbl_obj_settings);
        $database->query("DROP TABLE ".$this->tbl_obj_type);
        
        return true;
        
    }

    function install() {
        global $database;
        $sql = "
CREATE TABLE IF NOT EXISTS {$this->tbl_obj_type} (
  `obj_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `obj_type_latname` varchar(20) NOT NULL,
  `obj_type_name` varchar(20) NOT NULL,
  `obj_type_is_active` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`obj_type_id`)
);";
        $r = $database->query($sql);
        if ($database->is_error()) return $database->get_error();
        return true;
    }
    
    function section_add() {
        global $database;

        // проверяем, есть ли на этой странице секция этого же модлуля
        
        $r = select_row($this->tbl_section_settings, '*', "`page_id`=".process_value($this->page_id));
        if (gettype($r) === 'string') return "Неизвестная ошибка!";
        if ($r !== null && $r->numRows() > 0) return "На одну страницу можно установить одну такую секцию!";

        // проверяем, существует хоть какой-нибудь модуль wbs_portal_obj_*

        $r = select_row($this->tbl_obj_type, 'COUNT(`obj_type_id`) as ocount');
        if (gettype($r) === 'string') return "Неизвестная ошибка!";
        if ($r !== null && $r->fetchRow()['ocount'] === '0') return "Отсутствуют модули wbs_portal_obj_* !";
        
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
        
        $r = select_row($this->tbl_obj_settings, 'COUNT(`page_id`) as pcount', "`page_id`=".process_value($this->page_id)." AND "."`section_id`=".process_value($this->section_id));
        if (gettype($r) === 'string') return "Неизвестная ошибка!";
        if ($r !== null && $r->fetchRow()['pcount'] > 0) return "У данной секции есть объекты!";

        // если нет, удаляем настройки для данной секции

        $r = delete_row($this->tbl_section_settings, glue_fields(['page_id'=>$this->page_id, 'section_id'=>$this->section_id], 'AND'));

        return $r;
        
    }

}
}

/* Используется модулями wbs_portal_obj_* */
if (!class_exists('ModPortalObj')) {
class ModPortalObj extends _ModPortal {

    public $prefix = 'wbs_portal_obj_';
    public $obj_type_id = null;
    public $obj_type_is_active = null;
    
    function __construct($latname, $name, $page_id, $section_id) {
        parent::__construct($this->prefix.$latname, $page_id, $section_id);
        
        $this->obj_type_latname = $latname;
        $this->obj_type_name = $name;
        
        $r = select_row($this->tbl_obj_type, '*', "`obj_type_latname`=".process_value($this->obj_type_latname));
        //Ошибка возникнет при установке модуля, так как требуемой таблицы пока нет (появится после установки0
        if ($r !== false && $r !== null && gettype($r) !== 'string') {
            $row = $r->fetchRow();
            $this->obj_type_id = $row['obj_type_id'];
            $this->obj_type_is_active = $row['obj_type_is_active'];     
        }
    }

    /* Используется модулями wbs_portal_obj_* в процессе их установки */
    function install($sql_lines=null) {
        global $database;

        // создаём общую для всех таблицу, если главный модуль ещё не был установлен
        $clsModPortal = new ModPortal(null, null);
        $r = $clsModPortal->install();
        if ($r !== true) { return $r; }
        
        // проверяем на наличие дубликата

        $r = select_row($this->tbl_obj_type, "COUNT('obj_type_latname') as ocount", "`obj_type_latname`=".process_value($this->obj_type_latname));
        if (gettype($r) === 'string') return "Неизвестная ошибка!";
        if ($r !== null && $r->fetchRow()['ocount'] !== '0') return "Модуль с таким названием уже существует!";

        // если дубликатата нет, то добавляем

        if ($sql_lines !== null) {
            foreach ($sql_lines as $sql_line) $database->query($sql_line);
        }

        $r = insert_row($this->tbl_obj_type, ['obj_type_latname'=>$this->obj_type_latname, 'obj_type_name'=>$this->obj_type_name]);
        if (gettype($r) === 'string') return "Неизвестная ошибка!";
        
        return true;
    
    }

    function uninstall($sql_lines=null) {
        global $database;
    
        // проверяем, есть ли объекты в какой-либо из секций

        $r = select_row(
            [$this->tbl_obj_settings, $this->tbl_obj_type],
            'COUNT(`page_id`) as pcount',
            $this->tbl_obj_type.".`obj_type_latname`=".process_value($this->obj_type_latname)." AND ".$this->tbl_obj_settings.".`obj_type_id`=".$this->tbl_obj_type.".`obj_type_id`"
        );
        if (gettype($r) === 'string') return "Неизвестная ошибка!";
        if ($r->fetchRow()['pcount'] > 0) return "У модуля есть объекты!";

        // проверяем, используется ли этот модуль на секциях

        $r = select_row(
            [$this->tbl_section_settings, $this->tbl_obj_type],
            'COUNT(`page_id`) as pcount',
            $this->tbl_obj_type.".`obj_type_latname`=".process_value($this->obj_type_latname)." AND ".$this->tbl_section_settings.".`section_obj_type`=".$this->tbl_obj_type.".`obj_type_id`"
        );
        if (gettype($r) === 'string') return "Неизвестная ошибка!";
        if ($r !== null && $r->fetchRow()['pcount'] !== '0') return "Модуль установлен на некоторых секциях!";

        // если не используется, то удаляем объект

        if ($sql_lines !== null) {
            foreach ($sql_lines as $sql_line) $database->query($sql_line);
        }
        
        $r = delete_row($this->tbl_obj_type, '`obj_type_latname`='.process_value($this->obj_type_latname));
        if (gettype($r) === 'string') return "Неизвестная ошибка!";
        
        return true;
    }

    function split_arrays(&$fields) {
        $_fields = [];
        $f= "obj_id,page_id,section_id,obj_type_id,user_owner_id,is_active,is_deleted, moder_status,moder_comment,date_created,date_end_activity,substrate_color,substrate_opacity,substrate_border_color,substrate_border_left,substrate_border_right,bg_image,seo_description,seo_keywords";
        $common_fields = explode(',', $f);
        foreach ($common_fields as $k => $v) {
            if (!in_array($v, array_keys($fields))) continue;
            $_fields[$v] = $fields[$v];
            unset($fields[$v]);
        }
        return $_fields;
    }
    
    function _getobj_where($sets, &$aWhere) {
        
        if (isset($sets['obj_id']))     $aWhere[] = "{$this->tbl_obj_settings}.`obj_id`=".process_value($sets['obj_id']);
        //if (isset($sets['settlement_id']) && $sets['settlement_id'] !== null) $aWhere[] = '`settlement_id`='.process_value($sets['settlement_id']);
        if (isset($sets['is_active']))  $aWhere[] = "{$this->tbl_obj_settings}.`is_active`=".process_value($sets['is_active']);
        if (isset($sets['is_moder']))   $aWhere[] = "{$this->tbl_obj_settings}.`moder_status`=".process_value($sets['is_moder']);
        if (isset($sets['is_deleted'])) $aWhere[] = "{$this->tbl_obj_settings}.`is_deleted`=".process_value($sets['is_deleted']);

        if (isset($sets['owner_id']))   $aWhere[] = "{$this->tbl_obj_settings}.`user_owner_id`=".process_value($sets['owner_id']);
        else if (isset($sets['user_owner_id']))   $aWhere[] = "{$this->tbl_obj_settings}.`user_owner_id`=".process_value($sets['user_owner_id']);

        if (isset($sets['page_id']))    $aWhere[] = "{$this->tbl_obj_settings}.`page_id`=".process_value($sets['page_id']);
        if (isset($sets['section_id'])) $aWhere[] = "{$this->tbl_obj_settings}.`section_id`=".process_value($sets['section_id']);
        
    }
    
    //function obj_type_get($sets) {
    //    $keys = glue_fields($sets, 'AND');
    //    return select_row($this->tbl_obj_type, '*', $fields);
    //}
    
}
}
?>