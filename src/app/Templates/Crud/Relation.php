<?php
return '
    public function '.$column['foreign']['relationFunction'].'()
    {
        return $this->'.$column['foreign']['relation'].'('.$column['foreign']['class'].'::class, \''.$column['foreign']['foreign_column'].'\', \''.$column['foreign']['references'].'\');
    }
    ';