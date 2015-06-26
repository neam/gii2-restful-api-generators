<?php

namespace neam\gii2_restful_api_generators\yii1_rest_model;

use Yii;
use yii\gii\CodeFile;
use yii\helpers\Inflector;

class Generator extends \neam\gii2_dna_project_base_generators\yii1_model\Generator
{

    public $modelItemTypes = [];
    public $modelPath = '@app/models';

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Yii RESTful API Model Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates an ActiveRecord class and base class for the specified database table.';
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['rest-api-model.php', 'rest-api-model-extended.php'];
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];
        foreach ($this->modelItemTypes as $table => $modelClass) {


            $model = new $modelClass;

            $params = [
                'model' => $model,
                'ns' => $this->ns,
            ];

            $files[] = new CodeFile(
                Yii::getAlias($this->modelPath) . '/base/BaseRestApi' . $modelClass . '.php',
                $this->render('rest-api-model.php', $params)
            );

            $modelClassFile = Yii::getAlias($this->modelPath) . '/RestApi' . $modelClass . '.php';
            if ($this->generateModelClass || !is_file($modelClassFile)) {
                $files[] = new CodeFile(
                    $modelClassFile,
                    $this->render('rest-api-model-extended.php', $params)
                );
            }

        }
        return $files;
    }

}
