# The Bible Plugin

WordPress Plugin for a Bible Reading interface initially using the Bible Brain / Digital Bible Platform API.

## Plugin Details

- **Plugin URI:** [The Bible Plugin on GitHub](https://github.com/Reaching-Asia-Inc/The-Bible-Plugin)
- **Author:** [Reaching Asia](https://reachingasia.com)
- **Version:** rc1
- **Requires WordPress:** 4.7+
- **Tested up to:** 5.6
- **Requires PHP:** 7.4+

## Requirements

- PHP 7.4 or higher
- PHP extensions:
    - `json`
    - `gettext`
    - `zip`

## Installation

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the “Plugins” menu in WordPress.
3. Configure the API credentials or data source in the plugin settings.

## License

This plugin is licensed under the **GNU General Public License v2.0 or later**. See the [LICENSE](LICENSE) file for details.

## Development

This guide will help you set up the development environment for the Bible Plugin.

### Prerequisites

- PHP 7.4 or higher
- Node.js and npm
- Docker and DDEV (recommended)
- Composer
- WordPress

### Development Setup

Install PHP dependencies:

```bash
composer install
```

Install JavaScript dependencies:

```bash
npm install
```

#### DDEV Setup

Copy the example DDEV configuration to your WordPress root:

```bash
cp -r ddev/* /path/to/wordpress/ddev/
```

Start DDEV:

```bash
bash ddev start
```

### Testing

Install WordPress test environment:

```bash
  bin/install-wp-tests.sh <db-name> <db-user> <db-pass>, [db-host]
```

To install tests from within DDEV:

- `ddev ssh`
- `cd` to your plugin directory.
- run:

```bash
bash bin/install-wp-tests.sh testing db db db:3306
```

### Asset building

Development mode (with watch):

```bash
npm run dev
```

Production build:

```bash
bash npm run build
```
### Linting

To fix errors:

```bash
vendor/bin/phpcbf
```

To lint:

```bash
vendor/bin/phpcs
```

### Structure

Important files:

```
├── src/
│   ├── Controllers/         # API Controllers
│   ├── Providers/           # Service Providers
│   ├── Services/            # Business Logic
│   ├── Sources/             # Data Sources
│   │   ├── Aggregators/     # Data aggregation services
│   │   ├── BibleBrains/     # BibleBrains API integration
│   │   ├── Local/           # Local data sources
│   │   ├── Cache/           # Caching implementations
│   │   └── Contracts/       # Interfaces and abstracts
│   └── Resources/           # Resource Transformers
├── tests/
│   ├── fixtures/            # Test Fixtures
│   └── ...                  # Test Files
├── resources/
│   ├── js/                  # JavaScript Source
|   │── views/               # Plates-based template files
│   └── css/                 # Styles
├── config/
│   ├── app.php              # Main application config
│   ├── services.php         # Service providers config
│   └── options.php          # Plugin options config
├── bin/
    ├── install-wp-tests.sh  # WordPress test installer
    └── build.sh             # Asset build script
```
#### Working with Service Providers

The application uses League Container for dependency injection and service management. For detailed information about container setup, service providers, definitions, and advanced features, refer to the official League Container documentation:

[League Container Documentation](https://container.thephpleague.com/) - Official documentation

New services should be registered in using League Container's service provider pattern. The container supports auto-wiring, interface binding, shared services, and other dependency injection features detailed in the documentation. `services.php`

#### Views with League Plates

The plugin uses League Plates as its templating engine. Views are PHP files that use native PHP templating syntax with additional helpful features.

For detailed information about Plates templating, refer to:
- [Plates Documentation](https://platesphp.com/) - Official documentation

##### Basic Usage

To render a view, use the `view()` helper function:

#### Working with Aggregators (Example with BookAggregator)

```php
// Get aggregator from container
$bookAggregator = container()->get(BookAggregator::class);

// Basic usage
$books = $bookAggregator->all('ENGESV'); // Get all books for a Bible
$book = $bookAggregator->find('GEN', 'ENGESV'); // Find specific book
```

When creating a new Aggregator, you can define which field should be used for comparing and merging data from different sources by setting the COMPARE_KEY constant:

```php
class BibleAggregator extends Aggregator {
    const COMPARE_KEY = "name"; // Items will be compared using the 'name' field

    public function __construct(
        BibleBrainsBibles $bibles, //Bible brains API service
        ParatextBibles $paratext_bibles, //Paratext bibles
        BibleBrainsBibleNormalizer $bb_normalizer,  //Normalizer class that makes sure source data conforms to API
        ParatextBibleNormalizer $paratext_normalizer //Normalizer class that makes sure source data conforms to API
    ) {
        $this->bb_bibles = $bibles;
        $this->paratext_bibles = $paratext_bibles;
        $this->bb_normalizer = $bb_normalizer; //bb_normalizer MUST be set
        $this->paratext_normalizer = $paratext_normalizer; //paratext_normalizer MUST be set
    }

     public function all( array $params = [] ): array
    {
        return $this->aggregate(
            $this->paratext_bibles->all(),
            $this->bb_bibles->all( $params )
        );
    }

    public function find( $id, array $params ): ?array
    {
        return $this->aggregate_item(
            $this->paratext_bibles->find( $id ),
            $this->bb_bibles->find( $id, $params )
        );
    }
}
```

#### Working with Requests and Controllers

```php
class MyController {
    public function handle(RequestInterface $request) {
        // Get request data
        $method = $request->method();
        $postData = $request->all_post();
        $getData = $request->all_get();
        $urlParams = $request->all_url_params();

        // Validate and use request data
        $id = $request->get('id');
        if (empty($id)) {
            return [
                'code' => 400,
                'message' => 'Invalid ID',
                'errors' => ['id' => 'ID is required']
            ];
        }

        // Process request...
    }
}
```

#### Working with config

The plugin uses a simple configuration system where config files are stored in the `config` folder. You can access configuration values using dot notation through the helper function: `config()`


```php
// Basic usage
$value = config('key');

// With default value if key doesn't exist
$value = config('key', 'default');

// Accessing nested values with dot notation
$value = config('services.paratext.dirs');
```

##### Configuring Paratext Bible Sources

To add local paratext Bible translations to the plugin, add their directory paths to the `services.paratext.dirs` configuration in your `config/services.php`:

```
'services' => [
  'paratext' => [
    'dirs' => [
       dirname( __DIR__ ) . '/path/to/paratext/KJV'
    ]
  ]
]
```

paratext bibles can also be added via the `bible_plugin_local_bibles` filter.

#### Working with resources

Resources in this application are key structural data types that provide an abstraction layer for data handling. They encapsulate both the data structure and related business logic, separate from aggregation concerns.
Key aspects of Resources:

- Provide consistent data structures across the application
- Handle data type transformations
- Include business logic specific to the resource type
- Abstract away source-specific implementations
- Can be used directly in controllers without worrying about aggregation details

Example of when to use Resources:

```php
// In a controller, instead of working with raw data or aggregators directly
public function show(RequestInterface $request) {
    $bibleResource = container()->get(BibleResource::class);
    return $bibleResource->find($request->get('code'));
}
```

### Hooks

The plugin provides several WordPress filters that allow you to customize its behavior. All hooks are prefixed with `bible_plugin_` to avoid conflicts with other plugins.

#### Filters

##### Asset Management
- `bible_plugin_allowed_styles`: Modify the list of allowed stylesheet handles
  ```php
  add_filter('bible_plugin_allowed_styles', function($styles) {
      $styles[] = 'my-custom-style';
      return $styles;
  });
  ```

- `bible_plugin_allowed_scripts`: Modify the list of allowed script handles
  ```php
  add_filter('bible_plugin_allowed_scripts', function($scripts) {
      $scripts[] = 'my-custom-script';
      return $scripts;
  });
  ```

##### JavaScript Configuration
- `bible_plugin_javascript_globals`: Modify JavaScript global variables passed to the frontend
  ```php
  add_filter('bible_plugin_javascript_globals', function($globals) {
      $globals['customSetting'] = 'value';
      return $globals;
  });
  ```

##### Settings
- `bible_plugin_settings_tabs`: Modify available settings tabs
  ```php
  add_filter('bible_plugin_settings_tabs', function($tabs) {
      $tabs['custom-tab'] = [
          'label' => 'Custom Tab',
          'callback' => 'my_custom_tab_callback'
      ];
      return $tabs;
  });
  ```

##### Bible Data
- `bible_plugin_local_bibles`: Modify paratext Bible data before processing
  ```php
  add_filter('bible_plugin_local_bibles', function($bibles) {
      // Modify or add custom Paratext bibles
      $bibles['KJV'] = container()->get( ParatextBibles::class )->parse_dir( '/path/to/paratext/bible/KJV' );
      return $bibles;
  });
  ```

##### Views
- `bible_plugin_before_render_view`: Modify view data before it's rendered
  ```php
  add_filter('bible_plugin_before_render_view', function($data) {
      // Modify $data
      return $data;
  });
  ```

- `bible_plugin_after_render_view`: Modify rendered HTML output
  ```php
  add_filter('bible_plugin_after_render_view', function($html, $view, $args) {
      // Modify $html
      return $html;
  }, 10, 3);
  ```


##### Using Hooks

You can use the `namespace_string()` helper function to get the correct hook name:

```php
use function CodeZone\Bible\namespace_string;
add_filter(namespace_string('local_bibles'), function($bibles) { ... });
```
