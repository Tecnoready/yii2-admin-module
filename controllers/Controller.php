<?php

namespace asdfstudio\admin\controllers;

use asdfstudio\admin\base\Entity;
use asdfstudio\admin\Module;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\Controller as WebController;

/**
 * Class Controller
 * @package asdfstudio\admin\controllers
 * @property Module $module
 */
abstract class Controller extends WebController {

    const ALERT_TYPE_SUCCESS = "alert_success";
    const ALERT_TYPE_INFO = "alert_info";
    const ALERT_TYPE_WARNING = "alert_warning";
    const ALERT_TYPE_DANGER = "alert_danger";
    
//    public $layout = 'main'; 
    public $layout = false; 

    public function beforeAction($action) {
        if (parent::beforeAction($action)) {
            $this->view->params['breadcrumbs'][] = [
                'label' => \Yii::t('admin', 'Dashboard'),
                'url' => ['/admin/admin/index'],
            ];
            return true;
        }
        return false;
    }

    /**
     * Load registered item
     * @param string $entity Entity name
     * @return Entity
     */
    public function getEntity($entity) {
        if (isset($this->module->entities[$entity])) {
            return $this->module->entities[$entity];
        } elseif (isset($this->module->entitiesClasses[$entity])) {
            return $this->getEntity($this->module->entitiesClasses[$entity]);
        }
        return null;
    }
    
    /**
     * 
     * @param type $id
     * @param array $parameters
     * @param type $type self::ALERT_TYPE_*
     */
    protected function addAlertMessage($type,$id,array $parameters = []) {
        $alert = $this->trans($id,$parameters);
        \Yii::$app->session->addFlash($type, $alert);
    }
    
    protected function trans($id,array $parameters = [],$domain = "admin") {
        return \Yii::t($domain, $id,$parameters);
    }
    
    /**
     * 
     * @return \yii\web\Request
     */
    protected function getRequest() {
        return \Yii::$app->getRequest();
    }
}
