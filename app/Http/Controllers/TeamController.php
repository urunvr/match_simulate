<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Fixture;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Team::truncate();
        $teams = array(['Arsenal', 0.60, 0.58, 0.53, 0.35], ['Liverpool', 0.84, 1.00, 1.00, 0.65], ['Chelsea', 0.64, 0.87, 0.41, 1.00], ['Manchester City', 1.00, 0.91, 0.60, 1.00]);

        foreach($teams as $team){
            $t = new Team;
            $t->name = $team[0];
            $t->home_attack_strength = $team[1];
            $t->away_attack_strength = $team[2];
            $t->home_deffence_strength = $team[3];
            $t->away_deffence_strength = $team[4];
            $t->save();
        }

        $data['teams'] = Team::get('name');
        return view('teams', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $teams = Team::orderBy('points', 'desc')->get();
        $missMatchCount = Fixture::where('results', null)->count();
        if($missMatchCount > 0){
            $fixtureWeeks = Fixture::where('results', null)->get();
            $data['matches'] = [json_decode($fixtureWeeks[0]->matches), $fixtureWeeks[0]->week];
            
        }else{
            $lastFixture = Fixture::latest('id')->first();
            $data['matches'] = [json_decode( $lastFixture->matches), $lastFixture->week];
        }

        $prodictions = $this->calculateProdiction();
        $data['prodictions'] = $prodictions;
        $data['teams'] = $teams;
      
        return view('general', $data);

    }

    public function generateSchedule()
    {
        Fixture::truncate();
        $total_team = Team::count();
        $teams = Team::get();
        if($total_team > 0){
            $teamsArr = [];
            foreach($teams as $t){
                $teamsArr[] = $t->name;
            }
            $shuffleIds = $this->shuffle($teamsArr);
            $total_week = ($total_team - 1) * 2;
            $per_week_match_count = $total_team / 2;
            $fixtures = []; 
            $first_half_matches = [];
            $second_half_matches = [];
            for($i = 0; $i < $total_week / 2; $i++){
                $tempArr = $shuffleIds;
                $weekMatches = [];
                do{
                    $t1Index = rand(0, count($tempArr) - 1);
                    $t1 = $tempArr[$t1Index];
                    array_splice($tempArr, $t1Index, 1);
                    $t2Index = rand(0, count($tempArr) - 1);
                    $t2 = $tempArr[$t2Index];
                    array_splice($tempArr, $t2Index, 1);
                    $checkVal = $this->checkMatchExist($first_half_matches, [$t1, $t2]);
                    if($checkVal){
                        array_push($weekMatches, [$t1, $t2]);
                    }else{
                        $tempArr = $shuffleIds;
                    }

                }while(count($tempArr) > 1);
                array_push($first_half_matches, $weekMatches);
            }
            $second_half_matches = $this->getSecondHalf($first_half_matches);
            $fixture = array_merge($first_half_matches, $second_half_matches);

            if(count($fixture) > 0){
                foreach($fixture as $key => $fs){
                    $fixt = new Fixture;
                    $fixt->week = $key + 1;
                    $fixt->matches = json_encode($fs);
                    $fixt->save();
                }
            }

            $data['matches'] = $fixture;
            return view('fixtures', $data);
        }
    }

    public function shuffle($arr) 
    {
        for ($i = count($arr) - 1; $i > 0; $i--) {
            $j = rand(0, $i);
            [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
        }
        
        return $arr;
    }

    public function checkMatchExist($list, $matchArr)
    {
        if(count($list) > 0){
            foreach($list as $lss){
                foreach($lss as $ls){
                    if($ls == $matchArr) {
                        return false;
                    }else if($ls == array_reverse($matchArr)){
                        return false;
                    }
                }
            }
            return true;
        }else{
            return true;
        }
    }

    public function getSecondHalf($fh)
    {
        $matches = $fh;
        $newArr = [];
        if(count($matches) > 0){
            foreach($matches as $arr){
                $tempArr1 = [];
                foreach($arr as $mt){
                   $mt = array_reverse($mt);
                   $tempArr1[] = $mt;
                }
                $newArr[] = $tempArr1;
            }
            return $newArr;
        }
        return $newArr;
    }

    public function playNextWeek(){
        $fixtures = Fixture::where('results', null)->count();
        if((int)$fixtures > 0){
            $this->playWeekMatches();
            $nextFixture = Fixture::where('results', null)->get();
            $remainderMatch = Fixture::where('results', null)->count();
            if((int)$remainderMatch <= 3){
                $data['teams'] = Team::orderBy('points', 'desc')->get();
                $data['matches'] = [json_decode($nextFixture[0]->matches), $nextFixture[0]->week];
                $prodictions = $this->calculateProdiction();
                $data['prodictions'] = $prodictions;
                return $data;
            }else{
                $data['teams'] = Team::orderBy('points', 'desc')->get();
                $data['matches'] = [json_decode($nextFixture[0]->matches), $nextFixture[0]->week];
                return $data;
            }
        }

        $data['teams'] = Team::orderBy('points', 'desc')->get();
        $lastFixture = Fixture::latest('id')->first();
        $data['matches'] = [json_decode($lastFixture->matches), $lastFixture->week];
        $prodictions = $this->calculateProdiction();
        $data['prodictions'] = $prodictions;
        return $data;

    }

    public function simulateLeague(){
        $fixtures = Fixture::where('results', null)->count();
        for($i = 0; $i < $fixtures; $i++){
            $this->playWeekMatches();
        }
        $teams = Team::orderBy('points', 'desc')->get();
        $data['teams'] = $teams;
        $lastFixture = Fixture::latest('id')->first();
        $data['matches'] = [json_decode( $lastFixture->matches), $lastFixture->week];
        $prodictions = [];
        foreach ($teams as $key => $value) {
            $prodictions[] = $key == 0 ? [$value->name, 100] : [$value->name, 0];
        }
        $data['prodictions'] = $prodictions;

        return $data;
    }

    public function scoreCalc($str){
        $perf = rand(0, 5);
        $luck = rand(0, 2);
        $result = ($perf + $luck) * $str;
        return floor($result);
    }


    public function playWeekMatches(){
        $fixtures = Fixture::where('results', null)->get();
        if(count($fixtures) > 0){
            $targetWeek = $fixtures[0];
            $matches = json_decode($targetWeek->matches);
            $weekResults = [];
            foreach($matches as $match){
                $team1 = Team::where('name', $match[0])->get();
                $team2 = Team::where('name', $match[1])->get();
                $team1_strength = ($team1[0]->home_attack_strength + $team1[0]->away_attack_strength + $team1[0]->home_deffence_strength	 + $team1[0]->away_deffence_strength) / 4;
                $team2_strength = ($team2[0]->home_attack_strength + $team2[0]->away_attack_strength + $team2[0]->home_deffence_strength	 + $team2[0]->away_deffence_strength) / 4;

                $team1Score = $this->scoreCalc($team1_strength);
                $team2Score = $this->scoreCalc($team1_strength);
                if($team1Score == $team2Score){
                    $this->updateTeamPoints($team1[0]->id, $team2[0]->id, 0, $team1Score, $team2Score);
                }else if($team1Score > $team2Score){
                    $this->updateTeamPoints($team1[0]->id, $team2[0]->id, 1, $team1Score, $team2Score);
                }else if($team1Score < $team2Score){
                    $this->updateTeamPoints($team1[0]->id, $team2[0]->id, 2, $team1Score, $team2Score);
                }
                $weekResults[] = [$team1Score, $team2Score];
            }

            Fixture::where('id', $fixtures[0]->id)->update([
                'results' => json_encode($weekResults)
            ]);
        }
    }

    public function updateTeamPoints($id1, $id2, $type, $team1Score, $team2Score)
    {
        $team1 = Team::find($id1);
        $team2 = Team::find($id2);
        switch ($type) {
            case  0:
                $team1_update = Team::where('id', $team1->id)->update([
                    'played' => $team1->played + 1,
                    'drawn'  => $team1->drawn + 1,
                    'gf'     => $team1->gf + $team1Score,
                    'ga'     => $team1->ga + $team2Score,
                    'gd'     => $team1->gd + ($team1Score - $team2Score),
                    'points' => $team1->points + 1
                ]);

                $team2_update = Team::where('id', $team2->id)->update([
                    'played' => $team2->played + 1,
                    'drawn'  => $team2->drawn + 1,
                    'gf'     => $team2->gf + $team2Score,
                    'ga'     => $team2->ga + $team1Score,
                    'gd'     => $team2->gd + ($team2Score - $team1Score),
                    'points' => $team2->points + 1
                ]);
                break;
            case  1:
                $team1_update = Team::where('id', $team1->id)->update([
                    'played' => $team1->played + 1,
                    'won'    => $team1->won + 1,
                    'gf'     => $team1->gf + $team1Score,
                    'ga'     => $team1->ga + $team2Score,
                    'gd'     => $team1->gd + ($team1Score - $team2Score),
                    'points' => $team1->points + 3
                ]);

                $team2_update = Team::where('id', $team2->id)->update([
                    'played' => $team2->played + 1,
                    'lost'   => $team2->lost + 1,
                    'gf'     => $team2->gf + $team2Score,
                    'ga'     => $team2->ga + $team1Score,
                    'gd'     => $team2->gd + ($team2Score - $team1Score),
                ]);
                break;
            case  2:
                $team1_update = Team::where('id', $team1->id)->update([
                    'played' => $team1->played + 1,
                    'lost'   => $team1->lost + 1,
                    'gf'     => $team1->gf + $team1Score,
                    'ga'     => $team1->ga + $team2Score,
                    'gd'     => $team1->gd + ($team1Score - $team2Score),
                    
                ]);

                $team2_update = Team::where('id', $team2->id)->update([
                    'played' => $team2->played + 1,
                    'won'    => $team2->won + 1,
                    'gf'     => $team2->gf + $team2Score,
                    'ga'     => $team2->ga + $team1Score,
                    'gd'     => $team2->gd + ($team2Score - $team1Score),
                    'points' => $team2->points + 3
                ]);
                break;
            
            default:
                # code...
                break;
        }
    }


    public function calculateProdiction(){
        $remindMatches = Fixture::where('results', null)->count();
        $teamPoints = Team::orderBy('points', 'desc')->get('points');
        $teams = Team::orderBy('points', 'desc')->get();
        $closestDiff = $teams[0]->points - $teams[1]->points;
        $prodictions = [];
        if($closestDiff > $remindMatches * 3 || $remindMatches < 1 ){
            foreach($teams as $key => $t){
                $prodictions[] = $key == 0 ? [$t->name, 100] : [$t->name, 0];
            }
        }else{
            foreach($teams as $t){
                if($t->played){
                    $teamSucc = ($t->home_attack_strength + $t->away_attack_strength + $t->home_deffence_strength + $t->away_deffence_strength + ($t->won / $t->played)) / 5;
                    $prodictions[] =  [$t->name, round($teamSucc, 1)];
                }else{
                    $prodictions[] =  [$t->name, 0];
                }
            }
            
        }
        return $prodictions;
    }


    public function resetData(){
        $teams = Team::get();
        if(count($teams) > 0){
            foreach($teams as $team){
                $team->played = 0;
                $team->won = 0;
                $team->drawn = 0;
                $team->	lost = 0;
                $team->	gf = 0;
                $team->	ga = 0;
                $team->	gd = 0;
                $team->	points = 0;
                $team->save();
            }
        }
        
        $fixtures = Fixture::get();
        if(count($fixtures) > 0){
            foreach($fixtures as $f){
                $f->results = null;
                $f->save();
            }
        }
        $data['matches'] = [json_decode($fixtures[0]->matches), $fixtures[0]->week];
        $prodictions = $this->calculateProdiction();
        $data['prodictions'] = $prodictions;
        $data['teams'] = $teams;
        return $data;
    }

    
}





