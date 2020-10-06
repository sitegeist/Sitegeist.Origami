<?php
namespace Sitegeist\Origami\Aspects;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Sitegeist\Origami\Service\ImageOptimizationService;

/**
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class ThumbnailAspect
{
    /**
     * @var array
     * @Flow\InjectConfiguration
     */
    protected $settings;

    /**
     * @var ImageOptimizationService
     * @Flow\Inject
     */
    protected $imageOptimizationService;

    /**
     * After a thumbnail has been refreshed the resource is optimized, meaning the
     * image is only optimized once when created.
     *
     * A new resource is generated for every thumbnail, meaning the original is
     * never touched.
     *
     * Only local file system target is supported to keep it from being blocking.
     * It would however be possible to create a local copy of the resource,
     * process it, import it and set that as the thumbnail resource.
     *
     * @Flow\AfterReturning("method(Neos\Media\Domain\Model\Thumbnail->refresh())")
     * @param \Neos\Flow\Aop\JoinPointInterface $joinPoint The current join point
     * @return void
     */
    public function optimizeThumbnail(JoinPointInterface $joinPoint)
    {
        /** @var \Neos\Media\Domain\Model\Thumbnail $thumbnail */
        $thumbnail = $joinPoint->getProxy();
        $thumbnailResource = $thumbnail->getResource();
        if (!$thumbnailResource) {
            return;
        }

        $streamMetaData = stream_get_meta_data($thumbnailResource->getStream());
        $pathAndFilename = $streamMetaData['uri'];

        $file = escapeshellarg($pathAndFilename);
        $imageType = $thumbnailResource->getMediaType();

        if (array_key_exists($imageType, $this->settings['formats'])
            && array_key_exists('enabled', $this->settings['formats'][$imageType])
            && $this->settings['formats'][$imageType]['enabled']
        ) {
            $this->imageOptimizationService->optimizeImage($file, $imageType);
        }

    }
}
