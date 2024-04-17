<?php

namespace App\Tests\Service;

use App\Service\SlugService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\String\Slugger\SluggerInterface;

class SlugServiceTest extends KernelTestCase
{
    public function testMakeSlug()
    {
        self::bootKernel([
            'debug' => false,
            'doctrine.orm.controller_resolver.auto_mapping' => true,
        ]);

        $container = static::getContainer();

        /** @var SluggerInterface */
        $slugger = $container->get(SluggerInterface::class);

        $name = 'Hello World';
        $expectedSlug = 'hello-world';

        $slugService = new SlugService($slugger);
        $actualSlug = $slugService->makeSlug($name);

        $this->assertEquals($expectedSlug, $actualSlug);
    }
}
