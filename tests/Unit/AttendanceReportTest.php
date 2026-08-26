<?php

namespace Tests\Unit;

use App\Support\AttendanceReport;
use PHPUnit\Framework\TestCase;

class AttendanceReportTest extends TestCase
{
    private array $members = [
        ['id' => 1, 'name' => 'Alice', 'position' => 'Clerk'],
        ['id' => 2, 'name' => 'Bob',   'position' => 'Aide'],
    ];

    public function test_empty_rows_produce_zero_counts(): void
    {
        $out = AttendanceReport::summarize([], $this->members, 0);

        $this->assertCount(2, $out['members']);
        $this->assertSame(0, $out['members'][0]['P']);
        $this->assertSame(0, $out['members'][0]['present_rate']);
        $this->assertSame(['sessions' => 0, 'P' => 0, 'A' => 0, 'E' => 0, 'L' => 0], $out['totals']);
    }

    public function test_counts_each_status_separately(): void
    {
        $rows = [
            ['member_id' => 1, 'status' => 'P'],
            ['member_id' => 1, 'status' => 'P'],
            ['member_id' => 2, 'status' => 'P'],
            ['member_id' => 2, 'status' => 'A'],
            ['member_id' => 2, 'status' => 'E'],
            ['member_id' => 1, 'status' => 'L'],
        ];

        $out = AttendanceReport::summarize($rows, $this->members, 2);

        $alice = $out['members'][0];
        $bob   = $out['members'][1];

        $this->assertSame(['P' => 2, 'A' => 0, 'E' => 0, 'L' => 1], [
            'P' => $alice['P'], 'A' => $alice['A'], 'E' => $alice['E'], 'L' => $alice['L'],
        ]);
        $this->assertSame(['P' => 1, 'A' => 1, 'E' => 1, 'L' => 0], [
            'P' => $bob['P'], 'A' => $bob['A'], 'E' => $bob['E'], 'L' => $bob['L'],
        ]);

        // present_rate = round(P / sessionCount * 100)
        $this->assertSame(100, $alice['present_rate']); // 2/2
        $this->assertSame(50, $bob['present_rate']);     // 1/2

        $this->assertSame(['sessions' => 2, 'P' => 3, 'A' => 1, 'E' => 1, 'L' => 1], $out['totals']);
    }

    public function test_present_rate_is_zero_when_no_sessions(): void
    {
        $rows = [['member_id' => 1, 'status' => 'P']];
        $out = AttendanceReport::summarize($rows, $this->members, 0);
        $this->assertSame(0, $out['members'][0]['present_rate']);
    }
}
