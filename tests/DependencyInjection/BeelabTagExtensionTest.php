<?php

declare(strict_types=1);

namespace Beelab\TagBundle\Tests\DependencyInjection;

use Beelab\TagBundle\DependencyInjection\BeelabTagExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class BeelabTagExtensionTest extends TestCase
{
    public function testLoadSetParameters(): void
    {
        /** @var ContainerBuilder&\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerBuilder::class);
        /** @var ParameterBag&\PHPUnit\Framework\MockObject\MockObject $parameterBag */
        $parameterBag = $this->createMock(ParameterBag::class);

        $parameterBag->method('add');

        $container->method('getParameterBag')->willReturn($parameterBag);

        $extension = new BeelabTagExtension();
        $configs = [
            ['tag_class' => 'foo'],
            ['purge' => false],
        ];
        $extension->load($configs, $container);
        $this->assertTrue(true);
    }
}
