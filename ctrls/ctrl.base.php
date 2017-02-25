<?php

require_once(DUPLICATOR_PLUGIN_PATH . '/classes/utilities/class.util.php');

//Enum used to define the various test statues 
final class DUP_CTRL_Status
{
	const ERROR = -2;
	const FAILED = -1;
	const UNDEFINED = 0;
	const SUCCESS = 1;
}

/**
 * Base class for all controllers
 * 
 * @package Dupicator\ctrls\
 */
class DUP_CTRL_Base
{
	//Represents the name of the Nonce Action
	public $Action;
	
	//The return type valiad options: PHP, JSON-AJAX, JSON
	public $ReturnType = 'JSON-AJAX';

	public function SetResponseType($type)
	{
		$opts = array('PHP', 'JSON-AJAX', 'JSON');
		if (!in_array($type, $opts)) 
		{
			throw new Exception('The $type param must be one of the following: ' . implode(',', $opts) . ' for the following function [' . __FUNCTION__.']');
		}
		$this->ReturnType = $type;
	}
	
	public function PostParamMerge($post)
	{
		$post   = is_array($post) ? $post : array();
		return array_merge($_POST, $post);
	}
}



/**
 * A class structer used to report on controller methods
 * 
 * @package Dupicator\ctrls\
 */
class DUP_CTRL_Report
{
	//Properties
	public $RunTime;
	public $ReturnType;
	public $Results;
	public $Status;
}


/**
 * A class used format all controller responses in a consitent format
 * Every controller response will have a Report and Payload structer
 * The Payload is an array of the result response.  The Report is used
 * report on the overall status of the controller method
 * 
 * @package Dupicator\ctrls\
 */
class DUP_CTRL_Result
{
	//Properties
	public $Report;
	public $Payload;

	private $time_start;
	private $time_end;
	private $CTRL;
	
	function __construct(DUP_CTRL_Base $CTRL_OBJ) 
	{
		DUP_Util::hasCapability('read');
		$this->time_start	= $this->microtimeFloat();
		$this->CTRL			= $CTRL_OBJ;
		
		//Report Data
		$this->Report		=  new DUP_CTRL_Report();
		$this->Report->ReturnType = $CTRL_OBJ->ReturnType;
	}
	
	public function Process($payload, $test = DUP_CTRL_Status::UNDEFINED) 
	{
		if (is_array($this->Payload))
		{
			$this->Payload[] = $payload;
			$this->Report->Results = count($this->Payload);
		} else {
			$this->Payload = $payload;
			$this->Report->Results = 1;
		}
		
		$this->Report->Status = $test;
		$this->getProcessTime();
		
		switch ($this->CTRL->ReturnType) 
		{
			case 'JSON' :	
				return json_encode($this);
				break;
			
			case 'PHP' :
				return $this;
				break;			
			
			default:
				if (!headers_sent())  {
					header('Content-Type: application/json');
				}
				return die(json_encode($this));	
				break;
		}
	}
	
	public function ProcessError($exception) 
	{
		$payload = array();
		$payload['Message'] = $exception->getMessage();
		$payload['File']	= $exception->getFile();
		$payload['Line']	= $exception->getLine();
		$payload['Trace']	= $exception->getTraceAsString();
		$this->Process($payload, DUP_CTRL_Status::ERROR);	
		die(json_encode($this));
	}
	
	private function getProcessTime()
	{
		$this->time_end = $this->microtimeFloat();
		$this->Report->RunTime = $this->time_end - $this->time_start;
	}
	
	private function microtimeFloat()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	

	
}
?>