<?php
if (!defined('_PS_VERSION_'))
	exit;

include_once(_PS_MODULE_DIR_.'/deliverydateswizard/lib/bootstrap.php');

class DeliveryDatesWizard extends Module {

	const __MA_MAIL_DELIMITOR__ = ',';

	public $file;
	public $controller_translations;

	protected $controller_front;
	protected $controller_admin_hooks;
	protected $controller_config;
	protected $controller_config_carrier;
	protected $controller_daterange;
	protected $controller_timeslots;

	public function __construct()
	{
		$this->name = 'deliverydateswizard';
		$this->tab = 'checkout';
		$this->version = '1.0.8';
		$this->author = 'Musaffar Patel';
		parent::__construct();
		$this->displayName = $this->l('Delivery Dates Wizard');
		$this->description = $this->l('Allow customers to conveniently select a delivery date during checkout');
		$this->module_key = '09a67e606346a3d088679ab0d58c4965';
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		$this->file = __FILE__;
		$this->bootstrap = true;

		/* Initialise controllers */
		$this->controller_front = new DDWControllerFront($this);
		$this->controller_admin_hooks = new DDWAdminHooks($this);
		$this->controller_config = new DDWConfigController($this);
		$this->controller_config_carrier = new DDWConfigCarrierController($this);
		$this->controller_daterange = new DDWDateRange($this);
		$this->controller_timeslots = new DDWTimeSlots($this);
		$this->controller_translations = new DDWTranslations($this);
	}

	public function install()
	{
		if (!parent::install()
			|| !$this->registerHook('processCarrier')
			|| !$this->registerHook('beforeCarrier')
			|| !$this->registerHook('adminOrder')
			|| !$this->registerHook('backOfficeHeader')
			|| !$this->registerHook('paymentConfirm')
			|| !$this->registerHook('newOrder')
			|| !$this->registerHook('PDFInvoice')
			|| !$this->registerHook('DdwValidateOrder')
			|| !$this->registerHook('actionOrderDetail')
			|| !$this->registerHook('displayPDFInvoice')
			|| !$this->registerHook('displayCarrierList')
			|| !$this->registerHook('header')
			|| !$this->registerHook('displayPDFDeliverySlip')
			|| !$this->registerHook('actionCarrierUpdate')
			|| !$this->installModule())
			return false;
		return true;
	}

	private function installModule()
	{
		DDWInstall::installDB();
		DDWInstall::installData();
		return true;
	}

	public function uninstall()
	{
		DDWInstall::uninstall();
		parent::uninstall();
	}

	public function route()
	{
		/* Edit DDW Carrier details */
		if (Tools::getIsset('updatecarriers'))
			return $this->controller_config_carrier->renderMain();

		/* Render Add / Edit blocked date form */
		if (Tools::getIsset('adddateform') || Tools::getIsset('updateddw_blocked_dates'))
			return $this->controller_daterange->renderAddEditForm();

		/* Render Add Time Slot form */
		if (Tools::getIsset('addtimeslotform'))
			return $this->controller_timeslots->renderTimeSlotForm();

		/* Render Add Time Slot form */
		if (Tools::getIsset('updateddw_timeslots'))
			return $this->controller_timeslots->renderTimeSlotForm();

		/* Process Add date / date range form */
		if (Tools::getIsset('processDDWDateRange'))
			return $this->controller_daterange->processAddEditForm();

		/* Delete a blocked date */
		if (Tools::getIsset('deleteddw_blocked_dates'))
			return $this->controller_daterange->processDeleteBlockedDate();

		if (Tools::getIsset('processDDWTimeSlotAdd'))
			return $this->controller_timeslots->processTimeSlotForm();

		if (Tools::getIsset('deleteddw_timeslots'))
			return $this->controller_timeslots->processDeleteTimeSlotForm();

		/* Process the DDW Carrier form details */
		if (Tools::getIsset('processDDWCarrierGeneral'))
			$this->controller_config_carrier->processGeneralForm();

		if (Tools::getIsset('processDDWCarrierWeekdays'))
			return $this->controller_config_carrier->processWeekdaysForm();

		/* Process the Min Max Days Form */
		if (Tools::getIsset('processMinMaxForm'))
			return $this->controller_config_carrier->processMinMaxForm();

		/* Route Translations */
		if (Tools::getIsset('updateddw_translations'))
			return $this->controller_translations->renderEditForm();
		if (Tools::getIsset('processTranslationEdit'))
			return $this->controller_translations->processTranslationEditForm();


		/* Route the date/time update from the order details page */
		if (Tools::getIsset('processOrderDDWUpdate'))
			return $this->controller_admin_hooks->processOrderDDWUpdate();
	}

	public function getContent()
	{
		$result = $this->route();
		if (!$result)
			return $this->controller_config->renderMain();
		else
			return $result;
	}

	/* Store Hooks */

	public function hookProcessCarrier($params)
	{
		$cart = $params['cart'];
		if (!($cart instanceof Cart))
			return;
		DDWModel::saveToCart(Tools::getValue('ddw_order_date'), Tools::getValue('ddw_order_time'), $cart->id);
		$cart->ddw_order_date = Tools::getValue('ddw_order_date');
		$cart->ddw_order_time = Tools::getValue('ddw_order_time');
	}

	public function hookBeforeCarrier($params)
	{
	}

	public function hookDisplayCarrierList()
	{
		$html = '';
		$html .= $this->controller_front->renderFrontWidget();
		return $html;
	}

	public function hookActionOrderDetail($params)
	{
	}

	public function hookAdminOrder($params)
	{
		$html = $this->controller_admin_hooks->renderOrderDetailBlock($params['id_order']);
		return $html;
	}

	public function hookPaymentConfirm($params)
	{
		//return("ok");
	}

	public function hookDisplayPDFInvoice($params)
	{
		return $this->controller_admin_hooks->renderHookDisplayPDFInvoice($params);
	}

	public function hookDdwValidateOrder($params)
	{
		return DDWModel::getCartDDWDateTime($params['id_cart']);
	}

	public function hookNewOrder($params)
	{
		$cart = $params['cart'];
		if (!($cart instanceof Cart))
			return;
		DDWModel::saveToOrder($cart);
	}

	public function hookHeader($params)
	{
		$this->context->controller->addJqueryUI('ui.datepicker');
	}

	public function hookDisplayPDFDeliverySlip($params)
	{
		return $this->controller_admin_hooks->renderHookDisplayPDFDeliverySlip($params);
	}
	
	public function hookActionCarrierUpdate($params)
	{
		DDWModel::updateCarrierID($params['id_carrier'], $params['carrier']->id);
	}
	


}
?>