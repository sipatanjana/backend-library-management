<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    private function createAuthor(array $data = [])
    {
        $data = [
            'name' => 'John Doe',
            'bio' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'birth_date' => '2000-12-01',
        ];

        return $this->post('/api/authors', $data, ['Accept' => 'application/json']);
    }

    private function createBook(array $data = [])
    {
        $model = DB::table('authors')->latest('id')->first();
        if (!$data) {
            $data = [
                'author_id' => $model->id,
                'title' => 'Lorem ipsum',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'publish_date' => '2000-12-01',
            ];
        }

        return $this->post('/api/books', $data, ['Accept' => 'application/json']);
    }

    private function updateBook($id, array $data = [])
    {
        return $this->put("/api/books/$id", $data, ['Accept' => 'application/json']);
    }

    public function test_get_all_with_definition_author_books(): void
    {
        $this->createAuthor();
        $model = DB::table('authors')->latest('id')->first();

        $data = [
            'author_id' => $model->id,
            'title' => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'publish_date' => '2000-12-01',
        ];

        $data_two = [
            'author_id' => $model->id,
            'title' => 'Lorem dolor',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'publish_date' => '2000-12-01',
        ];

        $this->createBook($data);
        $this->createBook($data_two);

        $response = $this->getJson("/api/authors/$model->id/books");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'author_id',
                    'title',
                    'description',
                    'publish_date',
                    'deleted_at',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);
        $response->assertJsonCount(2, 'data');
    }

    public function test_get_all_books(): void
    {
        $this->createAuthor();
        $response = $this->createBook();
        $response = $this->get('/api/books');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'author_id',
                    'title',
                    'description',
                    'publish_date',
                    'deleted_at',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);
    }

    public function test_get_all_null_books(): void
    {
        $response = $this->get('/api/books');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => []
        ]);
    }

    public function test_create_books(): void
    {
        $this->createAuthor();
        $response = $this->createBook();

        $response->assertStatus(200);
        $this->assertDatabaseHas('books', ['title' => 'Lorem ipsum']);
    }

    public function test_handles_errors_null_data_create_books(): void
    {
        // Create With Null Data
        $nullData = [
            'author_id' => '',
            'title' => '',
            'description' => '',
            'publish_date' => '',
        ];

        $response = $this->post('/api/books', $nullData, ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('books', [
            'author_id' => 'The author id field is required.',
            'title' => 'The title field is required.',
            'description' => 'The description field is required.',
            'publish_date' => 'The publish date field is required.',
        ]);
    }

    public function test_handles_errors_invalid_author_data_create_books(): void
    {
        // Create With Invalid Author
        $failDateData = [
            'author_id' => 999,
            'title' => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'publish_date' => '1999-12-01',
        ];

        $responseDateFail = $this->post('/api/books', $failDateData, ['Accept' => 'application/json']);

        $responseDateFail->assertStatus(422);
        $this->assertDatabaseMissing('books', [
            'publish_date' => 'The selected author id is invalid.',
        ]);
    }

    public function test_handles_errors_invalid_date_data_create_books(): void
    {
        // Create With non format DATE
        $this->createAuthor();
        $model = DB::table('authors')->latest('id')->first();
        $failDateData = [
            'author_id' => $model->id,
            'title' => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'publish_date' => 'Lorem ipsum',
        ];

        $responseDateFail = $this->post('/api/books', $failDateData, ['Accept' => 'application/json']);

        $responseDateFail->assertStatus(422);
        $this->assertDatabaseMissing('books', [
            'publish_date' => 'The publish date field must be a valid date.',
        ]);
    }

    public function test_handles_errors_duplicate_create_books(): void
    {
        // Create With Duplicate Name With Same Author
        $this->createAuthor();
        $this->createBook();
        $model = DB::table('books')->latest('id')->first();
        $failUniqueData = [
            'author_id' => $model->author_id,
            'title' => $model->title,
            'description' => $model->description,
            'publish_date' => $model->publish_date,
        ];

        $responseUniqueFail = $this->post('/api/books', $failUniqueData, ['Accept' => 'application/json']);

        $responseUniqueFail->assertStatus(422);
        $this->assertDatabaseMissing('books', [
            'title' => 'The title has already been taken.',
        ]);
    }

    public function test_detail_books(): void
    {
        $this->createAuthor();
        $this->createBook();
        $model = DB::table('books')->latest('id')->first();
        $response = $this->getJson("/api/books/$model->id");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                [
                    'id' => $model->id,
                    'title' => 'Lorem ipsum',
                ]
            ]
        ]);
    }

    public function test_update_books(): void
    {
        $this->createAuthor();
        $response = $this->createBook();
        $model = DB::table('books')->latest('id')->first();

        $response = $this->updateBook($model->id, ['title' => 'Book Of Heaven']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('books', [
            'id' => $model->id,
            'title' => 'Book Of Heaven',
        ]);
    }

    public function test_author_not_found_on_delete()
    {
        $response = $this->delete("/api/books/999");

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Data Can\'t be Found']);
    }

    public function test_delete_books(): void
    {
        $this->createAuthor();
        $response = $this->createBook();
        $model = DB::table('books')->latest('id')->first();
        $response = $this->delete("/api/books/$model->id");

        $response->assertStatus(200);
    }
}
