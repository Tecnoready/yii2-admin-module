<?php
/**
 * Class ActionColumn
 *
 * @package asdfstudio\admin\grids
 */

namespace asdfstudio\admin\grids;


use asdfstudio\admin\helpers\AdminHelper;
use Yii;
use yii\helpers\Html;

class ActionColumn extends \yii\grid\ActionColumn {

    public $template = '
        <div class="btn-group">
          <button type="button" class="btn btn-default">{view}</button>
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
          </button>
          <ul class="dropdown-menu">
            <li>{view}</li> 
            <li>{update}</li> 
            <li role="separator" class="divider"></li>
            <li>{delete}</li>
          </ul>
        </div>
        ';
    /**
     * @inheritdoc
     */
    protected function initDefaultButtons() {

        $entity     = Yii::$app->getRequest()->getQueryParam('entity', null);
        $primaryKey = AdminHelper::getEntity($entity)->primaryKey();

        if (!isset($this->buttons['view'])) {
            $this->buttons['view'] = function ($url, $model, $key) use ($entity, $primaryKey) {
                $options = array_merge([
                    'title'      => Yii::t('admin', 'View'),
                    'aria-label' => Yii::t('admin', 'View'),
                    'data-pjax'  => '0',
                    'original-title'  => 'original-title',
                ], $this->buttonOptions);

                //Html::addCssClass($options, 'btn btn-primary');

                return Html::a('<span class="glyphicon glyphicon-zoom-in"></span>&nbsp;'.Yii::t('admin', 'View'), [
                    'manage/view',
                    'entity' => $entity,
                    'id'     => $model->{$primaryKey},
                ], $options);
            };
        }
        if (!isset($this->buttons['update'])) {
            $this->buttons['update'] = function ($url, $model, $key) use ($entity, $primaryKey) {
                $options = array_merge([
                    'title'      => Yii::t('admin', 'Edit'),
                    'aria-label' => Yii::t('admin', 'Edit'),
                    'data-pjax'  => '0',
                ], $this->buttonOptions);

                //Html::addCssClass($options, 'btn btn-warning');

                return Html::a("<span class=\"glyphicon glyphicon-edit\"></span>&nbsp;".Yii::t('admin', 'Edit'), [
                    'manage/update',
                    'entity' => $entity,
                    'id'     => $model->{$primaryKey},
                ], $options);
            };
        }
        if (!isset($this->buttons['delete'])) {
            $this->buttons['delete'] = function ($url, $model, $key) use ($entity, $primaryKey) {
                $options = array_merge([
                    'title'      => Yii::t('admin', 'Delete'),
                    'aria-label' => Yii::t('admin', 'Delete'),
                    'data'       => [
                        'confirm' => Yii::t('admin', 'Are you sure you want to delete this item?'),
                        'method'  => 'post',
                        'pjax'    => '0',
                    ],
                ], $this->buttonOptions);

                //Html::addCssClass($options, 'btn btn-danger');

                return Html::a("<i class='glyphicon glyphicon-remove'></i>&nbsp;".Yii::t('admin', 'Delete'), [
                    'manage/delete',
                    'entity' => $entity,
                    'id'     => $model->{$primaryKey},
                ], $options);
            };
        }
    }
}