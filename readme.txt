=== WooCommerce CashApp Payment Gateway ===
Contributors: JD Farrell
Tags: woocommerce, payment gateway, cashapp, payments, online payments
Requires at least: 5.0
Tested up to: 5.8
Stable tag: 1.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Copyright: 2023 Digital Compass. All rights reserved.

== Description ==

The WooCommerce CashApp Payment Gateway plugin allows your customers to make payments using CashApp directly on your WooCommerce store. This plugin seamlessly integrates with your store, providing a convenient and secure payment method for your customers.

== Features ==

- **Easy Integration**: Quickly add CashApp as a payment option on your WooCommerce store.
- **Secure Payments**: CashApp payments are processed securely, providing peace of mind for both you and your customers.
- **Customizable**: Easily customize the payment gateway title and description displayed to customers during checkout.
- **CashApp Tag Support**: Collect payments with your unique CashApp tag.
- **QR Code Support**: Optionally, provide a QR code for customers to scan and pay.
- **Email Verification**: Verify the authenticity of payment confirmation emails.
- **Debugging**: Enable logging for troubleshooting and debugging.

== Installation ==

1. Upload the `woocommerce-cashapp-payment-gateway` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure the plugin settings under 'WooCommerce' > 'Settings' > 'Payments'.

== Configuration ==

1. Visit 'WooCommerce' > 'Settings' > 'Payments'.
2. Enable the 'CashApp' payment method.
3. Set your desired title and description for the payment method.
4. Enter your CashApp tag.
5. Optionally, enable QR code support if you want to provide a QR code for payments.
6. Configure your email settings, including the email server, port, username, and password.
7. Set the Cron Interval (in seconds) for checking emails. The default is 300 seconds (5 minutes).
8. Optionally, enable 'Retain Data on Uninstall' to keep plugin data upon uninstallation for testing purposes.
9. Enable 'Debug Log' to log CashApp events for troubleshooting.

== Usage ==

1. Customers will see the CashApp payment option during checkout.
2. They enter their CashApp tag.
3. Optionally, they can scan the QR code for payment.
4. Complete the checkout process.

== Frequently Asked Questions ==

### How do I obtain a CashApp tag?

To obtain a CashApp tag, you need to create a CashApp account. Once you have an account, you'll be assigned a unique CashApp tag that you can use to receive payments.

### How does the plugin verify payment confirmation emails?

The plugin verifies the authenticity of payment confirmation emails by checking the email headers, domain validity, and DKIM signatures. Emails from genuine sources are processed further.

### Can I customize the appearance of the CashApp payment method on my store?

Yes, you can customize the payment gateway title and description displayed to customers during checkout. Additionally, you can choose to enable QR code support for a more user-friendly payment experience.

== Changelog ==

= 1.1 =
* Included Updater drop in folder for automatic updates handling.

= 1.0 =
* Initial release

== Upgrade Notice ==

This plugin requires a valid license for updates and support. Licenses can be purchased from [Digital Compass](https://idkcode.com/). Upon expiration, you will no longer receive updates or support for the plugin unless the license is renewed.

== Screenshots ==

1. CashApp Payment Gateway settings in WooCommerce.
2. CashApp payment option during checkout.

== Credits ==

This plugin was developed by Digital Compass.

== Support ==

For support or inquiries, please contact [Your Contact Information](https://idkcode.com/).

== Contribute ==

If you'd like to contribute to the development of this plugin, please visit the [GitHub repository](https://github.com/your-repo) and submit a pull request.

== License ==

This plugin is licensed under the GPLv2 or later. You can find a copy of the license [here](https://www.gnu.org/licenses/gpl-2.0.html).
Copyright © 2023 Digital Compass. All rights reserved under the scope of the license.