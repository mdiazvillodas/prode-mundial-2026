<?php

namespace Tests\Unit;

use App\Models\Prediction;
use App\Models\Team;
use App\Models\TournamentMatch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class PredictionModelTest extends TestCase
{
    public function test_predicted_qualified_team_relationship_exists(): void
    {
        $relation = (new Prediction())->predictedQualifiedTeam();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertSame('predicted_qualified_team_id', $relation->getForeignKeyName());
        $this->assertInstanceOf(Team::class, $relation->getRelated());
    }

    public function test_knockout_helpers_identify_knockout_matches(): void
    {
        $knockoutMatch = new TournamentMatch(['stage' => 'round_of_16']);
        $this->assertTrue($knockoutMatch->isKnockout());
        $this->assertTrue($knockoutMatch->requiresQualifiedTeamPrediction());

        $thirdPlaceMatch = new TournamentMatch(['stage' => 'third_place']);
        $this->assertTrue($thirdPlaceMatch->isKnockout());
        $this->assertTrue($thirdPlaceMatch->requiresQualifiedTeamPrediction());

        $groupMatch = new TournamentMatch(['stage' => 'group']);
        $this->assertFalse($groupMatch->isKnockout());
        $this->assertFalse($groupMatch->requiresQualifiedTeamPrediction());
    }
}
