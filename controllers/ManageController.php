<?php

namespace asdfstudio\admin\controllers;

use Yii;
use yii\base\Event;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use asdfstudio\admin\base\Entity;
use asdfstudio\admin\forms\Form;
use yii\web\ForbiddenHttpException;
use yii\helpers\Html;
use asdfstudio\admin\grids\Grid;
use asdfstudio\admin\components\AdminFormatter;
use yii\widgets\DetailView;

/**
 * Class ManageController
 * @package asdfstudio\admin\controllers
 * @property ActiveRecord $model
 */
class ManageController extends Controller {
    /* @var Entity */

    public $entity;
    /* @var ActiveRecord */
    private $_model = null;

    /**
     * @inheritdoc
     * @throws \yii\web\NotFoundHttpException
     */
    public function init() {
        $entity = Yii::$app->getRequest()->getQueryParam('entity', null);
        $this->entity = $this->getEntity($entity);
        if ($this->entity === null) {
            throw new NotFoundHttpException();
        }
        if (Yii::$app->getRequest()->getIsAjax()) {
            $this->layout = 'modal';
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => false,
                        'roles' => ['?']
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex($entity) {
        $entity = $this->getEntity($entity);
        if (method_exists($entity, 'canRead') && $entity->canRead()) {
            /* @var ActiveQuery $query */
            $query = call_user_func([$entity->getModelName(), 'find']);
            $condition = $entity->getModelConditions();
            if (is_callable($condition)) {
                $query = call_user_func($condition, $query);
            } elseif (is_array($condition)) {
                $query = $query->andWhere($condition);
            }

            $modelsProvider = new ActiveDataProvider([
                'query' => $query
            ]);
            Yii::$container->get("common.manager.breadcrumb")->breadcrumb([
                Yii::$app->getRequest()->url => Yii::t("admin", sprintf("%s list",$entity->slug())),
            ]);
            
            $grid = $entity->grid();

            $class = ArrayHelper::remove($grid, 'class', Grid::className());
            $filterModel = ArrayHelper::remove($grid, 'filterModel', null);
            if ($filterModel !== null && method_exists($filterModel, 'search')) {
                $modelsProvider = $filterModel->search(Yii::$app->request->queryParams);
            }
            $defaultGrid = [
                'dataProvider' => $modelsProvider,
                'filterModel' => $filterModel,
                'formatter' => [
                    'class' => AdminFormatter::className(),
                ],
            ];
            $grid = ArrayHelper::merge($defaultGrid, $grid);
            $htmlGrid = $class::widget($grid);
            
            $buttonsTop = $this->buildButtons(["create"],$entity);
            return $this->render('index.twig', [
                'entity' => $entity,
                'modelsProvider' => $modelsProvider,
                'htmlGrid' => $htmlGrid,
                'buttonsTop' => $buttonsTop,
            ]);
        } else {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    public function actionView() {
        if (method_exists($this->entity, 'canRead') && $this->entity->canRead()) {
            $entity = $this->entity;
            $url = \yii\helpers\Url::to([
                'manage/index',
                'entity' => $entity->slug(),
            ]);
            $breadcrumb = Yii::$container->get("common.manager.breadcrumb");
            $breadcrumb->breadcrumb([
                $url => Yii::t("admin", sprintf("%s list",$entity->slug())),
            ]);
            $breadcrumb->breadcrumb([
                Yii::$app->getRequest()->url => Yii::t("admin",(string)$this->model),
            ]);
            
            $showMapper = new \asdfstudio\admin\models\mapper\ShowMapper();
            $model = $this->model;
            $detail = $entity->detail();
            $entity->configureShowFields($showMapper);
            $class = ArrayHelper::remove($detail, 'class', DetailView::className());
            $defaultDetail = [
                'model' => $model,
                'formatter' => [
                    'class' => AdminFormatter::className(),
                ],
            ];
            $listFields = $showMapper->getList();
            if(count($listFields) > 0){
                $attributes = [];
                foreach ($listFields as $field => $parameters) {
                    if(count($parameters) == 0){
                        $attributes[] = $field;
                    }else{
                        $attributes[$field] = $parameters;
                    }
                }
                $defaultDetail["attributes"] = $attributes;
            }
            
            $detail = ArrayHelper::merge($defaultDetail, $detail);
            
            $buttonsTop = $this->buildButtons(["index","create","edit","delete"],$entity);
            
            $content = $class::widget($detail);
            return $this->render('view.twig', [
                'buttonsTop' => $buttonsTop,
                'content' => $content,
            ]);
        } else {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    public function actionUpdate() {
        if (method_exists($this->entity, 'canUpdate') && $this->entity->canUpdate()) {
            
            /* @var Form $form */
            $form = Yii::createObject(ArrayHelper::merge([
                'model' => $this->model,
            ], $this->entity->form()));
            $entity = $this->entity;
            $url = \yii\helpers\Url::to([
                'manage/index',
                'entity' => $entity->slug(),
            ]);
            $breadcrumb = Yii::$container->get("common.manager.breadcrumb");
            $breadcrumb->breadcrumb([
                $url => Yii::t("admin", sprintf("%s list",$entity->slug())),
            ])->breadcrumb([
                Yii::$app->getRequest()->url => Yii::t("admin",(string)$this->model),
            ]);
            if (Yii::$app->getRequest()->getIsPost()) {
                
                $form->load(Yii::$app->getRequest()->getBodyParams());
                $form->runActions();
                $form->beforeSave();
                if ($form->model->validate()) {
                    if ($form->model->save()) {
                        $form->afterSave();
                        $this->module->trigger(Entity::EVENT_UPDATE_SUCCESS, new Event([
                            'sender' => $form->model,
                        ]));
                        $this->addAlertMessage(self::ALERT_TYPE_SUCCESS,"flash.update.success",[strtolower($this->trans($this->entity->slug())),$form->model]);
                        if($this->getRequest()->post("update_and_edit") === null){
                            return $this->redirect($url);
                        }
                    } else {
                        $form->afterFail();
                        $this->module->trigger(Entity::EVENT_UPDATE_FAIL, new Event([
                            'sender' => $form->model,
                        ]));
                        $this->addAlertMessage(self::ALERT_TYPE_DANGER,"flash.update.error",[strtolower($this->trans($this->entity->slug())),$form->model]);
                    }
                }
            }
            $buttonsEntity = [];
            $actions = $form->actions();
            foreach($actions as $name => $action){
                if (isset($action['visible']) && !$action['visible']) continue;
                $buttonsEntity[]= html_entity_decode($action['class']::widget(array_merge($action, ['name' => $name])));
            }
            $buttonsEntity = array_merge($buttonsEntity,$this->buildButtons(["form_update"], $this->entity));
            
            $buttonsTop = $this->buildButtons(["view","index","create"], $entity);
            return $this->render('update.twig', [
                'entity' => $this->entity,
                'model' => $this->model,
                'form' => $form,
                'buttonsEntity' => $buttonsEntity,
                'buttonsTop' => $buttonsTop,
            ]);
        } else {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    public function actionDelete() {
        if (method_exists($this->entity, 'canDelete') && $this->entity->canDelete()) {
            if (Yii::$app->getRequest()->getIsPost()) {
                $transaction = Yii::$app->db->beginTransaction();
                if ($this->model->delete()) {
                    $this->addAlertMessage(self::ALERT_TYPE_SUCCESS,"flash.delete.success",[strtolower($this->trans($this->entity->slug())),$this->model]);
                    
                    
                    $this->module->trigger(Entity::EVENT_DELETE_SUCCESS, new Event([
                        'sender' => $this->model,
                    ]));
                } else {
                    $this->addAlertMessage(self::ALERT_TYPE_DANGER,"flash.delete.error",[strtolower($this->trans($this->entity->slug())),$this->model]);
                    $this->module->trigger(Entity::EVENT_DELETE_FAIL, new Event([
                        'sender' => $this->model,
                    ]));
                }
                $transaction->commit();

                return $this->redirect(['index', 'entity' => $this->entity->id]);
            }
            return $this->render('delete', [
                'entity' => $this->entity,
                'model' => $this->model,
            ]);
        } else {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    public function actionCreate() {
        if (method_exists($this->entity, 'canCreate') && $this->entity->canCreate()) {
            $model = Yii::createObject($this->entity->model(), []);
            /* @var Form $form */
            $form = Yii::createObject(ArrayHelper::merge([
                'model' => $model,
            ], $this->entity->form()));
            
            $url = \yii\helpers\Url::to([
                'manage/index',
                'entity' => $this->entity->slug(),
            ]);
            $newEntity = (string)$model;
            if(empty($newEntity)){
                $newEntity = "-";
            }
            $breadcrumb = Yii::$container->get("common.manager.breadcrumb");
            $breadcrumb->breadcrumb([
                $url => Yii::t("admin", sprintf("%s list",$this->entity->slug())),
            ])->breadcrumb([
                Yii::$app->getRequest()->url => Yii::t("admin",$newEntity),
            ]);
            
            if (Yii::$app->getRequest()->getIsPost()) {
                $form->load(Yii::$app->getRequest()->getBodyParams());
                $form->beforeSave();
                if ($form->model->validate()) {
                    var_dump($_POST);
                    DIE;
                    if ($form->model->save()) {
                        $form->afterSave();
                        $this->module->trigger(Entity::EVENT_CREATE_SUCCESS, new Event([
                            'sender' => $form->model,
                        ]));
                        $this->addAlertMessage(self::ALERT_TYPE_SUCCESS,"flash.create.success",[strtolower($this->trans($this->entity->slug())),$form->model]);
                        $this->getRequest()->post("create_and_edit");
                        $this->getRequest()->post("create_and_list");
                        $this->getRequest()->post("create_and_create");
                        return $this->redirect([
                            'update',
                            'entity' => $this->entity->id,
                            'id' => $form->model->primaryKey,
                        ]);
                    } else {
                        $form->afterFail();
                        
                        $this->addAlertMessage(self::ALERT_TYPE_DANGER,"flash.create.error",[strtolower($this->trans($this->entity->slug()))]);
                        
                        $this->module->trigger(Entity::EVENT_CREATE_FAIL, new Event([
                            'sender' => $form->model,
                        ]));
                    }
                }else{
                }
            }
            $buttonsEntity = [];
            $actions = $form->actions();
            foreach($actions as $name => $action){
                if (isset($action['visible']) && !$action['visible']) continue;
                $buttonsEntity[]= html_entity_decode($action['class']::widget(array_merge($action, ['name' => $name])));
            }
            $buttonsEntity = array_merge($buttonsEntity,$this->buildButtons(["form_create"], $this->entity));
            
            $buttonsTop = $this->buildButtons(["index","create"], $this->entity);
            return $this->render('create.twig', [
                'entity' => $this->entity,
                'model' => $model,
                'form' => $form,
                'buttonsEntity' => $buttonsEntity,
                'buttonsTop' => $buttonsTop,
            ]);
        } else {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    /**
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\BadRequestHttpException
     * @return ActiveRecord
     */
    public function getModel() {
        $entity = $this->entity;
        $id = Yii::$app->getRequest()->getQueryParam('id', null);
        if (!$id || !$entity) {
            throw new BadRequestHttpException();
        }
        $model = $this->loadModel($entity, $id);
        if (!$model) {
            throw new NotFoundHttpException();
        }
        return $model;
    }

    /**
     * Load model
     * @param Entity $entity
     * @param string|integer $id
     * @return ActiveRecord mixed
     */
    public function loadModel($entity, $id) {
        if ($this->_model) {
            return $this->_model;
        }
        /* @var ActiveRecord $modelClass */
        $modelClass = $entity->getModelName();
        /* @var ActiveQuery $query */
        $query = call_user_func([$modelClass, 'find']);
        $query->where([$entity->primaryKey() => $id]);

        $condition = $entity->getModelConditions();
        if (is_callable($condition)) {
            $query = call_user_func($condition, $query);
        } elseif (is_array($condition)) {
            $query = $query->andWhere($condition);
        }

        $this->_model = $query->one();
        return $this->_model;
    }
    
    /**
     * Construye botones
     * @param array $names
     * @param type $entity
     * @return type
     */
    private function buildButtons(array $names,$entity) {
        $buttons = [];
        $primaryKey = $entity->primaryKey();
        if(in_array("view",$names) && method_exists($entity, 'canRead') && $entity->canRead()){
            $actionColumn = new \asdfstudio\admin\grids\ActionColumn();
            $buttons[] = $actionColumn->buttons["view"](null,$this->model,null);
        }
        if(in_array("create",$names) && method_exists($this->entity, 'canCreate') && $this->entity->canCreate()){
            $buttons[] = Html::a("<i class='fa fa-plus-circle'></i>&nbsp;".Yii::t('admin', 'button.add_new'), ['create', 'entity' => $entity->id], ['class' => '']);
        }
        if(in_array("edit",$names) && method_exists($this->entity, 'canUpdate') && $this->entity->canUpdate()){
            $buttons []= Html::a("<i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".Yii::t('admin', 'button.edit'), ['update', 'entity' => $entity->id, 'id' => $this->model->{$primaryKey}], ['class' => '']);
        }
        if(in_array("delete",$names) && method_exists($this->entity, 'canDelete') && $this->entity->canDelete()){
            $buttons []= Html::a("<i class='glyphicon glyphicon-remove'></i>&nbsp;".Yii::t('admin', 'button.delete'), ['delete', 'entity' => $entity->id, 'id' => $this->model->{$primaryKey}], [
                'class' => '',
                'data' => [
                    'confirm' => $this->trans("question.delete.confirm",[$this->model]),
                    'method' => 'post',
                ],
            ]);
        }
        if(in_array("index",$names)){
            $url = \yii\helpers\Url::to([
                'manage/index',
                'entity' => $entity->slug(),
            ]);
            $buttons[] = Html::a('<i class="fa fa-list"></i>&nbsp;'.Yii::t('admin', 'button.return_to_index'), $url);
        }
        
        if(in_array("form_create", $names)){
            $buttons[] = html_entity_decode(\asdfstudio\admin\forms\widgets\Button::widget(
                ['label' => "<i class=\"fa fa-save\"></i>&nbsp;".Yii::t('admin', 'button.create_and_edit'),
                'name' => 'create_and_edit',
                'options' => [
                    'class' => 'btn btn-success',
                ],]));
            $buttons[] = html_entity_decode(\asdfstudio\admin\forms\widgets\Button::widget(
                ['label' => "<i class=\"fa fa-save\"></i><i class=\"fa fa-list\"></i>&nbsp;".Yii::t('admin', 'button.create_and_list'),
                'name' => 'create_and_list',
                'options' => [
                    'class' => 'btn btn-success',
                ],]));
            $buttons[] = html_entity_decode(\asdfstudio\admin\forms\widgets\Button::widget(
                ['label' => "<i class=\"fa fa-plus-circle\"></i>&nbsp;".Yii::t('admin', 'button.create_and_create'),
                'name' => 'create_and_create',
                'options' => [
                    'class' => 'btn btn-success',
                ],]));
        }
        if(in_array("form_update", $names)){
            $buttons[] = html_entity_decode(\asdfstudio\admin\forms\widgets\Button::widget(
                ['label' => "<i class=\"fa fa-save\"></i>&nbsp;".Yii::t('admin', 'button.update_and_edit'),
                'name' => 'update_and_edit',
                'options' => [
                    'class' => 'btn btn-success',
                ],]));
            $buttons[] = html_entity_decode(\asdfstudio\admin\forms\widgets\Button::widget(
                ['label' => "<i class=\"fa fa-save\"></i><i class=\"fa fa-list\"></i>&nbsp;".Yii::t('admin', 'button.update_and_list'),
                'name' => 'update_and_list',
                'options' => [
                    'class' => 'btn btn-success',
                ],]));
            if(method_exists($this->entity, 'canDelete') && $this->entity->canDelete()){
                $buttons []= Html::a("<i class='fa fa-minus-circle'></i>&nbsp;".Yii::t('admin', 'button.delete'), ['delete', 'entity' => $entity->id, 'id' => $this->model->{$primaryKey}], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => $this->trans("question.delete.confirm",[$this->model]),
                        'method' => 'post',
                    ],
                ]);
            }
        }
        return $buttons;
    }
}
