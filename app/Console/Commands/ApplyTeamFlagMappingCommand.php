<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Support\TeamFlagMapping;
use Illuminate\Console\Command;

class ApplyTeamFlagMappingCommand extends Command
{
    protected $signature = 'teams:apply-flag-mapping
        {--dry-run : Preview changes without writing to the database}
        {--force : Run without interactive confirmation}
        {--force-update : Overwrite existing country_code and flag_path values}';

    protected $description = 'Apply local team flag mapping to known national teams.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $forceUpdate = (bool) $this->option('force-update');

        $this->line($dryRun
            ? 'Team flag mapping dry run. No database changes will be written.'
            : 'Applying local team flag mapping.');

        if (! $this->option('force') && ! $this->confirm($dryRun
            ? 'Preview team flag mapping changes?'
            : 'Apply local flag mapping to teams?')) {
            $this->warn('Team flag mapping cancelled.');

            return self::FAILURE;
        }

        $rows = [];
        $counts = [
            'updated' => 0,
            'skipped_already_set' => 0,
            'missing_mapping' => 0,
            'missing_asset' => 0,
        ];

        Team::query()
            ->orderBy('name')
            ->each(function (Team $team) use (&$counts, &$rows, $dryRun, $forceUpdate): void {
                $mapping = TeamFlagMapping::forTeam($team);

                if ($mapping === null) {
                    $counts['missing_mapping']++;
                    $rows[] = [$team->name, $team->displayCode(), 'missing_mapping', 'No known flag mapping.'];

                    return;
                }

                if (! TeamFlagMapping::assetExists($mapping['flag_path'])) {
                    $counts['missing_asset']++;
                    $rows[] = [$team->name, $team->displayCode(), 'missing_asset', $mapping['flag_path']];

                    return;
                }

                $changes = [];

                if (
                    $forceUpdate
                    || blank($team->country_code)
                    || ($team->short_name === 'URU' && $team->country_code === 'URY')
                ) {
                    $changes['country_code'] = $mapping['country_code'];
                }

                if ($forceUpdate || blank($team->flag_path) || ! TeamFlagMapping::assetExists((string) $team->flag_path)) {
                    $changes['flag_path'] = $mapping['flag_path'];
                }

                if ($changes === []) {
                    $counts['skipped_already_set']++;
                    $rows[] = [$team->name, $team->displayCode(), 'skipped_already_set', 'country_code and flag_path already set.'];

                    return;
                }

                $counts['updated']++;

                if (! $dryRun) {
                    $team->forceFill($changes)->save();
                }

                $rows[] = [
                    $team->name,
                    $team->displayCode(),
                    $dryRun ? 'would_update' : 'updated',
                    implode(', ', array_map(
                        fn (string $field, string $value): string => "{$field}={$value}",
                        array_keys($changes),
                        $changes,
                    )),
                ];
            });

        $this->table(['Team', 'Code', 'Action', 'Details'], $rows);
        $this->line(sprintf(
            'Summary: updated=%d, skipped_already_set=%d, missing_mapping=%d, missing_asset=%d',
            $counts['updated'],
            $counts['skipped_already_set'],
            $counts['missing_mapping'],
            $counts['missing_asset'],
        ));

        if ($dryRun) {
            $this->warn('Dry run complete. No database changes were written.');
        } else {
            $this->components->info('Team flag mapping complete.');
        }

        return self::SUCCESS;
    }
}
