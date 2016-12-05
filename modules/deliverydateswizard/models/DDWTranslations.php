<?php
if (!defined('_PS_VERSION_'))
	exit;

class TDDWTranslation
{
	public $id_translation = -1;
	public $name = '';
	public $type = '';
	public $text_collection = array();
}

class DDWTranslationsModel
{
	private static $table_name = 'ddw_translations';

	public static function getTranslations($id_shop, $return_raw)
	{
		$ddw_translations_collection = array();
		$ddw_translations_raw = array();
		$languages = Context::getContext()->language->getLanguages();

		
		$sql = 'SELECT * FROM '._DB_PREFIX_.bqSQL(self::$table_name).'
				WHERE id_shop='.(int)$id_shop;
		$result = DB::getInstance()->executeS($sql);
		
		if ($result)
		{
			foreach ($result as $row)
			{
				$ddw_translation = new TDDWTranslation();
				$ddw_translation->id_translation = $row['id_translation'];
				$ddw_translation->name = $row['name'];
				$ddw_translation->type = $row['type'];

				/* create empty translations */
				foreach ($languages as $lang)
					$ddw_translation->text_collection[$lang['id_lang']] = '';


				$sql = 'SELECT
				            `id_translation`,
				            `id_lang`,
				            `text`
						FROM '._DB_PREFIX_.bqSQL(self::$table_name).'_lang WHERE id_translation='.(int)$row['id_translation'];
				$result2 = DB::getInstance()->executeS($sql);
				if ($result2)
				{
					foreach ($result2 as $key => $row2)
						$ddw_translation->text_collection[$row2['id_lang']] = $row2['text'];
				}

				$ddw_translations_collection[] = $ddw_translation;

				/* raw array format*/
				$raw_item = array();
				$raw_item['id_translation'] = $row['id_translation'];
				$raw_item['name'] = $ddw_translation->name;
				$raw_item['type'] = $ddw_translation->type;

				if (isset($ddw_translation->text_collection[Context::getContext()->language->id]))
					$raw_item['text'] = $ddw_translation->text_collection[Context::getContext()->language->id];
				else
					$raw_item['text'] = '';

				$ddw_translations_raw[] = $raw_item;
			}
		}
		if ($return_raw) return $ddw_translations_raw;
			else return $ddw_translations_collection;
	}

	/**
	 * @return TDDWTranslation
	 */
	public static function getTranslationByName($name)
	{
		$ddw_translation_collection = self::getTranslations(Context::getContext()->shop->id, false);
		foreach ($ddw_translation_collection as $ddw_translation)
			if (Tools::strtolower($ddw_translation->name) == Tools::strtolower($name))
				return $ddw_translation->text_collection[Context::getContext()->language->id];
		return false;
	}

	/**
	 * @return TDDWTranslation
	 */
	public static function getTranslation($id_translation)
	{
		$ddw_translation_collection = self::getTranslations(Context::getContext()->shop->id, false);
		foreach ($ddw_translation_collection as $ddw_translation)
			if ($ddw_translation->id_translation == $id_translation) return $ddw_translation;
		return false;
	}

	public static function saveTranslationLang(TDDWTranslation $ddw_translation)
	{
		$sql = 'DELETE FROM '._DB_PREFIX_.bqSQL(self::$table_name).'_lang WHERE id_translation = '.(int)$ddw_translation->id_translation;
		DB::getInstance()->execute($sql);
		foreach ($ddw_translation->text_collection as $id_lang => $text)
		{
			Db::getInstance()->insert(self::$table_name.'_lang', array(
				'id_translation' => (int)$ddw_translation->id_translation,
				'id_lang' => (int)$id_lang,
				'text' => pSQL($text, true)
			));
		}
	}

	public static function createTranslation(TDDWTranslation $ddw_translation, $id_shop)
	{
		$sql = 'SELECT COUNT(*) AS total_count FROM '._DB_PREFIX_.bqSQL(self::$table_name).'
				WHERE name LIKE "'.$ddw_translation->name.'"
				AND id_shop='.(int)$id_shop;
		$row = DB::getInstance()->getRow($sql);

		if ($row['total_count'] == 0)
		{
			Db::getInstance()->insert(self::$table_name, array(
				'id_shop' => (int)$id_shop,
				'name' => $ddw_translation->name,
				'type' => $ddw_translation->type
			));
			$ddw_translation->id_translation = DB::getInstance()->Insert_ID();
			self::saveTranslationLang($ddw_translation);
		}
	}
}