=== Plugin Name ===
Tags: woo, woocommerce, voice, voicepay, pay, with
Requires at least: 4.4
Tested up to: 5.9
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Accept payments with voice.

== Description ==

This plugin lets you accept payments with voice.

For more information, visit [Voice Pay official website](https://voicepay.cash/ "Voice Pay official website").

== Frequently Asked Questions ==

= Do the users must open a Voicepay account? =

Yes. Their payment methods are stored by Stripe and connected to a voicepay account.

= Which devices users can pay with? =

Alexa devices, at the moment. For technical reasons, users cannot checkout via Alexa APP.

= What about security and SCA (Strong Customer Authentication) =

We of course had to keep in mind this critical point. Actually this is solved by the payment gateway we adopted. Therefore is not represent a problem for our transaction but is related to the connection between Voicepay account and the customer's payment method.

== Installation ==

= Minimum Requirements =

* WordPress 4.4 or greater

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "Voice Pay" and click Search Plugins. Once you’ve found our plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your webserver via your favorite FTP application. The
WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

== Changelog ==

= 2.0.0 =
* Added support to the version 2 of the Alexa Skill.