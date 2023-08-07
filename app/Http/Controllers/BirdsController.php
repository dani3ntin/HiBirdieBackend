<?php

namespace App\Http\Controllers;
use App\Models\Birds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

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
        $bird->	personalNotes= $req->input('personalNotes');
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

    function getAllBirds(){
        return Birds::select("*")->get();
    }

    function getBirdsByUser($user){
        return Birds::select("*")->where("user", $user)->get();
    }

    function getBird($id){
        $bird = Birds::select("*")->where("id", $id)->get()[0];
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
            ]),
        ]);
    }
}
