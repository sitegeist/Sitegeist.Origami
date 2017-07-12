<?php
namespace Sitegeist\Origami\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\CompilingEvaluator;
use Neos\Eel\Utility;
use Neos\Flow\Log\SystemLoggerInterface;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Flow\ResourceManagement\ResourceManager;
use Flowpack\JobQueue\Common\Annotations as Job;

/**
 * @Flow\Scope("singleton")
 */
class ImageOptimizationService
{
    /**
     * @var SystemLoggerInterface
     * @Flow\Inject
     */
    protected $systemLogger;

    /**
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var CompilingEvaluator
     */
    protected $eelEvaluator;

    /**
     * @var array
     * @Flow\InjectConfiguration()
     */
    protected $settings;

    /**
     *
     * @return Result
     * @Job\Defer(queueName="imageOptimization")
     */
    public function optimizeImage ($file , $imageType) {
        $useGlobalBinary = $this->settings['useGlobalBinary'];
        $binaryRootPath = 'Private/Library/node_modules/';

        $librarySettings = $this->settings['formats'][$imageType];

        if ($librarySettings['enabled'] === false) {
            return;
        }

        if ($librarySettings['useGlobalBinary'] === true) {
            $useGlobalBinary = true;
        }

        $library = $librarySettings['library'];
        $binaryPath = $librarySettings['binaryPath'];
        $eelExpression = $librarySettings['arguments'];
        $parameters = array_merge($librarySettings['parameters'], ['file' => $file]);
        $arguments = Utility::evaluateEelExpression($eelExpression, $this->eelEvaluator, $parameters);

        $binaryPath = $useGlobalBinary === true ? $this->settings['globalBinaryPath'] . $library : $this->packageManager->getPackage('Sitegeist.Origami')->getResourcesPath() . $binaryRootPath . $binaryPath;
        $cmd = escapeshellcmd($binaryPath) . ' ' . $arguments;
        $output = [];
        exec($cmd, $output, $result);
        $failed = (int)$result !== 0;

        if ($failed) {
            $this->systemLogger->log($cmd, LOG_ERR, $output);
        } else {
            $this->systemLogger->log($cmd, LOG_INFO, $output);
        }
    }

}
