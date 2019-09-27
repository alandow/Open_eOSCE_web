<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Requests\PatientRequest;
use App\Media;
use App\Patient;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Request;

class StudentImageController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {

    }

    // shows the raw file. If there's no entry, show a default.
    public function display($id)
    {
        if (Gate::denies('is_admin')) {
            if (Gate::denies('view-students')) {
                return 0;
            }
        }
        try {
            $media = \App\Student_image::findOrFail($id);
            $path = $media->path;

        } catch (ModelNotFoundException $e) {
            $path = '/public/unknown-person.jpeg';
        }

        $file = Storage::get($path);
        $content_typestr = 'image/jpeg';
        $response = new \Illuminate\Http\Response($file, '200');
        $response->header("Content-Type", $content_typestr);
        return $response;
    }

    public function show($id)
    {
        if (Gate::denies('is_admin')) {
            if (Gate::denies('view-students')) {
                return 0;
            }
        }
        try {
            return \App\Student_image::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return \App\Student_image::findOrFail($id);
        }
    }

    public function download($id)
    {
        if (Gate::denies('is_admin')) {
            if (Gate::denies('view-students')) {
                return 0;
            }
        }
        try {
            $media = \App\Student_image::firstOrFail($id);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }

        $path = $media->path;

        $content_typestr = 'image/jpeg';

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

        if (Gate::denies('is_admin')) {
            if (Gate::denies('view-students')) {
                return 0;
            }
        }
        try {
            $media = \App\Student_image::findOrFail($id);
            $path = $media->path;
        } catch (ModelNotFoundException $e) {
            $path = '\public\unknown-person.jpg';
        }

//dd(Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix());
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

    // display a thumbnail from an id
    public function unknownthumb($size)
    {

        if (Gate::denies('is_admin')) {
            if (Gate::denies('view-students')) {
                return 0;
            }
        }
        try {
            $media = \App\Student_image::findOrFail($id);
            $path = $media->path;
        } catch (ModelNotFoundException $e) {
            $path = '\public\unknown-person.jpg';
        }

//dd(Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix());
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
     * @param Request $request
     * @return $this
     */
    public function update(Request $request)
    {
        if (Gate::denies('is_admin')) {
            if (Gate::denies('update-students')) {
                return 0;
            }
        }
        $input = $request->all();
        $input["student_id"] = Auth::user()->id;
        $file = Request::file('userfile');
        // handle file
        try {
            $media = \App\Student_image::findOrFail($input['id']);
        } catch (ModelNotFoundException $e) {
            $media = new \App\Student_image();
        }
        if (isset($file)) {
            // check it's not too big
            if ($file->getMaxFilesize() > $file->getSize()) {
                // get type
                $type = $file->getClientOriginalExtension();
                // @todo is this an allowed type?
                if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                    return array('status' => 'bad file type');
                }
                //dd($file);
                // get the md5 hash of the contents. This allows for different files with the same name in teh same directory...
                $md5name = md5(file_get_contents($file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename()));

                // store it to disk
                if (!(Storage::disk('local')->put('media' . DIRECTORY_SEPARATOR . $md5name, \Illuminate\Support\Facades\File::get($file)))) {
                    return '-1';
                }
                // save the record

                $media->name = $file->getClientOriginalName();
                $media->type = $type;
                $media->size = $file->getSize();
                $media->path = 'media' . DIRECTORY_SEPARATOR . $md5name;


            } else {
                return array('status' => 'no file');
            }
        }

        return array(
            'status' => strval($media->save())
        );
    }

    public function destroy(Requests\MediaRequest $request)
    {
        if (Gate::denies('is_admin')) {
            if (Gate::denies('update-patients')) {
                return 0;
            }
        }
        $input = $request->all();
        return Media::destroy($input['id']);
    }


    /**
     * Using explicit request. Does an auth check! might be handy :)
     * @param \App\Http\Requests\CreatePatientRequest $request
     * @return type
     */
    public function store(PatientRequest $request)
    {

    }


}

