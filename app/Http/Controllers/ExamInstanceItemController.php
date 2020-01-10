<?php

namespace App\Http\Controllers;

use App\Exam_instance;
use App\Exam_instance_item;
use App\Exam_instance_item_item;
use App\Userlog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;

class ExamInstanceItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->middleware('auth');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('update_exam')) {
            abort(403, 'Unauthorized action.');
        }
        $input = $request::all();

        $input['last_updated_by'] = Auth::user()->id;
        if (\App\Exam_instance_item::where('exam_instance_id', '=', $input['exam_instance_id'])->count() > 0) {
            $input['order'] = (\App\Exam_instance_item::where('exam_instance_id', '=', $input['exam_instance_id'])->max('order')) + 1;

        } else {
            $input['order'] = 0;
        }
//dd($input);
        $newentry = \App\Exam_instance_item::create($input);
        if (isset($input['items'])) {
            $items = json_decode($input['items'])->items;
            // dd($items);
            $i = 0;
            foreach ($items as $item) {
                $newitem = new Exam_instance_item_item();
                $newitem['exam_instance_items_id'] = $newentry->id;
                $newitem['label'] = $item->label;
                $newitem['description'] = $item->description;
                $newitem['value'] = $item->value;
                $newitem['needscomment'] = ($item->needscomment == 'undefined') ? 'false' : 'true';
                $newitem['order'] = $i;
                $newitem['last_updated_by'] = Auth::user()->id;
                $newitem->save();
                $i++;
            }

        }
        // add items
        $response = array(
            'status' => '0',
            'id' => $newentry->id,
            'msg' => 'Item created successfully',

        );
        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Exam_instance_item $exam_instance_item
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // eager loading subitems
        return Exam_instance_item::with('items')->find($id);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Exam_instance_item $exam_instance_item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if (Gate::denies('update_exam')) {
            abort(403, 'Unauthorized action.');
        }
        $input = $request::all();
        //       dd($input);
        //  $input['last_updated_by'] = Auth::user()->id;
        // update the main bits
        $item = \App\Exam_instance_item::findOrNew($input['id']);
        //  dd($input);
        $item->update($input);
//        $response = array(
//            'status' => $item->update($input) ? '0' : '-1'
//        );
        if (isset($input['items'])) {
            $items = json_decode($input['items'])->items;
            // sync items
            $existingitems = $item->items->pluck('id')->toArray();

            $itemsids = collect(json_decode($input['items'])->items)->pluck('id')->toArray();

            // work out which are deleted:
            $itemstodelete = array_diff($existingitems, $itemsids);
            // dd($items);
            // get rid of them
            Exam_instance_item_item::destroy($itemstodelete);

            $i = 0;
            foreach ($items as $item) {
                // if there's an id >0, it's an existing item
                if ($item->id > 0) {
                    $newitem = Exam_instance_item_item::find($item->id);
                } else {
                    // else it's new. Create a new one
                    $newitem = new Exam_instance_item_item();
                }
                $newitem['exam_instance_items_id'] = $input['id'];
                $newitem['label'] = $item->label;
                $newitem['description'] = $item->description;
                $newitem['value'] = $item->value;
                $newitem['needscomment'] = ($item->needscomment == 'undefined') ? 'false' : 'true';
                $newitem['order'] = $i;
                $newitem['last_updated_by'] = Auth::user()->id;
                $newitem->save();

                $i++;
            }
        }

        $response = array(
            'status' => '0',
            'msg' => 'Item updated successfully',

        );
        return $response;
    }


    /**
     * Re-orders the examination instance items
     * @param Request $request
     * @return array
     */
    public function reorder(Request $request)
    {
        $input = $request::all();
        foreach (json_decode($input['order']) as $id) {
            $item = Exam_instance_item::find($id->id);
            $item->order = $id->order;
            $item->save();
        }
        return array(
            'status' => 0,
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * Currently this is not very efficient- it leaves a lot of detritus from soft deletes.
     * @TODO make a 'cleanup' script at some point
     *
     * @param  $id the ID of the item to destroy
     * @return int
     *
     */
    public function ajaxdestroy(Request $request)
    {
        if (Gate::denies('update_exam')) {
            abort(403, 'Unauthorized action.');
        }
        $input = $request::all();
        //dd($input);

        if( \App\Exam_instance_item::destroy($input['id'])>0){
            return array(
                'status' => 0,
            );
        }else{
            return array(
                'status' => 1,
            );
        }
    }

    // get items for this item as an array
    public function getitemitemsasarray($id)
    {
        $itemitems = Exam_instance_item_item::where('exam_instance_items_id', '=', $id)->get();
        $returnVal = [];
        foreach ($itemitems as $itemitem) {
            $returnVal[] = array('id' => $itemitem->id, 'text' => $itemitem->label);
        }
        return $returnVal;
    }

    ///////////////////////////////////////////////////////////////
    // template functions
    ///////////////////////////////////////////////////////////////
    public function templateindex(Request $request = null)
    {

        // if (Auth::user()->can('update_templates')) {
        $users = \App\User::all();

        $templateslist = \App\Exam_instance_item::where('is_template', '=', 'true');
        $templates = $templateslist->sortable()->paginate(20);

        //    dd($templates);
        //dd($reviews);
        return view('examinstance.templates.items.list')
            ->with('items', $templates)
            ->with('users', $users);
//        } else {
//            abort(500, 'You don\'t have permission to do that');
//        }
    }

    public function templatestore(Request $request)
    {

        if (Gate::denies('update_exam')) {
            abort(403, 'Unauthorized action.');
        }
        $input = $request::all();
        $input['is_template'] = 'true';

//dd($input);
        $newentry = \App\Exam_instance_item::create($input);

        if (isset($input['items'])) {
            $items = json_decode($input['items'])->items;
            // dd($items);
            $i = 0;
            foreach ($items as $item) {
                $newitem = new Exam_instance_item_item();
                $newitem['exam_instance_items_id'] = $newentry->id;
                $newitem['label'] = $item->label;
                $newitem['description'] = $item->description;
                $newitem['value'] = $item->value;
                $newitem['needscomment'] = ($item->needscomment == 'undefined') ? 'false' : 'true';
                $newitem['order'] = $i;
                $newitem['last_updated_by'] = Auth::user()->id;
                $newitem->save();
                $i++;
            }

        }
        // add items
        $response = array(
            'status' => '0',
            'id' => $newentry->id,
            'msg' => 'Item created successfully',

        );
        return $response;
    }

    protected static function boot()
    {
        static::updating(function ($item) {
            Userlog::create(['crud'=>'update', 'action'=>'Exam Instance Item', 'new_value'=>$item->name, 'old_value'=>$item->getOriginal('name')])->save();
        });
        static::creating(function ($item) {
            Userlog::create(['crud'=>'create', 'action'=>'Exam Instance Item', 'new_value'=>$item->name])->save();
        });
        static::deleting(function ($item) {
            Userlog::create(['crud'=>'delete', 'action'=>'Exam Instance Item', 'old_value'=>$item->name])->save();
        });
    }

}
