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
          <button type="button" class="btn btn-default btn-sm">{view}</button>
          <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
        $entityObject = AdminHelper::getEntity($entity);
        $primaryKey = $entityObject->primaryKey();
        $self = $this;
        if (!isset($this->buttons['view'])) {
            $this->buttons['view'] = function ($url, $model, $key) use ($entity, $primaryKey) {
                $options = array_merge([
                    'title'      => Yii::t('admin', 'View'),
                    'aria-label' => Yii::t('admin', 'View'),
                    'data-pjax'  => '0',
                    'original-title'  => 'original-title',
                ], $this->buttonOptions);

                //Html::addCssClass($options, 'btn btn-primary');

                return Html::a('<i class="glyphicon glyphicon-zoom-in"></i>&nbsp;'.Yii::t('admin', 'button.view'), [
                    'manage/view',
                    'entity' => $entity,
                    'id'     => $model->{$primaryKey},
                ], $options);
            };
        }
        if (!isset($this->buttons['update'])) {
            $this->buttons['update'] = function ($url, $model, $key) use ($entity, $primaryKey,$entityObject) {
                if(method_exists($entityObject, 'canUpdate') && $entityObject->canUpdate()){
                    $options = array_merge([
                        'title'      => Yii::t('admin', 'Edit'),
                        'aria-label' => Yii::t('admin', 'Edit'),
                        'data-pjax'  => '0',
                    ], $this->buttonOptions);

                    //Html::addCssClass($options, 'btn btn-warning');

                    return Html::a("<i class=\"glyphicon glyphicon-edit\"></i>&nbsp;".Yii::t('admin', 'button.edit'), [
                        'manage/update',
                        'entity' => $entity,
                        'id'     => $model->{$primaryKey},
                    ], $options);
                }
            };
        }
        if (!isset($this->buttons['delete'])) {
            $this->buttons['delete'] = function ($url, $model, $key) use ($entity, $primaryKey,$entityObject,$self) {
                if(method_exists($entityObject, 'canDelete') && $entityObject->canDelete()){
                    $options = array_merge([
                    'title'      => Yii::t('admin', 'Delete'),
                    'aria-label' => Yii::t('admin', 'Delete'),
                    'data'       => [
                        'confirm' => Yii::t('admin', 'question.delete.confirm',[$model]),
                        'method'  => 'post',
                        'pjax'    => '0',
                    ],
                ], $this->buttonOptions);

                    //Html::addCssClass($options, 'btn btn-danger');

                    return Html::a("<i class='glyphicon glyphicon-remove'></i>&nbsp;".Yii::t('admin', 'button.delete'), [
                        'manage/delete',
                        'entity' => $entity,
                        'id'     => $model->{$primaryKey},
                    ], $options);
                }//fin if
            };
            if(method_exists($entityObject, 'canDelete') && $entityObject->canDelete()){
                
            }else{
                $self->template = '
                    <div class="btn-group">
                      <button type="button" class="btn btn-default btn-sm">{view}</button>
                      <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                      </button>
                      <ul class="dropdown-menu">
                        <li>{view}</li> 
                        <li>{update}</li> 
                        <li>{delete}</li>
                      </ul>
                    </div>
                    ';
            }
        }
    }
}