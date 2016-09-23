<?php

/*
 * This file is part of the BtoB4Rewards package.
 * 
 * (c) www.btob4rewards.com
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace asdfstudio\admin\models;

/**
 * Servicios del modulo (admin.module_service)
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
class AdminModuleService {
    
    /**
     * @return \asdfstudio\admin\Module
     */
    public function getImageProfileUser()
    {
        $imageProfileUser = \Yii::$app->getModule('admin-master')->imageProfileUser;
        if($imageProfileUser instanceof \Closure){
            $imageProfileUser = $imageProfileUser();
        }
        return $imageProfileUser;
    }
    public function renderSideBar() {
        return \Yii::$container->get("admin.builder.menu_sidebar")->render();
    }
}
