<?php


namespace FMDataAPI;


class ScriptParameter
{
    /** @var string */
    protected $layout;

    /** @var string */
    protected $script;

    /** @var ?string */
    protected $parameter;

    /**
     * ScriptParameter constructor.
     * @param $layout
     * @param $script
     * @param $parameter
     */
    public function __construct($layout, $script, $parameter = null)
    {
        $this->layout = $layout;
        $this->script = $script;
        $this->parameter = $parameter;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * @return ?string
     */
    public function getParameter()
    {
        return $this->parameter;
    }


}