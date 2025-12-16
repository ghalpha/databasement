<?php

test('health check returns success', function () {
    $response = $this->getJson(route('health.up'));

    $response->assertOk()
        ->assertJson(['success' => true]);
});

test('health debug returns application info', function () {
    $response = $this->getJson(route('health.debug'));

    $response->assertOk()
        ->assertJsonStructure([
            'ip_address',
            'url',
            'path',
            'hostname',
            'timestamp',
            'date_time_utc',
            'date_time_app',
            'timezone',
            'secure',
            'is_trusted_proxy',
        ]);
});
