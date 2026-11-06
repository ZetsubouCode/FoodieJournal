<?php

return [
    // Optional named shortcuts. Values use the same slugs/criteria as Explore.
    // Only presets whose options are publicly available are displayed.
    // ['id' => 'soto-semarang', 'label' => 'Soto di Semarang',
    //  'filters' => ['food_type' => ['soto'], 'city' => ['semarang']]],
    'presets' => [
        ['id' => 'coffee', 'label' => 'Kopi', 'filters' => ['food_type' => ['kopi']]],
        ['id' => 'pastry', 'label' => 'Pastry', 'filters' => ['food_type' => ['pastry']]],
        ['id' => 'noodles', 'label' => 'Mi & ramen', 'filters' => ['food_type' => ['mi-ramen']]],
        ['id' => 'bandung', 'label' => 'Bandung', 'filters' => ['city' => ['bandung']]],
        ['id' => 'personal', 'label' => 'Favorit pribadi', 'filters' => ['recommendation' => ['favorit-pribadi']]],
        ['id' => 'sweet', 'label' => 'Pencuci mulut', 'filters' => ['food_type' => ['pencuci-mulut']]],
    ],
];
