# Commerce Yar Pricing Table

A beautiful and responsive WordPress pricing table plugin with support for multiple pricing periods and dynamic content loading.

## Description

Commerce Yar Pricing Table is a powerful WordPress plugin that allows you to create and display beautiful, responsive pricing tables with support for multiple pricing periods (monthly, quarterly, biannual, and yearly). The plugin features a modern design, smooth transitions, and efficient data handling through caching.

## Features

- 🎯 Multiple pricing periods:
  - ماهانه (Monthly)
  - سه ماهه (Quarterly)
  - شش ماهه (Biannual)
  - سالانه (Yearly)
- 🚀 Responsive design with Swiper slider
- ⚡ AJAX-powered dynamic content loading
- 💾 WordPress caching integration for optimal performance
- 🔒 Secure data handling with nonce verification
- 🎨 Customizable styling options
- 📱 Mobile-friendly interface
- 🔍 Hidden price codes for internal tracking
- 🔄 Real-time pricing updates
- 🌐 RTL support for Persian language

## Installation

1. Upload the `commerce-yar-pricing-table` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the shortcode `[commerce_yar_pricing]` to display the pricing table in any post or page

## Usage

### Basic Shortcode
```
[commerce_yar_pricing]
```

### Shortcode with Parameters
```
[commerce_yar_pricing type="monthly" theme="default"]
```

### Available Parameters
- `type`: (optional) Initial pricing period to display
  - Values: `monthly`, `quarterly`, `biannual`, `yearly`
  - Default: `monthly`
- `theme`: (optional) Theme variant for the pricing table
  - Default: `default`

## Database Structure

The plugin creates a custom table `wp_commerce_yar_token` with the following structure:

```sql
CREATE TABLE wp_commerce_yar_token (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    pricing_type varchar(20) NOT NULL,
    title varchar(255) NOT NULL,
    price decimal(10,2) NOT NULL,
    features text NOT NULL,
    button_text varchar(255) NOT NULL,
    button_link text NOT NULL,
    price_code varchar(50) NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

## Caching

The plugin implements WordPress caching for optimal performance:
- Cache duration: 1 hour
- Automatic cache clearing on pricing updates
- Separate cache for each pricing period

## Development

### File Structure
```
commerce-yar-pricing-table/
├── includes/
│   ├── class-commerce-yar-pricing-table-activator.php
│   └── class-commerce-yar-pricing-table-ajax.php
├── templates/
│   └── pricing-table.php
├── commerce-yar-pricing-table.php
└── README.md
```

### Hooks and Filters

The plugin provides several action hooks and filters for customization:

```php
// Actions
do_action('commerce_yar_before_pricing_table');
do_action('commerce_yar_after_pricing_table');

// Filters
apply_filters('commerce_yar_pricing_data', $pricing_data);
apply_filters('commerce_yar_style_data', $style_data);
```

## Security

- Input sanitization using WordPress sanitization functions
- AJAX nonce verification
- Prepared SQL statements
- Capability checks for administrative actions
- XSS prevention through escaping

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

## Support

For support, feature requests, or bug reporting, please visit our [GitHub repository](https://github.com/your-repo/commerce-yar-pricing-table) or contact our support team.

## License

This plugin is licensed under the GPL v2 or later.

## Credits

- [Swiper](https://swiperjs.com/) - Mobile touch slider
- [SweetAlert2](https://sweetalert2.github.io/) - Beautiful alerts and modals

## Changelog

### 1.0.0
- Initial release
- Multiple pricing period support
- AJAX-powered content loading
- Caching implementation
- RTL support
- Mobile-responsive design 