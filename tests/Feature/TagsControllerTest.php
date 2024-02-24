<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TagsControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @test
     */
    public function it_can_lists_tags(){
        $response = $this->get('api/tags');
        $response->assertStatus(200);
        $response->assertJsonCount(3,'data');
        $response->assertJsonStructure([
            'data'=> [
                [
                    'id',
                    'name'
                ]
            ]
        ]);
    }
}
