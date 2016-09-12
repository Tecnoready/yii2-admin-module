<?php

namespace asdfstudio\admin\models\menu;


use yii\base\Object;

class Menu extends Object
{
    use ItemsCollectionTrait {
        addItem as addItemOriginal;
    }

    /**
     * Categories with items
     * @var Category[]
     */
    public $categories = [];
    /**
     * Render order
     * @var array
     */
    protected $order = [];
    
    public $groups = [];

    public function init() {
        parent::init();
        $this->groups = [];
    }
    
    public function addGroup($group,array $parameters = []) {
        if(isset($this->groups[$group])){
            throw new Exception(sprintf("The group '%s' is already added.",$group));
        }
        $this->groups[$group] = $parameters;
        return $this;
    }

    public function addCategory($label)
    {
        $category = new Category([
            'label' => $label,
        ]);
        $this->categories[] = $category;

        $index = sizeof($this->categories) - 1;
        $this->order[] = ['category', $index];

        return $category;
    }

    /**
     * @inheritdoc
     */
    public function addItem()
    {
        $index = call_user_func_array([$this, 'addItemOriginal'], func_get_args());

        $this->order[] = ['item', $index];
        return $this->items[$index];
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $res = [];

        foreach ($this->order as $order) {
            if ($order[0] == 'category') {
                $res[] = $this->categories[$order[1]]->toArray();
            } elseif ($order[0] == 'item') {
                $res[] = $this->items[$order[1]]->toArray();
            }
        }

        return $res;
    }
}
