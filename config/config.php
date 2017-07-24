<?php

return [
    'type_unknown' => 'unknown',
    'type_int' => 'int',
    'type_double' => 'double',
    'type_string' => 'string',
    'type_date' => 'date',
    'type_phone' => 'phone',
    'type_email' => 'email',
    'type_classification' => 'classification',
    'type_meta' => 'meta',

    'language' => 1, # Range 200
    'languages' => [
        'english' => [
            'id' => 2,
            'language_id' => 1,
            'iso_code' => 'en',
            'culture_name' => 'en-US',
            'date_format' => 'MM/DD/YYYY',
            'time_format' => 'MM/DD/YYYY h:mmA',
            'first_day_of_week' => 'sunday',
            'translations' => [
                'hu' => 'Angol',
                'en' => 'English'
            ]
        ],
        'hungarian' => [
            'id' => 3,
            'language_id' => 2,
            'iso_code' => 'hu',
            'culture_name' => 'hu-HU',
            'date_format' => 'YYYY.MM.DD.',
            'time_format' => 'YYYY.MM.DD. H:mm',
            'first_day_of_week' => 'monday',
            'translations' => [
                'hu' => 'Magyar',
                'en' => 'Hungarian'
            ]
        ],
    ],
    'default_language' => 'english',
    'relation' => 201, # Range 200 - 250
    'relations' => [
        'containing' => 202,
        'strong_containing' => 203,
    ],
];
