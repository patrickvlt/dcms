<?php
return
'$this->jExcel = [
        // Which request attribute belongs to which jExcel column? e.g. "name" => 0, "created_at" => 3
        "columns" => ['.$jExcelColumnsStr.'
        ],
        // How to autocorrect data?
        "autocorrect" => ['.$jExcelCorrectStr.'
        ],
        // Responses when attempting to import
        "responses" => ['.$jExcelResponseStr.'
        ]
        ];
';
