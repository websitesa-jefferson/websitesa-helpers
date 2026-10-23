<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Tests;

use Websitesa\Yii2\Helpers\Behaviors\UuidBehavior;
use Websitesa\Yii2\Helpers\Helpers\UuidHelper;
use yii\db\BaseActiveRecord;

class UuidBehaviorTest extends TestCase
{
    public function testGeneratesUuidV7BeforeInsertWhenUidIsEmpty(): void
    {
        $owner = $this->getMockBuilder(BaseActiveRecord::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttribute', 'setAttribute'])
            ->getMockForAbstractClass();
        $owner->expects(self::once())
            ->method('getAttribute')
            ->with('uid')
            ->willReturn('');
        $owner->expects(self::once())
            ->method('setAttribute')
            ->with(
                'uid',
                self::callback(static fn (mixed $value): bool => UuidHelper::isV7((string) $value))
            );

        $behavior = new UuidBehavior();
        $behavior->attach($owner);
        $owner->trigger(BaseActiveRecord::EVENT_BEFORE_INSERT);
    }

    public function testPreservesExistingUidByDefault(): void
    {
        $owner = $this->getMockBuilder(BaseActiveRecord::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttribute', 'setAttribute'])
            ->getMockForAbstractClass();
        $owner->expects(self::once())
            ->method('getAttribute')
            ->with('uid')
            ->willReturn('caller-provided-uid');
        $owner->expects(self::never())->method('setAttribute');

        $behavior = new UuidBehavior();
        $behavior->attach($owner);
        $owner->trigger(BaseActiveRecord::EVENT_BEFORE_INSERT);
    }

    public function testCanOverwriteAnExistingUidWhenConfigured(): void
    {
        $owner = $this->getMockBuilder(BaseActiveRecord::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttribute', 'setAttribute'])
            ->getMockForAbstractClass();
        $owner->expects(self::once())
            ->method('getAttribute')
            ->with('uid')
            ->willReturn('caller-provided-uid');
        $owner->expects(self::once())
            ->method('setAttribute')
            ->with(
                'uid',
                self::callback(static fn (mixed $value): bool => UuidHelper::isV7((string) $value))
            );

        $behavior = new UuidBehavior();
        $behavior->preserveNonEmptyValues = false;
        $behavior->attach($owner);
        $owner->trigger(BaseActiveRecord::EVENT_BEFORE_INSERT);
    }
}
