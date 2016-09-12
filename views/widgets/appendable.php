<?php
use yii\helpers\Html;
use yii\grid\GridView;

/**
 * @var yii\web\View $this
 * @var \asdfstudio\admin\base\Admin $entity
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var array $columns
 */
?>
<div class="row appendable-list">
    <div class="col-md-11">
        <?php echo GridView::widget([
            'summary' => '',
            'dataProvider' => $dataProvider,
            'columns' => array_merge($columns, [
                [
                    'class' => \yii\grid\ActionColumn::className(),
                    'buttons' => [
                        'view' => function($url, $model, $key) use ($entity) {
                            return Html::a(
                                Yii::t('admin', 'View'),
                                ['manage/view', 'entity' => $entity->id, 'id' => $model->id],
                                ['class' => 'btn btn-primary']
                            );
                        },
                        'update' => function($url, $model, $key) use ($entity) {
                            return Html::a(
                                Yii::t('admin', 'Edit'),
                                ['manage/update', 'entity' => $entity->id, 'id' => $model->id],
                                ['class' => 'btn btn-warning']
                            );
                        },
                        'delete' => function($url, $model, $key) use ($entity) {
                            return Html::a(
                                Yii::t('admin', 'Delete'),
                                ['manage/delete', 'entity' => $entity->id, 'id' => $model->id],
                                ['class' => 'btn btn-danger', 'data' => [
                                    'confirm' => Yii::t('admin', 'Are you sure you want to delete this item?'),
                                    'method' => 'post',
                                ],]
                            );
                        },
                    ],
                ]
            ]),
        ])?>
    </div>
    <div class="col-md-1">
        <div class="form-group pull-right">
            <?php echo Html::a(Yii::t('admin', 'Add'), ['create', 'entity' => $entity->id], ['class' => 'btn btn-success']) ?>
        </div>
    </div>
</div>
