<?php
/* Override for Delivery Dates Wizard */
abstract class PaymentModule extends PaymentModuleCore
{
	public function validateOrder($id_cart, $id_order_state, $amount_paid, $payment_method = 'Unknown', $message = null, $extra_vars = array(), $currency_special = null, $dont_touch_amount = false, $secure_key = false, Shop $shop = null)
	{
		$cart_ddw = Hook::exec('ddwValidateOrder', array('id_cart'=>$id_cart), null, true);
		$cart_ddw = $cart_ddw['deliverydateswizard'];

		$extra_vars['{DDW_ORDER_DATE}'] = '';
		$extra_vars['{DDW_ORDER_TIME}'] = '';

		if ($cart_ddw['ddw_order_date'] != '') $extra_vars['{DDW_ORDER_DATE}'] = Tools::displayDate($cart_ddw['ddw_order_date']);
		if ($cart_ddw['ddw_order_time'] != '') $extra_vars['{DDW_ORDER_TIME}'] = $cart_ddw['ddw_order_time'];

		return parent::validateOrder(
			$id_cart, $id_order_state, $amount_paid, $payment_method,
			$message, $extra_vars, $currency_special, $dont_touch_amount,
			$secure_key, $shop
		);
	}
}