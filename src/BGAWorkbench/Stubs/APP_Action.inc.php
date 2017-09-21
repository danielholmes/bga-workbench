<?php

define('AT_int', 0);        //  an integer
define('AT_posint', 1);     //  a positive integer
define('AT_float', 2);      //  a float
define('AT_email', 3);      //  an email
define('AT_url', 4);        //  a URL
define('AT_bool', 5);       //  1/0/true/false
define('AT_enum', 6);       //  argTypeDetails list the possible values
define('AT_alphanum', 7);   //  only 0-9a-zA-Z_ and space
define('AT_username', 8);   //  TEL username policy: alphanum caracters + accents
define('AT_login', 9);      //  AT_username | AT_email
define('AT_localurl', 10);  //  a local TEL mainsite url (that can go safely after the hash)
define('AT_password', 11);  //  anything (no filter)
define('AT_channel', 12);   //  anything (no filter)
define('AT_numberlist', 13);   //  exemple: 1,4;2,3;-1,2

abstract class APP_Action extends APP_DbObject
{
    /**
     * @var array
     */
    private $args;

    public function __construct()
    {
        $this->args = array();
    }

    /**
     * @param $argName
     * @param $argType
     * @param bool $mandatory
     * @param null $default
     * @param array $argTypeDetails
     * @param bool $bCanFail
     * @return mixed
     */
    protected function getArg($argName, $argType, $mandatory = false, $default = null, $argTypeDetails = array(), $bCanFail = false)
    {
        if (isset($this->args[$argName])) {
            return $this->args[$argName];
        }

        if (!$mandatory) {
            return $default;
        }

        throw new \InvalidArgumentException("Arg {$argName} not found");
    }

    /**
     * @param array $args
     * @return self
     */
    public function stubArgs(array $args)
    {
        $this->args = array_merge($this->args, $args);
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function stubArg($name, $value)
    {
        $this->args[$name] = $value;
        return $this;
    }

    /**
     * @param string $argName
     * @return bool
     */
    protected function isArg($argName) { return true; }

    protected function setAjaxMode() {}
}
