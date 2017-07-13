#Sitegeist.Origami
### Asynchronous optimization of images for Flow and Neos with a neos-jobqueue. 

This package is based on MOC.ImageOptimizer https://packagist.org/packages/moc/imageoptimizer)

### Authors & Sponsors

* Martin Ficzel - ficzel@sitegeist.de

*The development and the public-releases of this package is generously sponsored by our employer http://www.sitegeist.de.*

## Introduction

Neos CMS / Flow framework package that optimizes generated thumbnail images (jpg, png, gif, svg and more) for web presentation.
The original files of the editors are never affected since copies are always created for thumbnails.

The optimization is executed asynchronously by a jobrunner and not during page-creation. The image is imediately 
available in unoptimzed fashion. After optimization the new image-file will be served without changing the image-url.   

By default this package is using `jpegtran`, `optipng`, `gifsicle` and `svgo` but the exact command for each format 
can be configured via settings.

Should work with Linux, FreeBSD, OSX, Compatible with Neos 3.x+ / 4.x+

## Installation

Sitegeist.Origami is available via packagist. Just add "sitegeist/origami" : "~1.0" to the require section of the 
composer.json or run `composer require sitegeist/origami`. We use semantic-versioning so every breaking change 
will increase the major-version number.

### Image-Optimization Tools

Ensure the image manipulation libraries `jpegtran` (JPG), `optipng` (PNG), `gifsicle` (GIF) and `svgo` (SVG) are 
available on the server.

You can install the libraries globally using `npm`:

```
npm install -g jpegtran-bin optipng-bin gifsicle svgo
```

### Job-Queue

To actually optimize the images the imageOptimization-jobqeue has to be initialized and executed.

```
# This has to be done once on every server.
./flow queue:setup imageOptimization

# This is actually executing the optimization tasks. It should be run in intervals. 
# It depends on the target wether it should run forever, for a given interval or a given number of jobs.
./flow job:work imageOptimization
```

## Configuration

Using the `Settings` configuration, multiple options can be adjusted.

Each optimization for a media-format has to be enabled exlicitly since by default
all optimizations are disabled.

```
Sitegeist:
  Origami:
    formats:
      'image/jpeg':
        enabled: true

      'image/png':
        enabled: true
        
      'image/gif':
        enabled: true

      'image/svg+xml':
        enabled: true
```

You can replace the preconfigured optimization commands via settings.

```
Sitegeist:
  Origami:
    formats:
      'image/jpeg':
        command: "${'jpegoptim --strip-all --max=80 --all-progressive -o ' + file}"
```

When doing this you have to take care that you provide the necessary command on the target system.

## Usage

* Clear thumbnails to generate new ones that will automatically be optimized.

`./flow media:clearthumbnails`

* See system log for debugging and error output.
