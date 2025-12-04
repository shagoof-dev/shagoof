# Ecommerce Plugin Documentation

**Shagoof E-commerce Platform** - Multi-country deployment

**Production URLs:**
- **EG (Egypt)**: https://eg.shagoof.com
- **UAE**: https://uae.shagoof.com
- **SA (Saudi Arabia)**: https://sa.shagoof.com

**Git Branches:**
- `eg` - Main branch (Egypt)
- `uae` - UAE branch
- `sa` - Saudi Arabia branch

This document provides a comprehensive overview of the Ecommerce plugin in the Botble CMS system.

## Table of Contents

1. [Overview](#overview)
2. [Plugin Structure](#plugin-structure)
3. [Core Models](#core-models)
4. [Key Features](#key-features)
5. [Cart System](#cart-system)
6. [Order Management](#order-management)
7. [Product Management](#product-management)
8. [Customer Management](#customer-management)
9. [Payment & Shipping](#payment--shipping)
10. [Routes & Controllers](#routes--controllers)
11. [Services & Helpers](#services--helpers)
12. [Database Schema](#database-schema)

---

## Overview

The Ecommerce plugin is the core e-commerce functionality for the Botble CMS platform. It provides:

- **Version**: 3.10.11
- **Namespace**: `Botble\Ecommerce\`
- **Provider**: `Botble\Ecommerce\Providers\EcommerceServiceProvider`
- **Minimum Core Version**: 7.3.0

### Key Capabilities

- Product catalog management (simple & variable products)
- Shopping cart functionality
- Order processing and management
- Customer accounts and authentication
- Payment gateway integration
- Shipping management
- Discounts and coupons
- Product reviews and ratings
- Inventory management
- Multi-currency support
- Tax calculation
- Flash sales
- Wishlist functionality
- Product specifications
- Digital products with license codes

---

## Plugin Structure

```
platform/plugins/ecommerce/
├── plugin.json                    # Plugin metadata
├── config/                        # Configuration files
│   ├── cart.php                  # Cart settings
│   ├── email.php                 # Email templates config
│   ├── general.php              # General ecommerce settings
│   ├── order.php                # Order settings
│   ├── permissions.php          # Permission definitions
│   └── shipping.php             # Shipping settings
├── database/
│   └── migrations/              # 140+ migration files
├── helpers/                      # Helper functions
│   ├── brands.php
│   ├── common.php
│   ├── constants.php
│   ├── currencies.php
│   ├── customer.php
│   ├── discounts.php
│   ├── order.php
│   ├── prices.php
│   ├── product-attributes.php
│   ├── product-categories.php
│   ├── product-options.php
│   ├── product-variations.php
│   └── products.php
├── public/                       # Compiled assets
│   ├── css/                      # Stylesheets
│   ├── js/                       # JavaScript files
│   └── images/                   # Images
├── resources/                    # Source files
│   ├── assets/                  # Source assets (SCSS, JS)
│   ├── email-templates/         # Email templates
│   ├── lang/                    # Translations (50+ languages)
│   ├── templates/               # Invoice templates
│   └── views/                   # Blade templates (300+ files)
├── routes/                       # Route definitions
│   ├── ajax.php
│   ├── api.php
│   ├── base.php
│   ├── cart.php
│   ├── compare.php
│   ├── customer.php
│   ├── discount.php
│   ├── invoice.php
│   ├── order.php
│   ├── product.php
│   ├── product-inventory.php
│   ├── product-price.php
│   ├── product-specification.php
│   ├── review.php
│   ├── setting.php
│   ├── shipment.php
│   ├── shipping.php
│   ├── tax.php
│   └── wishlist.php
└── src/                         # PHP source code
    ├── AdsTracking/             # Facebook Pixel, Google Tag Manager
    ├── Cart/                    # Cart system
    ├── Charts/                  # Reporting charts
    ├── Commands/                # Artisan commands
    ├── Database/Seeders/        # Data seeders
    ├── Enums/                   # Enum classes (23 enums)
    ├── Events/                  # Event classes (17 events)
    ├── Exceptions/              # Custom exceptions
    ├── Exports/                  # Export classes
    ├── Facades/                  # Facade classes
    ├── Forms/                    # Form definitions (55 forms)
    ├── Http/                     # HTTP layer
    │   ├── Controllers/         # Controllers (262 files)
    │   │   ├── API/            # API controllers
    │   │   ├── Customers/      # Customer controllers
    │   │   ├── Fronts/         # Frontend controllers
    │   │   └── Settings/       # Settings controllers
    │   └── Requests/            # Form requests
    ├── Importers/                # Import classes
    ├── Jobs/                     # Queue jobs
    ├── Listeners/                # Event listeners (31 listeners)
    ├── Models/                   # Eloquent models (59 models)
    ├── Observers/                # Model observers
    ├── Option/                   # Product options
    ├── PanelSections/            # Admin panel sections
    ├── Providers/                # Service providers
    ├── Repositories/             # Repository pattern (99 repositories)
    ├── Rules/                    # Validation rules
    ├── Services/                 # Service classes (44 services)
    ├── Supports/                 # Support classes
    ├── Tables/                   # Data table definitions (33 tables)
    ├── Traits/                   # Reusable traits
    ├── ValueObjects/             # Value objects
    ├── Widgets/                  # Dashboard widgets (22 widgets)
    └── Plugin.php                # Main plugin class
```

---

## Core Models

### Product Models

#### `Product` (`ec_products`)
Main product model with extensive features:

**Key Fields:**
- `name`, `description`, `content`
- `sku`, `barcode`
- `price`, `sale_price`, `sale_type`
- `quantity`, `stock_status`
- `is_featured`, `is_variation`
- `brand_id`, `tax_id`
- `images`, `video_media`
- `weight`, `length`, `wide`, `height`
- `minimum_order_quantity`, `maximum_order_quantity`
- `with_storehouse_management`
- `allow_checkout_when_out_of_stock`
- `generate_license_code` (for digital products)
- `specification_table_id`

**Relationships:**
- `categories()` - Product categories (many-to-many)
- `tags()` - Product tags (many-to-many)
- `brand()` - Brand (belongs to)
- `tax()` - Tax (belongs to)
- `variations()` - Product variations (has many)
- `reviews()` - Product reviews (has many)
- `wishlist()` - Wishlist items (has many)
- `cart()` - Cart items (has many)
- `orderProducts()` - Order products (has many)

**Key Methods:**
- `getSalePrice()` - Get sale price
- `getOriginalPrice()` - Get original price
- `isOutOfStock()` - Check if out of stock
- `canAddToCart($qty)` - Check if can add to cart
- `getVariationByAttributes()` - Get variation by attributes

#### `ProductCategory` (`ec_product_categories`)
Hierarchical product categories with tree structure.

#### `ProductVariation` (`ec_product_variations`)
Product variations (e.g., size, color combinations).

#### `ProductAttribute` (`ec_product_attributes`)
Product attributes (e.g., Color, Size).

#### `ProductAttributeSet` (`ec_product_attribute_sets`)
Groups of product attributes.

#### `Brand` (`ec_brands`)
Product brands/manufacturers.

#### `ProductCollection` (`ec_product_collections`)
Product collections for grouping products.

#### `ProductLabel` (`ec_product_labels`)
Product labels (e.g., "New", "Sale", "Hot").

#### `ProductTag` (`ec_product_tags`)
Product tags for tagging.

### Order Models

#### `Order` (`ec_orders`)
Main order model:

**Key Fields:**
- `status` - Order status enum
- `user_id` - Customer ID
- `amount` - Total amount
- `sub_total` - Subtotal
- `tax_amount` - Tax amount
- `shipping_amount` - Shipping cost
- `discount_amount` - Discount amount
- `coupon_code` - Applied coupon
- `shipping_method`, `shipping_option`
- `is_confirmed`, `is_finished`
- `token` - Unique order token
- `completed_at` - Completion timestamp
- `cancellation_reason` - Cancellation reason
- `proof_file` - Payment proof file

**Relationships:**
- `user()` - Customer (belongs to)
- `products()` - Order products (has many)
- `address()` - Shipping address (has one)
- `billingAddress()` - Billing address (has one)
- `histories()` - Order history (has many)
- `shipment()` - Shipment (has one)
- `payment()` - Payment (belongs to)
- `invoice()` - Invoice (has one)

**Order Statuses:**
- `pending` - Pending payment
- `processing` - Processing
- `completed` - Completed
- `canceled` - Canceled
- `refunding` - Refunding
- `refunded` - Refunded

#### `OrderProduct` (`ec_order_product`)
Products in an order:

**Key Fields:**
- `order_id`, `product_id`
- `product_name`, `product_image`
- `qty` - Quantity
- `price` - Unit price
- `tax_amount` - Tax amount
- `weight` - Product weight
- `options` - Product options (JSON)
- `product_options` - Product options (JSON)
- `restock_quantity` - Quantity to restock
- `product_type` - Product type enum
- `license_code` - License code (for digital products)

#### `OrderAddress` (`ec_order_addresses`)
Shipping and billing addresses for orders.

#### `OrderHistory` (`ec_order_histories`)
Order status change history.

#### `OrderReturn` (`ec_order_returns`)
Order return requests.

### Customer Models

#### `Customer` (`ec_customers`)
Customer model extending Laravel's Authenticatable:

**Key Fields:**
- `name`, `email`, `phone`
- `avatar` - Profile picture
- `dob` - Date of birth
- `gender` - Gender
- `description` - Bio
- `status` - Account status
- `email_verified_at` - Email verification

**Relationships:**
- `orders()` - Customer orders (has many)
- `addresses()` - Customer addresses (has many)
- `reviews()` - Product reviews (has many)
- `wishlist()` - Wishlist items (has many)

#### `Address` (`ec_customer_addresses`)
Customer shipping/billing addresses.

### Cart Models

#### `Cart` (Session-based)
Cart is stored in session, not database:

**Key Methods:**
- `add($id, $name, $qty, $price, $options)` - Add item
- `update($rowId, $qty)` - Update quantity
- `remove($rowId)` - Remove item
- `content()` - Get all items
- `count()` - Get item count
- `total()` - Get total price
- `subtotal()` - Get subtotal
- `tax()` - Get tax amount
- `destroy()` - Empty cart

**Cart Item Structure:**
- `rowId` - Unique row ID
- `id` - Product ID
- `name` - Product name
- `qty` - Quantity
- `price` - Unit price
- `options` - Product options (attributes, variations)

### Other Models

#### `Discount` (`ec_discounts`)
Discounts and coupons.

#### `FlashSale` (`ec_flash_sales`)
Flash sale campaigns.

#### `Review` (`ec_reviews`)
Product reviews and ratings.

#### `Wishlist` (`ec_wish_lists`)
Customer wishlists.

#### `Currency` (`ec_currencies`)
Multi-currency support.

#### `Tax` (`ec_taxes`)
Tax rates and rules.

#### `Shipping` (`ec_shipping`)
Shipping methods.

#### `ShippingRule` (`ec_shipping_rules`)
Shipping rules based on location/weight.

#### `Shipment` (`ec_shipments`)
Shipment tracking.

#### `Invoice` (`ec_invoices`)
Order invoices.

---

## Key Features

### 1. Product Management

#### Product Types
- **Simple Products** - Standard products
- **Variable Products** - Products with variations (size, color, etc.)
- **Digital Products** - Downloadable products with license codes
- **Grouped Products** - Products sold as a group

#### Product Variations
- Support for multiple attributes (e.g., Size + Color)
- Automatic variation generation
- Variation-specific pricing and inventory
- Default variation selection

#### Product Options
- Global options (reusable across products)
- Product-specific options
- Option types: Text, Textarea, Dropdown, Radio, Checkbox

#### Product Specifications
- Specification groups
- Specification attributes
- Specification tables
- Display specifications on product pages

### 2. Cart System

**Session-Based Cart:**
- Stored in Laravel session
- Supports multiple cart instances
- Automatic tax calculation
- Coupon/discount application
- Cart persistence across sessions

**Cart Features:**
- Add/update/remove items
- Quantity validation
- Stock checking
- Price calculation
- Tax calculation
- Shipping cost calculation
- Discount application

**Cart Events:**
- `cart.added` - Item added
- `cart.updated` - Item updated
- `cart.removed` - Item removed
- `cart.cleared` - Cart cleared

### 3. Order Management

#### Order Lifecycle
1. **Pending** - Order created, awaiting payment
2. **Processing** - Payment confirmed, order processing
3. **Completed** - Order fulfilled and delivered
4. **Canceled** - Order canceled
5. **Refunding** - Refund in process
6. **Refunded** - Refund completed

#### Order Features
- Order creation from cart
- Order confirmation emails
- Order tracking
- Order history
- Order cancellation
- Order returns
- Invoice generation
- Payment proof upload
- Guest checkout support

#### Order Calculations
- Subtotal calculation
- Tax calculation
- Shipping cost calculation
- Discount application
- Final amount calculation

### 4. Customer Management

#### Customer Features
- Customer registration
- Email verification
- Password reset
- Profile management
- Address management
- Order history
- Product reviews
- Wishlist
- Downloads (for digital products)
- Account deletion

#### Customer Authentication
- Separate customer guard (`customer`)
- Customer-specific middleware
- Customer dashboard
- Customer API authentication

### 5. Payment Integration

**Supported Payment Methods:**
- Cash on Delivery (COD)
- Bank Transfer
- PayPal
- Stripe
- Razorpay
- Paystack
- SSLCommerz
- Mollie
- Stripe Connect (for marketplace)

**Payment Features:**
- Payment gateway integration
- Payment status tracking
- Payment proof upload
- Refund processing
- Payment callbacks

### 6. Shipping Management

**Shipping Features:**
- Multiple shipping methods
- Shipping rules based on:
  - Location (country, state, city)
  - Weight
  - Price
- Shipping cost calculation
- Shipment tracking
- Shipping labels
- Store locators

**Shipping Methods:**
- Default shipping
- Free shipping
- Flat rate shipping
- Weight-based shipping
- Price-based shipping
- Location-based shipping

### 7. Discounts & Coupons

**Discount Types:**
- Percentage discount
- Fixed amount discount
- Free shipping

**Discount Targets:**
- All products
- Specific products
- Product categories
- Product collections
- Specific customers

**Discount Features:**
- Coupon codes
- Usage limits
- Minimum purchase requirements
- Expiry dates
- Customer-specific discounts

### 8. Product Reviews

**Review Features:**
- Star ratings (1-5 stars)
- Review text
- Review replies
- Review moderation
- Review approval
- Review statistics

### 9. Inventory Management

**Inventory Features:**
- Stock tracking
- Low stock alerts
- Out of stock handling
- Stock status management
- Quantity management
- Automatic stock deduction on order

### 10. Multi-Currency Support

**Currency Features:**
- Multiple currencies
- Currency switching
- Exchange rate management
- Automatic exchange rate updates
- Currency formatting

### 11. Tax Management

**Tax Features:**
- Tax rates
- Tax rules
- Tax calculation
- Tax-inclusive/exclusive pricing
- Location-based tax
- Product-specific tax

### 12. Flash Sales

**Flash Sale Features:**
- Time-limited sales
- Discount percentages
- Product-specific flash sales
- Countdown timers
- Automatic activation/deactivation

### 13. Wishlist

**Wishlist Features:**
- Add/remove products
- Share wishlist
- Move to cart
- Wishlist management

### 14. Product Comparison

**Comparison Features:**
- Compare multiple products
- Side-by-side comparison
- Feature comparison

---

## Cart System

### Cart Implementation

The cart system is session-based and uses the `Cart` facade:

```php
use Botble\Ecommerce\Facades\Cart;

// Add item to cart
Cart::instance('cart')->add($productId, $productName, $quantity, $price, $options);

// Update cart item
Cart::instance('cart')->update($rowId, $quantity);

// Remove item
Cart::instance('cart')->remove($rowId);

// Get cart content
$items = Cart::instance('cart')->content();

// Get cart total
$total = Cart::instance('cart')->total();

// Clear cart
Cart::instance('cart')->destroy();
```

### Cart Item Structure

```php
CartItem {
    rowId: string,        // Unique row identifier
    id: int|string,       // Product ID
    name: string,         // Product name
    qty: int,             // Quantity
    price: float,         // Unit price
    options: array,       // Product options (attributes, variations)
    subtotal: float,      // Subtotal (qty * price)
    tax: float,           // Tax amount
    total: float          // Total (subtotal + tax)
}
```

### Cart Routes

**Frontend Routes:**
- `GET /cart` - View cart
- `POST /cart/add-to-cart` - Add item
- `POST /cart/update` - Update cart
- `GET /cart/remove/{id}` - Remove item
- `GET /cart/destroy` - Empty cart

**API Routes:**
- `POST /api/cart` - Add to cart
- `PUT /api/cart/{id}` - Update cart item
- `DELETE /api/cart/{id}` - Remove item

---

## Order Management

### Order Creation Flow

1. **Cart Checkout** - Customer proceeds to checkout
2. **Information Collection** - Collect shipping/billing info
3. **Shipping Calculation** - Calculate shipping costs
4. **Tax Calculation** - Calculate taxes
5. **Discount Application** - Apply coupons/discounts
6. **Payment Processing** - Process payment
7. **Order Creation** - Create order record
8. **Inventory Update** - Deduct stock
9. **Email Notification** - Send confirmation email
10. **Order Confirmation** - Confirm order

### Order Controllers

**Admin Controllers:**
- `OrderController` - Order management
- `OrderExportController` - Order export
- `OrderReturnController` - Order returns

**Frontend Controllers:**
- `PublicCheckoutController` - Checkout process
- `OrderController` (Customer) - Customer order management

**API Controllers:**
- `OrderController` (API) - Order API endpoints
- `OrderTrackingController` - Order tracking

### Order Routes

**Admin Routes:**
- `GET /ecommerce/orders` - List orders
- `GET /ecommerce/orders/{id}` - View order
- `POST /ecommerce/orders` - Create order
- `PUT /ecommerce/orders/{id}` - Update order
- `POST /ecommerce/orders/{id}/confirm` - Confirm order
- `POST /ecommerce/orders/{id}/cancel` - Cancel order
- `POST /ecommerce/orders/{id}/refund` - Refund order

**Frontend Routes:**
- `GET /checkout/{token}` - Checkout page
- `POST /checkout/{token}/process` - Process checkout
- `GET /checkout/{token}/success` - Checkout success

**Customer Routes:**
- `GET /customer/orders` - Customer orders
- `GET /customer/orders/{id}` - View order
- `POST /customer/orders/{id}/cancel` - Cancel order

---

## Product Management

### Product Controllers

**Admin Controllers:**
- `ProductController` - Product CRUD
- `ProductCategoryController` - Category management
- `ProductTagController` - Tag management
- `ProductAttributeSetsController` - Attribute management
- `ProductOptionController` - Option management
- `ProductPriceController` - Bulk price management
- `ProductInventoryController` - Bulk inventory management
- `ProductLicenseCodeController` - License code management

**Frontend Controllers:**
- `PublicProductController` - Product display
- `QuickViewController` - Quick view
- `QuickShopController` - Quick shop

**API Controllers:**
- `ProductController` (API) - Product API
- `ProductCategoryController` (API) - Category API

### Product Routes

**Admin Routes:**
- `GET /ecommerce/products` - List products
- `GET /ecommerce/products/create` - Create product
- `POST /ecommerce/products` - Store product
- `GET /ecommerce/products/{id}/edit` - Edit product
- `PUT /ecommerce/products/{id}` - Update product
- `DELETE /ecommerce/products/{id}` - Delete product
- `POST /ecommerce/products/{id}/duplicate` - Duplicate product

**Frontend Routes:**
- `GET /products` - Product listing
- `GET /products/{slug}` - Product detail
- `GET /ajax/quick-view/{id}` - Quick view

---

## Customer Management

### Customer Controllers

**Admin Controllers:**
- `CustomerController` - Customer management
- `AddressController` - Address management
- `ExportCustomerController` - Customer export
- `ImportCustomerController` - Customer import

**Frontend Controllers:**
- `LoginController` - Customer login
- `RegisterController` - Customer registration
- `PublicController` - Customer dashboard
- `OrderController` (Customer) - Customer orders
- `ForgotPasswordController` - Password reset
- `ResetPasswordController` - Password reset

**API Controllers:**
- `AccountDeletionController` - Account deletion

### Customer Routes

**Admin Routes:**
- `GET /customers` - List customers
- `GET /customers/{id}` - View customer
- `POST /customers` - Create customer
- `PUT /customers/{id}` - Update customer
- `DELETE /customers/{id}` - Delete customer

**Frontend Routes:**
- `GET /login` - Login page
- `POST /login` - Login
- `GET /register` - Registration page
- `POST /register` - Register
- `GET /customer/overview` - Customer dashboard
- `GET /customer/orders` - Customer orders
- `GET /customer/address` - Addresses
- `GET /customer/edit-account` - Edit account

---

## Payment & Shipping

### Payment Integration

Payment is handled through the `payment` plugin integration:

```php
// Create payment for order
$payment = app(CreatePaymentForOrderService::class)->execute($order, $paymentData);
```

### Shipping Integration

Shipping calculation:

```php
use Botble\Ecommerce\Services\HandleShippingFeeService;

$shippingFee = app(HandleShippingFeeService::class)
    ->execute($shippingData, $products, $orderTotal);
```

---

## Routes & Controllers

### Route Files

The plugin uses multiple route files for organization:

- `base.php` - Base admin routes
- `product.php` - Product routes
- `order.php` - Order routes
- `cart.php` - Cart routes
- `customer.php` - Customer routes
- `discount.php` - Discount routes
- `shipping.php` - Shipping routes
- `tax.php` - Tax routes
- `review.php` - Review routes
- `wishlist.php` - Wishlist routes
- `compare.php` - Compare routes
- `invoice.php` - Invoice routes
- `ajax.php` - AJAX routes
- `api.php` - API routes

### Controller Organization

**Admin Controllers:**
- Located in `src/Http/Controllers/`
- Handle admin panel operations
- Use repository pattern
- Return views or JSON responses

**Frontend Controllers:**
- Located in `src/Http/Controllers/Fronts/`
- Handle public-facing pages
- Use theme system
- Return Blade views

**API Controllers:**
- Located in `src/Http/Controllers/API/`
- Handle API requests
- Return JSON responses
- Use API authentication

**Customer Controllers:**
- Located in `src/Http/Controllers/Customers/`
- Handle customer account operations
- Use customer authentication middleware

---

## Services & Helpers

### Key Services

#### Product Services
- `ProductPriceService` - Price calculation
- `ProductCrossSalePriceService` - Cross-sale pricing
- `ProductCacheService` - Product caching
- `StoreProductService` - Product storage
- `CreateProductVariationsService` - Variation creation
- `DuplicateProductService` - Product duplication

#### Order Services
- `HandleCheckoutOrderData` - Checkout data handling
- `CreatePaymentForOrderService` - Payment creation
- `HandleShippingFeeService` - Shipping calculation
- `HandleTaxService` - Tax calculation
- `HandleApplyCouponService` - Coupon application
- `HandleRemoveCouponService` - Coupon removal

#### Cart Services
- `AbandonedCartService` - Abandoned cart tracking

#### Other Services
- `ProductWishlistService` - Wishlist management
- `TaxCalculatorService` - Tax calculation
- `StoreCurrenciesService` - Currency management
- `PromotionCacheService` - Promotion caching

### Helper Functions

Located in `helpers/` directory:

- `products.php` - Product helpers
- `order.php` - Order helpers
- `customer.php` - Customer helpers
- `prices.php` - Price helpers
- `currencies.php` - Currency helpers
- `discounts.php` - Discount helpers
- `shipping.php` - Shipping helpers
- `product-attributes.php` - Attribute helpers
- `product-variations.php` - Variation helpers
- `product-categories.php` - Category helpers
- `product-options.php` - Option helpers

**Example Helper Functions:**
```php
get_product_by_id($id)
get_products($params)
get_featured_products($params)
get_products_on_sale($params)
get_cart()
get_order_by_token($token)
format_price($price)
```

---

## Database Schema

### Main Tables

**Product Tables:**
- `ec_products` - Products
- `ec_product_categories` - Categories
- `ec_product_category_product` - Product-category pivot
- `ec_product_tags` - Tags
- `ec_product_tag_product` - Product-tag pivot
- `ec_product_variations` - Variations
- `ec_product_variation_items` - Variation items
- `ec_product_attributes` - Attributes
- `ec_product_attribute_sets` - Attribute sets
- `ec_product_with_attribute_set` - Product-attribute pivot
- `ec_brands` - Brands
- `ec_product_collections` - Collections
- `ec_product_collection_products` - Collection-product pivot
- `ec_product_labels` - Labels
- `ec_product_label_products` - Label-product pivot
- `ec_options` - Options
- `ec_option_value` - Option values
- `ec_global_options` - Global options
- `ec_global_option_value` - Global option values
- `ec_product_files` - Product files (digital products)
- `ec_product_license_codes` - License codes

**Order Tables:**
- `ec_orders` - Orders
- `ec_order_product` - Order products
- `ec_order_addresses` - Order addresses
- `ec_order_histories` - Order history
- `ec_order_returns` - Order returns
- `ec_order_return_items` - Return items
- `ec_order_return_histories` - Return history
- `ec_order_referrals` - Order referrals
- `ec_order_tax_information` - Tax information

**Customer Tables:**
- `ec_customers` - Customers
- `ec_customer_addresses` - Customer addresses
- `ec_customer_password_resets` - Password resets
- `ec_customer_recently_viewed_products` - Recently viewed
- `ec_customer_used_coupons` - Used coupons

**Cart & Wishlist:**
- `ec_cart` - Cart (for persistence)
- `ec_wish_lists` - Wishlists
- `ec_shared_wishlists` - Shared wishlists

**Other Tables:**
- `ec_currencies` - Currencies
- `ec_taxes` - Taxes
- `ec_tax_rules` - Tax rules
- `ec_tax_products` - Product-tax pivot
- `ec_discounts` - Discounts
- `ec_discount_products` - Discount-product pivot
- `ec_discount_customers` - Discount-customer pivot
- `ec_discount_product_categories` - Discount-category pivot
- `ec_discount_product_collections` - Discount-collection pivot
- `ec_flash_sales` - Flash sales
- `ec_flash_sale_products` - Flash sale products
- `ec_reviews` - Reviews
- `ec_review_replies` - Review replies
- `ec_shipping` - Shipping methods
- `ec_shipping_rules` - Shipping rules
- `ec_shipping_rule_items` - Shipping rule items
- `ec_shipments` - Shipments
- `ec_shipment_histories` - Shipment history
- `ec_store_locators` - Store locators
- `ec_invoices` - Invoices
- `ec_invoice_items` - Invoice items
- `ec_grouped_products` - Grouped products
- `ec_product_views` - Product views
- `ec_specification_groups` - Specification groups
- `ec_specification_attributes` - Specification attributes
- `ec_specification_tables` - Specification tables
- `ec_product_specification_attribute` - Product specifications

**Translation Tables:**
- `ec_products_translations`
- `ec_product_categories_translations`
- `ec_product_attributes_translations`
- `ec_product_attribute_sets_translations`
- `ec_brands_translations`
- `ec_product_collections_translations`
- `ec_product_labels_translations`
- `ec_product_tags_translations`
- `ec_flash_sales_translations`
- `ec_taxes_translations`
- `ec_options_translations`
- `ec_option_value_translations`
- `ec_global_options_translations`
- `ec_global_option_value_translations`
- `ec_specification_groups_translations`
- `ec_specification_attributes_translations`
- `ec_specification_tables_translations`

---

## Facades

The plugin provides several facades:

- `Cart` - Cart operations
- `OrderHelper` - Order helper functions
- `OrderReturnHelper` - Order return helpers
- `EcommerceHelper` - General ecommerce helpers
- `ProductCategoryHelper` - Category helpers
- `Currency` - Currency operations
- `InvoiceHelper` - Invoice helpers
- `FlashSale` - Flash sale operations

---

## Events & Listeners

### Key Events

- `OrderCreated` - Order created
- `OrderCompleted` - Order completed
- `OrderCanceled` - Order canceled
- `ProductQuantityUpdatedEvent` - Product quantity updated
- `OrderStatusChanged` - Order status changed

### Listeners

31 listeners handle various events for:
- Order processing
- Email notifications
- Inventory updates
- Cache invalidation
- Analytics tracking

---

## Permissions

The plugin defines extensive permissions in `config/permissions.php`:

- `plugins.ecommerce` - Main ecommerce permission
- `products.*` - Product permissions
- `orders.*` - Order permissions
- `customers.*` - Customer permissions
- `ecommerce.report.*` - Report permissions
- `ecommerce.settings` - Settings permission
- And many more...

---

## Configuration

### General Settings (`config/general.php`)
- Product settings
- Cart settings
- Checkout settings
- Review settings
- Digital product settings

### Cart Settings (`config/cart.php`)
- Cart instance name
- Cart expiration
- Tax calculation settings

### Order Settings (`config/order.php`)
- Order number format
- Order confirmation settings
- Order status settings

### Shipping Settings (`config/shipping.php`)
- Shipping methods
- Shipping calculation settings

### Email Settings (`config/email.php`)
- Email template settings
- Notification settings

---

## API Endpoints

The plugin provides RESTful API endpoints:

**Products:**
- `GET /api/products` - List products
- `GET /api/products/{id}` - Get product
- `GET /api/product-categories` - List categories

**Cart:**
- `POST /api/cart` - Add to cart
- `PUT /api/cart/{id}` - Update cart
- `DELETE /api/cart/{id}` - Remove from cart

**Orders:**
- `POST /api/orders` - Create order
- `GET /api/orders/{id}` - Get order
- `GET /api/orders/tracking/{token}` - Track order

**Customers:**
- `POST /api/customers/register` - Register
- `POST /api/customers/login` - Login
- `GET /api/customers/me` - Get current customer

---

## Best Practices

1. **Use Repositories** - Always use repository interfaces, not models directly
2. **Use Facades** - Use provided facades for common operations
3. **Use Helpers** - Use helper functions for common tasks
4. **Handle Events** - Listen to events for custom logic
5. **Validate Input** - Always validate user input
6. **Check Permissions** - Verify permissions before operations
7. **Use Services** - Use service classes for complex operations
8. **Cache Products** - Use product cache for performance
9. **Handle Errors** - Properly handle exceptions
10. **Follow Conventions** - Follow Laravel and Botble conventions

---

## Additional Resources

- Plugin Source: `platform/plugins/ecommerce/`
- Helper Functions: `platform/plugins/ecommerce/helpers/`
- Models: `platform/plugins/ecommerce/src/Models/`
- Controllers: `platform/plugins/ecommerce/src/Http/Controllers/`
- Services: `platform/plugins/ecommerce/src/Services/`
- Routes: `platform/plugins/ecommerce/routes/`

---

**Note:** This documentation is based on the ecommerce plugin version 3.10.11. Always refer to the latest codebase for the most up-to-date information.

