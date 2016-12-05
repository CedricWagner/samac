<?php
class DDWInstall
{
	public static function installDB()
	{
		$return = true;
		$return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ddw` (
			  `id_ddw` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `id_carrier` int(10) unsigned NOT NULL,
			  `required` char(1) NOT NULL DEFAULT 0,
			  `enabled` char(1) DEFAULT 0,
			  `weekdays` varchar(32) DEFAULT NULL,
			  `min_days` mediumint(8) unsigned DEFAULT 0,
			  `max_days` mediumint(8) unsigned DEFAULT 0,
			  `cutofftime_enabled` char(1) DEFAULT 0,
			  `cutofftime_hours` smallint(5) unsigned DEFAULT 0,
			  `cutofftime_minutes` smallint(5) unsigned DEFAULT 0,
			  PRIMARY KEY (`id_ddw`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');

		$return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ddw_blocked_dates` (
			`id_blockeddate` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_carrier` int(10) unsigned DEFAULT NULL,
            `id_shop` int(10) unsigned DEFAULT NULL,
            `recurring` smallint(5) unsigned DEFAULT NULL,
            `start_date` date DEFAULT NULL,
            `end_date` date DEFAULT NULL,
            PRIMARY KEY (`id_blockeddate`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;');

		$return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ddw_timeslots` (
            `id_timeslot` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_carrier` int(10) unsigned DEFAULT NULL,
            `id_shop` int(10) unsigned DEFAULT NULL,
            `position` int(10) unsigned DEFAULT NULL,
            PRIMARY KEY (`id_timeslot`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;');

		$return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ddw_timeslots_lang` (
			  `id_timeslot` int(10) unsigned NOT NULL DEFAULT "0",
			  `id_lang` int(10) unsigned NOT NULL DEFAULT "0",
			  `time_slot` varchar(128) DEFAULT NULL,
			  PRIMARY KEY (`id_timeslot`,`id_lang`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;');

		$return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ddw_translations` (
			  `id_translation` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `id_shop` int(10) unsigned DEFAULT NULL,
			  `name` varchar(128) DEFAULT NULL,
			  `type` varchar(32) NOT NULL,
			  PRIMARY KEY (`id_translation`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;');

		$return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ddw_translations_lang` (
			  `id_translation` int(10) unsigned DEFAULT NULL,
			  `id_lang` int(10) unsigned DEFAULT NULL,
			  `text` text
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;');


		$return &= Db::getInstance()->execute('
				ALTER TABLE  `'._DB_PREFIX_.'cart` ADD  `ddw_order_date` DATETIME NOT NULL
			');

		self::addColumn('cart', 'ddw_order_date', 'DATETIME NOT NULL');
		self::addColumn('cart', 'ddw_order_time', 'VARCHAR(64)');
		self::addColumn('orders', 'ddw_order_date', 'DATETIME NOT NULL');
		self::addColumn('orders', 'ddw_order_time', 'VARCHAR(64)');
		return $return;
	}

	private static function addColumn($table, $name, $type)
	{
		try
		{
			$return = Db::getInstance()->execute('ALTER TABLE  `'._DB_PREFIX_.''.$table.'` ADD `'.$name.'` '.$type);
		} catch(Exception $e)
		{
			return true;
		}
		return true;
	}

	private static function dropColumn($table, $name)
	{
		Db::getInstance()->execute('ALTER TABLE  `'._DB_PREFIX_.''.$table.'` DROP `'.$name.'`');
	}
	
	protected static function dropTable($table_name)
	{
		$sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$table_name.'`';
		DB::getInstance()->execute($sql);
	}	
	
	private static function createTranslation($name, $type, $text)
	{
		$languages = Context::getContext()->language->getLanguages();

		$ddw_translation = new TDDWTranslation();
		$ddw_translation->name = $name;
		$ddw_translation->type = $type;
		foreach ($languages as $lang)
			$ddw_translation->text_collection[$lang['id_lang']] = $text;
		DDWTranslationsModel::createTranslation($ddw_translation, Context::getContext()->shop->id);
	}

	private static function installTranslations()
	{
		self::createTranslation('date_invoice_label', 'text', 'Delivery Date');
		self::createTranslation('time_invoice_label', 'text', 'Delivery Time');
		self::createTranslation('text_checkout', 'html', '<strong>Select delivery date and time below</strong>');
		self::createTranslation('required_error', 'text', 'You must select a delivery date to continue');
	}

	public static function installData()
	{
		self::installTranslations();
	}

	public static function uninstall()
	{
		self::dropColumn('cart', 'ddw_order_date');
		self::dropColumn('cart', 'ddw_order_time');
		self::dropColumn('orders', 'ddw_order_date');
		self::dropColumn('orders', 'ddw_order_time');
		
		self::dropTable('ddw');
		self::dropTable('ddw_blocked_dates');
		self::dropTable('ddw_timeslots');
		self::dropTable('ddw_timeslots_lang');
		self::dropTable('ddw_translations');
		self::dropTable('ddw_translations_lang');		
	}

}