<?php
namespace WebFW\Externals;

class PHPTemplate
{

	private $vars; /// Holds all the template variables
	private $file;

	/**
	* Constructor
	*
	* @param $file string the file name you want to load
	*/
	public function __construct($file = null, $directory = '', $altdirectory = '', $p_die_on_file_not_found = false)
	{
		//
		// file is forced into lowercase ...
		//

		$lfile=strtolower($file);
		if (file_exists($directory . $lfile)){
			//
			// If template exists in application specific directory
			// load it ...
			//
			$this->file = $directory . $lfile;
		} else {
			if (file_exists($altdirectory . $lfile)){
				//
				// If template exists in alternative (tipicaly common) application specific directory
				// load it ...
				//
				$this->file = $altdirectory . $lfile;
			} else {
				//
				// There is no appropriate template file
				// well...it's dying again ...
				//
				if( $p_die_on_file_not_found )
				{
					die('No template could be found (' . $lfile . ')<br>Either in ' . $directory . '<br>Or in ' . $altdirectory . '<br>');
				}
				$this->file = $altdirectory . $lfile;
			}
		}
	}

	/**
	* Sets a template variable.
	*/
	public function set($name, $value)
	{
		$this->vars[$name] = $value instanceof PHPTemplate ? $value->fetch() : $value;
	}

	public function __set($name, $value)
	{
		$this->set($name, $value);
	}

	/**
	* Open, parse, and return the template file.
	*
	* @param $file string the template file name
	*/
	public function fetch($file = false)
	{
		if(!$file) {
			$file = $this->file;
		}

		if( !is_readable( $file ) )
		{
			throw new \WebFW\Core\Exception( 'Cannot load template file: ' . $file );
		}

		//var_dump( $file );
		if(is_array($this->vars)){	 // if there are any vars
		extract($this->vars);	  // Extract these vars to local namespace
		}
		ob_start();					// Start output buffering
		include($file);				// Include the file
		$contents = ob_get_contents(); // Get the contents of the buffer
		ob_end_clean();				// End buffering and discard

		return $contents;			  // Return the contents
	}

	public function parse()
	{
	}

	public function out($file = false)
	{
		echo $this->fetch($file);
	}

	public function mergeArray($array){
		//
		// Merge array implementation
		//
		if(is_array($array)){
			foreach($array as $key=>$value){
				$this->set($key, $value);
			}
		}
	}

}
