<?php

namespace Beelab\TagBundle\Tests;

/**
 * A third stub of a Taggable class.
 */
class TaggableStub3 extends TaggableStub
{
    #[\Override]
    public function getTagNames(): array
    {
        return [];
    }
}
