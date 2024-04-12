<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageManager
{
    /**
     * Height: 100px.
     */
    public const SIZE_THUMBNAIL = 'thumbnail';

    /**
     * Height: 300px.
     */
    public const SIZE_SMALL = 'small';

    /**
     * Height: 600px.
     */
    public const SIZE_MEDIUM = 'medium';

    /**
     * Height: 1200px.
     */
    public const SIZE_LARGE = 'large';

    /**
     * All sizes:
     * - thumbnail (height: 100px)
     * - small (height: 300px)
     * - medium (height: 600px)
     * - large (height: 1200px)
     */
    public const SIZE_ALL = [
        self::SIZE_THUMBNAIL,
        self::SIZE_SMALL,
        self::SIZE_MEDIUM,
        self::SIZE_LARGE,
    ];

    private const SIZES = [
        [
            'name' => self::SIZE_THUMBNAIL,
            'height' => 100,
        ],
        [
            'name' => self::SIZE_SMALL,
            'height' => 300,
        ],
        [
            'name' => self::SIZE_MEDIUM,
            'height' => 600,
        ],
        [
            'name' => self::SIZE_LARGE,
            'height' => 1200,
        ],
    ];

    public function __construct(
        private FileManager $fileManager,
    ) {
    }

    /**
     * Save an image in different sizes.
     * 
     * @param UploadedFile|string $image 
     * @param string              $uploadDirectory 
     * @param null|string         $filename 
     * @param array               $sizes 
     * @param bool                $unique 
     * 
     * @return string Filename of the saves image.
     */
    public function saveImage(
        UploadedFile|string $image,
        string $uploadDirectory,
        ?string $filename = null,
        array $sizes = [],
        bool $unique = false,
    ): string {
        if ($image instanceof UploadedFile) {
            $fileContent = $image->getContent();

            // Save original image
            $filename = $this->fileManager->saveUploadedFile($image, $uploadDirectory . '/original', $filename, $unique);
        }

        if (is_string($image)) {
            $fileContent = $image;

            // Save original image
            $filename = $this->fileManager->saveRawFile($image, $uploadDirectory . '/original', $filename, $unique);
        }

        // Save resized images
        foreach ($this->resize($fileContent, $sizes) as $resizedImage) {
            $this->fileManager->saveGdImageToWebp(
                $resizedImage['image'],
                $uploadDirectory . '/' . $resizedImage['size'],
                $filename
            );
        }

        return $filename;
    }

    /**
     * Delete an image and all its sized copies.
     * 
     * @param mixed $image 
     */
    public function deleteImage(string $directory, string $filename): void
    {
        $sizes = array_merge(self::SIZE_ALL, ['original']);

        foreach ($sizes as $size) {
            $this->fileManager->delete(
                directory: $directory . '/' . $size,
                filename: $filename
            );
        }
    }

    /**
     * Resize an image to the given sizes.
     * 
     * @param File  $image Image to be resized.
     * @param array $sizes Target sizes. Available sizes: thumbnail, small, medium, large.
     * 
     * @return \Generator<array>
     */
    public function resize(string $imageContent, array $sizes = []): \Generator
    {
        $gdImage = imagecreatefromstring($imageContent);

        if (!$gdImage) {
            return;
        }

        [$width, $height] = getimagesizefromstring($imageContent);

        foreach (self::SIZES as $size) {
            if (in_array($size['name'], $sizes)) {
                $percent = min($size['height'] / $height, 1);
                $newWidth = (int) ($width * $percent);
                $resizedImage = imagescale($gdImage, $newWidth);

                yield [
                    'size' => $size['name'],
                    'image' => $resizedImage,
                ];
            }
        }
    }
}
