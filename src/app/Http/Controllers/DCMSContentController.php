<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pveltrop\DCMS\Classes\Content;
use Stevebauman\Purify\Facades\Purify;

class DCMSContentController extends Controller
{
    public function update(Request $request)
    {
        $request = json_decode($request->getContent());

        $dirty = $request->contentValue;
        dd(Purify::clean($dirty));
        
        $content = Content::find($request->contentUID);
        if (!$content) {
            $content = Content::create([
                'UID'  => $request->contentUID,
                'value' => $request->contentValue
            ]);
        } else {
            $content->update([
                'UID'  => $request->contentUID,
                'value' => $request->contentValue
            ]);
            $content->save();
        }
        
        return response()->json([
            'message' => __('Content has been updated.'),
        ],200);
    }
}
