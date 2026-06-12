<?php

namespace Tests\Unit;

use App\Models\TournamentMatch;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TournamentMatchKnockoutTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function representativeRoundLabels(): array
    {
        return [
            'group stage' => ['Group Stage - 1', TournamentMatch::STAGE_GROUP],
            'group letter' => ['Group A', TournamentMatch::STAGE_GROUP],
            'round of 32' => ['Round of 32', TournamentMatch::STAGE_ROUND_OF_32],
            'round of 16' => ['Round of 16', TournamentMatch::STAGE_ROUND_OF_16],
            'quarter-finals' => ['Quarter-finals', TournamentMatch::STAGE_QUARTER_FINAL],
            'semi-finals' => ['Semi-finals', TournamentMatch::STAGE_SEMI_FINAL],
            'third place final' => ['3rd Place Final', TournamentMatch::STAGE_THIRD_PLACE],
            'third place wording' => ['Third Place', TournamentMatch::STAGE_THIRD_PLACE],
            'final' => ['Final', TournamentMatch::STAGE_FINAL],
            'fraction round of 32' => ['1/16-finals', TournamentMatch::STAGE_ROUND_OF_32],
            'fraction round of 16' => ['1/8-finals', TournamentMatch::STAGE_ROUND_OF_16],
            'fraction quarter' => ['1/4-finals', TournamentMatch::STAGE_QUARTER_FINAL],
            'fraction semi' => ['1/2-finals', TournamentMatch::STAGE_SEMI_FINAL],
        ];
    }

    #[DataProvider('representativeRoundLabels')]
    public function test_round_label_maps_to_expected_stage(string $round, string $expectedStage): void
    {
        $this->assertSame($expectedStage, TournamentMatch::stageFromApiRound($round));
    }

    public function test_group_stage_does_not_require_qualified_team_prediction(): void
    {
        $match = new TournamentMatch(['stage' => TournamentMatch::stageFromApiRound('Group Stage - 1')]);

        $this->assertSame(TournamentMatch::STAGE_GROUP, $match->stage);
        $this->assertFalse($match->isKnockout());
        $this->assertFalse($match->requiresQualifiedTeamPrediction());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function knockoutRoundLabels(): array
    {
        return [
            'round of 32' => ['Round of 32', TournamentMatch::STAGE_ROUND_OF_32],
            'round of 16' => ['Round of 16', TournamentMatch::STAGE_ROUND_OF_16],
            'quarter-finals' => ['Quarter-finals', TournamentMatch::STAGE_QUARTER_FINAL],
            'semi-finals' => ['Semi-finals', TournamentMatch::STAGE_SEMI_FINAL],
            'third place' => ['3rd Place Final', TournamentMatch::STAGE_THIRD_PLACE],
            'final' => ['Final', TournamentMatch::STAGE_FINAL],
        ];
    }

    #[DataProvider('knockoutRoundLabels')]
    public function test_knockout_round_labels_require_qualified_team_prediction(string $round, string $expectedStage): void
    {
        $match = new TournamentMatch(['stage' => TournamentMatch::stageFromApiRound($round)]);

        $this->assertSame($expectedStage, $match->stage);
        $this->assertTrue($match->isKnockout());
        $this->assertTrue($match->requiresQualifiedTeamPrediction());
    }

    public function test_unknown_or_empty_round_labels_map_to_null_stage(): void
    {
        $this->assertNull(TournamentMatch::stageFromApiRound(null));
        $this->assertNull(TournamentMatch::stageFromApiRound(''));
        $this->assertNull(TournamentMatch::stageFromApiRound('   '));
        $this->assertNull(TournamentMatch::stageFromApiRound('Friendly'));
        $this->assertNull(TournamentMatch::stageFromApiRound('Preliminary Round'));
    }

    public function test_unmapped_round_does_not_silently_become_knockout_or_require_qualified_team(): void
    {
        $match = new TournamentMatch(['stage' => TournamentMatch::stageFromApiRound('Mystery Round')]);

        $this->assertNull($match->stage);
        $this->assertFalse($match->isKnockout());
        $this->assertFalse($match->requiresQualifiedTeamPrediction());
    }

    public function test_round_label_matching_is_case_insensitive_and_trimmed(): void
    {
        $this->assertSame(TournamentMatch::STAGE_ROUND_OF_16, TournamentMatch::stageFromApiRound('  round of 16  '));
        $this->assertSame(TournamentMatch::STAGE_FINAL, TournamentMatch::stageFromApiRound('FINAL'));
        $this->assertSame(TournamentMatch::STAGE_THIRD_PLACE, TournamentMatch::stageFromApiRound('THIRD PLACE'));
    }
}
