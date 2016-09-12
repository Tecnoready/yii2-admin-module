<?php

/*
 * This file is part of the BtoB4Rewards package.
 * 
 * (c) www.btob4rewards.com
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace asdfstudio\admin\models\mapper;

/**
 * Maper de show de elementos
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
class ShowMapper 
{
    protected $model;
    protected $list;
    public function __construct($model) {
        $this->list = [];
        $this->model = $model;
    }
    public function add($name,array $fieldDescriptionOptions = array()) {
        $this->list[$name] = $fieldDescriptionOptions;
        
        return $this;
    }
    
    public function getList() {
        return $this->list;
    }
    
    public function getModel() {
        return $this->model;
    }
}
