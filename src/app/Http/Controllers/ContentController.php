<?php

namespace Pveltrop\DCMS\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pveltrop\DCMS\Classes\Content;
use App\Http\Controllers\Controller;
use Stevebauman\Purify\Facades\Purify;

include __DIR__ . '/../../Helpers/DCMS.php';

/**
 * Override authenticate and entries method below to correctly use this Controller
 *
 * Class ContentController
 * @package Pveltrop\DCMS\Http\Controllers
 */
class ContentController extends Controller
{
    /**
    * Define conditions a user must match to spawn a DCMS editor.
    * @return JsonResponse
    */
    public function authenticate()
    {
        return response()->json(['message' => 'Unauthenticated'], 422);
    }

    /**
     * Define which content entries can be changed.
     * @return array
     */
    public function entries(): array
    {
        return [
            // 'example-uid' => true,
            // 'foo-bar' => auth()->user() ? true : false,
            // '0198706924' => false
        ];
    }

    // The methods below work out of the box

    /**
     * Edit accessable content.
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $canBeEdited = false;
        $request = json_decode($request->getContent(), true);

        foreach ($this->entries() as $entryKey => $entryValue) {
            if ($entryKey === $request->contentUID && $entryValue === true) {
                $canBeEdited = true;
            }
        }

        if (!$canBeEdited) {
            return response()->json([
                'message' => __('This entry can\'t be changed.'),
            ], 422);
        }

        $contentValue = $request->contentValue;
        $cleanContent = Purify::clean($contentValue);

        if (preg_match('/([a-z]|[A-Z])/m', $cleanContent) === 0) {
            return response()->json([
                'message' => __('Content is empty.'),
            ], 422);
        }

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
        ], 200);
    }

    /**
     * Clear stored content.
     * @param Request $request
     * @return JsonResponse
     */
    public function clear(Request $request): JsonResponse
    {
        $canBeCleared = false;
        $request = json_decode($request->getContent(), true);

        foreach ($this->entries() as $entryKey => $entryValue) {
            if ($entryKey === $request->contentUID && $entryValue === true) {
                $canBeCleared = true;
            }
        }

        if (!$canBeCleared) {
            return response()->json([
                'message' => __('This entry can\'t be deleted.'),
            ], 422);
        }

        $content = Content::find($request->contentUID);

        if ($content) {
            $content->delete();
            return response()->json([
                'message' => __('Content has been deleted.'),
            ], 200);
        } else {
            return response()->json([
                'message' => __('Unable to delete content.'),
            ], 422);
        }
    }
}
