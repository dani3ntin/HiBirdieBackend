<?php

namespace App\Http\Controllers;
use App\Models\Birds;
use App\Models\Likes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use App\Http\Controllers\LikesController;
use Carbon\Carbon;

class BirdsController extends Controller
{
    function findDefaultBirdPic($name){
        if(stripos($name, "crow")) return 'defaultBirds/cornacchia.jpg';
        if(stripos($name, "sparrow")) return 'defaultBirds/passero.jpg';
        if(stripos($name, "robin")) return 'defaultBirds/pettirosso.jpg';
        if(stripos($name, "pigeon")) return 'defaultBirds/piccione.jpg';
        if(stripos($name, "dove")) return 'defaultBirds/tortora.jpg';
        return 'defaultBirds/defaultBird.jpg';
    }

    function addBird(Request $req){ //la proprietà dell'immagine deve chiamarsi photo
        $bird = new Birds;
        $bird->name = $req->input('name');
        $bird->sightingDate = $req->input('sightingDate');
        if($req->input('personalNotes')){
            $bird->personalNotes = $req->input('personalNotes');
        }else{
            $bird->personalNotes = '';
        }
        $bird->xPosition = $req->input('xPosition');
        $bird->yPosition = $req->input('yPosition');
        if($req->file('photo') == null){
            $bird->photoPath = $this->findDefaultBirdPic($req->input('name'));
        }else{
            $bird->photoPath = $req->file('photo')->store("birds/");
        }
        $bird->user = $req->input('user');
        $bird->deleted = 0;
        $bird->save();
        return $bird;
    }

    function editBird(Request $req){ //la proprietà dell'immagine deve chiamarsi photo
        if($req->file('photo') == null){
            Birds::where('id', $req->input('id'))->update([
                'name' => $req->input('name'),
                'sightingDate' => $req->input('sightingDate'),
                'personalNotes' => $req->input('personalNotes'),
                'xPosition' => $req->input('xPosition'),
                'yPosition' => $req->input('yPosition'),
            ]);
        }
        else{
            Birds::where('id', $req->input('id'))->update([
                'name' => $req->input('name'),
                'sightingDate' => $req->input('sightingDate'),
                'personalNotes' => $req->input('personalNotes'),
                'xPosition' => $req->input('xPosition'),
                'yPosition' => $req->input('yPosition'),
                'photoPath' => $req->file('photo')->store("birds/"),
            ]);
        }
    }

    function getAllBirds($requestingUser) {
        return Birds::leftJoin('likes', 'birds.id', '=', 'likes.bird')
            ->select(
                'birds.id', 'birds.sightingDate', 'birds.personalNotes', 'birds.xPosition', 'birds.yPosition', 'birds.photoPath', 'birds.user', 'birds.deleted', 'birds.name', 
                DB::raw('COUNT(likes.bird) AS likes'),
                DB::raw('MAX(CASE WHEN likes.user = ? THEN 1 ELSE 0 END) AS userPutLike')
            )
            ->where("birds.deleted", "=", "0")
            ->groupBy(
                'birds.id', 'birds.sightingDate', 'birds.personalNotes', 'birds.xPosition', 'birds.yPosition', 'birds.photoPath', 'birds.user', 'birds.deleted', 'birds.name'
            )
            ->setBindings([$requestingUser, $requestingUser])
            ->get();
    }

    function getAllBirdsExceptYours($requestingUser) {
        $birds = Birds::leftJoin('likes', 'birds.id', '=', 'likes.bird')
            ->select(
                'birds.id', 'birds.sightingDate', 'birds.personalNotes', 'birds.xPosition', 'birds.yPosition', 'birds.photoPath', 'birds.user', 'birds.deleted', 'birds.name', 
                DB::raw('COUNT(likes.bird) AS likes'),
                DB::raw('MAX(CASE WHEN likes.user = ? THEN 1 ELSE 0 END) AS userPutLike')
            )
            ->where("birds.deleted", "=", "0")
            ->where("birds.user", "<>", $requestingUser)
            ->groupBy(
                'birds.id', 'birds.sightingDate', 'birds.personalNotes', 'birds.xPosition', 'birds.yPosition', 'birds.photoPath', 'birds.user', 'birds.deleted', 'birds.name'
            )
            ->setBindings([$requestingUser, $requestingUser, $requestingUser])
            ->get();
        if(!$birds) return [];
        return $birds;
    }

    // maximumDays, maximumDistance, requestingUser, latUser, longUser
    function getBirdsWithFilter(Request $req) {
        $maximumDays = $req->input('maximumDays');
        $maximumDistance = $req->input('maximumDistance');
        if($maximumDays == 0){
            $maximumDays = 9999999;
        }
        if($maximumDistance == 0){
            $maximumDistance = 9999999;
        }
        $requestingUser = $req->input('requestingUser');
        $maximumDays = Carbon::now()->subDays($maximumDays - 1)->format('Y-d-m');
        $latUser = $req->input('latUser');
        $lonUser = $req->input('lonUser');
        $birds = DB::select("
            SELECT
                b.id, b.sightingDate, b.personalNotes, b.xPosition, b.yPosition, b.photoPath, b.user, b.deleted, b.name,
                COUNT(l.bird) AS likes,
                MAX(CASE WHEN l.user = '".$requestingUser."' THEN 1 ELSE 0 END) AS userPutLike,
                6371 * ACOS(COS(RADIANS(".$latUser.")) * COS(RADIANS(b.xPosition)) * COS(RADIANS(".$lonUser.") - RADIANS(b.yPosition)) + SIN(RADIANS(".$latUser.")) * SIN(RADIANS(b.xPosition))) AS distance
            FROM Birds AS b
            LEFT JOIN likes AS l ON b.id = l.bird
            GROUP BY
                b.id, b.sightingDate, b.personalNotes, b.xPosition, b.yPosition, b.photoPath, b.user, b.deleted, b.name
            HAVING
                distance < ".$maximumDistance." AND b.sightingDate >= '".$maximumDays."' AND b.deleted = 0
            ORDER BY
                b.sightingDate DESC;
        ");
        if(!$birds) return [];
        return $birds;
    }

    function getBirdsWithFilterExceptYours(Request $req) {
        $maximumDays = $req->input('maximumDays');
        $maximumDistance = $req->input('maximumDistance');
        if($maximumDays == 0){
            $maximumDays = 99999;
        }
        if($maximumDistance == 0){
            $maximumDistance = 9999999;
        }
        $requestingUser = $req->input('requestingUser');
        $maximumDays = Carbon::now()->subDays($maximumDays)->format('Y-m-d');
        $latUser = $req->input('latUser');
        $lonUser = $req->input('lonUser');
        $birds =  DB::select("
            SELECT
                b.id, b.sightingDate, b.personalNotes, b.xPosition, b.yPosition, b.photoPath, b.user, b.deleted, b.name,
                COUNT(l.bird) AS likes,
                MAX(CASE WHEN l.user = '".$requestingUser."' THEN 1 ELSE 0 END) AS userPutLike,
                6371 * ACOS(COS(RADIANS(".$latUser.")) * COS(RADIANS(b.xPosition)) * COS(RADIANS(".$lonUser.") - RADIANS(b.yPosition)) + SIN(RADIANS(".$latUser.")) * SIN(RADIANS(b.xPosition))) AS distance
            FROM birds AS b
            LEFT JOIN likes AS l ON b.id = l.bird
            GROUP BY
                b.id, b.sightingDate, b.personalNotes, b.xPosition, b.yPosition, b.photoPath, b.user, b.deleted, b.name
            HAVING
                distance < ".$maximumDistance." AND b.sightingDate >= '".$maximumDays."' AND b.sightingDate <= '".Carbon::now()->format('Y-m-d')."' AND b.deleted = 0 AND b.user <> '".$requestingUser."'
            ORDER BY
                b.sightingDate DESC;
        ");
        if(!$birds) return [];
        return $birds;
    }

    function getBirdsByUsernameWithDistance(Request $req) {
        $requestingUser = $req->input('requestingUser');
        $authorUsername = $req->input('authorUsername');
        $latUser = $req->input('latUser');
        $lonUser = $req->input('lonUser');
        $birds = DB::select("
            SELECT
                b.id, b.sightingDate, b.personalNotes, b.xPosition, b.yPosition, b.photoPath, b.user, b.deleted, b.name,
                COUNT(l.bird) AS likes,
                MAX(CASE WHEN l.user = '".$requestingUser."' THEN 1 ELSE 0 END) AS userPutLike,
                6371 * ACOS(COS(RADIANS(".$latUser.")) * COS(RADIANS(b.xPosition)) * COS(RADIANS(".$lonUser.") - RADIANS(b.yPosition)) + SIN(RADIANS(".$latUser.")) * SIN(RADIANS(b.xPosition))) AS distance
            FROM Birds AS b
            LEFT JOIN likes AS l ON b.id = l.bird
            WHERE b.user = '".$authorUsername."' AND b.deleted = '0'
            GROUP BY
                b.id, b.sightingDate, b.personalNotes, b.xPosition, b.yPosition, b.photoPath, b.user, b.deleted, b.name
            ORDER BY
                b.sightingDate DESC;
        ");
        if(!$birds) return [];
        return $birds;
    }

    
    function getBirdsByUser($user, $requestingUser) {
        $birds = Birds::leftJoin('likes', 'birds.id', '=', 'likes.bird')
            ->select(
                'birds.id', 'birds.sightingDate', 'birds.personalNotes', 'birds.xPosition', 'birds.yPosition', 'birds.photoPath', 'birds.user', 'birds.deleted', 'birds.name', 
                DB::raw('COUNT(likes.bird) AS likes'),
                DB::raw('MAX(CASE WHEN likes.user = ? THEN 1 ELSE 0 END) AS userPutLike')
            )
            ->where('birds.user', '=', $user)
            ->where("birds.deleted", "=", "0")
            ->groupBy(
                'birds.id', 'birds.sightingDate', 'birds.personalNotes', 'birds.xPosition', 'birds.yPosition', 'birds.photoPath', 'birds.user', 'birds.deleted', 'birds.name'
            )
            ->setBindings([$requestingUser, $user, $user])
            ->get();
        if(!$birds) return [];
        return $birds;
    }

    function getBird($id, $requestingUser){
        $bird = Birds::select("*")->where("id", $id)->where("deleted", "=", "0")->get()[0];
        $likes = count(Likes::select("*")->where("bird", $id)->get());

        $request = new Request([
            'user' => $requestingUser,
            'bird' => $id,
        ]);

        $likesController = new LikesController;
        $userPutLike = $likesController->userPutLike($request);
        $path = $bird->photoPath;

        if (!Storage::exists($path)) {
            abort(404);
        }

        $image = Storage::get($path);
        $mimeType = Storage::mimeType($path);

        return new Response($image, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
            'imageInfos' => json_encode([
                'id' => $bird->id,
                'name' => $bird->name, 
                'sightingDate'=> $bird->sightingDate,
                'personalNotes'=> $bird->personalNotes,
                'xPosition'=> $bird->xPosition,
                'yPosition'=> $bird->yPosition,
                'user'=> $bird->user,
                'deleted'=> $bird->deleted,
                'likes' => $likes,
                'userPutLike' => $userPutLike
            ]),
        ]);
    }

    function deleteBird($id){
        Birds::where('id', $id)->update([
            'deleted' => '1',
        ]);
    }
}
