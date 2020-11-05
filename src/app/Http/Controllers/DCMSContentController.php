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

        $contentValue = $request->contentValue;
        $cleanContent = Purify::clean($contentValue);
        
        $content = Content::find($request->contentUID);
        if (!$content) {
            $content = Content::create([
                'UID'  => $request->contentUID,
                'value' => $cleanContent
            ]);
        } else {
            $content->update([
                'UID'  => $request->contentUID,
                'value' => $cleanContent
            ]);
            $content->save();
        }
        
        return response()->json([
            'message' => __('Content has been updated.'),
        ],200);
    }
}
