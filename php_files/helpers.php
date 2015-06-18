<?php

abstract class Enum
{
    final public function __construct($value)
    {
        $c = new ReflectionClass($this);
        if(!in_array($value, $c->getConstants())) {
            throw IllegalArgumentException();
        }
        $this->value = $value;
    }

    final public function __toString()
    {
        return $this->value;
    }
}

function errorExit($line){
    echo json_encode(array("error" => $line));
    exit();
}

function init(){
    date_default_timezone_set('America/Los_Angeles');

    header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
    header('Pragma: no-cache'); // HTTP 1.0.
    header('Expires: 0');
}

function log_debug( $log_line ) {
        $log_file = './gridgen.log';
        $time_stamp = new DateTime();
        $log_line = $time_stamp -> format("Y-m-d H:i:s") . $log_line . "\n";
        file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
}

function sort($a, $b) {
	return strlen($b) - strlen($a);
}


?>
