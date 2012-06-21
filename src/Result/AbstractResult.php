<?php

namespace LimocartPhpSdk\Result;

abstract class AbstractResult
{

    /**
     * @var boolean
     */
    protected $_success = false;

    /**
     * @var array
     */
    protected $_variables = array();

    /**
     * @var array
     */
    protected $_responseInfo = array();

    /**
     * @param boolean $success
     */
    public function setSuccess($success)
    {
        $this->_success = $success;
    }

    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->_success;
    }

    /**
     * @param array $variables
     */
    public function setVariables($variables)
    {
        $this->_variables = $variables;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->_variables;
    }

    /**
     * @param array $responseInfo
     */
    public function setResponseInfo($responseInfo)
    {
        $this->_responseInfo = $responseInfo;
    }

    /**
     * @return array
     */
    public function getResponseInfo()
    {
        return $this->_responseInfo;
    }

}
