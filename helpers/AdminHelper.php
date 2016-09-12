<?php


namespace asdfstudio\admin\helpers;


use Yii;
use asdfstudio\admin\Module;
use asdfstudio\admin\base\Admin;
use yii\db\ActiveRecord;

class AdminHelper
{
    /**
     * @param string $entity Admin class name or Id
     * @return Admin|null
     */
    public static function getEntity($entity)
    {
        /* @var Module $module */
        $module = Yii::$app->controller->module;

        if (isset($module->entities[$entity])) {
            return $module->entities[$entity];
        } elseif (isset($module->entitiesClasses[$entity])) {
            return static::getEntity($module->entitiesClasses[$entity]);
        }

        return null;
    }

    /**
     * Return value of nested attribute.
     *
     * ```php
     * // e.g. $post is Post model. We need to get a name of owmer. Owner is related model.
     *
     * AdminHelper::resolveAttribute('owner.username', $post); // it returns username from owner attribute
     * ```
     *
     * @param string $attribute
     * @param ActiveRecord $model
     * @return string
     */
    public static function resolveAttribute($attribute, $model)
    {
        $path = explode('.', $attribute);
        $attr = $model;
        foreach ($path as $a) {
            $attr = $attr->{$a};
        }
        return $attr;
    }
}
