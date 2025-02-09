<?php

declare(strict_types=1);

namespace RedditImage\Tests\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;

class htmlHasVideoWithAudio extends Constraint {
    private string $format;
    private string $videoUrl;
    private string $audioUrl;

    public function __construct(string $format, string $videoUrl, string $audioUrl) {
        $this->format = $format;
        $this->videoUrl = $videoUrl;
        $this->audioUrl = $audioUrl;
    }

    public function matches($other): bool {
        if (!is_string($other)) {
            return false;
        }

        $dom = new \DomDocument('1.0', 'UTF-8');
        if ($dom->loadHTML($other, LIBXML_NOERROR) === false) {
            return false;
        }

        $xpath = new \DOMXpath($dom);
        $videos = $xpath->query("body/div/video[@class='reddit-image'][@controls='true'][@preload='metadata']/source[@src='{$this->videoUrl}'][@type='video/{$this->format}']");

        if ($videos->length !== 1) {
            return false;
        }

        $audios = $xpath->query("body/div/video/audio[@controls='true']/source[@src='{$this->audioUrl}']");

        if ($audios->length !== 1) {
            return false;
        }

        return true;
    }

    public function toString(): string {
        return "has the {$this->format} video with {$this->videoUrl} source and {$this->audioUrl} audio";
    }
}
