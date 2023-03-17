<?php

declare(strict_types=1);

use RedditImage\Transformer\DisplayTransformer;
use RedditImage\Transformer\InsertTransformer;

class RedditImageExtension extends Minz_Extension {
    private const DEFAULT_IMAGEHEIGHT = 70;
    private const DEFAULT_MUTEDVIDEO = true;
    private const DEFAULT_DISPLAYIMAGE = true;
    private const DEFAULT_DISPLAYVIDEO = true;
    private const DEFAULT_DISPLAYORIGINAL = true;
    private const DEFAULT_DISPLAYMETADATA = false;
    private const DEFAULT_DISPLAYTHUMBNAILS = false;

    private DisplayTransformer $displayTransformer;
    private InsertTransformer $insertTransformer;

    public function autoload($class_name): void {
        if (0 === strpos($class_name, 'RedditImage')) {
            $class_name = substr($class_name, 12);
            include $this->getPath() . DIRECTORY_SEPARATOR . str_replace('\\', '/', $class_name) . '.php';
        }
    }

    public function init(): void {
        spl_autoload_register([$this, 'autoload']);

        $this->registerTranslates();

        $current_user = Minz_Session::param('currentUser');
        $filename = "style.{$current_user}.css";
        $filepath = join_path($this->getPath(), 'static', $filename);

        if (file_exists($filepath)) {
            Minz_View::appendStyle($this->getFileUrl($filename, 'css'));
        }

        $this->displayTransformer = new DisplayTransformer($this->getDisplayImage(), $this->getDisplayVideo(), $this->getMutedVideo(), $this->getDisplayOriginal(), $this->getDisplayMetadata(), $this->getDisplayThumbnails());
        $this->insertTransformer = new InsertTransformer($this->getImgurClientId());

        $this->registerHook('entry_before_display', [$this->displayTransformer, 'transform']);
        $this->registerHook('entry_before_insert', [$this->insertTransformer, 'transform']);
    }

    public function handleConfigureAction(): void {
        $this->registerTranslates();

        $current_user = Minz_Session::param('currentUser');

        if (Minz_Request::isPost()) {
            $configuration = [
                'imageHeight' => (int) Minz_Request::param('image-height', static::DEFAULT_IMAGEHEIGHT),
                'mutedVideo' => (bool) Minz_Request::param('muted-video'),
                'displayImage' => (bool) Minz_Request::param('display-image'),
                'displayVideo' => (bool) Minz_Request::param('display-video'),
                'displayOriginal' => (bool) Minz_Request::param('display-original'),
                'displayMetadata' => (bool) Minz_Request::param('display-metadata'),
                'displayThumbnails' => (bool) Minz_Request::param('display-thumbnails'),
                'imgurClientId' => Minz_Request::param('imgur-client-id'),
            ];
            $this->setUserConfiguration($configuration);
            file_put_contents(
                join_path($this->getPath(), 'static', "style.{$current_user}.css"),
                "img.reddit-image, video.reddit-image {max-height:{$configuration['imageHeight']}vh;}",
            );
        }
    }

    public function getImageHeight(): int {
        return $this->getUserConfigurationValue('imageHeight', static::DEFAULT_IMAGEHEIGHT);
    }

    public function getMutedVideo(): bool {
        return $this->getUserConfigurationValue('mutedVideo', static::DEFAULT_MUTEDVIDEO);
    }

    public function getDisplayImage(): bool {
        return $this->getUserConfigurationValue('displayImage', static::DEFAULT_DISPLAYIMAGE);
    }

    public function getDisplayVideo(): bool {
        return $this->getUserConfigurationValue('displayVideo', static::DEFAULT_DISPLAYVIDEO);
    }

    public function getDisplayOriginal(): bool {
        return $this->getUserConfigurationValue('displayOriginal', static::DEFAULT_DISPLAYORIGINAL);
    }

    public function getDisplayMetadata(): bool {
        return $this->getUserConfigurationValue('displayMetadata', static::DEFAULT_DISPLAYMETADATA);
    }

    public function getDisplayThumbnails(): bool {
        return $this->getUserConfigurationValue('displayThumbnails', static::DEFAULT_DISPLAYTHUMBNAILS);
    }

    public function getImgurClientId(): string {
        return $this->getUserConfigurationValue('imgurClientId', '');
    }
}
