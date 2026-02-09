<?php

declare(strict_types=1);

namespace Beelab\TagBundle\Tests\Listener;

use Beelab\TagBundle\Listener\TagSubscriber;
use Beelab\TagBundle\Tests\NonTaggableStub;
use Beelab\TagBundle\Tests\TaggableStub;
use Beelab\TagBundle\Tests\TaggableStub2;
use Beelab\TagBundle\Tests\TaggableStub3;
use Beelab\TagBundle\Tests\TagStub;
use Doctrine\Common\Persistence\Mapping\MappingException as LegacyMappingException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\MappingException;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\Group('unit')]
final class TagSubscriberTest extends TestCase
{
    public function testNonexistentClass(): void
    {
        if (\class_exists('Doctrine\Common\Persistence\Mapping\MappingException')) {
            $this->expectException(LegacyMappingException::class);
        } else {
            $this->expectException(MappingException::class);
        }

        new TagSubscriber('ClassDoesNotExist');
    }

    public function testInvalidClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TagSubscriber(NonTaggableStub::class);
    }

    public function testGetSubscribedEvents(): void
    {
        $tag = $this->createStub(\Beelab\TagBundle\Tag\TagInterface::class);
        $subscriber = new TagSubscriber($tag::class);

        $this->assertContains('onFlush', $subscriber->getSubscribedEvents());
    }

    public function testOnFlush(): void
    {
        $tag = $this->createStub(\Beelab\TagBundle\Tag\TagInterface::class);
        /** @var OnFlushEventArgs&\PHPUnit\Framework\MockObject\MockObject $args */
        $args = $this->createMock(OnFlushEventArgs::class);
        $manager = $this->createMock(EntityManager::class);
        $repo = $this->createStub(\Doctrine\ORM\EntityRepository::class);
        $uow = $this->createMock(\Doctrine\ORM\UnitOfWork::class);
        $metadata = $this->createStub(ClassMetadata::class);

        $args->expects($this->once())->method('getObjectManager')->willReturn($manager);
        $manager->expects($this->once())->method('getUnitOfWork')->willReturn($uow);
        $manager->method('getRepository')->willReturn($repo);
        $manager->method('getClassMetadata')->willReturn($metadata);
        $uow
            ->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([new TaggableStub(), new NonTaggableStub()])
        ;
        $uow
            ->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([new TaggableStub2()])
        ;
        $uow->expects($this->never())->method('getScheduledEntityDeletions');

        $subscriber = new TagSubscriber($tag::class);
        $subscriber->onFlush($args);
    }

    public function testOnFlushEntityWithoutTagsUpdate(): void
    {
        $tag = $this->createStub(\Beelab\TagBundle\Tag\TagInterface::class);
        /** @var OnFlushEventArgs&\PHPUnit\Framework\MockObject\MockObject $args */
        $args = $this->createMock(OnFlushEventArgs::class);
        $manager = $this->createMock(EntityManager::class);
        $uow = $this->createMock(\Doctrine\ORM\UnitOfWork::class);
        $metadata = $this->createStub(ClassMetadata::class);

        $args->expects($this->once())->method('getObjectManager')->willReturn($manager);
        $manager->expects($this->once())->method('getUnitOfWork')->willReturn($uow);
        $manager->method('getClassMetadata')->willReturn($metadata);
        $uow
            ->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([])
        ;
        $uow
            ->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([new TaggableStub3()])
        ;
        $uow->expects($this->never())->method('getScheduledEntityDeletions');

        $subscriber = new TagSubscriber($tag::class);
        $subscriber->onFlush($args);
    }

    public function testOnFlushEntityWithoutTagsInsert(): void
    {
        $tag = $this->createStub(\Beelab\TagBundle\Tag\TagInterface::class);
        /** @var OnFlushEventArgs&\PHPUnit\Framework\MockObject\MockObject $args */
        $args = $this->createMock(OnFlushEventArgs::class);
        $manager = $this->createMock(EntityManager::class);
        $uow = $this->createMock(\Doctrine\ORM\UnitOfWork::class);
        $metadata = $this->createStub(ClassMetadata::class);

        $args->expects($this->once())->method('getObjectManager')->willReturn($manager);
        $manager->expects($this->once())->method('getUnitOfWork')->willReturn($uow);
        $manager->method('getClassMetadata')->willReturn($metadata);
        $uow
            ->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([new TaggableStub3()])
        ;
        $uow
            ->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([])
        ;
        $uow->expects($this->never())->method('getScheduledEntityDeletions');

        $subscriber = new TagSubscriber($tag::class);
        $subscriber->onFlush($args);
    }

    public function testOnFlushWithPurge(): void
    {
        $tag = new TagStub();
        /** @var OnFlushEventArgs&\PHPUnit\Framework\MockObject\MockObject $args */
        $args = $this->createMock(OnFlushEventArgs::class);
        $manager = $this->createMock(EntityManager::class);
        $uow = $this->createMock(\Doctrine\ORM\UnitOfWork::class);

        $args->expects($this->once())->method('getObjectManager')->willReturn($manager);
        $manager->expects($this->once())->method('getUnitOfWork')->willReturn($uow);
        $uow->expects($this->once())->method('getScheduledEntityInsertions')->willReturn([]);
        $uow->expects($this->once())->method('getScheduledEntityUpdates')->willReturn([]);
        $uow
            ->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([new TaggableStub()])
        ;

        $subscriber = new TagSubscriber($tag::class, true);
        $subscriber->onFlush($args);
    }

    public function testSetTags(): void
    {
        $tag = $this->createStub(\Beelab\TagBundle\Tag\TagInterface::class);
        /** @var OnFlushEventArgs&\PHPUnit\Framework\MockObject\MockObject $args */
        $args = $this->createMock(OnFlushEventArgs::class);
        $manager = $this->createMock(EntityManager::class);
        $uow = $this->createMock(\Doctrine\ORM\UnitOfWork::class);

        $args->expects($this->once())->method('getObjectManager')->willReturn($manager);
        $manager->expects($this->once())->method('getUnitOfWork')->willReturn($uow);
        // TODO create some stubs of taggable entities and non-taggable entities...
        $uow->expects($this->once())->method('getScheduledEntityInsertions')->willReturn([$tag]);
        $uow->expects($this->once())->method('getScheduledEntityUpdates')->willReturn([]);
        $uow->expects($this->once())->method('getScheduledEntityDeletions')->willReturn([]);

        $subscriber = new TagSubscriber($tag::class, true);
        $subscriber->onFlush($args);
    }
}
