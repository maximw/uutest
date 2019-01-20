<?php


namespace PieceofScript\Services\Generators\Generators\LocalStorage;


use PieceofScript\Services\Generators\Generators\LocalStorage\Services\LocalStorage;
use PieceofScript\Services\Generators\Generators\ParametrizedGenerator;

abstract class BaseLocalStorageGenerator extends ParametrizedGenerator
{
    /** @var LocalStorage */
    protected $localStorage;

    public function __construct(LocalStorage $localStorage)
    {
        parent::__construct();
        $this->localStorage = $localStorage;
    }

}