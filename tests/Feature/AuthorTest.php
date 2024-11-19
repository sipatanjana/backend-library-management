<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthorTest extends TestCase
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

    private function updateAuthor($id, array $data = [])
    {
        return $this->put("/api/authors/$id", $data, ['Accept' => 'application/json']);
    }

    public function test_get_all_authors(): void
    {
        $response = $this->get('/api/authors');

        $response->assertStatus(200);
    }

    public function test_create_authors(): void
    {
        $response = $this->createAuthor();

        $response->assertStatus(200);
        $this->assertDatabaseHas('authors', ['name' => 'John Doe']);
    }

    public function test_handles_errors_null_data_create_authors(): void
    {
        // Create With Null Data
        $nullData = [
            'name' => '',
            'bio' => '',
            'birth_date' => '',
        ];

        $response = $this->post('/api/authors', $nullData, ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('authors', [
            'name' => 'The name field is required.',
            'bio' => 'The bio field is required.',
            'birth_date' => 'The birth_date field is required.',
        ]);
    }

    public function test_handles_errors_invalid_date_data_create_authors(): void
    {
        // Create With non format DATE
        $failDateData = [
            'name' => 'John Doe',
            'bio' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'birth_date' => 'Lorem ipsum',
        ];

        $responseDateFail = $this->post('/api/authors', $failDateData, ['Accept' => 'application/json']);

        $responseDateFail->assertStatus(422);
        $this->assertDatabaseMissing('authors', [
            'birth_date' => 'The birth date field must be a valid date.',
        ]);
    }

    public function test_handles_errors_create_authors(): void
    {
        // Create With Duplicate Name
        $response = $this->createAuthor();
        $model = DB::table('authors')->latest('id')->first();
        $failUniqueData = [
            'name' => $model->name,
            'bio' => $model->bio,
            'birth_date' => $model->birth_date,
        ];

        $responseUniqueFail = $this->post('/api/authors', $failUniqueData, ['Accept' => 'application/json']);

        $responseUniqueFail->assertStatus(422);
        $this->assertDatabaseMissing('authors', [
            'name' => 'The name has already been taken.',
        ]);
    }

    public function test_detail_authors(): void
    {
        $response = $this->createAuthor();
        $model = DB::table('authors')->latest('id')->first();
        $response = $this->getJson("/api/authors/$model->id");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                [
                    'id' => $model->id,
                    'name' => 'John Doe',
                ]
            ]
        ]);
    }

    public function test_update_authors(): void
    {
        $response = $this->createAuthor();
        $model = DB::table('authors')->latest('id')->first();

        $response = $this->updateAuthor($model->id, ['name' => 'John Beiden']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('authors', [
            'id' => $model->id,
            'name' => 'John Beiden',
        ]);
    }

    public function test_author_not_found_on_delete()
    {
        $response = $this->delete("/api/authors/999");

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Data Can\'t be Found']);
    }

    public function test_delete_authors(): void
    {
        $response = $this->createAuthor();
        $model = DB::table('authors')->latest('id')->first();
        $response = $this->delete("/api/authors/$model->id");

        $response->assertStatus(200);
    }
}
