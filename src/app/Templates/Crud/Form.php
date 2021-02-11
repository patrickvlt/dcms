<?php

return '<?php

namespace App\Forms;

'.$formImports.'
class '.$this->model.'Form
{
    public function fields(){
        return ['.$formFieldsStr.'
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
