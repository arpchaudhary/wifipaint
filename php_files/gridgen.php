<?php

//Elegant solution using the reflectionclass 
//Creates a basic enum type implementation
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

class Point{
	public $x;
	public $y;

	//Default values for simpler implementation
	public function __construct($x = 0, $y = 0){
		$this->x = is_numeric($x) ? $x : 0;
		$this->y = is_numeric($y) ? $y : 0;
	}

	public function __toString(){
		return "(" . $this->x . "," . $this->y . ")";
	}

	//convenience wrapper to decode from string
	//On error throws IllegalArgumentException instead of failing silently
	public function fromString($point_str){
		if(is_string($point_str) && preg_match("/\([0-9]+,[0-9]+\)/", $point_str)){
			$proc_str = substr($point_str, 1, -1); //This should remove the brackets
			$values = explode(',', $proc_str); //Explode into two parts based no comma
			$this->x = $values[0];
			$this->y = $values[1];
		}else
			throw IllegalArgumentException();

		//To allow chaining of calls
		return $this;
	}

	public function randomInit($min_x = 0, $min_y = 0, $max_x = 100, $max_y = 100){
		$this->x = rand($min_x, $max_x);
		$this->y = rand($min_y, $max_y);
		return $this;
	}
}

//A grid is nothing but a start point and end point and data storage
//This allows us to make a rectangular grid at any point in time
class Grid{
	private $data_map;
	private $start_point;
	private $end_point;

	public function __construct($start_point, $end_point){
		$this->data_map = array();
		$this->start_point = (isset($start_point) && ($start_point instanceof Point)) ? $start_point : new Point();
		$this->end_point = (isset($end_point) && ($end_point instanceof Point)) ? $end_point : new Point();
	}

	private function is_valid_grid_point($point) {
		return 	($point instanceof Point) &&
				($point->x >= $this->start_point->x && $point->y >= $this->start_point->y) && 
				($point->x <= $this->end_point->x && $point->y <= $this->end_point->y);
	}

	private function is_valid_data($character) {
		return (is_string($character) && strlen($character) == 1);
	}

	public function getGrid(){
		return $this->data_map;
	}

	public function getStartPoint(){
		return $this->start_point;
	}

	public function getEndPoint(){
		return $this->end_point;
	}

	public function setData($point, $character){
		if($this->is_valid_grid_point($point) && $this->is_valid_data($character)){
			//This basically converts the co-ordinates to a string that PHP arrays can understand.
			//Shows an interesting usage
			$this->data_map[(string)$point] = $character;
		}
	}

	
	public function check_point_exists($point){
		//echo "Array key exists : " . ((array_key_exists((string)$point, $this->data_map)) ? "yes" : "no");
		return ($this->is_valid_grid_point($point) && array_key_exists((string)$point, $this->data_map));
	}
	
	public function getData($point){
		if($this->check_point_exists($point))
			return $this->data_map[(string)$point];
		return NULL;
	}

	public function __toString(){
		$result = "";
		foreach ($this->data_map as $point_str => $character) {
			$result .= $point_str . ' : ' . $character . PHP_EOL;
		}
		return $result;
	}
}


//Enum is a user defined class and not a 
class Layout extends Enum{
	const HORIZONTAL = "HORIZONTAL";
	const VERTICAL = "VERTICAL";
	const DIAGONAL = "DIAGONAL";
}

class Direction extends Enum{
	const TOP_LEFT = 1;
	const TOP = 2;
	const TOP_RIGHT = 3;
	const LEFT = 4;
	const RIGHT = 5;
	const BOTTOM_LEFT = 6;
	const BOTTOM = 7;
	const BOTTOM_RIGHT = 8;
}


//The Grid maker generates only square grids as of now
//This functionality can be easily extended
class GridMaker{
	
	private $grid;
	private $length;
	private $alpha_list;
	private $word_list;

	public function __construct($length, $word_list){
		
		$start_point = new Point();
		$end_point = new Point($length - 1, $length -1);
		$this->length = $length;
		$this->word_list = $word_list;
		$this->alpha_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$this->grid = new Grid($start_point, $end_point);

		

	}

	private function setRandomData(){
		$random_str = "HZABHAYUYIXEIORATKMHNMURIDGAIPKPKBMLNMQINAEOOSRTASCMNTHMHNURAVSU";
		$i = $j =  0;
		$len = 8;
		foreach(str_split($random_str) as $character){
			$this->grid->setData(new Point($i,$j), $character);	
			$j++;
			if($j != 0 && $j % $len == 0){
				$i++;
				$j = 0;
			}
			if($i != 0 && $i % $len == 0){
				break;
			}
		}
	}

	public function runGridEngine(){
		//this is a proxy call for getRandomData
		$this->setRandomData();

		//This will fill the raw grid.
		
		// foreach($word_list as $word){

		// 	$randomPoint = (new Point()) -> randomInit($this->grid->getStartPoint()->x,$this->grid->getStartPoint()->y,
		// 												$this->grid->getEndPoint()->x, $this->grid->getEndPoint()->y);
		// }

	}

	public function getProcessedGrid(){
		$this->runGridEngine();
		$result = array();
		$result["len"] = $this->length;
		$result["grid"] = $this->grid->getGrid();
		$result["word_list"] = $this->word_list;
		//$grid_arr = array();
		// for($i = 0; $i < $this->length; $i++){
		// 	//$curr_row = "";
		// 	// for($j = 0; $j < $this->length; $j++){
		// 	// 	$p = new Point($i, $j);
		// 	// 	$data = $this->grid->getData($p);				
		// 	// 	$curr_row .= ($data != NULL) ? $data : " "; 
		// 	// }
		// 	$grid_arr[$i] = $curr_row;
		// 	//array_push($grid_arr, $curr_row);
		// }
		//$result["grid"] = $grid_arr;
		return json_encode($result, JSON_PRETTY_PRINT);
	}



	public function getRawGrid(){
		return (string)$this->grid;
	}

}

//To test the output
//we are giving the word list right here
//We can also put it in redis in different sets 
//so that php does not have to take care of reading in general

$game_grid = new GridMaker(8, array("ARPIT", "ABHAY", "VARUN", "ANKIT", "HIMANSHU", "URMILESH", "AMAN", "MONK"));
echo $game_grid->getProcessedGrid();
//echo $game_grid->getProcessedGrid();
?>
