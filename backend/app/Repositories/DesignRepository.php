<?php

namespace App\Repositories;

use App\Models\Design;

class DesignRepository
{
    public function paginateForUser(int $userId, int $perPage = 24)
    {
        return Design::with('template')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $attributes): Design
    {
        return Design::create($attributes);
    }

    public function markCompleted(Design $design, string $outputPath): Design
    {
        $design->update([
            'status' => 'completed',
            'output_path' => $outputPath,
        ]);

        return $design;
    }

    public function markFailed(Design $design, string $reason): Design
    {
        $design->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);

        return $design;
    }
}
