<?php

namespace asdfstudio\admin;

use asdfstudio\admin\base\Admin;
use asdfstudio\admin\models\menu\Menu;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;

class Module extends \yii\base\Module implements BootstrapInterface {

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'asdfstudio\admin\controllers';

    /**
     * URL prefix
     *
     * @var string
     */
    public $urlPrefix = '/admin';

    /**
     * Registered models
     *
     * @var array
     */
    public $entities = [];

    /**
     * Contains Class => Id for fast search
     *
     * @var array
     */
    public $entitiesClasses = [];

    /**
     * Asset bundle
     *
     * @var string
     */
    public $assetBundle = 'asdfstudio\admin\AdminAsset';

    /**
     * Top menu navigation
     * Example configuration
     *
     * ```php
     *  [
     *      [
     *          'label' => 'First item',
     *          'url' => ['index', 'param' => 'value']
     *      ],
     *      [
     *          'label' => 'Dropdown item',
     *          'items' => [
     *              ['label' => 'First child', 'url' => ['first']],
     *              ['label' => 'Second child', 'url' => ['second']],
     *          ]
     *      ]
     *  ]
     *
     * @var Menu
     */
    public $menu;

    /**
     * Sidebar menu navigation
     * Example configuration
     *
     * ```php
     *  [
     *      [
     *          'label' => 'First item',
     *          'url' => ['index', 'param' => 'value']
     *      ],
     *      [
     *          'label' => 'Dropdown item',
     *          'items' => [
     *              ['label' => 'First child', 'url' => ['first']],
     *              ['label' => 'Second child', 'url' => ['second']],
     *          ]
     *      ]
     *  ]
     * ```
     *
     * @var Menu
     */
    public $sidebar;
    
    /**
     * Imagen del perfil del usuario
     * @var string|Closure
     */
    public $imageProfileUser = null;
    
    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
        $this->setViewPath(dirname(__FILE__) . '/views');

        $this->menu    = new Menu();
        $this->sidebar = new Menu();
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app) {
        $this->registerRoutes([
            $this->urlPrefix . ''                                                     => 'admin/admin/index',
            $this->urlPrefix . '/manage/<entity:[\w\d\._-]+>'                         => 'admin/manage/index',
            $this->urlPrefix . '/manage/<entity:[\w\d\._-]+>/create'                  => 'admin/manage/create',
            $this->urlPrefix . '/manage/<entity:[\w\d\._-]+>/<id:[\w\d\._-]+>'        => 'admin/manage/view',
            $this->urlPrefix . '/manage/<entity:[\w\d\._-]+>/<id:[\w\d\._-]+>/update' => 'admin/manage/update',
            $this->urlPrefix . '/manage/<entity:[\w\d\._-]+>/<id:[\w\d\._-]+>/delete' => 'admin/manage/delete',

        ]);
        Yii::$container->setSingleton("admin.module_service",function(){
            return new models\AdminModuleService();
        });
        Yii::$container->setSingleton("admin.builder.menu_sidebar",function(){
            return new models\menu\MenuSidebarBuilder();
        });
        Yii::$container->setSingleton("common.manager.breadcrumb",function(){
            $breadcrumb = new \Tecnoready\Yii2\Common\Services\BreadcrumbManager();
            $breadcrumb->breadcrumb([
                "/admin/admin/index" => Yii::t("admin", "Dashboard"),
            ]);
            return $breadcrumb;
        });
        Yii::setAlias('admin-module',__DIR__ . '/');
        $this->registerTranslations();
    }

    /**
     * Register admin module routes
     *
     * @param array $rules
     */
    public function registerRoutes($rules) {
        Yii::$app->getUrlManager()->addRules($rules);
    }

    /**
     * Register model in admin dashboard
     *
     * @param string|Admin $entity
     * @param bool          $forceRegister
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function registerEntity($entity, $forceRegister = false) {
        $instance = new $entity([
        ]);
        $id = call_user_func_array([$instance, 'slug'],[]);

        if (isset($this->entities[$id]) && !$forceRegister) {
            throw new InvalidConfigException(sprintf('Item with id "%s" already registered', $id));
        }

        $this->entities[$id] = new $entity([
            'id' => $id,
        ]);

        $this->entitiesClasses[$entity] = $id;
        
        return $this->entities[$id];
    }
    
    public function addMenuGroup($group,array $parameters = []) {
        $this->sidebar->addGroup($group, $parameters);
        
        return $this;
    }
    
    /**
     * Register model in admin dashboard
     *
     * @param string|Admin $entity
     * @param bool          $forceRegister
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function addEntity($entity,$parameters = [], $forceRegister = false) {
        $group = $icon = $tag = null;
        $labelCatalogue = "admin";
        if(isset($parameters["group"])){
            $group = $parameters["group"];
        }
        if(isset($parameters["labelCatalogue"])){
            $labelCatalogue = $parameters["labelCatalogue"];
        }
        if(isset($parameters["icon"])){
            $icon = $parameters["icon"];
        }
        if(isset($parameters["tag"])){
            $tag = $parameters["tag"];
        }
        //label_catalogue,group
        $instance = new $entity([
        ]);
        $id = call_user_func_array([$instance, 'slug'],[]);

        if (isset($this->entities[$id]) && !$forceRegister) {
            throw new InvalidConfigException(sprintf('Item with id "%s" already registered', $id));
        }

        $this->entities[$id] = new $entity([
            'id' => $id,
        ]);

        $this->entitiesClasses[$entity] = $id;
        $entityInstance = $this->entities[$id];
        
        $item = $this->sidebar->addItem($entityInstance);
        $item->group = $group;
        $item->icon = $icon;
        $item->labelCatalogue = $labelCatalogue;
        $item->tag = $tag;
        $item->label = $entityInstance->slug();
        
        return $entityInstance;
    }

    /**
     * Register controller in module. Needed for creating custom pages
     *
     * @param string $id
     * @param string $controller
     */
    public function registerController($id, $controller) {
        $this->controllerMap[$id] = [
            'class' => $controller,
        ];
    }

    /**
     * Register translations
     */
    protected function registerTranslations() {
        $i18n = Yii::$app->i18n;

        $i18n->translations['admin'] = [
            'class'          => 'asdfstudio\admin\i18n\PhpFilesMessageSource',
//            'fileMap' => [
//                'admin' => 'admin.php',
//            ],
            'basePath'       => '@vendor/tecnoready/yii2-admin-module/messages',
        ];
    }

    public function canRead() {
        return true;
    }

}
