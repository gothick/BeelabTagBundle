<?php

declare(strict_types=1);

namespace Beelab\TagBundle\Tests\Entity;

use Beelab\TagBundle\Tag\TagInterface;
use Beelab\TagBundle\Tests\Entity;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\Group('unit')]
final class AbstractTaggableTest extends TestCase
{
    public function testHasTag(): void
    {
        /** @var TagInterface&\PHPUnit\Framework\MockObject\MockObject $tag */
        $tag = $this->createStub(TagInterface::class);
        $entity = new Entity();
        $entity->addTag($tag);
        $this->assertTrue($entity->hasTag($tag));
    }

    public function testRemoveTag(): void
    {
        /** @var TagInterface&\PHPUnit\Framework\MockObject\MockObject $tag */
        $tag = $this->createStub(TagInterface::class);
        $entity = new Entity();
        $entity->addTag($tag);
        $entity->removeTag($tag);
        $this->assertFalse($entity->hasTag($tag));
    }

    public function testGetTags(): void
    {
        /** @var TagInterface&\PHPUnit\Framework\MockObject\MockObject $tag */
        $tag = $this->createStub(TagInterface::class);
        $entity = new Entity();
        $entity->addTag($tag);
        $this->assertCount(1, $entity->getTags());
    }

    public function testGetTagsText(): void
    {
        $entity = new Entity();
        $entity->setTagsText('foo, bar, baz');
        $this->assertSame('', $entity->getTagsText());
    }

    public function testGetTagNames(): void
    {
        $entity = new Entity();
        $this->assertSame([], $entity->getTagNames());
    }
}
