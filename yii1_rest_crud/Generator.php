<?php

namespace neam\gii2_restful_api_generators\yii1_rest_crud;

use Yii;
use yii\gii\CodeFile;
use neam\gii2_restful_api_generators\yii1_rest_crud\providers\CallbackProvider;
//use neam\gii2_restful_api_generators\yii1_rest_crud\providers\DateTimeProvider;
//use neam\gii2_restful_api_generators\yii1_rest_crud\providers\EditorProvider;
//use neam\gii2_restful_api_generators\yii1_rest_crud\providers\RelationProvider;
use yii\helpers\Json;

/**
 * Yii Workflow UI Generator.
 * @author Fredrik Wollsén <fredrik@neam.se>
 * @since 1.0
 */
class Generator extends \neam\gii2_workflow_ui_generators\yii1_crud\Generator
{

    public $baseControllerClass = 'Controller';

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Yii RESTful API CRUD Controller Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates REST-API endpoints that implement CRUD (Create, Read, Update, Delete)
            operations for the specified data model';
    }

    static public function getCoreProviders()
    {
        return [
            CallbackProvider::className(),
            //DateTimeProvider::className(),
            //EditorProvider::className(),
            //RelationProvider::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['controller.php', 'item-rest-api-requests-blueprint.md.php'];
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];

        // Controller
        $controllerPath = $this->getControllerPath();
        $controllerFile = $controllerPath . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.php';
        $files[] = new CodeFile($controllerFile, $this->render('controller.php'));

        // Controller API blueprint
        $controllerPath = $this->getBlueprintsPath();
        $controllerFile = $controllerPath . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.md';
        $files[] = new CodeFile($controllerFile, $this->render('item-rest-api-requests-blueprint.md.php'));

        /*
        // View path
        $viewPath = $this->getViewPath();

        // Edit workflow actions
        // TODO
        foreach ($this->getModel()->flowSteps() as $step => $attributes) {
            $stepViewPath = $viewPath . '/steps/' . $step . ".php";
            $this->getModel()->scenario = "edit-step";
            $files[] = new CodeFile($stepViewPath, $this->render('edit-step.php', compact("step", "attributes")));
        }

        // Translate workflow actions
        // TODO
        foreach ($this->getModel()->flowSteps() as $step => $attributes) {

            $translatableAttributes = $this->getModel()->matchingTranslatable($attributes);

            if (empty($translatableAttributes)) {
                continue;
            }

            $stepViewPath = $viewPath . '/translate/steps/' . $step . ".php";
            $this->getModel()->scenario = "translate-step";
            $files[] = new CodeFile($stepViewPath, $this->render('translate-step.php', compact("step", "translatableAttributes")));
        }

        // Other actions
        // TODO
        $templatePath = $this->getTemplatePath() . '/views';
        foreach (scandir($templatePath) as $file) {
            if (is_file($templatePath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $files[] = new CodeFile("$viewPath/$file", $this->render("views/$file"));
            }
        }
        */

        return $files;
    }

    public function getBlueprintsPath()
    {
        return \Yii::getAlias(str_replace('views', 'blueprints', $this->viewPath)) . '/';
    }

    /**
     * Alter validation rules to not require model class extending from an ActiveRecord class
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();

        // Alter the rule that restricts model classes to yii2 active records so that we can use yii 1 active records
        foreach ($rules as $k=>&$rule) {
            if ($rule[0][0] === "modelClass" && $rule[1] === "validateClass") {
                unset($rules[$k]);
            }
        }

        return $rules;
    }

    /**
     * Skip checks if yii 1 model class is valid
     */
    public function validateModelClass()
    {
    }

    /**
     * Get model
     */
    public function getModel()
    {
        /* @var $class CActiveRecord */
        $class = str_replace("RestApi", "", $this->modelClass);
        return $class::model();
    }


}
