<?php

/**
 * Base class for all controllers
 * 
 * @package Dupicator\ctrls\
 */
class DUP_CTRL_Base
{

}

//Enum used to define the various test statues 
final class DUP_CTRL_ResultStatus
{
	const ERROR = -2;
	const FAILED = -1;
	const UNDEFINED = 0;
	const SUCCESS = 1;
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
	public $Payload = array();
	
	private $time_start;
	private $time_end;
	
	public function __construct() 
	{
		$this->time_start = $this->microtimeFloat();
		$this->Report   =  new DUP_CTRL_Report();
	}
	
	public function Process($payload, $test = DUP_CTRL_ResultStatus::UNDEFINED) 
	{
		$this->Payload[] = $payload;
		$this->Report->Results = count($this->Payload);
		$this->Report->Status = $test;
		$this->getProcessTime();
		die(json_encode($this));
	}
	
	public function ProcessError($exception) 
	{
		$payload = array();
		$payload['Message'] = $exception->getMessage();
		$payload['File']	= $exception->getFile();
		$payload['Line']	= $exception->getLine();
		$payload['Trace']	= $exception->getTraceAsString();
		$this->Process($payload, DUP_CTRL_ResultStatus::ERROR);	
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