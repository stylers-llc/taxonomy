<?php

Route::group([
    'middleware' => ['web'],
    'prefix' => 'stylerstaxonomy',
    'namespace' => 'Stylers\Taxonomy\Controllers'
], function () {
    Route::resource('/description', 'DescriptionController', ['except' => ['show', 'index']]);

    Route::put('/taxonomy/priorities', 'TaxonomyController@setPriorities');
    Route::resource('/taxonomy', 'TaxonomyController', ['except' => ['show', 'index']]);
});

Route::group([
    'middleware' => ['web'],
    'prefix' => 'stylerstaxonomy',
    'namespace' => 'Stylers\Taxonomy\Controllers'
], function () {
    Route::resource('/description', 'DescriptionController', ['only' => ['show', 'index']]);

    Route::get('/taxonomy/descendants/{id}', 'TaxonomyController@getDescendants');
    Route::get('/taxonomy/children/{id?}', 'TaxonomyController@getChildren');
    Route::get('/taxonomy/leaves/{id}', 'TaxonomyController@getLeaves');
    Route::get('/taxonomy/ancestors/{id}', 'TaxonomyController@getAncestors');
    Route::get('/taxonomy/ancestors-and-self/{id}', 'TaxonomyController@getAncestorsAndSelf');
    Route::get('/taxonomy/siblings/{id}', 'TaxonomyController@getSiblings');
    Route::resource('/taxonomy', 'TaxonomyController', ['only' => ['show', 'index']]);
});