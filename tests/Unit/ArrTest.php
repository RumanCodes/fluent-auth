<?php

namespace FluentAuth\Tests\Unit;

use FluentAuth\App\Helpers\Arr;

class ArrTest extends BaseTestCase
{
    public function testHas()
    {
        $array = ['name' => 'John', 'age' => 30];
        $this->assertTrue(Arr::has($array, 'name'));
        $this->assertFalse(Arr::has($array, 'email'));

        $array = ['user' => ['name' => 'John', 'email' => 'john@example.com']];
        $this->assertTrue(Arr::has($array, 'user.name'));
        $this->assertFalse(Arr::has($array, 'user.age'));

        $this->assertTrue(Arr::has($array, ['user.name', 'user.email']));
        $this->assertFalse(Arr::has($array, ['user.name', 'user.age']));
    }

    public function testGet()
    {
        $array = ['name' => 'John', 'age' => 30];
        $this->assertEquals('John', Arr::get($array, 'name'));
        $this->assertEquals(30, Arr::get($array, 'age'));
        $this->assertEquals('default', Arr::get($array, 'email', 'default'));

        $array = ['user' => ['name' => 'John', 'email' => 'john@example.com']];
        $this->assertEquals('John', Arr::get($array, 'user.name'));
        $this->assertEquals('john@example.com', Arr::get($array, 'user.email'));
        $this->assertEquals('default', Arr::get($array, 'user.age', 'default'));

        $this->assertEquals($array, Arr::get($array, null));
        $this->assertEquals('default', Arr::get([], 'name', 'default'));
    }

    public function testSet()
    {
        $array = [];
        Arr::set($array, 'name', 'John');
        $this->assertEquals(['name' => 'John'], $array);

        $array = [];
        Arr::set($array, 'user.name', 'John');
        $this->assertEquals(['user' => ['name' => 'John']], $array);

        $array = ['user' => []];
        Arr::set($array, 'user.email', 'john@example.com');
        $this->assertEquals(['user' => ['email' => 'john@example.com']], $array);

        $array = ['old' => 'value'];
        $result = Arr::set($array, null, ['new' => 'value']);
        $this->assertEquals(['new' => 'value'], $array);
        $this->assertEquals(['new' => 'value'], $result);
    }

    public function testOnly()
    {
        $array = [
            'name' => 'John',
            'email' => 'john@example.com',
            'age' => 30,
            'city' => 'New York'
        ];

        $result = Arr::only($array, 'name');
        $this->assertEquals(['name' => 'John'], $result);

        $result = Arr::only($array, ['name', 'email']);
        $this->assertEquals(['name' => 'John', 'email' => 'john@example.com'], $result);

        $array = ['user' => ['name' => 'John', 'email' => 'john@example.com']];
        $result = Arr::only($array, ['user.name']);
        $this->assertEquals(['user' => ['name' => 'John']], $result);
    }

    public function testExcept()
    {
        $array = [
            'name' => 'John',
            'email' => 'john@example.com',
            'age' => 30,
            'city' => 'New York'
        ];

        $result = Arr::except($array, 'age');
        $expected = [
            'name' => 'John',
            'email' => 'john@example.com',
            'city' => 'New York'
        ];
        $this->assertEquals($expected, $result);

        $result = Arr::except($array, ['age', 'city']);
        $expected = [
            'name' => 'John',
            'email' => 'john@example.com'
        ];
        $this->assertEquals($expected, $result);
    }

    public function testForget()
    {
        $array = [
            'name' => 'John',
            'user' => [
                'email' => 'john@example.com',
                'age' => 30
            ]
        ];

        Arr::forget($array, 'name');
        $this->assertFalse(isset($array['name']));
        $this->assertTrue(isset($array['user']));

        Arr::forget($array, 'user.age');
        $this->assertTrue(isset($array['user']['email']));
        $this->assertFalse(isset($array['user']['age']));
    }

    public function testFirst()
    {
        $array = [1, 2, 3, 4, 5];

        $result = Arr::first($array, function ($key, $value) {
            return $value % 2 === 0;
        });
        $this->assertEquals(2, $result);

        $this->assertEquals('default', Arr::first($array, function ($key, $value) {
            return $value > 10;
        }, 'default'));
    }

    public function testAccessible()
    {
        $this->assertTrue(Arr::accessible(['name' => 'John']));
        $this->assertFalse(Arr::accessible('string'));
        $this->assertFalse(Arr::accessible(123));

        $arrayAccess = new \ArrayObject(['name' => 'John']);
        $this->assertTrue(Arr::accessible($arrayAccess));
    }

    public function testExists()
    {
        $array = ['name' => 'John'];
        $this->assertTrue(Arr::exists($array, 'name'));
        $this->assertFalse(Arr::exists($array, 'age'));

        $arrayAccess = new \ArrayObject(['name' => 'John']);
        $this->assertTrue(Arr::exists($arrayAccess, 'name'));
    }

    public function testValue()
    {
        $this->assertEquals('test', Arr::value('test'));

        $closure = function () { return 'closure_result'; };
        $this->assertEquals('closure_result', Arr::value($closure));
    }

    public function testDot()
    {
        $array = [
            'user' => [
                'name' => 'John',
                'profile' => [
                    'age' => 30,
                    'city' => 'New York'
                ]
            ],
            'settings' => [
                'theme' => 'dark'
            ]
        ];

        $result = Arr::dot($array);
        $expected = [
            'user.name' => 'John',
            'user.profile.age' => 30,
            'user.profile.city' => 'New York',
            'settings.theme' => 'dark'
        ];

        $this->assertEquals($expected, $result);
    }

    public function testIsTrue()
    {
        $this->assertTrue(Arr::isTrue(['active' => true], 'active'));
        $this->assertTrue(Arr::isTrue(['active' => 1], 'active'));
        $this->assertTrue(Arr::isTrue(['active' => 'yes'], 'active'));

        $this->assertFalse(Arr::isTrue(['active' => false], 'active'));
        $this->assertFalse(Arr::isTrue(['active' => 0], 'active'));
        $this->assertFalse(Arr::isTrue(['active' => 'false'], 'active'));
        $this->assertFalse(Arr::isTrue(['active' => '0'], 'active'));

        $this->assertFalse(Arr::isTrue([], 'active'));
    }
}
