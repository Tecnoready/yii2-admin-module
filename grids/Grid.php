<?php


namespace asdfstudio\admin\grids;


use Yii;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\widgets\LinkPager;

class Grid extends GridView {

    public $summaryOptions = ['class' => 'summary pull-left pagination'];
    
    /**
     * @var string the layout that determines how different sections of the list view should be organized.
     * The following tokens will be replaced with the corresponding section contents:
     *
     * - `{summary}`: the summary section. See [[renderSummary()]].
     * - `{errors}`: the filter model error summary. See [[renderErrors()]].
     * - `{items}`: the list items. See [[renderItems()]].
     * - `{sorter}`: the sorter. See [[renderSorter()]].
     * - `{pager}`: the pager. See [[renderPager()]].
     */
    public $layout = '
            <div class="ibox-title">
                <h5> </h5>
                <div class="ibox-tools">
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                </div>
            </div>
            <div class="ibox-content text-center css-animation-box">
            {items}
            </div>
            <div class="ibox-footer">
            {summary}{pager}
            &nbsp;
            <br/><br/><br/>
            </div>
            <br/><br/>';
    
    /**
     * @inheritdoc
     */
    public function init() {
        if (empty($this->columns)) {
            $this->columns = $this->columns();
        }
        if (!isset($this->columns['actions'])) {
            $this->columns['actions'] = ['class' => 'asdfstudio\admin\grids\ActionColumn'];
        }
        parent::init();
    }

    /**
     * List of grid columns
     *
     * @return array
     */
    public function columns() {
        $columns = [];
        $models  = $this->dataProvider->getModels();
        $model   = reset($models);
        if (is_array($model) || is_object($model)) {
            foreach ($model as $name => $value) {
                $columns[] = $name;
            }
        }
        return $columns;
    }
    
    /**
     * Renders the pager.
     * @return string the rendering result
     */
    public function renderPager()
    {
        $pagination = $this->dataProvider->getPagination();
        if ($pagination === false || $this->dataProvider->getCount() <= 0) {
            return '';
        }
        /* @var $class LinkPager */
        $pager = $this->pager;
        $class = ArrayHelper::remove($pager, 'class', LinkPager::className());
        $pager['pagination'] = $pagination;
        $pager['view'] = $this->getView();
        $pager['options'] = ['class' => 'pagination pull-right'];
        return $class::widget($pager);
    }
    
    public function configureShowFields(\asdfstudio\admin\models\mapper\ShowMapper $show) {
        
    }
}
