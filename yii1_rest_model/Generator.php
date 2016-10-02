<?php

namespace neam\gii2_restful_api_generators\yii1_rest_model;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
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

        $exceptions = [];

        foreach ($this->modelItemTypes as $table => $modelClass) {

            try {

                $model = $this->getModel($modelClass);

                $params = [
                    'model' => $model,
                    'ns' => $this->ns,
                    'itemTypeAttributes' => $this->getItemTypeAttributes(
                        $model
                    ),
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

            } catch (\Exception $e) {
                $exceptions[] = $e;
            }

        }

        // Print information about exceptions that has occurred
        if (!empty($exceptions)) {
            $summary = "";
            foreach ($exceptions as $exception) {
                /** @var \Exception $exception */
                $summary .= "\n------\n{" . get_class($exception) . "} " . $exception->getMessage(
                    ) . " [" . $exception->getFile() . ", line " . $exception->getLine(
                    ) . "]\n\nTrace: \n\n" . $exception->getTraceAsString() . "\n\n";
            }
            throw new \Exception("Exceptions occurred during generation: \n$summary");
        }

        return $files;
    }

    /**
     * Get item type attributes with additional metadata required during generation
     * TODO: Do not keep copy-pasted copies here and in angular_crud/Generator
     */
    public function getItemTypeAttributes($model)
    {
        $modelClass = str_replace('propel\\models\\', '', get_class($model));
        if (!method_exists($model, 'itemTypeAttributes')) {
            throw new \Exception("Model $modelClass does not have method itemTypeAttributes()");
        }
        $itemTypeAttributes = $model->itemTypeAttributes();
        foreach ($itemTypeAttributes as $attribute => &$attributeInfo) {

            // Do not decorate deep attributes with relation information yet - they are decorated on a needs basis further down
            if (strpos($attribute, '/') !== false) {
                continue;
            }

            // Decorate with relation information
            $this->decorateRelationInfo($modelClass, $attribute, $attributeInfo);

        }
        foreach ($itemTypeAttributes as $attribute => &$attributeInfo) {
            // Decorate with additional information about nested attributes
            if (strpos($attribute, '/') !== false) {
                $_ = explode('/', $attribute);
                $throughAttribute = $_[0];
                $deepAttribute = $_[1];
                // Nest deep attribute information
                $attributeInfo['throughAttribute'] = $itemTypeAttributes[$throughAttribute];
                $relatedModelClass = $attributeInfo['throughAttribute']['relatedModelClass'];
                $this->decorateRelationInfo($relatedModelClass, $deepAttribute, $attributeInfo);
                $itemTypeAttributes[$throughAttribute]['deepAttributes'][$deepAttribute] = $attributeInfo;
                continue;
            }
        }
        return $itemTypeAttributes;
    }

    public function decorateRelationInfo($modelClass, $attribute, &$attributeInfo)
    {

        $tableMapClass = "\\propel\\models\\Map\\{$modelClass}TableMap";
        if (!class_exists($tableMapClass)) {
            throw new \Exception(
                "Propel object model classes seem to be missing for model class $modelClass - specifically $tableMapClass does not exist"
            );
        }
        /** @var \Propel\Runtime\Map\TableMap $tableMap */
        $tableMap = $tableMapClass::getTableMap();

        try {

            $relations = [];

            switch ($attributeInfo['type']) {
                case "has-many-relation":
                case "many-many-relation":
                case "belongs-to-relation":

                    foreach ($tableMap->getRelations() as $relation) {
                        if ($relation->getType() === \Propel\Runtime\Map\RelationMap::ONE_TO_MANY) {
                            $relations[] = $relation->getName();
                        }
                    }

                    /** @var \Propel\Runtime\Map\RelationMap $relationInfo */
                    $relationInfo = null;
                    if (!empty($attributeInfo['db_column'])) {
                        // Method 1 - Use db_column information

                        if (strpos($attributeInfo['db_column'], ".") === false) {
                            throw new \Exception($attributeInfo['type']. " db_column needs to contain a dot that separates the related table with the relation attribute");
                        }

                        $_ = explode(".", $attributeInfo['db_column']);
                        $relatedTable = $_[0];
                        $relatedColumn = $_[1];

                        $relations = $tableMap->getRelations();
                        $relationInfo = null;
                        foreach ($relations as $candidateRelation) {
                            $columnMappings = $candidateRelation->getColumnMappings();
                            if (array_key_exists($attributeInfo['db_column'], $columnMappings)) {
                                $relationInfo = $candidateRelation;
                                break;
                            }
                        }
                    } else {
                        // Method 2 - Guess based on attribute name
                        $_ = explode("RelatedBy", $attribute);
                        $relatedModelClass = Inflector::singularize(ucfirst($_[0]));
                        if (in_array($relatedModelClass, $relations)) {
                            $relationName = $relatedModelClass;
                        } elseif (isset($_[1]) && in_array($relatedModelClass . "RelatedBy" . $_[1], $relations)) {
                            $relationName = $relatedModelClass . "RelatedBy" . $_[1];
                        } else {
                            $relationName = $attribute;
                        }
                        $relationInfo = $tableMap->getRelation($relationName);

                    }

                    $attributeInfo['relatedModelClass'] = $relationInfo->getForeignTable()->getPhpName();
                    $attributeInfo['relatedItemGetterMethod'] = "get" . $relationInfo->getName();
                    $attributeInfo['relatedItemSetterMethod'] = "set" . $relationInfo->getName();

                    break;
                case "has-one-relation":

                    foreach ($tableMap->getRelations() as $relation) {
                        if ($relation->getType() === \Propel\Runtime\Map\RelationMap::MANY_TO_ONE) {
                            $relations[] = $relation->getName();
                        }
                    }

                    /** @var \Propel\Runtime\Map\RelationMap $relationInfo */
                    $relationInfo = null;
                    if (!empty($attributeInfo['db_column'])) {
                        // Method 1 - Use db_column information
                        $column = $tableMap->getColumn($attributeInfo['db_column']);
                        $relationInfo = $column->getRelation();
                    } else {
                        // Method 2 - Guess based on attribute name
                        $relationName = ucfirst($attribute);
                        $relationInfo = $tableMap->getRelation($relationName);
                    }

                    /** @var \Propel\Runtime\Map\ColumnMap $localColumn */
                    $localColumns = $relationInfo->getLocalColumns();
                    $localColumn = array_shift($localColumns);
                    $attributeInfo['relatedModelClass'] = $relationInfo->getForeignTable()->getPhpName();
                    $attributeInfo['fkAttribute'] = $localColumn->getName();
                    $attributeInfo['relatedItemGetterMethod'] = "get" . $relationInfo->getName();
                    $attributeInfo['relatedItemSetterMethod'] = "set" . $relationInfo->getName();

                    break;
                case "ordinary":
                case "primary-key":
                    break;
                default:
                    // ignore
                    break;
            }

        } catch (\Propel\Runtime\Map\Exception\RelationNotFoundException $e) {
            throw new \Exception(
                "Could not find {$attributeInfo['type']} relation information for $modelClass->$attribute: " . $e->getMessage(
                ) . "\nAvailable relations for {$tableMap->getPhpName()}: \n - " . implode("\n - ", $relations)
                . (empty($attributeInfo['db_column']) ? "\n\nHint: By setting the db_column property in the item type attribute metadata, the relation information can be determined without guessing" : "")
            );
        } catch (\Propel\Runtime\Map\Exception\ColumnNotFoundException $e) {
            throw new \Exception(
                "Could not find {$attributeInfo['type']} relation information for $modelClass->$attribute due to a column not found exception: " . $e->getMessage(
                ) . "\nAvailable relations for {$tableMap->getPhpName()}: \n - " . implode("\n - ", $relations)
                . (empty($attributeInfo['db_column']) ? "\n\nHint: Make sure that the db_column property in the item type attribute metadata points to an existing column" : "")
            );
        }

    }

    /**
     * Get propel model
     */
    public function getModel($modelClass)
    {
        /* @var $class ActiveRecordInterface */
        $class = '\\propel\\models\\' . $modelClass;
        return new $class();
    }

}
