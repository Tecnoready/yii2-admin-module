<?php

namespace asdfstudio\admin\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;

class AdminController extends Controller {

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

    public function actionIndex() {
        if (method_exists($this->module, 'canRead') && $this->module->canRead()) {
            return $this->render('index.twig');
        } else {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

}
