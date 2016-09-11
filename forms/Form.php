<?php


namespace asdfstudio\admin\forms;


use asdfstudio\admin\forms\widgets\Button;
use asdfstudio\admin\forms\widgets\Input;
use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * Class Form
 * @package asdfstudio\admin\forms
 *
 * Renders form with defined fields and layout.
 *
 * ```php
 *
 * ```
 */
class Form extends Model
{
    /**
     * Model used in form
     * @var ActiveRecord
     */
    public $model;
    /**
     * View file
     */
    public $viewFile = null;
    /**
     * Html form options
     */
    public $options = [];

    /**
     * List of model fields displayed in form
     *
     * ```php
     *  return [
     *      'id' => [
     *          'class' => Input::className(),
     *      ],
     *      'username' => [
     *          'class' => Input::className(),
     *      ],
     *      'status' => [
     *          'class' => Select::className(),
     *          'items' => [User::STATUS_ACTIVE => 'Active', User::STATUS_DELETED => 'Deleted'],
     *      ],
     *  ];
     * ```
     *
     * Default is all model attributes with class = Input
     *
     * @return array
     */
    public function fields()
    {
        $fields = [];
        foreach ($this->model->attributes() as $attribute) {
            $fields[$attribute] = [
                'class' => Input::className(),
            ];
        }

        return $fields;
    }

    /**
     * Form actions, triggered when form button is clicked
     *
     * ```php
     *  return [
     *      'ban' => [
     *          'class' => Button::className(),
     *          'label' => 'Ban',
     *          'options' => [
     *              'class' => 'btn btn-danger', // bootstrap button classes
     *          ],
     *          'action' => function($model, $form) { // can be callable or string
     *               $model->status = User::STATUS_BANNED;
     *               return true; // return true if success
     *           }
     *           // or
     *           'action' => 'ban', // actionBan will be called
     *      ],
     * ];
     * ```
     * @return array
     */
    public function actions()
    {
        return [
            'save' => [
                'class' => Button::className(),
                'label' => "<i class=\"fa fa-save\"></i>&nbsp;".Yii::t('admin', 'button.save'),
                'options' => [
                    'class' => 'btn btn-success'
                ],
            ]
        ];
    }

    /**
     * Run actions
     * @return bool
     */
    public function runActions()
    {
        $data = Yii::$app->getRequest()->getBodyParams();
        $actions = $this->actions();

        foreach ($actions as $id => $action) {
            $closure = isset($action['action']) ? $action['action'] : null;
            if (isset($data[$id])) {
                if (is_callable($closure)) {
                    call_user_func($closure, $this->model);
                } elseif (is_string($closure)) {
                    call_user_func([$this->model, $closure]);
                } else {

                }
            }
        }
    }

    /**
     * Load data into model. Using setters
     * @see [[Model::load()]]
     * @param array $data
     * @param null $formName
     * @return bool
     */
    public function load($data, $formName = null)
    {
        $scope = $formName === null ? $this->model->formName() : $formName;
        if ($scope != '' && isset($data[$scope])) {
            $data = $data[$scope];
        }

        foreach ($data as $attribute => $value) {
            $this->model->{$attribute} = $value;
        }
        return true;
    }

    /**
     * Triggers before form saving model
     */
    public function beforeSave() {}

    /**
     * Triggers after model saved
     */
    public function afterSave() {}

    /**
     * Triggers after model's saving fails
     */
    public function afterFail() {}
}
