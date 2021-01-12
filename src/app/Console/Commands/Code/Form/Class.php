<?php

return '<?php

namespace App\Forms;

class '.$model.'Form
{
    public function fields(){
        return [
            // "title" => [
            //     "label" => [
            //         "text" => __("Title")
            //     ]
            // ],
            // "category_id" => [
            //     "label" => [
            //         "text" => __("Category")
            //     ],
            //     "select" => [
            //         "data-type" => "slimselect",
            //         "options" => [
            //             "data" => Category::all(),
            //             "primaryKey" => "id",
            //             "foreignKey" => "category_id",
            //             "showKey" => "name",
            //         ]
            //     ],
            //     "small" => [
            //         "text" => __("Select which category this page is for.")
            //     ]
            // ],
            // "thumbnail" => [
            //     "carousel" => [
            //         "height" => "200px"
            //     ],
            //     "label" => [
            //         "text" => __("Thumbnail")
            //     ],
            //     "input" => [
            //         "type" => "file",
            //         "data-type" => "filepond",
            //         "data-filepond-prefix" => "post",
            //         "data-filepond-mime" => "image",
            //     ]
            // ],
            // "banner" => [
            //     "carousel" => [
            //         "height" => "200px"
            //     ],
            //     "label" => [
            //         "text" => __("Banner")
            //     ],
            //     "input" => [
            //         "type" => "file",
            //         "data-type" => "filepond",
            //         "data-filepond-prefix" => "post",
            //         "data-filepond-mime" => "image",
            //     ]
            // ],
            // "content" => [
            //     "label" => [
            //         "text" => __("Content")
            //     ],
            //     "textarea" => [
            //         "data-type" => "tinymce",
            //     ]
            // ],
            // "content_imgs" => [
            //     "carousel" => [
            //         "height" => "200px"
            //     ],
            //     "label" => [
            //         "text" => __("Content Images")
            //     ],
            //     "input" => [
            //         "type" => "file",
            //         "data-type" => "filepond",
            //         "data-filepond-prefix" => "post",
            //         "data-filepond-mime" => "image",
            //     ]
            // ],
            // "tags" => [
            //     "label" => [
            //         "text" => __("Tags")
            //     ],
            //     "select" => [
            //         "data-type" => "slimselect",
            //         "data-slimselect-auto-close" => "false",
            //         "multiple" => true,
            //         "options" => [
            //             "data" => Tag::all(),
            //             "primaryKey" => "id",
            //             "showKey" => "title",
            //         ]
            //     ],
            //     "small" => [
            //         "text" => __("Add tags to this post.")
            //     ]
            // ],
        ];
    }

    public function routes(){
        return [
            // "store" => ,
            // "update" => ,
            // "destroy" => ,
        ];
    }
}
';