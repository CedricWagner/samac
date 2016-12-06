<?php
/**
* 
*/
class SyncProduct extends Synchronizable
{
	
	public static $definition = array(
		'table' => 'sync_products',
		'primary' => 'id',
		'fields' => array(
			'id' =>			 		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
			'sync_id' => 			array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ws_id' => 				array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ps_id' => 				array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
			'ws_date_update' =>		array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true),
			'action' =>				array('type' => self::TYPE_STRING, 'validate' => 'isString','required' => true),
		),
	);

	
	public static function getCountBySynchronization($id, $action=false){
		$table_name = self::$definition['table'];
		$where_clause = '';
		if ($action) {
			$where_clause = 'AND action LIKE "'.$action.'"';
		}
		$sql = 'SELECT COUNT(id) FROM '._DB_PREFIX_.$table_name.' WHERE sync_id = '.$id.' '.$where_clause;
		return $row = Db::getInstance()->getValue($sql);
	}

	public static function proceedLineSync($line,$sync){
		$table_name = self::$definition['table'];

		//get last product sync
		$ws_id = self::getLineValue($line,'ART_ID');
		$sql = 'SELECT id FROM '._DB_PREFIX_.$table_name.' WHERE ws_id = '.(int)$ws_id.' ORDER BY ws_date_update DESC';
		$id = Db::getInstance()->getValue($sql);

		//init syncProduct
		$sp = new SyncProduct();
		
		if($id){
			$sp->action = self::ACTION_EDIT;
			$lastSyncProduct = new SyncProduct($id);
			$sp->ps_id = $lastSyncProduct->ps_id;
		}else{
			$sp->action = self::ACTION_ADD;
		}

		//get the product if edit, otherwise create a new product
		$product = new Product($sp->action==self::ACTION_ADD?null:$sp->ps_id);
		$product->name[1] = self::getLineValue($line,'ART_DESIGNATION');
		$product->description[1] = self::getLineValue($line,'ART_DESCRIPTION');
		$product->description_short[1] = self::getLineValue($line,'ART_DESCRIPTION');
		$product->reference = self::getLineValue($line,'ART_CODE');
		$product->weight = self::getLineValue($line,'ART_POI');
		$product->height = self::getLineValue($line,'ART_HAU');
		$product->width = self::getLineValue($line,'ART_LAR');
		$product->depth = self::getLineValue($line,'ART_LON');
		$product->price = str_replace(',', '.', self::getLineValue($line,'ART_PRIXPUB'));
		$product->date_add = self::getLineValue($line,'ART_DATEUPDATE');
		$product->date_upd = self::getLineValue($line,'ART_DATEUPDATE');
		$product->quantity = self::getLineValue($line,'ART_STK');
		$product->ecotax = self::getLineValue($line,'ART_ECOPAR');
		$idCategory = DB::getInstance()->getValue('SELECT sc.ps_id FROM '._DB_PREFIX_.'sync_categories sc WHERE sc.ws_id LIKE "'.self::getLineValue($line,'ART_ASF').'" ORDER BY sc.ws_date_update DESC, sc.sync_id DESC');
		$product->id_category_default = $idCategory;

		$product->modifierWsLinkRewrite();

		if( $product->save() ){
			//set product stock
			StockAvailable::setQuantity($product->id, 0, (int) self::getLineValue($line,'ART_STK'));
			
			//set the category
			$product->addToCategories(array($idCategory));

			//set feature values
			$product->deleteFeatures();

			//-- families
			$fFamille = SyncManager::getFeatureByName('Famille');
			$product->addFeaturesToDB($fFamille->id,Db::getInstance()->getValue('SELECT sf.ps_id FROM '._DB_PREFIX_.'sync_features sf WHERE sf.ws_id LIKE "'.self::getLineValue($line,'FAM_DESIGNATION').'" AND sf.ps_id_feature = '.($fFamille->id).' ORDER BY sf.ws_date_update DESC, sf.sync_id DESC'));
			//-- categories
			$fCategory = SyncManager::getFeatureByName('Catégorie');
			$product->addFeaturesToDB($fCategory->id,Db::getInstance()->getValue('SELECT sf.ps_id FROM '._DB_PREFIX_.'sync_features sf WHERE sf.ws_id LIKE "'.self::getLineValue($line,'ART_CAT').'" AND sf.ps_id_feature = '.($fCategory->id).' ORDER BY sf.ws_date_update DESC, sf.sync_id DESC'));
			//-- natures
			$fNature = SyncManager::getFeatureByName('Nature');
			$product->addFeaturesToDB($fNature->id,Db::getInstance()->getValue('SELECT sf.ps_id FROM '._DB_PREFIX_.'sync_features sf WHERE sf.ws_id LIKE "'.self::getLineValue($line,'ART_NAT').'" AND sf.ps_id_feature = '.($fNature->id).' ORDER BY sf.ws_date_update DESC, sf.sync_id DESC'));
			//-- collections
			$fCol = SyncManager::getFeatureByName('Collection');
			$product->addFeaturesToDB($fCol->id,Db::getInstance()->getValue('SELECT sf.ps_id FROM '._DB_PREFIX_.'sync_features sf WHERE sf.ws_id LIKE "'.self::getLineValue($line,'ART_COL').'" AND sf.ps_id_feature = '.($fCol->id).' ORDER BY sf.ws_date_update DESC, sf.sync_id DESC'));


			//add syncProduct entry
			$sp->ws_id = $line['ART_ID'];
			$sp->ps_id = $product->id;
			$sp->ws_date_update = $line['ART_DATEUPDATE'];
			$sp->sync_id = $sync->id;
			$sp->save();
		}
	}
}

?>