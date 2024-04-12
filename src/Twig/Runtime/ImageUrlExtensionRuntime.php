<?php

namespace App\Twig\Runtime;

use App\Service\TrickService;
use App\Service\UserService;
use Twig\Extension\RuntimeExtensionInterface;

class ImageUrlExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private TrickService $trickService,
        private UserService $userService,
    ) {
        // Inject dependencies if needed
    }

    /**
     * @param string $library   Name of the picture library.
     * @param mixed  $attribute Either the trick's picture filename or the user object.
     * @param string $size      Optional. The desired image size. See ImageResizer sizes. Default = 'original'.
     * 
     * @return null|string The full path of the image file, or null if the file is not found.
     */
    public function getUrl(string $library, mixed $attribute, string $size = 'original'): ?string
    {
        $libraries = ['tricks', 'users'];

        if (!in_array($library, $libraries)) {
            $erreorMessage = "The library must be one of the following: " . join(', ', $libraries) . ". Got '$library'";
            throw new \LogicException($erreorMessage);
        }

        // Check if the file exists
        $fullpath = match ($library) {
            'tricks' => $this->trickService->getTrickPictureFilename((string) $attribute, $size),
            'users' => $this->userService->getProfilePictureFilename($attribute, $size),
        };

        if (!is_file((string) $fullpath)) {
            return null;
        }

        $url = preg_replace('#.*/public#', '', $fullpath);

        return $url;
    }
}
