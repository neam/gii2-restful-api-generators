<?php
/**
 * @link http://neamlabs.com/
 * @copyright Copyright (c) 2015 Neam AB
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace neam\gii2_restful_api_generators;

use yii\base\Application;
use yii\base\BootstrapInterface;


/**
 * Class Bootstrap
 * @package neam\workflow_ui_generators
 * @author Fredrik Wollsén <fredrik@neam.se>
 * @author Tobias Munk <tobias@diemeisterei.de>
 */
class Bootstrap implements BootstrapInterface
{

    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        if ($app->hasModule('gii')) {
            if (!isset($app->getModule('gii')->generators['yii1-rest-crud'])) {
                $app->getModule('gii')->generators['yii1-rest-crud'] = 'neam\gii2_restful_api_generators\yii1_rest_crud\Generator';
            }
            if (!isset($app->getModule('gii')->generators['yii1-rest-model'])) {
                $app->getModule('gii')->generators['yii1-rest-model'] = 'neam\gii2_restful_api_generators\yii1_rest_model\Generator';
            }
        }
    }
}