<?php

/*
 * This file is part of the BtoB4Rewards package.
 * 
 * (c) www.btob4rewards.com
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace asdfstudio\admin\twig\extension;

use Twig_SimpleFunction;

/**
 * Extension del administrador
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
class AdminExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    public function getFunctions() {
        return [
            new Twig_SimpleFunction('unset',array($this,"unsetVar")),
        ];
    }
    
    public function getGlobals() {
        return [
            "adminModuleService" => \Yii::$container->get("admin.module_service"),
        ];
    }
    
    public function unsetVar(array $array,$key) {
        unset($array[$key]);
    }
    
    public function getName() {
        return "yii2_admin";
    }
}
