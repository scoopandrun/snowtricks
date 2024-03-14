<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;
use nadar\quill\Lexer;

class QuillExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // Inject dependencies if needed
    }

    public function quillFilter(string $delta): string
    {
        // Check that delta is a valid JSON string
        json_decode($delta);
        $deltaIsValidJson = json_last_error() === JSON_ERROR_NONE;

        if ($deltaIsValidJson) {
            return (new Lexer($delta))->render();
        } else {
            // Fallback, display the content with HTML characters converted
            return htmlspecialchars($delta);
        }
    }
}
