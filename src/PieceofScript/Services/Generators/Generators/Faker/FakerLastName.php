<?php


namespace PieceofScript\Services\Generators\Generators\Faker;


use PieceofScript\Services\Generators\Generators\FakerGenerator;
use PieceofScript\Services\Values\Hierarchy\BaseLiteral;
use PieceofScript\Services\Values\StringLiteral;

class FakerLastName extends FakerGenerator
{
    const NAME = 'Faker\\lastName';

    public function run(): BaseLiteral
    {
        $gender = null;
        if (isset($this->arguments[0])) {
            $gender = $this->arguments[0]->toString()->getValue();
            if (strtolower($gender) !== 'male' && strtolower($gender) !== 'female') {
                $gender = null;
            }
        }

        return new StringLiteral($this->faker->lastName($gender));
    }

}