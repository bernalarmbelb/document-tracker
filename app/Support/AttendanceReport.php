<?php

namespace App\Support;

class AttendanceReport
{
    /**
     * @param array $rows    list of ['member_id'=>int, 'status'=>'P'|'A'|'E'|'L']
     * @param array $members list of ['id'=>int, 'name'=>string, 'position'=>string]
     * @param int   $sessionCount total sessions in range (present-rate denominator)
     * @return array{members: array, totals: array}
     */
    public static function summarize(array $rows, array $members, int $sessionCount): array
    {
        $counts = []; // member_id => ['P'=>..,'A'=>..,'E'=>..,'L'=>..]
        foreach ($members as $m) {
            $counts[$m['id']] = ['P' => 0, 'A' => 0, 'E' => 0, 'L' => 0];
        }

        $totals = ['sessions' => $sessionCount, 'P' => 0, 'A' => 0, 'E' => 0, 'L' => 0];

        foreach ($rows as $row) {
            $id = $row['member_id'];
            $status = $row['status'];
            if (!isset($counts[$id]) || !isset($counts[$id][$status])) {
                continue; // unknown member or status — ignore
            }
            $counts[$id][$status]++;
            $totals[$status]++;
        }

        $out = [];
        foreach ($members as $m) {
            $c = $counts[$m['id']];
            $out[] = [
                'id' => $m['id'],
                'name' => $m['name'],
                'position' => $m['position'],
                'P' => $c['P'],
                'A' => $c['A'],
                'E' => $c['E'],
                'L' => $c['L'],
                'present_rate' => $sessionCount > 0 ? (int) round($c['P'] / $sessionCount * 100) : 0,
            ];
        }

        return ['members' => $out, 'totals' => $totals];
    }
}
