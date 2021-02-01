<?php

return '<?php

namespace App\\Datatables;

use Pveltrop\\DCMS\\Classes\\Datatable;

class ' . $this->model . 'Datatable extends Datatable
{
    /**
     * @param $field
     * @param $value
     */

    public function filter($field=null, $value=null)
    {
        if ($field == "price"){
            $this->query->where($field,"<=",$value);
        } else {
            $this->query->where($field,"=",$value);
        }
    }
}';
