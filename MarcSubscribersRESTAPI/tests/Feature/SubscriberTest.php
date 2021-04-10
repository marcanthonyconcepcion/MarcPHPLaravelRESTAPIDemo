<?php
/*
 * Copyright (c) 2021.
 * Marc Concepcion
 * marcanthonyconcepcion@gmail.com
 */

namespace Tests\Feature;
use Tests\TestCase;
use App\Models\Subscriber;


class SubscriberTest extends TestCase
{
    public function testPost()
    {
        $payload = [    'email_address' => 'riseofskywalker@starwars.com',
                        'first_name' => 'Rey',
                        'last_name' => 'Palpatine',
                        'activation_flag' => false  ];
        $this->json('POST', '/api/subscribers?email_address='.$payload['email_address'].
                                                        '&last_name='.$payload['last_name'].
                                                        '&first_name='.$payload['first_name'], $payload)
            ->assertStatus(201)
            ->assertJson([  'email_address' => 'riseofskywalker@starwars.com',
                            'first_name' => 'Rey',
                            'last_name' => 'Palpatine',
                            'activation_flag' => false  ]);
    }

    public function testGet()
    {
        $subscriber = Subscriber::factory(Subscriber::class)->create([
            'email_address' => 'riseofskywalker@starwars.com',
            'first_name' => 'Rey',
            'last_name' => 'Palpatine',
            'activation_flag' => false
        ]);
        $this->json('GET', '/api/subscribers/'.$subscriber->id.'/')->assertStatus(200);
    }

    public function testGetAll()
    {
        Subscriber::factory(Subscriber::class)->create([
            'email_address' => 'riseofskywalker@starwars.com',
            'first_name' => 'Rey',
            'last_name' => 'Palpatine',
            'activation_flag' => false
        ]);
        $this->json('GET', '/api/subscribers')->assertStatus(200);
    }

    public function testPut()
    {
        $subscriber = Subscriber::factory(Subscriber::class)->create([
            'email_address' => 'riseofskywalker@starwars.com',
            'first_name' => 'Rey',
            'last_name' => 'Palpatine',
            'activation_flag' => false
        ]);
        $payload = ['last_name' => 'Skywalker'];
        $this->json('PUT', '/api/subscribers/'.$subscriber->id.'?last_name='.$payload['last_name'], $payload)
            ->assertStatus(200)
            ->assertJson([  'email_address' => 'riseofskywalker@starwars.com',
                            'first_name' => 'Rey',
                            'last_name' => 'Skywalker',
                            'activation_flag' => false  ]);
    }

    public function testPatch()
    {
        $subscriber = Subscriber::factory(Subscriber::class)->create([
            'email_address' => 'riseofskywalker@starwars.com',
            'first_name' => 'Rey',
            'last_name' => 'Palpatine',
            'activation_flag' => false
        ]);
        $payload = ['last_name' => 'Skywalker'];
        $this->json('PATCH', '/api/subscribers/'.$subscriber->id.'?last_name='.$payload['last_name'], $payload)
             ->assertStatus(200)
             ->assertJson([ 'email_address' => 'riseofskywalker@starwars.com',
                            'first_name' => 'Rey',
                            'last_name' => 'Skywalker',
                            'activation_flag' => false  ]);
    }

    public function testDelete()
    {
        $subscriber = Subscriber::factory(Subscriber::class)->create([
            'email_address' => 'riseofskywalker@starwars.com',
            'first_name' => 'Rey',
            'last_name' => 'Palpatine',
            'activation_flag' => false
        ]);
        $this->json('DELETE', '/api/subscribers/'.$subscriber->id)->assertStatus(204);
    }

    public function testGetNonExistentRecord()
    {
        $this->json('GET', '/api/subscribers/4000/')->assertStatus(404);
    }

    public function testGetNonExistentResource()
    {
        $this->json('GET', '/api/notsubscribers/')->assertStatus(400);
    }

    public function testGetNonAPICall()
    {
        $this->json('GET', '/nonapi')->assertStatus(404);
    }

    public function testNonCRUDMethod()
    {
        $this->json('TRACE', '/api/subscribers/')->assertStatus(405);
        $this->json('PATCH', '/api/subscribers/')->assertStatus(405);
    }

    public function testPostWithID()
    {
        $payload = [
            'email_address' => 'riseofskywalker@starwars.com',
            'first_name' => 'Rey',
            'last_name' => 'Palpatine',
            'activation_flag' => false
        ];
        $this->json('POST', '/api/subscribers/1', $payload)->assertStatus(405);
    }

    public function testPostWithoutPayload()
    {
        $this->json('POST', '/api/subscribers/')->assertStatus(405);
    }

    public function testPutWithoutID()
    {
        $payload = [
            'email_address' => 'riseofskywalker@starwars.com',
            'first_name' => 'Rey',
            'last_name' => 'Palpatine',
            'activation_flag' => false
        ];
        $this->json('PUT', '/api/subscribers/', $payload)->assertStatus(405);
    }

    public function testPatchWithoutID()
    {
        $payload = [
            'email_address' => 'riseofskywalker@starwars.com',
            'first_name' => 'Rey',
            'last_name' => 'Palpatine',
            'activation_flag' => false
        ];
        $this->json('PATCH', '/api/subscribers/', $payload)->assertStatus(405);
    }

    public function testPutWithoutPayload()
    {
        $subscriber = Subscriber::factory(Subscriber::class)->create([
            'email_address' => 'riseofskywalker@starwars.com',
            'first_name' => 'Rey',
            'last_name' => 'Palpatine',
            'activation_flag' => false
        ]);
        $this->json('PUT', '/api/subscribers/'.$subscriber->id)->assertStatus(405);
    }

    public function testPatchWithoutPayload()
    {
        $subscriber = Subscriber::factory(Subscriber::class)->create([
            'email_address' => 'riseofskywalker@starwars.com',
            'first_name' => 'Rey',
            'last_name' => 'Palpatine',
            'activation_flag' => false
        ]);
        $this->json('PATCH', '/api/subscribers/'.$subscriber->id)->assertStatus(405);
    }

    public function testDeleteWithoutID()
    {
        $this->json('DELETE', '/api/subscribers/')->assertStatus(405);
    }
}
