<?php
class Point{
	public $x;
	public $y;

	function __construct($x = 0, $y = 0) {
		$this->x = is_numeric($x) ? $x : 0;
		$this->y = is_numeric($y) ? $y : 0;
	}

	function fromString($point_str){
		if(is_string($point_str) && preg_match("/\([0-9]+,[0-9]+\)/", $point_str)){
			$proc_str = substr($point_str, 1, -1); //This should remove the brackets
			$values = explode(',', $proc_str); //Explode into two parts based no comma
			$this->x = $values[0];
			$this->y = $values[1];
		}else
			throw IllegalArgumentException();
	}

	public function randomInit($max_x = 100, $max_y = 100){
		$this->x = rand(0,$max_x);
		$this->y = rand(0,$max_y);
		return $this;
	}

	function __toString(){
		return "(" . $this->x . "," . $this->y . ")";
	}

}

$p1 = new Point(2231,3123);
$p2 = new Point(4,5);

//echo "p1 == p2 : " .(($p1 == $p2) ? "TRUE" : "FALSE") . PHP_EOL;
//echo "p1 === p2 : " .(($p1 === $p2) ? "TRUE" : "FALSE") . PHP_EOL;

//echo (string)$p1 . PHP_EOL;

$str_p = (string)$p1;
//echo "P1 : " . $str_p . PHP_EOL;

//Now let's create a new point with the same co-ordinates
$p3 = new Point();
$p3->fromString((string)$p1);

//echo "P3 : " . (string)$p3 . PHP_EOL;

//echo "p1 == p2 : " .(($p1 == $p3) ? "TRUE" : "FALSE") . PHP_EOL;

$p4 = (new Point())-> randomInit(10,10);
echo "P4 : " . (string)$p4;


?>