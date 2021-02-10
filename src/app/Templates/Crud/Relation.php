<?php
return '
    public function '.$column['method'].'()
    {
        return $this->'.$column['relation'].'('.$column['class'].'::class, \''.$column['name'].'\', \''.$column['value'].'\');
    }
    ';
