<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Tests;

use Websitesa\Yii2\Helpers\Bases\BaseFixture;
use Websitesa\Yii2\Helpers\Helpers\UuidHelper;
use yii\db\Connection;

class BaseFixtureTest extends TestCase
{
    public function testGeneratesUuidV7ForMissingNullAndEmptyUidValues(): void
    {
        $dataFile = tempnam(sys_get_temp_dir(), 'yii2-base-fixture-');
        if ($dataFile === false) {
            throw new \RuntimeException('Could not create temporary fixture data file.');
        }

        $data = [
            'missing'   => ['name' => 'missing'],
            'null'      => ['uid' => null],
            'empty'     => ['uid' => ''],
            'preserved' => ['uid' => 'caller-provided-uid'],
        ];

        try {
            file_put_contents($dataFile, '<?php return ' . var_export($data, true) . ';');
            $fixture = new class ([
                'tableName' => 'fixture',
                'dataFile'  => $dataFile,
                'db'        => new Connection(),
            ]) extends BaseFixture {
                /** @return array<string|int, array<string, mixed>> */
                public function getRows(): array
                {
                    return $this->getData();
                }
            };

            $rows = $fixture->getRows();

            foreach (['missing', 'null', 'empty'] as $key) {
                self::assertTrue(UuidHelper::isV7((string) $rows[$key]['uid']));
            }
            self::assertSame('caller-provided-uid', $rows['preserved']['uid']);
        } finally {
            unlink($dataFile);
        }
    }
}
