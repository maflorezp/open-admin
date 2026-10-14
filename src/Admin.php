<?php

namespace OpenAdmin\Admin;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use OpenAdmin\Admin\Auth\Database\Menu;
use OpenAdmin\Admin\Controllers\AuthController;
use OpenAdmin\Admin\Layout\Content;
use OpenAdmin\Admin\Traits\HasAssets;
use OpenAdmin\Admin\Widgets\Navbar;

/**
 * Class Admin.
 */
class Admin
{
    use HasAssets;

    /**
     * The Open-admin version.
     *
     * @var string
     */
    public const VERSION = '1.0.29';

    /**
     * Cabecera con la que una respuesta a una petición PJAX le pide al navegador una recarga completa.
     *
     * @var string
     */
    public const PJAX_RELOAD_HEADER = 'X-PJAX-RELOAD';

    /**
     * @var Navbar
     */
    protected $navbar;

    /**
     * @var array
     */
    protected $menu = [];

    /**
     * @var string
     */
    public static $metaTitle;

    /**
     * @var string
     */
    public static $favicon;

    /**
     * @var array
     */
    public static $extensions = [];

    /**
     * @var []Closure
     */
    protected static $bootingCallbacks = [];

    /**
     * @var []Closure
     */
    protected static $bootedCallbacks = [];

    /**
     * Returns the long version of Open-admin.
     *
     * @return string The long application version
     */
    public static function getLongVersion()
    {
        return sprintf('Open-admin <comment>version</comment> <info>%s</info>', self::VERSION);
    }

    /**
     * @param $model
     * @param Closure $callable
     *
     * @return \OpenAdmin\Admin\Grid
     *
     * @deprecated since v1.6.1
     */
    public function grid($model, Closure $callable)
    {
        return new Grid($this->getModel($model), $callable);
    }

    /**
     * @param $model
     * @param Closure $callable
     *
     * @return \OpenAdmin\Admin\Form
     *
     *  @deprecated since v1.6.1
     */
    public function form($model, Closure $callable)
    {
        return new Form($this->getModel($model), $callable);
    }

    /**
     * Build a tree.
     *
     * @param $model
     * @param Closure|null $callable
     *
     * @return \OpenAdmin\Admin\Tree
     */
    public function tree($model, Closure $callable = null)
    {
        return new Tree($this->getModel($model), $callable);
    }

    /**
     * Build show page.
     *
     * @param $model
     * @param mixed $callable
     *
     * @return Show
     */
    public function show($model, $callable = null)
    {
        return new Show($this->getModel($model), $callable);
    }

    /**
     * @param Closure $callable
     *
     * @return \OpenAdmin\Admin\Layout\Content
     */
    public function content(Closure $callable = null)
    {
        return new Content($callable);
    }

    /**
     * @param $model
     *
     * @return mixed
     */
    public function getModel($model)
    {
        if ($model instanceof Model) {
            return $model;
        }

        if (is_string($model) && class_exists($model)) {
            return $this->getModel(new $model());
        }

        throw new InvalidArgumentException("$model is not a valid model");
    }

    /**
     * Left sider-bar menu.
     *
     * @return array
     */
    public function menu()
    {
        if (!empty($this->menu)) {
            return $this->menu;
        }

        $menuClass = config('admin.database.menu_model');

        /** @var Menu $menuModel */
        $menuModel = new $menuClass();

        return $this->menu = $menuModel->toTree();
    }

    /**
     * @param array $menu
     *
     * @return array
     */
    public function menuLinks($menu = [])
    {
        if (empty($menu)) {
            $menu = $this->menu();
        }

        $links = [];

        foreach ($menu as $item) {
            if (!empty($item['children'])) {
                $links = array_merge($links, $this->menuLinks($item['children']));
            } else {
                $links[] = Arr::only($item, ['title', 'uri', 'icon']);
            }
        }

        return $links;
    }

    /**
     * Set admin title.
     *
     * @param string $title
     *
     * @return void
     */
    public static function setTitle($title)
    {
        self::$metaTitle = $title;
    }

    /**
     * Get admin title.
     *
     * @return string
     */
    public function title()
    {
        return self::$metaTitle ? self::$metaTitle : config('admin.title');
    }

    /**
     * @param null|string $favicon
     *
     * @return string|void
     */
    public function favicon($favicon = null)
    {
        if (is_null($favicon)) {
            return static::$favicon;
        }

        static::$favicon = $favicon;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        return static::guard()->user();
    }

    /**
     * Get the guard name.
     *
     * @return string
     */
    public function guardName()
    {
        return config('admin.auth.guard') ?: 'admin';
    }

    /**
     * Attempt to get the guard from the local cache.
     *
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    public function guard()
    {
        return Auth::guard(static::guardName());
    }

    /**
     * Set navbar.
     *
     * @param Closure|null $builder
     *
     * @return Navbar
     */
    public function navbar(Closure $builder = null)
    {
        if (is_null($builder)) {
            return $this->getNavbar();
        }

        call_user_func($builder, $this->getNavbar());
    }

    /**
     * Get navbar object.
     *
     * @return \OpenAdmin\Admin\Widgets\Navbar
     */
    public function getNavbar()
    {
        if (is_null($this->navbar)) {
            $this->navbar = new Navbar();
        }

        return $this->navbar;
    }

    /**
     * Register the open-admin builtin routes.
     *
     * @return void
     *
     * @deprecated Use Admin::routes() instead();
     */
    public function registerAuthRoutes()
    {
        $this->routes();
    }

    /**
     * Register the open-admin builtin routes.
     *
     * @return void
     */
    public function routes()
    {
        $attributes = [
            'prefix'     => config('admin.route.prefix'),
            'middleware' => config('admin.route.middleware'),
        ];

        app('router')->group($attributes, function ($router) {
            /* @var \Illuminate\Support\Facades\Route $router */
            $router->namespace('\OpenAdmin\Admin\Controllers')->group(function ($router) {
                /* @var \Illuminate\Routing\Router $router */
                $router->resource('auth/users', 'UserController')->names('admin.auth.users');
                $router->resource('auth/roles', 'RoleController')->names('admin.auth.roles');
                $router->resource('auth/permissions', 'PermissionController')->names('admin.auth.permissions');
                $router->resource('auth/menu', 'MenuController', ['except' => ['create']])->names('admin.auth.menu');
                $router->resource('auth/logs', 'LogController', ['only' => ['index', 'destroy']])->names('admin.auth.logs');

                $router->post('_handle_form_', 'HandleController@handleForm')->name('admin.handle-form');
                $router->post('_handle_action_', 'HandleController@handleAction')->name('admin.handle-action');
                $router->get('_handle_selectable_', 'HandleController@handleSelectable')->name('admin.handle-selectable');
                $router->get('_handle_renderable_', 'HandleController@handleRenderable')->name('admin.handle-renderable');
            });

            $authController = config('admin.auth.controller', AuthController::class);

            /* @var \Illuminate\Routing\Router $router */
            $router->get('auth/login', $authController.'@getLogin')->name('admin.login');
            $router->post('auth/login', $authController.'@postLogin');
            $router->get('auth/logout', $authController.'@getLogout')->name('admin.logout');
            $router->get('auth/setting', $authController.'@getSetting')->name('admin.setting');
            $router->put('auth/setting', $authController.'@putSetting');
        });

        // El refresco del token solo exige sesión iniciada: sin admin.permission (un rol limitado
        // no podría refrescar) ni admin.log (llenaría el registro de operaciones cada pocos minutos)
        $csrfMiddleware = array_values(array_diff((array) config('admin.route.middleware'), ['admin']));
        $csrfMiddleware[] = 'admin.auth';

        app('router')->group([
            'prefix'     => config('admin.route.prefix'),
            'middleware' => $csrfMiddleware,
        ], function ($router) {
            // Acción de controlador y no closure, para que route:cache siga funcionando
            $router->get('_refresh_csrf_', '\\OpenAdmin\\Admin\\Controllers\\HandleController@refreshCsrfToken')
                ->name('admin.refresh-csrf');
        });
    }

    /**
     * Extend a extension.
     *
     * @param string $name
     * @param string $class
     *
     * @return void
     */
    public static function extend($name, $class)
    {
        static::$extensions[$name] = $class;
    }

    /**
     * @param callable $callback
     */
    public static function booting(callable $callback)
    {
        static::$bootingCallbacks[] = $callback;
    }

    /**
     * @param callable $callback
     */
    public static function booted(callable $callback)
    {
        static::$bootedCallbacks[] = $callback;
    }

    /**
     * Bootstrap the admin application.
     */
    public function bootstrap()
    {
        $this->fireBootingCallbacks();

        require config('admin.bootstrap', admin_path('bootstrap.php'));

        $this->addAdminAssets();

        $this->fireBootedCallbacks();
    }

    /**
     * Add JS & CSS assets to pages.
     */
    protected function addAdminAssets()
    {
        $assets = Form::collectFieldAssets();

        self::css($assets['css']);
        self::js($assets['js']);
    }

    /**
     * Call the booting callbacks for the admin application.
     */
    protected function fireBootingCallbacks()
    {
        foreach (static::$bootingCallbacks as $callable) {
            call_user_func($callable);
        }
    }

    /**
     * Call the booted callbacks for the admin application.
     */
    protected function fireBootedCallbacks()
    {
        foreach (static::$bootedCallbacks as $callable) {
            call_user_func($callable);
        }
    }

    public static function asset($asset)
    {
        return admin_asset('/vendor/open-admin/'.$asset);
    }

    public static function js_trans()
    {
        $lang_array = json_encode(__('admin'));

        return '<script>var admin_lang_arr = '.$lang_array.'</script>';
    }

    /**
     * Apaga la navegación PJAX en la página de esta petición: los enlaces, los formularios y las
     * recargas pasan a ser navegaciones completas del navegador.
     *
     * Sirve para páginas que necesitan cargar su propio <head> (assets, estilos) y que PJAX no
     * recargaría. Se llama desde el controlador antes de devolver el contenido.
     *
     * @throws HttpResponseException si la página se pidió por PJAX: corta la petición con una
     *                               respuesta vacía que ordena al navegador recargarla completa.
     *
     * @return void
     */
    public function disablePjax()
    {
        $request = request();

        // Llegó por PJAX (un clic desde otra página): en vez de pintarla dentro del #main, se le pide
        // al navegador que la cargue completa. Solo en lecturas: en un POST/PUT cortar aquí se saltaría
        // el guardado, y su redirección vuelve a pasar por aquí como GET.
        if ($request->pjax() && $request->isMethod('GET')) {
            // La recarga es una petición más: se conservan los flash (errores, old input, toastr)
            // que esta petición iba a consumir, para que los pinte la página completa.
            if ($request->hasSession()) {
                $request->session()->reflash();
            }

            throw new HttpResponseException(response('', 200, [self::PJAX_RELOAD_HEADER => '1']));
        }

        static::script('window.disablePjax = true;');
    }
}
