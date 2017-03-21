<?php

global $Utilities;

$Utilities = [];

/*
Assert each API response (Library Instance) against its JSON configuration (schema)
*/
$Utilities['assertAPIObject'] = function ($testCaseInstance, $apiObjectConfig, $apiObject) {

    // print_r(PHP_EOL);
    foreach ($apiObjectConfig['fields'] as $field => $config) {
        $testCaseInstance->assertObjectHasAttribute($field, $apiObject);

        $assertingMessage = '   * Asserting field '.$field;

        /*
        If the field is required then type is tested:
        */
        if ($config['required'] === true) {
            $assertingMessage = $assertingMessage.'/'.$config['type'];
            $testCaseInstance->assertInternalType($config['type'], $apiObject->{$field});
        /*
        If the field is not required but it exists then type is tested:
        */
        } elseif (!is_null($apiObject->{$field})) {
            $assertingMessage = $assertingMessage.'/'.$config['type'];
            $testCaseInstance->assertInternalType($config['type'], $apiObject->{$field});
        }//End of if

        // print_r($assertingMessage.PHP_EOL);
    }
};
