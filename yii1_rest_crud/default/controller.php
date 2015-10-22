<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

$model = $generator->getModel();

$modelClassSingular = get_class($model);
$modelClassSingularWords = Inflector::camel2words($modelClassSingular);
$modelClassPluralWords = Inflector::pluralize($modelClassSingularWords);
$modelClassPlural = Inflector::camelize($modelClassPluralWords);

$dnaModelClassSingular = str_replace("RestApi", "", $modelClassSingular);

echo "<?php\n";
?>

class <?=$generator->controllerClass?> extends <?=$generator->baseControllerClass."\n"?>
{

    protected $_modelName = "<?=$generator->modelClass?>"; //model to be used as resource

    public function actions() //determine which of the standard actions will support the controller
    {
        return array(
            'list' => array( //use for get list of objects
                'class' => 'WRestListAction',
                'filterBy' => array(
                    //this param is used in `where` expression when forming an db query
                    // 'name_in_table' => '<?= $dnaModelClassSingular ?>_request_param_name'
                    'foo' => 'bar',
                ),
                'limit' => '<?= $dnaModelClassSingular ?>_limit', //request parameter name, which will contain limit of object
                'page' => '<?= $dnaModelClassSingular ?>_page', //request parameter name, which will contain requested page num
                'order' => '<?= $dnaModelClassSingular ?>_order', //request parameter name, which will contain ordering for sort
            ),
            'delete' => 'WRestDeleteAction',
            'get' => 'WRestGetAction',
            'create' => 'WRestCreateAction', //provide 'scenario' param
            'update' => array(
                'class' => 'WRestUpdateAction',
                'scenario' => 'update', //as well as in WRestCreateAction optional param
            )
        );
    }

<?php
    /*
    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array_merge($this->itemAccessRules(), array(
            array(
                'allow',
                'actions' => array(
                    'customAction', // placeholder - rename when/if you add the first custom action
                ),
                'users' => array('@'),
            ),
            array(
                'deny',
                'users' => array('*'),
            ),
        ));
    }
    * /

    /**
     * @param int $id the model id.
     * @return <?=$generator->modelClass."\n"?>
     * @throws CHttpException
     * /
    /*
    public function loadModel($id)
    {
        $model = <?=$generator->modelClass?>::model()->findByPk($id);
        if ($model === null) {
            throw new CHttpException(404, Yii::t('model', 'The requested page does not exist.'));
        }
        return $model;
    }
    * /

    /**
     * TODO
     * /
    /*
    public function actionValidation($model)
    {
        if (isset($_POST['ajax']) && ($_POST['ajax'] === '<?=Inflector::camel2id($generator->modelClass)?>-form' || $_POST['ajax'] === 'item-form')) {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
    */
?>
}
