<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Pveltrop\DCMS\Classes\Content;
use Stevebauman\Purify\Facades\Purify;

include __DIR__ . '/../Helpers/DCMS.php';

trait DCMSContentController
{
    /**
    * Define conditions a user must match to spawn a DCMS editor.
    * @return \Illuminate\Http\JsonResponse
    */

    public function authenticate()
    {
        return response()->json(['message' => 'Unauthenticated'],422);
    }

    /**
    * Define which entries can be edited.
    */

    public function entries(): array
    {
        return [
            // 'example-uid' => true,
            // 'foo-bar' => auth()->user() ? true : false,
            // '0198706924' => false
        ];
    }

    /**
    * Edit accessable content.
    * @return \Illuminate\Http\JsonResponse
    */

    public function update(Request $request)
    {
        $canBeEdited = false;
        $request = json_decode($request->getContent());

        foreach($this->entries() as $entryKey => $entryValue){
            if ($entryKey == $request->contentUID && $entryValue == true){
                $canBeEdited = true;
            }
        }

        if(!$canBeEdited){
            return response()->json([
                'message' => __('This entry can\'t be changed.'),
            ],422);
        }

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
