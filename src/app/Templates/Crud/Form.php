<?php

return '<?php

namespace App\Forms;

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
