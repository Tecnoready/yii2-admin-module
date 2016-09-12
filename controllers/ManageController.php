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
            
            $buttons = [
                Html::a("<i class='fa fa-plus-circle'></i>&nbsp;".Yii::t('admin', 'button.add_new'), ['create', 'entity' => $entity->id], ['class' => ''])
            ];
            return $this->render('index.twig', [
                'entity' => $entity,
                'modelsProvider' => $modelsProvider,
                'htmlGrid' => $htmlGrid,
                'buttons' => $buttons,
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
            $primaryKey = $entity->primaryKey();
            
            $buttons = [];
            $buttons []= Html::a("<i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".Yii::t('admin', 'button.edit'), ['update', 'entity' => $entity->id, 'id' => $model->{$primaryKey}], ['class' => 'btn btn-primary btn-sm']);
            $buttons []= Html::a("<i class='glyphicon glyphicon-remove'></i>&nbsp;".Yii::t('admin', 'button.delete'), ['delete', 'entity' => $entity->id, 'id' => $model->{$primaryKey}], [
                'class' => 'btn btn-danger btn-sm',
                'data' => [
                    'confirm' => Yii::t('admin', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]);
            
            $content = $class::widget($detail);
            return $this->render('view.twig', [
                'buttons' => $buttons,
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
                        return $this->redirect($url);
                    } else {
                        $form->afterFail();
                        $this->module->trigger(Entity::EVENT_UPDATE_FAIL, new Event([
                            'sender' => $form->model,
                        ]));
                        $this->addAlertMessage(self::ALERT_TYPE_DANGER,"flash.update.error",[strtolower($this->trans($this->entity->slug())),$form->model]);
                    }
                }
            }
            $buttons = [];
            $actions = $form->actions();
            foreach($actions as $name => $action){
                if (isset($action['visible']) && !$action['visible']) continue;
                $buttons[]= html_entity_decode($action['class']::widget(array_merge($action, ['name' => $name])));
            }
            $actionColumn = new \asdfstudio\admin\grids\ActionColumn();
            $buttonsTop = [];
            $buttonsTop[] = $actionColumn->buttons["view"](null,$this->model,null);
            return $this->render('update.twig', [
                'entity' => $this->entity,
                'model' => $this->model,
                'form' => $form,
                'buttonsEntity' => $buttons,
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
                    $this->module->trigger(Entity::EVENT_DELETE_SUCCESS, new Event([
                        'sender' => $this->model,
                    ]));
                } else {
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

            if (Yii::$app->getRequest()->getIsPost()) {
                $form->load(Yii::$app->getRequest()->getBodyParams());
                $form->beforeSave();
                if ($form->model->validate()) {
                    if ($form->model->save()) {
                        $form->afterSave();
                        $this->module->trigger(Entity::EVENT_CREATE_SUCCESS, new Event([
                            'sender' => $form->model,
                        ]));

                        return $this->redirect([
                            'update',
                            'entity' => $this->entity->id,
                            'id' => $form->model->primaryKey,
                        ]);
                    } else {
                        $form->afterFail();
                        $this->module->trigger(Entity::EVENT_CREATE_FAIL, new Event([
                            'sender' => $form->model,
                        ]));
                    }
                }
            }

            return $this->render('create', [
                'entity' => $this->entity,
                'model' => $model,
                'form' => $form,
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
}
