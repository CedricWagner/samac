<?php
/**
* 
*/
class Notification extends ObjectModel
{
	
	public $id;
	public $content;
	public $level;
	public $source;
	public $date;

	private $__targets;

	public static $definition = array(
		'table' => 'notifications',
		'primary' => 'id',
		'fields' => array(
			'id' =>			 		array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
			'content' => 			array('type' => self::TYPE_STRING, 'required' => true),
			'level' => 				array('type' => self::TYPE_STRING, 'required' => true, 'size' => 1),
			'source' => 			array('type' => self::TYPE_STRING, 'required' => true, 'size' => 1),
			'date' => 				array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true),
		),
	);

	public static function getLastNotifications($nb=50){
		$lstNotifs = array();
		$result = Db::getInstance()->executeS('SELECT id FROM '._DB_PREFIX_.'notifications ORDER BY date DESC LIMIT '.$nb);
		foreach ($result as $aNotif) {
			$notif = new Notification($aNotif['id']);
			$lstNotifs[] = $notif;
		}

		return $lstNotifs;
	}

	public function getTargets(){
		if(!$this->__targets){
			$result = Db::getInstance()->executeS('SELECT id FROM '._DB_PREFIX_.'notification_customers nc WHERE nc.notif_id = '.$this->id);
			foreach ($result as $aNotifCustomer) {
				$nc = new NotificationCustomer($aNotifCustomer['id']);
				$this->__targets[] = $nc;
			}
		}

		return $this->__targets;
	}

}

?>