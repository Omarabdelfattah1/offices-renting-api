<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OfficesControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @test
     */
    public function it_lists_approved_and_visible_offices_only(){
        $host = User::factory()->create();
        Office::factory()->count(3)->create(["user_id"=> $host->id]);
        Office::factory()->create(["user_id"=> $host->id,'approval_status'=>2]);
        Office::factory()->create(["user_id"=> $host->id,'hidden'=>true]);
        $response = $this->get('api/offices');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data'=> [
                'data',
            ]
        ]);
        $response->assertJsonCount(3,'data.data');
    }
    /**
     * @test
     */
    public function it_filter_offices_by_host_id(){
        $host = User::factory()->create();
        $host2 = User::factory()->create();
        $office = Office::factory()->create(["user_id"=> $host->id]);
        Office::factory()->create(["user_id"=> $host2->id]);
        $response = $this->get('api/offices?host_id='. $host->id);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data'=> [
                'data',
            ]
        ]);
        $response->assertJsonCount(1,'data.data');
        $this->assertEquals($office->id, $response->json()['data']['data'][0]['id']);
    }
    /**
     * @test
     */
    public function it_filter_offices_by_user_id(){
        $host = User::factory()->create();
        $user = User::factory()->create();
        $office = Office::factory()->create(["user_id"=> $host->id]);
        $office2 = Office::factory()->create(["user_id"=> $host->id]);
        Reservation::factory()->create(["user_id"=> $user->id,"office_id"=> $office->id]);
        $response = $this->get('api/offices?user_id='. $user->id);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data'=> [
                'data',
            ]
        ]);
        $response->assertJsonCount(1,'data.data');
        $this->assertEquals($office->id, $response->json()['data']['data'][0]['id']);
    }
    /**
     * @test
     */
    public function it_includes_tags_and_images(){
        $host = User::factory()->create();
        $office = Office::factory()->create(["user_id"=> $host->id]);
        $tags = Tag::factory(2)->create();
        $office->tags()->attach($tags);
        $office->images()->create(['path'=> 'image.jpg']);
        $response = $this->get('api/offices');
        $response->assertOk();
        $this->assertIsArray($response->json()['data']['data'][0]['images']);
        $this->assertIsArray($response->json()['data']['data'][0]['tags']);
        $this->assertCount(1,$response->json()['data']['data'][0]['images']);
        $this->assertCount(2,$response->json()['data']['data'][0]['tags']);
        $this->assertEquals($host->id,$response->json()['data']['data'][0]['user_id']);
    }
    /**
     * @test
     */
    public function it_includes_active_reservations_count(){
        $host = User::factory()->create();
        $user = User::factory()->create();
        $office = Office::factory()->create(["user_id"=> $host->id]);
        Reservation::factory(2)->for($office)->create(["user_id"=> $user->id,'status'=> Reservation::STATUS_ACTIVE]);
        Reservation::factory(1)->for($office)->create(["user_id"=> $user->id,'status'=> Reservation::STATUS_CANCELED]);
        $response = $this->get('api/offices');
        $response->assertOk();
        $this->assertEquals(1,count($response->json()['data']['data']));
        $this->assertEquals(2,$response->json()['data']['data'][0]['reservations_count']);
    }
}
