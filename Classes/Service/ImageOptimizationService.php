<?php
namespace Sitegeist\Origami\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\CompilingEvaluator;
use Neos\Eel\Utility;
use Neos\Flow\Log\SystemLoggerInterface;
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

        if (array_key_exists($imageType, $this->settings['formats'])
            && array_key_exists('enabled', $this->settings['formats'][$imageType])
            && $this->settings['formats'][$imageType]['enabled']
        ) {
            $command = $this->settings['formats'][$imageType]['command'];
            $commandEvaluated = Utility::evaluateEelExpression($command, $this->eelEvaluator, ['file' => $file]);

            $output = [];
            exec($commandEvaluated, $output, $result);
            $failed = (int)$result !== 0;

            if ($failed) {
                $this->systemLogger->log($commandEvaluated, LOG_ERR, $output);
            } else {
                $this->systemLogger->log($commandEvaluated, LOG_INFO, $output);
            }
        } else {
            $this->systemLogger->log(sprintf('Could not optimize image %s of type %s because missing or disabled configuration', $file, $imageType), LOG_ERR);
        }
    }

}
