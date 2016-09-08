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

            return $this->render('index.twig', [
                'entity' => $entity,
                'modelsProvider' => $modelsProvider,
            ]);
        } else {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    public function actionView() {
        if (method_exists($this->entity, 'canRead') && $this->entity->canRead()) {
            return $this->render('view', [
                'entity' => $this->entity,
                'model' => $this->model,
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
                    } else {
                        $form->afterFail();
                        $this->module->trigger(Entity::EVENT_UPDATE_FAIL, new Event([
                            'sender' => $form->model,
                        ]));
                    }
                }
            }
            return $this->render('update', [
                'entity' => $this->entity,
                'model' => $this->model,
                'form' => $form,
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
