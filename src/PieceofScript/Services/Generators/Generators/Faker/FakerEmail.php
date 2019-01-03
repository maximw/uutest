<?php


namespace PieceofScript\Services\Generators\Generators\Faker;


use PieceofScript\Services\Generators\Generators\FakerGenerator;
use PieceofScript\Services\Values\Hierarchy\BaseLiteral;
use PieceofScript\Services\Values\StringLiteral;

class FakerEmail extends FakerGenerator
{
    const NAME = 'Faker\\email';

    public function run(): BaseLiteral
    {
        return new StringLiteral($this->faker->email);
    }

}