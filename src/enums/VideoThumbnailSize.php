<?php

namespace appmanschap\youtubeplaylistimporter\enums;

enum VideoThumbnailSize: string
{
    case DEFAULT = 'default';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case STANDARD = 'standard';
    case MAXRES = 'maxres';

    /**
     * Get the first available size possible.
     *
     * @param string $size
     * @return string
     */
    public function firstAvailableSize(string $size): string
    {
        $thumbnailSizes = array_column(VideoThumbnailSize::cases(), 'value');
        $requestedSizeKey = array_search($size, $thumbnailSizes);
        $possibleSizeKey = array_search($this->value, $thumbnailSizes);

        if (!$requestedSizeKey || !$possibleSizeKey) {
            return $this->value;
        }

        if ($requestedSizeKey <= $possibleSizeKey) {
            return $thumbnailSizes[$requestedSizeKey];
        }

        return $this->value;
    }
}
