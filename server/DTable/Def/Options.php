<?php

namespace DTable\Def;

/**
 * Class Options
 *
 * Helper class to get/set options.
 *
 * @package DTable\Def
 */
abstract class Options
{
    protected $values = [];
    protected $available = [];

    /**
     * option examaple: person, person.name, person.age
     *
     * @param array $available array of available options
     * @param array $default
     */
    public function __construct(array $available, array $default = [])
    {
        $this->available = $available;

        foreach ($default as $name => $value)
        {
            $this->set($name, $value);
        }
    }

    /**
     * check the option is available or not
     *
     * @param $name
     * @return bool
     */
    protected function check($name)
    {
        return in_array($name, $this->available);
    }

    /**
     * set an option value
     *
     * person       => array("person" => <value>)
     * person.name  => array("person" => array("name" => <value>)
     *
     * if value is instance of Options, then it will be parsed and get the options from it
     *
     * @param $name
     * @param $value
     * @return $this
     * @throws \Exception
     */
    protected function set($name, $value)
    {
        if (!$this->check($name))
        {
            throw new \Exception("Invalid option {$name}");
        }

        $name = explode(".", $name);
        $count = count($name);
        $i = 0;
        $values = &$this->values;

        foreach ($name as $part)
        {
            $i++;

            if ($i == $count)
            {
                $values[$part] = $value;
            }
            else
            {
                if (!isset($values[$part]) || !is_array($values[$part]))
                {
                    $values[$part] = array();
                }
            }

            $values = &$values[$part];
        }

        return $this;
    }

    /**
     * Build the array
     *
     * @param $values
     * @return array
     */
    protected function build(&$values)
    {
        $result = [];

        foreach ($values as $key => $value)
        {
            if (is_object($value) && ($value instanceof Options))
            {
                $result[$key] = $value->toArray();
            }
            else if (is_array($value))
            {
                $result[$key] = $this->build($values[$key]);
            }
            else
            {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Get the array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->build($this->values);
    }
}