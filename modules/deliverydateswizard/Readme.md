Delivery Dates Wizard
==================
Introduction
------------
Delivery Dates Wizard (hereby referred to as DDW) is a module for Prestashop which allows your customers to choose a delivery date and time for
their orders. The module displays a calendar during checkout and available time slots. This is a great module if you need to allow your customers provide you with a date and time their order should be delivered or picked up from store. An essential module for merchants selling flowers, gifts, DIY products or any other product which benefits from having a deliverable date and time; The module also provides you (the merchant) with some important powerful features, all which will be discussed throughout this document.

Installation
------------
The installation procedure is very much similar to most other Prestashop modules. No core changes required therefore installation is straight forward, be sure to follow the instructions below to ensure a successful installation.

 1. Upload the deliverydateswizard module folder to your stores {root}/modules folder
 2. Once uploaded, login to your Prestashop Back Office and head over to the “Modules” section
 3. Search for the module “Delivery Dates Wizard” and click Install
 4. After a successful installation, the module configuration screen is presented to you. More details on this in the next section.
 5. In some cases Installation may fail due to a Module overrides conflict. In this case you can locate the override files and merge them manually or
contact me for additional support (refer to the end of this guide for contact details)

Configuration
-------------
The module configuration allows you to set up your calendars and time slots as well as manage some additional features.

**Setting up a Calendar**
DDW Allows you to setup a different calendar and time slots for each carrier in your store, further more each calendar can have it’s own unique set of blocked dates, weekdays and cut off times.
Choose a carrier for which you wish to enable the calendar for, click the edit link which appears next to the carrier name in the Carriers Table.
A new screen is displayed, divided in to 5 sections; namely, “General Settings”, “Blocked Weekdays”, “Min / Max Days”, “Blocked Dates” and “Time slots”. Each section is explained further below.

***General Settings***
*Enabled:* Enable or disable the module for the chosen carrier
*Required:* If enabled, will display a prompt if the customer does not select a delivery and time during checkout.

***Blocked Weekdays***
Tick any weekdays you’d like disabled for selection in the calendar widget.

***Min Max Days***
This section allows you to control which days are blocked in respect to the current date and time.
*Min Days:* Select the number of days from today to disable for selection in the calendar. Can be used with the cut off time (see below)
*Max Days:* Select The number of days from today which should be selectable in the calendar. All dates after this number will be disabled.
*Cut off time:* If you’ve set up min days (above) but you would like to introduce a cut off time during the day after which the Min days rolls over
another day, then set define cut off time and hours here.

***Blocked Dates***
The Blocked dates section allows you to set up a list of single dates or date ranges to disable for selection. This feature is useful for setting up holidays.
To set up a blocked date, click the add button in the “Blocked Dates” Table. This should take you to a new screen with the following options:

*Recurring:* If you’d like this date or date range to recur annually, tick the tick box.
*From:* The start of the date range to block
*To:* The end of the date range to block

***Time Slots***
Time slots allow you to set up a list of selectable time slots which are displayed next to the calendar during checkout. For example, you may wish to setup a time slot list similar to the following:

09:00 to 12:00
13:00 to 17:00
18:00 to 21:00

The customer will have the option to select one time slot during checkout. To add a new time slot click the add button in the time slots table and complete the following options

*time slots:* Your time slot text, you may enter any text in this field (e.g 09:00 to 12:00)
*position:* Manage the order in which the time slots are displayed by entering a number in this field, which will correspond to it’s visual position on the front end.

After setting up the options above and saving them, the module should now appear during the checkout process on the carrier selection screen.

Where can I see the date and time for the orders?
------------------
The delivery date and time for orders as selected by the customer during checkout are displayed in the following areas:
The Order Details page
The Orders List Page - You can also filter by Order Delivery Date
The Order Invoice
The Order Confirmation Email to the customer (see below for details on how to set this up).

**Displaying the Order Delivery Date and time in the Order Confirmation Email**
Before the order delivery date and time can be displayed in the order confirmation email, we need to two short codes to your order confirmation emails. Your order confirmation can be located in:

    /mails/en/order_conf.html

Where “en” should be substituted with the language(s) of your store. After locating the files add the following two variables to your file, feel free to add additional styling if required:

    <strong>Delivery date:</strong> {DDW_ORDER_DATE}<br>
    <strong>Delivery time:</strong> {DDW_ORDER_TIME}<br>

Integrating with the Mail Alerts Module
------------------
The Delivery Dates Wizard module can also be integrated with the Prestashop Mail Alerts module, the process however is manual and requires modifying the mail alerts module. Follow the steps below to integrate with the Mail Alerts Module.

1. In the /modules/mailalerts/mailalerts.php file, add the following lines of code to the $template_vars array in the validateOrder function. The array is declared around line 350 but this may vary from version to version and from site to site:

    '{ddw_order_date}' => $order->ddw_order_date,
    
    '{ddw_order_time}' => $order->ddw_order_time,

2. Add the following code just before the line of code which starts: if ($dir_mail) Mail::Send(. Again this is in the same file and function and occurs
around line 434.

    if (Module::isEnabled('DeliveryDatesWizard'))
    {
    	$cart_ddw = Hook::exec('ddwValidateOrder', array('id_cart'=>$params['cart']->id), null, true);
    	$cart_ddw = $cart_ddw['deliverydateswizard'];
    	$template_vars['{ddw_order_date}'] = ($cart_ddw['ddw_order_date'] != '' ? Tools::displayDate($cart_ddw['ddw_order_date']) : '');
    	$template_vars['{ddw_order_time}'] = ($cart_ddw['ddw_order_time'] != '' ? $cart_ddw['ddw_order_time'] : '');
    }


Support & Feedback
------------------

Should you run into any problems regarding the installation or usage of the module please feel free to contact me through the Prestashop addons site.

I would also welcome any feedback on the module, your feedback will help improve the module in the future.

