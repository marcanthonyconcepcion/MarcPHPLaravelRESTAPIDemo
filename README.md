# Marc PHP Laravel REST API Demo

## PHP Laravel Installation Instructions

### Get Started
1. Install Laravel.
```unix
> composer global require laravel/installer
```
2. Enable *ext-fileinfo* in *php.ini* file.
```
php.ini
-------
extension=fileinfo
```
3. Create REST API Project.
```
> composer create-project --prefer-dist laravel/laravel MarcSubscribersRESTAPI
```
4. Change directory to the name of the API project: *MarcSubscribersRESTAPI*.
```
> cd MarcSubscribersRESTAPI
```
5. Edit the *.env* file to configure the database's parameters.
```
.env
----
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=subscribers_database
DB_USERNAME=user
DB_PASSWORD=password
```
### Create Model
6. Create the Subscriber Model Laravel object.
```
> php artisan make:model Subscriber -m
```
7. Run this SQL query to create the `subscribers_database`.
```
MySQL script:
--------------
drop database if exists `subscribers_database`;
create database if not exists `subscribers_database`;
```
8. Edit the *MarcSubscribersRESTAPI\app\database\migrations\yyyy_mm_dd_tttttt_create_subscribers_table.php* to 
   configure the custom columns meant to be created on this `Subscribers` table.
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateSubscribersTable extends Migration
{
    public function up()
    {
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email_address');
            $table->string('last_name');
            $table->string('first_name');
            $table->boolean('activation_flag')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscribers');
    }
}
```
9. Perform migration to create the `subscribers` database table.
```
> php artisan migrate
```
10. Edit the *MarcSubscribersRESTAPI\app\Models\Subscribers.php*.
```php
<?php

namespace App\Models;

use Database\Factories\SubscriberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Subscriber extends Model
{
    use HasFactory;
    protected $fillable = ['email_address','last_name','first_name', 'activation_flag'];

    /** @return SubscriberFactory */
    protected static function newFactory()
    {
        return SubscriberFactory::new();
    }
}
```
11. Create the Subscribers Table Seeder.
```
> php artisan make:seeder SubscribersTableSeeder
```
12. Edit the *MarcSubscribersRESTAPI\app\database\seeders\SubscribersTableSeeder.php* 
    to create the test database seeder.
```php
<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Subscriber;


class SubscribersTableSeeder extends Seeder
{
    public function run()
    {
        Subscriber::truncate();
        $faker = \Faker\Factory::create();
        for ($i = 0; $i < 10; $i++)
        {
            Subscriber::create([
                'email_address' => $faker->email,
                'first_name' => $faker->name,
                'last_name' => $faker->lastName,
                'activation_flag' => $faker->boolean,
            ]);
        }
    }
}
```

13. Edit also the *MarcSubscribersRESTAPI\database\seeders\DatabaseSeeder.php*
for the master Database Seeder to call the custom generated Subscribers Table Seeder.
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(SubscribersTableSeeder::class);
    }
}
```
14. Populate the `subscribers` database with test records.
```
> php artisan db:seed --class=SubscribersTableSeeder
```
### Create Controller
14. Create the Subscriber Controller.
```
> php artisan make:controller SubscriberController
```
15. Compose the *MarcSubscribersRESTAPI\app\Http\Controllers\SubscriberController.php*.
```php
<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Subscriber;


class SubscriberController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        if (Subscriber::all()->count() == 0)
        {
            return response()->json(null,204);
        }
        return response()->json(Subscriber::all());
    }

    public function show(Subscriber $subscriber): \Illuminate\Http\JsonResponse
    {
        return response()->json($subscriber);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        if($request->getQueryString() == null)
        {
            return response()->json(
                ["error"=> "HTTP command POST without query parameters is not allowed. Please provide an acceptable HTTP command."],
                405);
        }
        return response()->json(Subscriber::create($request->all()), 201);
    }

    public function update(Request $request, Subscriber $subscriber): \Illuminate\Http\JsonResponse
    {
        if($request->getQueryString() == null)
        {
            return response()->json(
                ["error"=> "HTTP command PUT/PATCH without query parameters is not allowed. Please provide an acceptable HTTP command."],
                405);
        }
        $subscriber->update($request->all());
        return response()->json($subscriber);
    }

    /**
     * @throws \Exception
     */
    public function delete(Subscriber $subscriber): \Illuminate\Http\JsonResponse
    {
        $subscriber->delete();
        return response()->json(null, 204);
    }
}
```

16. Edit the *MarcSubscribersRESTAPI\routes\api.php*.
```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriberController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('subscribers', [SubscriberController::class,'index']);
Route::get('subscribers/{subscriber}', [SubscriberController::class,'show']);
Route::post('subscribers', [SubscriberController::class,'store']);
Route::put('subscribers/{subscriber}', [SubscriberController::class,'update']);
Route::patch('subscribers/{subscriber}', [SubscriberController::class,'update']);;
Route::delete('subscribers/{subscriber}', [SubscriberController::class,'delete']);
Route::fallback(function () {
    return response()->json(['error' => 'Invalid URL syntax. Please provide acceptable HTTP URL.'], 400);
});
```

17. Edit the *MarcSubscribersRESTAPI\app\Exceptions\Handler.php*
to accommodate HTTP error code handling and exception/error handling.
```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        });
        $this->renderable(function (HttpException $e) {
            if ($e->getStatusCode() == 404)
            {
                return response()->json(['error' => 'The records or resources that you requested are not available.'],
                    404);
            }
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        });
        $this->renderable(function (Throwable $e) {
            return response()->json(
                ['error' => 'Error caused by server or client. Please provide acceptable API Command'],500);
        });
    }
}
```
### Create PHP Unit Tests
18. Set the **DB_CONNECTION** to *mysql* *MarcSubscribersRESTAPI\phpunit.xml*.
```xml
<php>
   <server name="DB_CONNECTION" value="mysql"/>
</php>
```
19. Edit *MarcSubscribersRESTAPI\tests\TestCase.php* to incorporate database test seeding.
```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations;

    function setUp() : void
    {
        parent::setUp();
        Artisan::call('db:seed');
    }
}
```
20. Compose the *MarcSubscribersRESTAPI\database\factories\SubscriberFactory.php* 
to create a Subscriber database factory.
```php
<?php

namespace Database\Factories;

use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;
use JetBrains\PhpStorm\ArrayShape;


class SubscriberFactory extends Factory
{
    protected string $model = Subscriber::class;

    #[ArrayShape(['email_address' => "string", 'first_name' => "string", 'last_name' => "string", 'activation_flag' => "bool"])]
    public function definition()
    {
        return [
            'email_address' => $this->faker->email,
            'first_name' => $this->faker->name,
            'last_name' => $this->faker->lastName,
            'activation_flag' => $this->faker->boolean,
        ];
    }
}
```
21. Create the Subscriber Test PHPUnit test template.
```
> php artisan make:test SubscriberTest
```
22. Compose the *MarcSubscribersRESTAPI\tests\Feature\SubscriberTest.php*
    to create the Subscriber Test PHP Unit Feature Test Cases.
```php
<?php

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
```
23. Test if the PHP Unit tests would return good test results.
```
> php artisan test
Warning: TTY mode is not supported on Windows platform.

   PASS  Tests\Unit\ExampleTest
  ✓ basic test

   PASS  Tests\Feature\ExampleTest
  ✓ basic test

   PASS  Tests\Feature\SubscriberTest
  ✓ post
  ✓ get
  ✓ get all
  ✓ put
  ✓ patch
  ✓ delete
  ✓ get non existent record
  ✓ get non existent resource
  ✓ get non a p i call
  ✓ non c r u d method
  ✓ post with i d
  ✓ post without payload
  ✓ put without i d
  ✓ patch without i d
  ✓ put without payload
  ✓ patch without payload
  ✓ delete without i d

  Tests:  19 passed
  Time:   7.18s
```
### Set up the System Test
24. Test launch the Laravel development server via Artisan Console. Press CTRL-C to terminate.
```
> php artisan serve
Starting Laravel development server: http://127.0.0.1:8000
[Thu Apr  1 11:11:11 2021] PHP 8.0.1 Development Server (http://127.0.0.1:8000) started
```
25. Perform migration again to refresh the state of the `subscriber` table in the MySQL `subscriber_database`.
```
> php artisan migrate
```
26. Populate the new `subscribers` table with test records if you want to.
```
> php artisan db:seed --class=SubscribersTableSeeder
```
27. Use [HTTPie](https://httpie.org/) as a good HTTP client console.

28. Perform System Test as stipulated on the next section below:

## FUNCTIONAL TEST SAMPLES

### Requirement 1: Create a new subscriber user record

#### Demonstrates POST without ID and CREATE a specified single record
```
>  http post http://127.0.0.1:8000/api/subscribers?email_address=riseofskywalker@starwars.com"&"last_name=Palpatine"&"first_name=Rey
HTTP/1.1 201 Created
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59

{
    "created_at": "2021-04-10T14:00:59.000000Z",
    "email_address": "riseofskywalker@starwars.com",
    "first_name": "Rey",
    "id": 11,
    "last_name": "Palpatine",
    "updated_at": "2021-04-10T14:00:59.000000Z"
}
```

### Requirement 2-1: Fetch a subscriber user record

#### Demonstrates GET with ID and RETRIEVE a specified single record
```
> http get http://127.0.0.1:8000/api/subscribers/11
HTTP/1.1 200 OK
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58

{
    "activation_flag": 0,
    "created_at": "2021-04-10T14:00:59.000000Z",
    "email_address": "riseofskywalker@starwars.com",
    "first_name": "Rey",
    "id": 11,
    "last_name": "Palpatine",
    "updated_at": "2021-04-10T14:00:59.000000Z"
}
```

### Requirement 2-2: Fetch all subscriber user records

#### Demonstrates GET without ID and RETRIEVE all single records
```
> http get http://127.0.0.1:8000/api/subscribers
HTTP/1.1 200 OK
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59

[
    {
        "activation_flag": 1,
        "created_at": "2021-04-10T14:00:46.000000Z",
        "email_address": "dwisozk@marquardt.com",
        "first_name": "Ally Purdy",
        "id": 1,
        "last_name": "Koelpin",
        "updated_at": "2021-04-10T14:00:46.000000Z"
    },
    {
        "activation_flag": 1,
        "created_at": "2021-04-10T14:00:46.000000Z",
        "email_address": "zwisoky@gmail.com",
        "first_name": "Antonietta Bauch",
        "id": 2,
        "last_name": "Wuckert",
        "updated_at": "2021-04-10T14:00:46.000000Z"
    },
    {
        "activation_flag": 1,
        "created_at": "2021-04-10T14:00:46.000000Z",
        "email_address": "lockman.ewell@yahoo.com",
        "first_name": "Mrs. Caterina Trantow",
        "id": 3,
        "last_name": "Abshire",
        "updated_at": "2021-04-10T14:00:46.000000Z"
    },
    {
        "activation_flag": 1,
        "created_at": "2021-04-10T14:00:46.000000Z",
        "email_address": "qfeeney@yahoo.com",
        "first_name": "Dr. Theron McLaughlin",
        "id": 4,
        "last_name": "Schaden",
        "updated_at": "2021-04-10T14:00:46.000000Z"
    },
    {
        "activation_flag": 1,
        "created_at": "2021-04-10T14:00:46.000000Z",
        "email_address": "uturcotte@yahoo.com",
        "first_name": "Verona Swift",
        "id": 5,
        "last_name": "Batz",
        "updated_at": "2021-04-10T14:00:46.000000Z"
    },
    {
        "activation_flag": 0,
        "created_at": "2021-04-10T14:00:46.000000Z",
        "email_address": "may.parisian@hotmail.com",
        "first_name": "Neva Cruickshank",
        "id": 6,
        "last_name": "Kilback",
        "updated_at": "2021-04-10T14:00:46.000000Z"
    },
    {
        "activation_flag": 0,
        "created_at": "2021-04-10T14:00:46.000000Z",
        "email_address": "feeney.golden@gmail.com",
        "first_name": "Montana Graham I",
        "id": 7,
        "last_name": "Jaskolski",
        "updated_at": "2021-04-10T14:00:46.000000Z"
    },
    {
        "activation_flag": 0,
        "created_at": "2021-04-10T14:00:46.000000Z",
        "email_address": "mervin30@yahoo.com",
        "first_name": "Florian Bergstrom",
        "id": 8,
        "last_name": "Prohaska",
        "updated_at": "2021-04-10T14:00:46.000000Z"
    },
    {
        "activation_flag": 1,
        "created_at": "2021-04-10T14:00:46.000000Z",
        "email_address": "leonora.stehr@yahoo.com",
        "first_name": "Mike Reilly",
        "id": 9,
        "last_name": "Maggio",
        "updated_at": "2021-04-10T14:00:46.000000Z"
    },
    {
        "activation_flag": 0,
        "created_at": "2021-04-10T14:00:46.000000Z",
        "email_address": "phyllis.spencer@yahoo.com",
        "first_name": "Ms. Kiera Huels",
        "id": 10,
        "last_name": "Marquardt",
        "updated_at": "2021-04-10T14:00:46.000000Z"
    },
    {
        "activation_flag": 0,
        "created_at": "2021-04-10T14:00:59.000000Z",
        "email_address": "riseofskywalker@starwars.com",
        "first_name": "Rey",
        "id": 11,
        "last_name": "Palpatine",
        "updated_at": "2021-04-10T14:00:59.000000Z"
    }
]
```

If there are no records in the database, the API shall return an *HTTP 204: No Content* status code.
```
> http get http://127.0.0.1:8000/api/subscribers
HTTP/1.1 204 No Content
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
```

### Requirement 3-1: Edit an existing subscriber user record

#### Demonstrates PUT with ID and UPDATE a specified single record
```
> http put http://127.0.0.1:8000/api/subscribers/11?last_name=Skywalker
HTTP/1.1 200 OK
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59

{
    "activation_flag": 0,
    "created_at": "2021-04-10T14:00:59.000000Z",
    "email_address": "riseofskywalker@starwars.com",
    "first_name": "Rey",
    "id": 11,
    "last_name": "Skywalker",
    "updated_at": "2021-04-10T14:04:03.000000Z"
}
```

### Requirement 3-2: Edit an existing subscriber user record

#### Demonstrates PATCH with ID and UPDATE a specified single record
```
> http patch http://127.0.0.1:8000/api/subscribers/1?activation_flag=1
HTTP/1.1 200 OK
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59

{
    "activation_flag": "1",
    "created_at": "2021-04-10T15:01:52.000000Z",
    "email_address": "riseofskywalker@starwars.com",
    "first_name": "Rey",
    "id": 1,
    "last_name": "Palpatine",
    "updated_at": "2021-04-10T15:04:39.000000Z"
}
```

### Requirement 4: Delete an existing subscriber user record

#### Demonstrates DELETE with ID and DELETE a specified single record
```
> http delete http://127.0.0.1:8000/api/subscribers/11
HTTP/1.1 204 No Content
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58
```

### Error Test Case 1: Get a record of a subscriber who does not exist.
```
> http get http://127.0.0.1:8000/api/subscribers/400
HTTP/1.1 404 Not Found
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58

{
    "error": "The records or resources that you requested are not available."
}
```

### Error Test Case 2: Call an API without the prescribed 'subscribers' model
```
> http get http://127.0.0.1:8000/api/
HTTP/1.0 404 Not Found
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1

{
    "error": "The records or resources that you requested are not available. "
}
```

### Error Test Case 3: Call an API with a model that is not 'subscribers'
```
> http get http://127.0.0.1:8000/api/notsubscribers/11
HTTP/1.1 400 Bad Request
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59

{
    "error": "Invalid URL syntax. Please provide acceptable HTTP URL."
}
```
### Error Test Case 4: Call HTTP commands that are not being used by the API.
```
> http trace http://127.0.0.1:8000/api/subscribers/
HTTP/1.0 405 Method Not Allowed
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1

{
    "error": "The TRACE method is not supported for this route. Supported methods: GET, HEAD, PUT, PATCH, DELETE."
}
```
### Error Test Case 5-1: POST with specified ID.
```
> http post http://127.0.0.1:8000/api/subscribers/12?email_address=riseofskywalker@starwars.com"&"last_name=Palpatine"&"first_name=Rey
HTTP/1.0 405 Method Not Allowed
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1

{
    "error": "The POST method is not supported for this route. Supported methods: GET, HEAD, PUT, PATCH, DELETE."
}
```

### Error Test Case 5-2: POST without required parameters
```
> http post http://127.0.0.1:8000/api/subscribers/
HTTP/1.1 405 Method Not Allowed
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58

{
    "error": "HTTP command POST without query parameters is not allowed. Please provide an acceptable HTTP command."
}
```

### Error Test Case 5-3: PUT without specified ID.
```
> http put http://127.0.0.1:8000/api/subscribers?last_name=Skywalker
HTTP/1.0 405 Method Not Allowed
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1

{
    "error": "The PUT method is not supported for this route. Supported methods: GET, HEAD, POST."
}
```

### Error Test Case 5-4: PATCH without specified ID.
```
> http patch http://127.0.0.1:8000/api/subscribers/?activation_flag=1
HTTP/1.0 405 Method Not Allowed
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1

{
    "error": "The PATCH method is not supported for this route. Supported methods: GET, HEAD, POST."
}
```

### Error Test Case 5-5: PUT without required parameters
```
> http put http://127.0.0.1:8000/api/subscribers/1
HTTP/1.1 405 Method Not Allowed
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 57

{
    "error": "HTTP command PUT/PATCH without query parameters is not allowed. Please provide an acceptable HTTP command."
}
```

### Error Test Case 5-6: PATCH without required parameters
```
> http patch http://127.0.0.1:8000/api/subscribers/1
HTTP/1.1 405 Method Not Allowed
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59

{
    "error": "HTTP command PUT/PATCH without query parameters is not allowed. Please provide an acceptable HTTP command."
}
```

### Error Test Case 5-7: DELETE without specified ID
```
> http delete http://127.0.0.1:8000/api/subscribers/
HTTP/1.0 405 Method Not Allowed
Access-Control-Allow-Origin: *
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1

{
    "error": "The DELETE method is not supported for this route. Supported methods: GET, HEAD, POST."
}
```
### Error Test Case 6: API Request to other unrecognized API resources
```
> http get http://127.0.0.1:8000/notapi
HTTP/1.0 404 Not Found
Cache-Control: no-cache, private
Connection: close
Content-Type: application/json
Host: 127.0.0.1:8000
X-Powered-By: PHP/8.0.1

{
    "error": "The records or resources that you requested is not available. "
}
```

For more inquiries, please feel free to e-mail me at marcanthonyconcepcion@gmail.com.

Thank you.

:copyright: 2021 Marc Concepcion

### END
