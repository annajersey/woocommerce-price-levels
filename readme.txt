=== WooCommerce Price Levels ===
Contributors: ethanpil
Tags: woocommerce, price levels
Stable tag: 1.0

Offer products to customers at different pricing levels, set specific prices per role, or calculate from cost, MSRP or another role.

== Description ==

This plugin works with the Woocommerce e-commerce system for WordPress. It allows the store owner to set custom pricing for customer groups (WordPress roles). Admin can create new customer roles / groups and assign customers to them. Additionally you can specify what type of pricing is provided to each role. 

Pricing for roles can be assigned as follows:

* Static Price Per Role (Enter on Product Editor Screen)
* Calculate Price from Another Role (Plus or Minus %)
* Calculate Price from Cost Price (Plus or Minus %)
* Calculate Price from MSRP (Plus or Minus %)

== Installation ==

1. Upload the entire 'woocommerce-price-levels' folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. You must have WooCommerce installed and activated to use this plugin

== Usage ==

In the WooCommerce section of the WordPress admin, there is a new subsection called "Customer Levels" where you can define the Roles or Customer Levels which will recieve special pricing. In every scenario, if there is never a price the system will always revert to the Regular Price. (If 0 is entered as a price, the product will be free. This way some roles can have access to free products, while others may be required to purchase.)

On the product editor screen, each role that is enabled for custom pricing will have a price box available. Additionally, cost and MSRP pricing fields will be available for use to calculate pricing.

When customers are logged in and shopping, they will see their assigned pricing.




== Changelog ==

= 1.0.0 =
* Initial Release
