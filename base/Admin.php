<?php

namespace asdfstudio\admin\base;

use asdfstudio\admin\forms\Form;
use yii\base\Component;
use yii\grid\GridView;
use yii\helpers\Inflector;
use ReflectionClass;
use yii\widgets\DetailView;

/**
 * Class Admin
 * @package asdfstudio\admin
 */
abstract class Admin extends Component {

    /**
     * Triggers after new model creation
     */
    const EVENT_CREATE_SUCCESS = 'entity_create_success';
    const EVENT_CREATE_FAIL = 'entity_create_fail';

    /**
     * Trigers after model updated
     */
    const EVENT_UPDATE_SUCCESS = 'entity_update_success';
    const EVENT_UPDATE_FAIL = 'entity_update_fail';

    /**
     * Triggers after model deleted
     */
    const EVENT_DELETE_SUCCESS = 'entity_delete_success';
    const EVENT_DELETE_FAIL = 'entity_delete_fail';

    /**
     * @var string Admin Id
     */
    public $id;

    /**
     * @var array Labels
     */
    public $labels;
    
    /**
     * @var ReflectionClass()
     */
    private $reflection;
    
    public function __construct($config = array()) {
        $this->reflection = new ReflectionClass(static::className());
        return parent::__construct($config);
    }
    
    /**
     * Primary key for model. MUST be unique.
     * Using for loading model from DB and URL generation.
     * Default is `id`
     *
     * @return string
     */
    public function primaryKey() {
        return 'id';
    }

    /**
     * Should return an array with single and plural form of model name, e.g.
     *
     * ```php
     *  return ['User', 'Users'];
     * ```
     *
     * @return array
     */
    public static function labels() {
        $class = new ReflectionClass(static::className());
        $class = $class->getShortName();

        return [$class, Inflector::pluralize($class)];
    }

    /**
     * Slug for url, e.g.
     * Slug should match regex: [\w\d-_]+
     *
     * ```php
     *  return 'user'; // url will be /admin/manage/user[<id>[/<action]]
     * ```
     *
     * @return string
     */
    public function slug() {
        $model = $this->model();
        return strtolower(str_replace("Admin","",$this->reflection->getShortName()));
    }
    public function id() {
        $model = $this->model();
        
        return substr(md5($this->reflection->getName()), 0, 10).'-'.$this->slug();
    }

    /**
     * Access control rules
     *
     * @see [[yii\filters\AccessRule]]
     * ```php
     *  return [
     *      [
     *          'actions' => ['index', 'view', 'update'],
     *          'roles' => ['@'],
     *          'allow' => true,
     *      ],
     *  ];
     * ```
     *
     * @return array
     */
    public function access() {
        return [];
    }

    /**
     * Model's class name
     *
     * ```php
     *  return [
     *      'class' => vendorname\blog\Post::className(),
     *      'condition' => function($query) { // can be null, array or callable
     *          return $query->where('owner_id' => 1);
     *      }
     *  ]
     * ```
     *
     * @return array
     */
    abstract public function model();

    /**
     * Return model's name (namespace + class)
     *
     * @return array|null
     */
    public function getModelName() {
        $model = $this->model();
        if (is_array($model) && isset($model['class'])) {
            return $model['class'];
        } elseif (is_string($model)) {
            return $model;
        }
        return null;
    }

    /**
     * Return model's query conditions
     *
     * @return array|callable
     */
    public function getModelConditions() {
        $model = $this->model();
        if (is_array($model) && isset($model['condition'])) {
            return $model['condition'];
        }
        return null;
    }

    /**
     * Class name of form using for update or create operation
     * Default form class is `asdfstudio\admin\forms\Form`
     * For configuration syntax see [[asdfstudio\admin\forms\Form]]
     *
     * ```php
     *  return [
     *      'class' => vendorname\blog\forms\PostForm::className(),
     *  ];
     * ```
     *
     * @return array
     */
    public function form() {
        $classForm = $this->reflection->getNamespaceName().'\Form';
        if(class_exists($classForm)){
            $class = $classForm;
        }else{
            $class = Form::className();
        }
        return [
            'class' => $class,
        ];
    }

    /**
     * Detail view of model
     * Default detail view class is `asdfstudio\admin\details\Detail`
     * For configuration syntax see [[asdfstudio\admin\details\Detail]]
     *
     * ```php
     *  return [
     *      'class' => vendorname\blog\details\PostDetail::className(),
     *  ];
     * ```
     *
     * @return array
     */
    public function detail() {
        return [
            'class' => DetailView::className(),
        ];
    }

    /**
     * Class name of form using for update or create operation
     * Default grid class is `asdfstudio\admin\grids\Grid`
     * For configuration syntax see [[asdfstudio\admin\grids\Grid]]
     *
     * ```php
     *  return [
     *      'class' => vendorname\blog\grids\PostGrid::className(),
     *  ];
     * ```
     *
     * @return array
     */
    public function grid() {
        $classGrid = $this->reflection->getNamespaceName().'\Grid';
        if(class_exists($classGrid)){
            $class = $classGrid;
        }else{
            $class = \asdfstudio\admin\grids\Grid::className();
        }
        return [
            'class' => $class,
        ];
    }

    public function configureShowFields(\asdfstudio\admin\models\mapper\ShowMapper $show) {
        
    }
    
    public function canRead() {
        return true;
    }

    public function canCreate() {
        return true;
    }

    public function canUpdate() {
        return true;
    }

    public function canDelete() {
        return true;
    }

}
