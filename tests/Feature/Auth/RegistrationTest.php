<?php

// Registration is disabled in this project (admin creates accounts).
// Fortify's Features::registration() is commented out in config/fortify.php.

test('registration route is not available', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});