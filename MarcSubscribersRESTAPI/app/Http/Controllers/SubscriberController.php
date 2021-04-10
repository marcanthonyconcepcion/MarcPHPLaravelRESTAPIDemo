<?php
/*
 * Copyright (c) 2021.
 * Marc Concepcion
 * marcanthonyconcepcion@gmail.com
 */

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
