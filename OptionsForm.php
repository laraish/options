<?php

namespace Laraish\Options;

use Laraish\Contracts\Options\OptionsForm as OptionsFormContract;
use Laraish\Contracts\Options\OptionsRepository as OptionsRepositoryContract;

class OptionsForm implements OptionsFormContract
{
    /**
     * The Options object to be attached to this field.
     * @var OptionsRepositoryContract
     */
    private $options;

    /**
     * The options page that this field resides.
     * @var string
     */
    private $optionsPage;

    /**
     * OptionsForm constructor.
     *
     * @param OptionsRepositoryContract $options
     * @param string $optionsPage
     */
    public function __construct(OptionsRepositoryContract $options, $optionsPage)
    {
        $this->options     = $options;
        $this->optionsPage = $optionsPage;
    }

    final private function generate($type, $name, array $configs)
    {
        /** @var \Laraish\Contracts\Options\OptionsFieldGenerator $generator */

        $generatorClassName = '\\Laraish\\Options\\OptionsFieldGenerator\\' . ucfirst($type) . 'FieldGenerator';
        $generator          = new $generatorClassName($name, $this->options, $this->optionsPage, $configs);

        return $generator->generate();
    }

    final public function text($name, array $configs = [])
    {
        return $this->generate('text', $name, $configs);
    }


    final public function hidden($name, array $configs = [])
    {
        return $this->generate('hidden', $name, $configs);
    }


    final public function number($name, array $configs = [])
    {
        return $this->generate('number', $name, $configs);
    }


    final public function url($name, array $configs = [])
    {
        return $this->generate('url', $name, $configs);
    }


    final public function email($name, array $configs = [])
    {
        return $this->generate('email', $name, $configs);
    }


    final public function color($name, array $configs = [])
    {
        return $this->generate('color', $name, $configs);
    }


    final public function search($name, array $configs = [])
    {
        return $this->generate('search', $name, $configs);
    }


    final public function date($name, array $configs = [])
    {
        return $this->generate('date', $name, $configs);
    }


    final public function time($name, array $configs = [])
    {
        return $this->generate('time', $name, $configs);
    }


    final public function range($name, array $configs = [])
    {
        return $this->generate('range', $name, $configs);
    }


    final public function checkbox($name, array $configs = [])
    {
        return $this->generate('checkbox', $name, $configs);
    }


    final public function checkboxes($name, array $configs = [])
    {
        return $this->generate('checkboxes', $name, $configs);
    }


    final public function radios($name, array $configs = [])
    {
        return $this->generate('radios', $name, $configs);
    }


    final public function file($name, array $configs = [])
    {
        return $this->generate('file', $name, $configs);
    }

    final public function media($name, array $configs = [])
    {
        return $this->generate('media', $name, $configs);
    }

    final public function password($name, array $configs = [])
    {
        return $this->generate('password', $name, $configs);
    }


    final public function textarea($name, array $configs = [])
    {
        return $this->generate('textarea', $name, $configs);
    }


    final public function select($name, array $configs = [])
    {
        return $this->generate('select', $name, $configs);
    }
}