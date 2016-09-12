<?php

namespace asdfstudio\admin\forms\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQueryInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use asdfstudio\admin\helpers\AdminHelper;

/**
 * Class Select
 * @package asdfstudio\admin\widgets
 *
 * Renders active select widget with related models
 */
class Select extends Base
{
    /**
     * @var ActiveQueryInterface|array
     */
    public $query;
    /**
     * @var string
     */
    public $labelAttribute;
    /**
     * @var array
     */
    public $items = [];
    /**
     * @var bool
     */
    public $multiple = false;
    /**
     * @var bool Allows empty value
     */
    public $allowEmpty = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (is_callable($this->query)) {
            $this->query = call_user_func($this->query, $this->model);
        }
        if ($this->query instanceof ActiveQueryInterface) {
//            if (!$this->labelAttribute) {
//                throw new InvalidConfigException('Parameter "labelAttribute" is required');
//            }
            $itemsResult = $this->query->all();
            foreach ($itemsResult as $i => $model) {
                $id = $model->id;
                if($this->labelAttribute){
                    $value = AdminHelper::resolveAttribute($this->labelAttribute, $model);
                }else{
                    $value = (string)$model;
                }
                $this->items[$id] = $value;
            }
        }
        if ($this->allowEmpty) {
            $this->items = ArrayHelper::merge([
                null => Yii::t('yii', '(not set)')
            ], $this->items);
        }
        parent::init();
    }
    public function run() 
    {
        if (!$this->multiple) {
            return parent::run();
        } else {
            $input = $this->renderInput($this->model->{$this->attribute}, $this->attribute);
            $res = $this->appendable ? $this->wrapAppendable($input) : $input;
        }
        return $res;
    }
    /**
     * @inheritdoc
     */
    public function renderInput($value, $attribute = null)
    {
        $select_id = Html::getInputId($this->model, $attribute ? $attribute : $this->attribute);
        $this->view->registerJs("$('#{$select_id}').selectpicker();");

        Html::addCssClass($this->options, 'form-control');
        if(!isset($this->options['multiple'])){
            $this->options['multiple'] = $this->multiple;
        }
        return Html::activeDropDownList($this->model, $attribute ? $attribute : $this->attribute, $this->items, $this->options);
    }
}
