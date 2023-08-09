<?php

namespace App\Http\Controllers;
use App\Models\Birds;
use App\Models\Likes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use App\Http\Controllers\LikesController;

class BirdsController extends Controller
{
    function findDefaultBirdPic($name){
        if($name == "Crow") return 'defaultBirds/cornacchia.jpg';
        if($name == "Sparrow") return 'defaultBirds/cornacchia.jpg';
        if($name == "Robin") return 'defaultBirds/cornacchia.jpg';
        if($name == "Pigeon") return 'defaultBirds/cornacchia.jpg';
        if($name == "Dove") return 'defaultBirds/cornacchia.jpg';
        return 'defaultBirds/defaultBird.jpg';
    }

    function addBird(Request $req){ //la proprietà dell'immagine deve chiamarsi photo
        $bird = new Birds;
        $bird->name = $req->input('name');
        $bird->sightingDate = $req->input('sightingDate');
        $bird->personalNotes= $req->input('personalNotes');
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

    function getAllBirds($requestingUser) {
        return Birds::leftJoin('likes', 'birds.id', '=', 'likes.bird')
            ->select(
                'birds.id', 'birds.sightingDate', 'birds.personalNotes', 'birds.xPosition', 'birds.yPosition', 'birds.photoPath', 'birds.user', 'birds.deleted', 'birds.name', 
                DB::raw('COUNT(likes.bird) AS likes'),
                DB::raw('MAX(CASE WHEN likes.user = ? THEN 1 ELSE 0 END) AS userPutLike')
            )
            ->groupBy(
                'birds.id', 'birds.sightingDate', 'birds.personalNotes', 'birds.xPosition', 'birds.yPosition', 'birds.photoPath', 'birds.user', 'birds.deleted', 'birds.name'
            )
            ->setBindings([$requestingUser])
            ->get();
    }
    
    
    
    function getBirdsByUser($user, $requestingUser) {
        return Birds::leftJoin('likes', 'birds.id', '=', 'likes.bird')
            ->select(
                'birds.id', 'birds.sightingDate', 'birds.personalNotes', 'birds.xPosition', 'birds.yPosition', 'birds.photoPath', 'birds.user', 'birds.deleted', 'birds.name', 
                DB::raw('COUNT(likes.bird) AS likes'),
                DB::raw('MAX(CASE WHEN likes.user = ? THEN 1 ELSE 0 END) AS userPutLike')
            )
            ->where('birds.user', '=', $user) // Aggiunta della clausola WHERE
            ->groupBy(
                'birds.id', 'birds.sightingDate', 'birds.personalNotes', 'birds.xPosition', 'birds.yPosition', 'birds.photoPath', 'birds.user', 'birds.deleted', 'birds.name'
            )
            ->setBindings([$requestingUser, $user])
            ->get();
    }

    function getBird($id, $requestingUser){
        $bird = Birds::select("*")->where("id", $id)->get()[0];
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
}
