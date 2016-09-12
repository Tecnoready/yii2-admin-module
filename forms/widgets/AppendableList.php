<?php


namespace asdfstudio\admin\forms\widgets;


use asdfstudio\admin\base\Admin;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class AppendableList extends Base
{
    /**
     * Query for load all items
     * @var ActiveQuery|callable
     */
    public $query;
    /**
     * Admin class
     * @var string|Admin
     */
    public $entity;
    /**
     * Grid columns
     * @var array|null
     */
    public $columns = null;
    /**
     * Widget template
     * @var string
     */
    public $template = '@vendor/sgdot/yii2-admin-module/views/widgets/appendable';

    public function init()
    {
        parent::init();
        if (is_string($this->entity)) {
            $id = call_user_func([$this->entity, 'slug']);
            $this->entity = \Yii::createObject([
                'class' => $this->entity,
                'id' => $id,
            ]);
        }
    }

    public function renderInput($value, $attribute = null)
    {
        $query = is_callable($this->query) ? call_user_func($this->query, $this->model): $this->query;
        return $this->render($this->template, [
            'entity' => $this->entity,
            'columns' => $this->columns,
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
            ]),
        ]);
    }
}
