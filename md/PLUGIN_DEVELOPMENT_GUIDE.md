# Plugin Development Guide for Botble CMS

**Shagoof E-commerce Platform** - Multi-country deployment

**Production URLs:**
- **EG (Egypt)**: https://eg.shagoof.com (Main Branch: `eg`)
- **UAE**: https://uae.shagoof.com (Branch: `uae`)
- **SA (Saudi Arabia)**: https://sa.shagoof.com (Branch: `sa`)

**Git Branch Workflow:**
- Main development happens in `eg` branch
- Changes are merged to `uae` and `sa` branches as needed
- Each branch has its own `.env` configuration
- Switch branches: `git checkout eg|uae|sa`

This guide explains how to create a plugin for the Botble CMS system used in this Laravel e-commerce platform.

## Table of Contents

1. [Plugin Structure](#plugin-structure)
2. [Required Files](#required-files)
3. [Step-by-Step Plugin Creation](#step-by-step-plugin-creation)
4. [Plugin Components](#plugin-components)
5. [Best Practices](#best-practices)
6. [Examples](#examples)

---

## Plugin Structure

All plugins must be located in the `platform/plugins/` directory. Each plugin follows a standard directory structure:

```
platform/plugins/your-plugin-name/
├── plugin.json                    # Plugin metadata (REQUIRED)
├── screenshot.png                  # Plugin screenshot (optional)
├── config/                        # Configuration files
│   ├── general.php               # General settings
│   └── permissions.php           # Permission definitions
├── database/                      # Database related files
│   ├── migrations/               # Database migrations
│   └── factories/                # Model factories (optional)
├── helpers/                       # Helper functions
│   └── constants.php             # Constants
├── public/                        # Public assets (CSS, JS, images)
│   ├── css/
│   ├── js/
│   └── images/
├── resources/                     # Source files
│   ├── assets/                   # Source assets (SCSS, JS)
│   │   ├── js/
│   │   └── sass/
│   ├── lang/                     # Language files
│   │   └── en/
│   └── views/                    # Blade templates
├── routes/                        # Route definitions
│   ├── web.php                   # Web routes
│   └── api.php                   # API routes (optional)
└── src/                          # PHP source code
    ├── Contracts/                # Interfaces/Contracts
    ├── Events/                   # Event classes
    ├── Exceptions/               # Custom exceptions
    ├── Facades/                  # Facade classes
    ├── Forms/                    # Form definitions
    ├── Http/                     # HTTP layer
    │   ├── Controllers/         # Controllers
    │   └── Requests/            # Form requests
    ├── Listeners/                # Event listeners
    ├── Models/                   # Eloquent models
    ├── Providers/                # Service providers
    ├── Repositories/             # Repository pattern
    │   ├── Caches/              # Cache decorators
    │   ├── Eloquent/            # Eloquent repositories
    │   └── Interfaces/         # Repository interfaces
    ├── Services/                 # Service classes
    ├── Supports/                 # Support classes
    ├── Tables/                   # Data table definitions
    ├── Traits/                   # Reusable traits
    └── Plugin.php                # Main plugin class (REQUIRED)
```

---

## Required Files

### 1. `plugin.json` - Plugin Metadata

This file contains essential information about your plugin:

```json
{
    "id": "botble/your-plugin-name",
    "name": "Your Plugin Name",
    "namespace": "Botble\\YourPluginName\\",
    "provider": "Botble\\YourPluginName\\Providers\\YourPluginServiceProvider",
    "author": "Your Name",
    "url": "https://yourwebsite.com",
    "version": "1.0.0",
    "description": "Description of your plugin",
    "minimum_core_version": "7.3.0"
}
```

**Fields:**
- `id`: Unique identifier (format: `botble/plugin-name`)
- `name`: Display name
- `namespace`: PHP namespace (must match directory structure)
- `provider`: Full class name of the service provider
- `author`: Author name
- `url`: Author/plugin website
- `version`: Semantic version (e.g., "1.0.0")
- `description`: Brief description
- `minimum_core_version`: Minimum Botble CMS version required

### 2. `src/Plugin.php` - Main Plugin Class

This class handles plugin lifecycle events:

```php
<?php

namespace Botble\YourPluginName;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    /**
     * Called when plugin is activated
     */
    public static function activated(): void
    {
        // Run migrations
        app('migrator')->run(database_path('migrations'));
        
        // Set default settings
        Setting::set([
            'your_plugin_setting' => 'default_value',
        ])->save();
    }

    /**
     * Called when plugin is deactivated
     */
    public static function deactivated(): void
    {
        // Cleanup if needed
    }

    /**
     * Called when plugin is removed/deleted
     */
    public static function remove(): void
    {
        // Drop database tables
        Schema::dropIfExists('your_plugin_table');
        
        // Remove settings
        Setting::delete([
            'your_plugin_setting',
        ]);
    }
}
```

### 3. `src/Providers/YourPluginServiceProvider.php` - Service Provider

This is the main service provider that bootstraps your plugin:

```php
<?php

namespace Botble\YourPluginName\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Support\ServiceProvider;

class YourPluginServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    /**
     * Register services
     */
    public function register(): void
    {
        // Bind repository interfaces
        $this->app->bind(
            YourPluginInterface::class,
            function () {
                return new YourPluginRepository(new YourPluginModel());
            }
        );
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        $this
            ->setNamespace('plugins/your-plugin-name')
            ->loadAndPublishConfigurations(['permissions', 'general'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadRoutes(['web', 'api'])
            ->loadHelpers()
            ->loadAndPublishViews();

        // Register dashboard menu items
        DashboardMenu::beforeRetrieving(function (): void {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-plugins-your-plugin',
                    'priority' => 8,
                    'icon' => 'ti ti-icon-name',
                    'name' => 'plugins/your-plugin-name::your-plugin.name',
                    'permissions' => ['your-plugin.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-your-plugin-list',
                    'parent_id' => 'cms-plugins-your-plugin',
                    'priority' => 1,
                    'name' => 'plugins/your-plugin-name::your-plugin.name',
                    'icon' => 'ti ti-list',
                    'url' => fn () => route('your-plugin.index'),
                    'permissions' => ['your-plugin.index'],
                ]);
        });
    }
}
```

---

## Step-by-Step Plugin Creation

### Step 1: Create Plugin Directory Structure

1. Create the main plugin directory:
   ```bash
   mkdir -p platform/plugins/your-plugin-name
   ```

2. Create the required subdirectories:
   ```bash
   cd platform/plugins/your-plugin-name
   mkdir -p config database/migrations helpers public/{css,js,images}
   mkdir -p resources/{assets/{js,sass},lang/en,views}
   mkdir -p routes src/{Contracts,Events,Exceptions,Facades,Forms,Http/{Controllers,Requests},Listeners,Models,Providers,Repositories/{Caches,Eloquent,Interfaces},Services,Supports,Tables,Traits}
   ```

### Step 2: Create `plugin.json`

Create the `plugin.json` file with your plugin metadata (see example above).

### Step 3: Create Main Plugin Class

Create `src/Plugin.php` extending `PluginOperationAbstract` (see example above).

### Step 4: Create Service Provider

Create `src/Providers/YourPluginServiceProvider.php` (see example above).

### Step 5: Create Configuration Files

**`config/permissions.php`:**
```php
<?php

return [
    [
        'name' => 'Your Plugin',
        'flag' => 'your-plugin.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'your-plugin.create',
        'parent_flag' => 'your-plugin.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'your-plugin.edit',
        'parent_flag' => 'your-plugin.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'your-plugin.destroy',
        'parent_flag' => 'your-plugin.index',
    ],
];
```

**`config/general.php`:**
```php
<?php

return [
    'setting_key' => 'default_value',
];
```

### Step 6: Create Database Migration

Create migration file in `database/migrations/`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('your_plugin_table', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('your_plugin_table');
    }
};
```

### Step 7: Create Model

**`src/Models/YourPluginModel.php`:**
```php
<?php

namespace Botble\YourPluginName\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class YourPluginModel extends BaseModel
{
    protected $table = 'your_plugin_table';

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];
}
```

### Step 8: Create Repository Interface

**`src/Repositories/Interfaces/YourPluginInterface.php`:**
```php
<?php

namespace Botble\YourPluginName\Repositories\Interfaces;

use Botble\Repository\Interfaces\RepositoryInterface;

interface YourPluginInterface extends RepositoryInterface
{
}
```

### Step 9: Create Repository Implementation

**`src/Repositories/Eloquent/YourPluginRepository.php`:**
```php
<?php

namespace Botble\YourPluginName\Repositories\Eloquent;

use Botble\Repository\Eloquent\BaseRepository;
use Botble\YourPluginName\Models\YourPluginModel;
use Botble\YourPluginName\Repositories\Interfaces\YourPluginInterface;

class YourPluginRepository extends BaseRepository implements YourPluginInterface
{
    public function model(): string
    {
        return YourPluginModel::class;
    }
}
```

### Step 10: Create Controller

**`src/Http/Controllers/YourPluginController.php`:**
```php
<?php

namespace Botble\YourPluginName\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\YourPluginName\Forms\YourPluginForm;
use Botble\YourPluginName\Http\Requests\YourPluginRequest;
use Botble\YourPluginName\Repositories\Interfaces\YourPluginInterface;
use Botble\YourPluginName\Tables\YourPluginTable;
use Illuminate\Http\Request;

class YourPluginController extends BaseController
{
    public function __construct(
        protected YourPluginInterface $repository
    ) {
    }

    public function index(YourPluginTable $table)
    {
        page_title()->setTitle(trans('plugins/your-plugin-name::your-plugin.name'));

        return $table->renderTable();
    }

    public function create()
    {
        page_title()->setTitle(trans('plugins/your-plugin-name::your-plugin.create'));

        return YourPluginForm::create()->renderForm();
    }

    public function store(YourPluginRequest $request, BaseHttpResponse $response)
    {
        $item = $this->repository->create($request->input());

        return $response
            ->setPreviousUrl(route('your-plugin.index'))
            ->setNextUrl(route('your-plugin.edit', $item->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit($id)
    {
        $item = $this->repository->findOrFail($id);

        page_title()->setTitle(trans('plugins/your-plugin-name::your-plugin.edit') . ' "' . $item->name . '"');

        return YourPluginForm::createFromModel($item)->renderForm();
    }

    public function update($id, YourPluginRequest $request, BaseHttpResponse $response)
    {
        $item = $this->repository->findOrFail($id);
        $item->fill($request->input());
        $item->save();

        return $response
            ->setPreviousUrl(route('your-plugin.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy($id, BaseHttpResponse $response)
    {
        $this->repository->delete($id);

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
```

### Step 11: Create Routes

**`routes/web.php`:**
```php
<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\YourPluginName\Http\Controllers'], function (): void {
    AdminHelper::registerRoutes(function (): void {
        Route::group(['prefix' => 'your-plugin', 'as' => 'your-plugin.'], function (): void {
            Route::resource('', 'YourPluginController')->parameters(['' => 'your-plugin']);
        });
    });
});
```

### Step 12: Create Language Files

**`resources/lang/en/your-plugin.php`:**
```php
<?php

return [
    'name' => 'Your Plugin',
    'create' => 'Create',
    'edit' => 'Edit',
    'delete' => 'Delete',
];
```

### Step 13: Create Form (Optional)

**`src/Forms/YourPluginForm.php`:**
```php
<?php

namespace Botble\YourPluginName\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Forms\FormHelper;
use Botble\YourPluginName\Http\Requests\YourPluginRequest;
use Botble\YourPluginName\Models\YourPluginModel;

class YourPluginForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->setupModel(new YourPluginModel())
            ->setValidatorClass(YourPluginRequest::class)
            ->setFormOption('template', 'core/base::forms.form-content-only')
            ->add('name', 'text', [
                'label' => trans('plugins/your-plugin-name::your-plugin.name'),
                'required' => true,
            ])
            ->add('description', 'textarea', [
                'label' => trans('plugins/your-plugin-name::your-plugin.description'),
            ])
            ->setBreakFieldPoint('status');
    }
}
```

### Step 14: Create Table (Optional)

**`src/Tables/YourPluginTable.php`:**
```php
<?php

namespace Botble\YourPluginName\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Tables\TableAbstract;
use Botble\YourPluginName\Models\YourPluginModel;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;

class YourPluginTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(YourPluginModel::class)
            ->addColumns([
                TableColumn::make('id')
                    ->title(trans('core/base::tables.id'))
                    ->width(20),
                TableColumn::make('name')
                    ->title(trans('plugins/your-plugin-name::your-plugin.name'))
                    ->alignLeft(),
                TableColumn::make('created_at')
                    ->title(trans('core/base::tables.created_at'))
                    ->width(100),
            ])
            ->setDefaultActions(['edit', 'delete']);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()
            ->query()
            ->select([
                'id',
                'name',
                'created_at',
            ]);

        return $this->applyScopes($query);
    }
}
```

### Step 15: Register Plugin

After creating all files, you need to:

1. **Clear cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

2. **Run composer dump-autoload:**
   ```bash
   composer dump-autoload
   ```

3. **Activate plugin via admin panel:**
   - Go to Admin Panel → Plugins
   - Find your plugin and click "Activate"

### Step 16: Multi-Branch Deployment (Shagoof Specific)

If you're working on the Shagoof multi-country setup:

1. **Create plugin in EG branch** (main branch):
   ```bash
   git checkout eg
   # Create plugin files here
   git add .
   git commit -m "Add new plugin: your-plugin-name"
   git push origin eg
   ```

2. **Merge to other branches**:
   ```bash
   # Merge to UAE
   git checkout uae
   git merge eg
   git push origin uae
   
   # Merge to SA
   git checkout sa
   git merge eg
   git push origin sa
   ```

3. **Configure per branch**:
   - Each branch has its own `.env` file
   - Update environment variables per deployment
   - Activate plugin on each production server

---

## Plugin Components

### Models

- Extend `Botble\Base\Models\BaseModel`
- Use `BaseStatusEnum` for status fields
- Define `$fillable` and `$casts` properties

### Controllers

- Extend `Botble\Base\Http\Controllers\BaseController`
- Use dependency injection for repositories
- Return `BaseHttpResponse` for AJAX responses
- Use `page_title()` helper for page titles

### Forms

- Extend `Botble\Base\Forms\FormAbstract`
- Use `FormHelper` for field creation
- Set validator class in `setup()` method

### Tables

- Extend `Botble\Base\Tables\TableAbstract`
- Define columns in `setup()` method
- Override `query()` for custom queries

### Repositories

- Follow Repository Pattern
- Create interface in `Repositories/Interfaces/`
- Implement in `Repositories/Eloquent/`
- Optionally add cache decorator in `Repositories/Caches/`

### Routes

- Use `AdminHelper::registerRoutes()` for admin routes
- Use `Theme::registerRoutes()` for frontend routes
- Group routes by namespace

### Language Files

- Store in `resources/lang/{locale}/`
- Use namespace format: `plugins/your-plugin-name::key`
- Support multiple languages

### Assets

- Source files in `resources/assets/`
- Compiled files in `public/`
- Use Laravel Mix for compilation
- Create `webpack.mix.js` if needed

---

## Best Practices

1. **Naming Conventions:**
   - Use kebab-case for plugin directory name
   - Use PascalCase for class names
   - Use snake_case for database tables
   - Use camelCase for variables and methods

2. **Namespace:**
   - Follow PSR-4 autoloading
   - Match namespace to directory structure
   - Use `Botble\YourPluginName\` format

3. **Permissions:**
   - Always define permissions in `config/permissions.php`
   - Check permissions in controllers
   - Use permission flags in routes

4. **Database:**
   - Always use migrations
   - Include `up()` and `down()` methods
   - Use proper foreign keys and indexes

5. **Translation:**
   - Never hardcode strings
   - Use translation keys
   - Support multiple languages

6. **Error Handling:**
   - Use try-catch blocks
   - Return proper error responses
   - Log errors appropriately

7. **Security:**
   - Validate all inputs
   - Use Form Requests for validation
   - Sanitize user inputs
   - Check permissions

8. **Performance:**
   - Use repository pattern with caching
   - Eager load relationships
   - Optimize database queries
   - Cache expensive operations

---

## Examples

### Example 1: Simple Plugin (FAQ)

Reference: `platform/plugins/faq/`

This plugin demonstrates:
- Basic CRUD operations
- Categories and items
- Form handling
- Table display

### Example 2: Plugin with Settings (Ads)

Reference: `platform/plugins/ads/`

This plugin demonstrates:
- Settings management
- Shortcode integration
- Frontend display
- Google AdSense integration

### Example 3: Complex Plugin (Ecommerce)

Reference: `platform/plugins/ecommerce/`

This plugin demonstrates:
- Complex models and relationships
- Multiple repositories
- Event listeners
- Service classes
- API endpoints

---

## Common Tasks

### Adding Menu Items

```php
DashboardMenu::beforeRetrieving(function (): void {
    DashboardMenu::make()
        ->registerItem([
            'id' => 'cms-plugins-your-plugin',
            'priority' => 8,
            'icon' => 'ti ti-icon-name',
            'name' => 'plugins/your-plugin-name::your-plugin.name',
            'permissions' => ['your-plugin.index'],
        ]);
});
```

### Registering Shortcodes

```php
use Botble\Shortcode\Facades\Shortcode;

Shortcode::register('your-shortcode', __('Title'), __('Description'), function ($shortcode) {
    return view('plugins/your-plugin-name::shortcode', compact('shortcode'))->render();
});
```

### Adding Filters/Actions

```php
add_filter('filter_name', function ($value) {
    // Modify $value
    return $value;
}, 128);

add_action('action_name', function ($param) {
    // Do something
}, 128);
```

### Publishing Assets

```php
php artisan vendor:publish --tag=your-plugin-name-public
```

---

## Troubleshooting

### Plugin Not Appearing

1. Check `plugin.json` syntax
2. Verify namespace matches directory structure
3. Run `composer dump-autoload`
4. Clear cache: `php artisan cache:clear`

### Routes Not Working

1. Verify routes are registered in service provider
2. Check route names and prefixes
3. Clear route cache: `php artisan route:clear`

### Assets Not Loading

1. Check asset paths in views
2. Verify assets are published
3. Run `npm run dev` or `npm run prod`

### Database Issues

1. Check migration files
2. Verify table names match
3. Run migrations: `php artisan migrate`

---

## Additional Resources

- Botble CMS Documentation: https://docs.botble.com
- Laravel Documentation: https://laravel.com/docs
- Plugin Examples: Check `platform/plugins/` directory

---

**Note:** This guide is based on the Botble CMS plugin system. Always refer to the latest documentation and existing plugin examples for best practices.

