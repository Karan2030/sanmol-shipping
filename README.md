### Installation

1. Download or clone the plugin repository.

2. then move the plugin folder into your WordPress -> wp-content -> plugins :

3. Log in to your WordPress admin dashboard.

4. Go to Plugins → Installed Plugins, then find "SanShip Express", and click Activate.

5. Go to WooCommerce → Settings → Shipping and enable SanShip Express as a shipping method (it is set on by default).

6. Go to WooCommerce → Settings → SanShip Express and add:
- Client ID
- Client Secret
- Mode: Sandbox / Production

## Screenshot

![Screenshot](assets/sanship-screenshots)

### Dummy API Integration

This plugin uses two public mock APIs to simulate live shipping features.

1. Live Rate Endpoint

`GET https://jsonplaceholder.typicode.com/posts/1`

Used during WooCommerce checkout to simulate shipping rates based on the data.

json response
{
  "userId": 1,
  "id": 1,
  "title": "test title",
  "body": "test body data"
}


### Plugin Architecture & Flow
Plugin Architecture

Main File: sanmol-shipping.php 
Initializes the plugin only if the woocommerce is installed, loads dependencies, and registers the shipping method.

Key Components:

class-sanship-shipping-method.php - registers “SanShip Express” using "WC_Shipping_Method"

class-sanship-settings.php - this is used to add the settings page under WooCommerce

Plugin Flow:

1. Admin activates plugin → enables “SanShip Express” in shipping zones.
2. This shipping method is added to the woocommerce and also this option is available while checkout.
3. when merchant mark order as “Completed” - the POST request simulates shipment.
4. Tracking number saved and shown to admin & customer.
5. Tracking link shown on My Account of user as well as in orders section.

Design Decision

- Used WordPress-native APIs and WooCommerce hooks for compatibility.
- Settings stored using to keep config inside WooCommerce.
- escaped all the outputs using esc_html and esc_url functions
- Used mock APIs jsonplaceholder and reqres.in to simulate live production behavior
- used __() function for tranlations purposes
  
Trade-offs

- Dummy APIs don’t support real auth or rate structures- it is calculated based on words length.
- No actual labels generated — dummy PDF link used as placeholder.
- Free API token auth is used.

More Info
- Each class and method includes DocBlocks for clarity.
- Key WooCommerce hooks are commented with usage context.
