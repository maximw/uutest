<?php


namespace PieceofScript\Services\Generators\Generators\Faker;


use PieceofScript\Services\Generators\Generators\FakerGenerator;
use PieceofScript\Services\Values\Hierarchy\BaseLiteral;
use PieceofScript\Services\Values\StringLiteral;

class FakerColorRgb extends FakerGenerator
{
    const NAME = 'Faker\\colorRgb';

    public function run(): BaseLiteral
    {
        return new StringLiteral($this->faker->rgbColor);
    }

}