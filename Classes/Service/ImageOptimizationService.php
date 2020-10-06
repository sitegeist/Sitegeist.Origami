<?php
namespace Sitegeist\Origami\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\CompilingEvaluator;
use Neos\Eel\Utility;
use Flowpack\JobQueue\Common\Annotations as Job;
use Psr\Log\LoggerInterface;
use Neos\Flow\Log\Utility\LogEnvironment;

/**
 * @Flow\Scope("singleton")
 */
class ImageOptimizationService
{
    /**
     * @var LoggerInterface
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
                $this->systemLogger->error($commandEvaluated, array_merge(LogEnvironment::fromMethodName(__METHOD__), $output));
            } else {
                $this->systemLogger->info($commandEvaluated, array_merge(LogEnvironment::fromMethodName(__METHOD__), $output));
            }
        } else {
            $this->systemLogger->error(sprintf('Could not optimize image %s of type %s because missing or disabled configuration', $file, $imageType));
        }
    }

}
