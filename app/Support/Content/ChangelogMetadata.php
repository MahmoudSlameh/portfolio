<?php

namespace App\Support\Content;

use App\Enums\CareerBranch;
use App\Models\Experience;
use Illuminate\Support\Collection;

/**
 * Derives the git-flavoured metadata (branch, version, commit, message) the Changelog template
 * shows for each career entry. Explicit values entered in the panel always win.
 *
 * Versions count up per branch from the oldest role: `main` → v1.0.0, v2.0.0…; other branches
 * → `oss/1.0.0`, `freelance/1.0.0`…
 */
final class ChangelogMetadata
{
    /**
     * @param  iterable<Experience>  $experiences
     * @return array<int, array{branch: CareerBranch, version: string, commit: string, message: string}> keyed by experience id
     */
    public static function for(iterable $experiences): array
    {
        $chronological = Collection::make($experiences)
            ->sortBy([
                fn (Experience $a, Experience $b): int => $a->start_date <=> $b->start_date,
                fn (Experience $a, Experience $b): int => $a->id <=> $b->id,
            ])
            ->values();

        $counters = [];
        $metadata = [];

        foreach ($chronological as $experience) {
            $branch = $experience->resolved_branch;
            $position = $counters[$branch->value] = ($counters[$branch->value] ?? 0) + 1;

            $metadata[$experience->id] = [
                'branch' => $branch,
                'version' => $experience->version ?? self::version($branch, $position),
                'commit' => $experience->resolved_commit,
                'message' => $experience->commit_message ?? self::message($experience, $branch, $position),
            ];
        }

        return $metadata;
    }

    private static function version(CareerBranch $branch, int $position): string
    {
        return $branch === CareerBranch::Main ? "v{$position}.0.0" : "{$branch->value}/{$position}.0.0";
    }

    private static function message(Experience $experience, CareerBranch $branch, int $position): string
    {
        if ($branch === CareerBranch::Main) {
            return $position === 1
                ? 'init: first commit'
                : "feat(career): join {$experience->organization} as {$experience->role}";
        }

        return "chore({$branch->value}): {$experience->role} at {$experience->organization}";
    }
}
