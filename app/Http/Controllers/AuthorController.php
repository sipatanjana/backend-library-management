<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Http\Resources\BookResource;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\AuthorResource;
use App\Http\Requests\StoreAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $model = Author::paginate();
        $data = Cache::remember('authors', now()->addMinutes(2), function () use ($model) {
            return AuthorResource::collection($model);
        });

        return $data;
    }

    /**
     * Display all listing of the resource.
     */
    public function books($id)
    {
        $model = Book::where('author_id', $id)->paginate();
        $data = Cache::remember('author_books', now()->addMinutes(2), function () use ($model) {
            return BookResource::collection($model);
        });

        return $data;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAuthorRequest $request)
    {
        $data = $request->all();
        $models = Author::create($data);
        $model = Author::where('id', $models->id)->get();

        return AuthorResource::collection($model);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $model = Author::where('id', $id)->get();

        return AuthorResource::collection($model);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAuthorRequest $request, $id)
    {
        $data = $request->all();
        $models = Author::where('id', $id)->first();
        if ($models) {
            $models->update($data);
            $model = Author::where('id', $models->id)->get();
            return AuthorResource::collection($model);
        } else return $this->failedFunction('Data Can\'t be Found', null, 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $model = Author::where('id', $id)->first();
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
