<?php

namespace asdfstudio\admin\models\menu;


use yii\base\Object;
use yii\helpers\Inflector;

/**
 * Class Item
 * @package asdfstudio\admin\models\menu
 */
class Item extends Object
{
    /**
     * Item id. Using for ordering
     * @var
     */
    public $id;
    /**
     * Item label
     * @var string
     */
    public $label;
    /**
     * Routes
     * @see http://www.yiiframework.com/doc-2.0/guide-url.html
     * @var string|array
     */
    public $url;
    /**
     * Active checker
     * @var callable|boolean
     */
    public $active;
    
    /**
     * Grupo
     * @var type 
     */
    public $group;
    
    public $labelCatalogue;
    
    public $icon;
    
    public $tag;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!$this->id) {
            $this->id = Inflector::slug($this->label);
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['id', 'string', 'length' => [0, 255]],
            ['label', 'string', 'length' => [1, 255]],
            ['url', 'safe'],
            ['active', 'safe'],
        ];
    }

    /**
     * @return bool
     */
    public function validate()
    {
        if(mb_strlen($this->id) < 0 || mb_strlen($this->id) > 255) {
            return false;
        }
        if(mb_strlen($this->label) < 1 || mb_strlen($this->label) > 255) {
            return false;
        }
        return true;
    }

    /**
     * Render item into array
     * @return array
     */
    public function toArray()
    {
        $active = (is_callable($this->active) ? call_user_func($this->active) : $this->active);
        return [
            'label' => $this->label,
            'url' => $this->url,
            'active' => $active,
        ];
    }
}
