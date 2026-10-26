<?php

namespace Tests\Feature\Assignment;

use App\Domains\Assignments\Services\AssignmentGroupUnitParser;
use PHPUnit\Framework\TestCase;

class AssignmentGroupUnitParserTest extends TestCase
{
    private AssignmentGroupUnitParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new AssignmentGroupUnitParser();
    }

    public function test_it_parses_valid_group_names(): void
    {
        $cases = [
            'Kelompok 1 Unit 1' => 1,
            'Kelompok 02 Unit 02' => 2,
            'kelompok 3 unit 03' => 3,
            'KELOMPOK 10 UNIT 1' => 1,
            'Kelompok 12 Unit 2' => 2,
        ];

        foreach ($cases as $groupName => $expectedUnit) {
            $this->assertSame(
                $expectedUnit,
                $this->parser->parse($groupName),
                "Gagal memetakan: {$groupName}",
            );
        }
    }

    public function test_it_returns_null_for_invalid_group_names(): void
    {
        $cases = [
            'Kelompok Unit 1',
            'Kelompok 1',
            'Kelompok 1 Unit 4',
            'Kelompok 1 Unit 0',
            'Kelompok A Unit 1',
            'Nama Kelompok Tidak Valid',
        ];

        foreach ($cases as $groupName) {
            $this->assertNull(
                $this->parser->parse($groupName),
                "Nama kelompok seharusnya tidak valid: {$groupName}",
            );
        }
    }

    public function test_it_generates_unit_labels(): void
    {
        $this->assertSame(
            'Unit 1',
            $this->parser->label('Kelompok 1 Unit 1'),
        );

        $this->assertSame(
            'Unit 2',
            $this->parser->label('Kelompok 2 Unit 2'),
        );

        $this->assertSame(
            'Unit 3',
            $this->parser->label('Kelompok 3 Unit 3'),
        );
    }
}
