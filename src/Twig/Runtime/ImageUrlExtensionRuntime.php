<?php

namespace App\Twig\Runtime;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\RuntimeExtensionInterface;

class ImageUrlExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        #[Autowire('%app.uploads.pictures%/')]
        private readonly string $picturesUploadsDirectory,
        private UserService $userService,
    ) {
        // Inject dependencies if needed
    }

    /**
     * @param string $library Name of the picture library.
     * @param mixed $attribute Either the trick's filename or the user object.
     * 
     * @return null|string The full path of the image file, or null if the file is not found.
     */
    public function getUrl(string $library, mixed $attribute): ?string
    {
        $libraries = ['tricks', 'users'];

        if (!in_array($library, $libraries)) {
            $erreorMessage = "The library must be one of the following: " . join(', ', $libraries) . ". Got '$library'";
            throw new \LogicException($erreorMessage);
        }

        // Check if the file exists
        $fullpath = match ($library) {
            'tricks' => $this->picturesUploadsDirectory . 'tricks/' . $attribute,
            'users' => $this->userService->getProfilePictureFilename($attribute),
        };

        if (!is_file($fullpath)) {
            return null;
        }

        $url = preg_replace('#.*/public#', '', $fullpath);

        return $url;
    }
}
