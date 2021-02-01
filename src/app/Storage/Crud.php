<?php

// This is an example file with pre-defined attributes for the Crud command.

$this->model = 'Foo';
$this->prefix = 'foo';
$this->amountToSeed = 10;

$this->columns = [
    "title" => [
        "attributes" => [
            "name" => "title",
            "type" => "string",
            "nullable" => 0,
            "unsigned" => 0,
            "required" => 1,
        ],
        "validation" => [
            0 => "min:1",
            1 => "string"
        ],
        "seed" => '$faker->word()',
    ],
    "user_id" => [
        "attributes" => [
            "name" => "user_id",
            "type" => "bigInteger",
            "nullable" => 0,
            "unsigned" => 1,
            "required" => 1,
        ],
        "foreign" => [
            "foreign_column" => "user_id",
            "references" => "id",
            "class" => "User",
            "table" => "users",
            "relation" => "belongsTo",
            "relationFunction" => "user",
            "onUpdate" => "cascade",
            "onDelete" => "cascade",
        ],
        "validation" => [
            0 => "min:0",
            1 => "exists:users,id",
        ],
        "seed" => '$faker->randomElement(User::all()->pluck("id"))'
    ],
];
