<?php

namespace App\Http\Controllers;

use App\User;
use App\User_image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Request;


class UserImageController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {

    }

    public
    function createmedia(Request $request)
    {

        $input = $request::all();
        $input["user_id"] = Auth::user()->id;
        // handle file
        //dd($input);
        // $uploadedfile = $input["userfile"];
        $file = Request::file('userfile');
        // resize to a sensible proportion

        // check it's not too big
        if ($file->getMaxFilesize() > $file->getSize()) {
            // get type
            $type = $file->getClientOriginalExtension();
            // @todo is this an allowed type?

            // get the md5 hash of the contents. This allows for different files with the same name in teh same directory...
            $md5name = md5(file_get_contents($file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename()));

            // store it to disk
            if (!(Storage::disk('local')->put('media' . DIRECTORY_SEPARATOR . $md5name, File::get($file)))) {
                return '-1';
            }
            // save the record
            $newentry = new Unit_instances_media();
            $newentry->user_id = $input["unit_id"];
            $newentry->name = $file->getClientOriginalName();
            $newentry->type = $type;
            $newentry->size = $file->getSize();
            $newentry->description = $input["description"];
            $newentry->path = 'media' . DIRECTORY_SEPARATOR . $md5name;
            $newentry->save();
            return $newentry->id;

        } else {
            return '-1';
        }
        //$response = array(
        //  'status' => 'success',
        //'msg' => 'Setting created successfully',
        //);

        return '-1';
    }

    // shows the image
    public function display($id)
    {
        if (!isset(User::find($id)->image->id)) {
            $path = '/public/user_image_placeholder.png';
            $file = Storage::get($path);
            $response = new \Illuminate\Http\Response($file, '200');
            $response->header("Content-Type", 'image/png');
            return $response;
        }
        $imageid = User::find($id)->image->id;

        $media = \App\User_image::find($imageid);
        if (!$media) {
            $path = '/public/user_image_placeholder.png';
            $file = Storage::get($path);
            $response = new \Illuminate\Http\Response($file, '200');
            $response->header("Content-Type", 'image/png');
            return $response;
        }
        switch (strtolower($media->type)) {
            case 'jpg':
                $path = $media->path;
                $content_typestr = 'image/jpeg';
                break;
            case 'png':
                $path = $media->path;
                $content_typestr = 'image/png';
                break;
            case 'bmp':
                $path = $media->path;
                $content_typestr = 'image/bmp';
                break;

            default:
                $path = '/public/user_image_placeholder.png';
                $content_typestr = 'image/png';
                break;
        }
        $file = Storage::get($path);
        $response = new \Illuminate\Http\Response($file, '200');
        $response->header("Content-Type", $content_typestr);
        return $response;
    }

    public function show($id)
    {

        return \App\User_image::findOrFail($id);
    }

    public function download($id)
    {

        $media = \App\User_image::findOrFail($id);
        $content_typestr = "";
        $path = $media->path;
        switch ($media->type) {
            case 'jpg':

                $content_typestr = 'image/jpeg';
                break;
            case 'png':
                $content_typestr = 'image/png';
                break;
            case 'bmp':
                $content_typestr = 'image/bmp';
                break;

            default:
                $content_typestr = 'application/octet-stream';
                break;
        }
        //  dd(Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix().$path);
        //dd($path);
        $file = Storage::get($path);

        $response = new \Illuminate\Http\Response($file, '200');
        $response->header("Content-Type", $content_typestr);
        // suggest to browser a download
        $response->header("Content-Disposition", 'attachment; filename=' . $media->name);
        return $response;
    }

    // display a thumbnail from an id
    public function thumb($id, $size)
    {

        try {
            $imageid = User::find($id)->image->id;
            $media = \App\User_image::find($imageid);
            if (!$media) {
                $path = '/public/user_image_placeholder.png';
                $file = Storage::get($path);
            }
            switch ($media->type) {
                case 'jpg':
                case 'png':
                case 'bmp':
                    $path = $media->path;
                    $file = Storage::get($path);
                    break;
                default:
                    $file = Storage::get('/public/user_image_placeholder.png');
                    break;
            }
        } catch (\Exception $e) {
            $path = '/public/user_image_placeholder.png';
            $file = Storage::get($path);
        }


        $file = Storage::get($path);
        $image = Image::make($file);

        // get image height and width
        $origheight = $image->height();
        $origwidth = $image->width();
        // work out the ratio
        $ratio = ($origheight / $origwidth);
// work out size
        $sizeArr = array(($ratio < 0 ? ($size * $ratio) : $size), ($ratio < 0 ? $size : ($size * $ratio)));
        // resize, dump out
        return $image->resize($sizeArr[0], $sizeArr[1])->response();
    }


    /**
     * Updates the record
     * @param $id
     * @param PatientRequest $request
     * @return $this
     */
    public function update(Request $request)
    {
        $input = $request::all();

        // filter out unauthorised actions
        if (!((Auth::user()->id == $input["user_id"]) || (Auth::user()->can('update_other_user')))) {

            abort(403, 'Unauthorized action.');
        }
        if (isset($input["user_id"])) {
            // find the user
            $user = User::find($input['user_id']);
        } else {
            abort(500, 'No such user.');
        }


        // is there an image with the user already existing?

        if (!isset($user->image->id)) {
            // if not, make one
            $media = new User_image();
            $media->user_id = $user->id;
        } else {
            $media = \App\User_image::find($user->image->id);
        }

        $file = Request::file('userfile');
        // resize to a sensible proportion
        // dd($file);
        if (isset($file)) {
            // check it's not too big
            if ($file->getMaxFilesize() > $file->getSize()) {
                // get type
                $type = strtolower($file->getClientOriginalExtension());

                // is this an allowed type?
                if (!in_array($type, ['jpg', 'png', 'bmp'])) {
                    return '-1';
                }
                // get the md5 hash of the contents. This allows for different files with the same name in teh same directory...
                $md5name = md5(file_get_contents($file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename()));
                // reduce the file to a useful size
                $image = Image::make(file_get_contents($file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename()));
                // get image height and width
                // resize, dump out
                if (!(Storage::disk('local')->put('media' . DIRECTORY_SEPARATOR . $md5name, $image->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode('jpg', 75)))
                ) {
                    return '-1';
                }
                // save the record
                $media->description = $input["description"];
                $media->name = $file->getClientOriginalName();
                $media->type = 'jpg';
                $media->size = $file->getSize();
                $media->path = 'media' . DIRECTORY_SEPARATOR . $md5name;


            } else {
                return '-1';
            }
        }
        $media->description = $input["description"];
        return array(
            'status' => strval($media->save())
        );
    }

    public function destroy(Request $request)
    {
        $input = $request::all();
        $status = User_image::destroy($input['id']);
        $response = array(
            'status' => $status,
        );
        return $response;

    }


}

