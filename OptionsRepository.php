<?php

namespace Laraish\Options;

use Laraish\Contracts\Options\OptionsRepository as OptionsRepositoryContract;

class OptionsRepository implements OptionsRepositoryContract
{
    /**
     * The option-name.
     * @var string
     */
    private $optionName;

    /**
     * The retrieved data from `get_option('optionName')`.
     * @var array
     */
    private $options = [];

    /**
     * All the the instances this class has created.
     * The key of this array is a `optionName`.
     * @var static[]
     */
    private static $instances = [];

    /**
     * @param $optionName
     *
     * @return static
     */
    final public static function getInstance($optionName)
    {
        if (isset(static::$instances[$optionName])) {
            return static::$instances[$optionName];
        }

        return static::$instances[$optionName] = new static($optionName);
    }

    /**
     * Options constructor.
     * If instantiate this class more than once with the same `optionName`,
     * the subsequent instantiation always returns the same instance that was created for the first time.
     *
     * @param string $optionName
     */
    public function __construct($optionName)
    {
        $options = get_option($optionName);

        // If the option does not exist or does not have a value,
        // then add a new option to the database.
        if ($options === false) {
            $options = [];
            add_option($optionName, $options);
        }

        $this->optionName = $optionName;
        $this->options    = (array)$options;

        add_action("update_option_{$optionName}", [$this, 'syncOption'], 10, 2);
    }


    /**
     * Synchronize with the latest value of the option.
     *
     * @param $old_value
     * @param $value
     */
    final public function syncOption($old_value, $value)
    {
        $this->options = (array)$value;
    }

    /**
     * Get a particular value of an option in an options array.
     *
     * @param $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->options)) {
            return $this->options[$key];
        }

        $this->set($key, $default);

        return $default;
    }

    /**
     * Update the option.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return boolean
     */
    public function set($key, $value)
    {
        $this->options[$key] = $value;

        return update_option($this->optionName(), $this->options);
    }

    /**
     * Delete the option from database.
     *
     * @return boolean
     */
    public function delete()
    {
        return delete_option($this->optionName());
    }

    /**
     * Get the option-name
     * @return string
     */
    final public function optionName()
    {
        return $this->optionName;
    }
}