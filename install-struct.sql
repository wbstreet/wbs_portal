DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_portal_section_settings`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_portal_section_settings` (
  `page_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `section_obj_type` int(11),
  `section_is_active` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`page_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_portal_obj_settings`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_portal_obj_settings` (
  `obj_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `obj_type_id` int(11) NOT NULL,
  `user_owner_id` int(11) NOT NULL,
  `is_active` int(11) NOT NULL DEFAULT 1,
  `is_deleted` int(11) NOT NULL DEFAULT '0',
  `moder_status` int(11) NOT NULL DEFAULT '2',
  `moder_comment` varchar(255) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_end_activity` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `substrate_color` varchar(7) NOT NULL DEFAULT '#ffffff',
  `substrate_opacity` int(11) NOT NULL DEFAULT '90',
  `substrate_border_color` varchar(7) NOT NULL DEFAULT '#ffffff',
  `substrate_border_left` int(11) DEFAULT '0',
  `substrate_border_right` int(11) DEFAULT '0',
  `bg_image` int(11),
  `seo_description` varchar(255) DEFAULT '',
  `seo_keywords` varchar(255) DEFAULT '',
  PRIMARY KEY (`obj_id`)
){TABLE_ENGINE=MyISAM};