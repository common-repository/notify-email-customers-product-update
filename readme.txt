=== Bulk Email Notify Customers on Product Update for WooCommerce ===
Contributors: taz_bambu
Donate link: https://extend-wp.com/
Tags:  update notify, woocommerce notification, notify customers, send email, bulk email
Requires at least: 3.0.1
Requires PHP: 5.2.4
Tested up to: 6.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Bulk Email & Notify WooCommerce Customers when you update the Product they bought. Keep them on Track!
 
== Description ==

A handy and easy to use plugin to **send bulk emails** and **Notify WooCommerce Customers on Product Update**. 
Notify WooCommerce Customers when you update your Product. You can **Query by Order Date** of the specific product to notify the right customers. 
This plugin provides a **metabox** where you can select to send an Email when you create or Update a product.
This hooks into Save Post, so the email if subject and content are not empty will be send to the emails defined.
As far as WooCommerce Products, the plugin provides date fields to **Query Customers by Order creation date**.
Last but not least, you can select to include the metabox in **other post types**, such as **Post, Page** etc.

[youtube https://www.youtube.com/watch?v=4usW7pWO8Jc&rel=0]


= HOW DOES IT WORK =


* once activating the plugin, go to its Settings (submenu of WooCommerce or Products -Notify Customers- ) , enable the metabox ( enabled by default on activation )
* in plugin Settings page, you can select the post types where the email functionality metabox will appear ( product is default ) . You do this by writing comma separated in a textare * you need to know name of post type.
* Go to Add a new Product or Update an existing. In the edit screen the metabox will appear  - as long as the post type is select.
* In order to send email you need to
-- click the checkbox 'Enable'
-- for products :
1) select order creation date (FROM - TO logic) * if sending email to newly created product Order Date fields are not visible as there are no sales yet, but will be used when updating an existing product.
2) wait for Ajax technology to load the emails in the textarea or write emails on your own
3) fill your subject
4) fill your content
* the plugin will send email on post save  - name and email of your email will be admin email and website name.

* Once you update or Save the Email will be sent.

**MAIN FEATURES**

<blockquote>
- Query WooCommerce Customers by Order date per Product
- Ability to Enable in other Post types ( in this case order date fields will obviously not exist )
- Send Email on Product Update ( Post Save action ) - or any other post type if you enable it
- Send Bulk Emails to the comma separated email addresses
</blockquote>

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins` directory and unzip, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Products / Notify Customers or WooCommerce /  Notify Customers submenu to define your settings.
4. Edit your Product and you will find the Send Email metabox to select whether you need to send an Email on Product Update

== Frequently Asked Questions ==

= Can I use notify feature in other post types? = 
Yes, go to settings and insert comma separated the name of post types, eg page,post,product

= What if I am creating a new product? Can I still send an email ? = 
Yes, the only difference is there will be no date fields as this newly added product has no sales.




== Screenshots ==

1. Settings Page
2. Product Edit Screen

== Changelog ==

= 1.1 =
declare hpos compatibility 

= 1.0 =

== Upgrade Notice ==

= 1.0 =

= 1.1 =
declare hpos compatibility 