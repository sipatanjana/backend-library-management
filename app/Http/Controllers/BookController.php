<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $model = Book::paginate();
        $data = Cache::remember('books', now()->addMinutes(2), function () use ($model) {
            return BookResource::collection($model);
        });

        return $data;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        $data = $request->all();
        $models = Book::create($data);
        $model = Book::where('id', $models->id)->get();

        return BookResource::collection($model);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $model = Book::where('id', $id)->get();

        return BookResource::collection($model);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, $id)
    {
        $data = $request->all();
        $models = Book::where('id', $id)->first();
        if ($models) {
            $models->update($data);
            $model = Book::where('id', $models->id)->get();
            return BookResource::collection($model);
        } else return $this->failedFunction('Data Can\'t be Found', null, 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $model = Book::where('id', $id)->first();
        if ($model) {
            try {
                $model->delete();
                return $this->succesFunction('Data successfully erased');
            } catch (\Throwable $th) {
                return $this->failedFunction('Cannot be deleted, Data is still in use', null, 401);
            }
        } else return $this->failedFunction('Data Can\'t be Found', null, 404);
    }
}
