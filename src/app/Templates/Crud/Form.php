<?php

return '<?php

namespace App\Forms;

class '.$this->model.'Form
{
    public function fields(){
        return ['.$this->formFieldsStr.'
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
