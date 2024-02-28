<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        Reservation::factory(2)->create(["user_id"=> $user->id,'status'=> Reservation::STATUS_ACTIVE,'office_id'=>$office->id]);
        Reservation::factory(1)->for($office)->create(["user_id"=> $user->id,'status'=> Reservation::STATUS_CANCELED]);
        $response = $this->get('api/offices');
        $response->assertOk();
        $this->assertEquals(1,count($response->json()['data']['data']));
        $this->assertEquals(2,$response->json()['data']['data'][0]['reservations_count']);
    }
    /**
     * @test
     */
    public function it_shows_nearest_first(){
        //30.794096019163433, 30.99861976286775
        $office1 = Office::factory()->create(["lat"=> '31.27745136666827','lng'=> '30.766084600741376']);
        $office2 = Office::factory()->create(["lat"=> '31.106159680148618','lng'=> '30.955557695807308']);
        $response = $this->get('api/offices?lat=30.720367838343044&lng=31.26045372671444');
        // $response->dd();
        $response->assertOk();
        $this->assertEquals($office2->id,$response->json()['data']['data'][0]['id']);
        $response2 = $this->get('api/offices');
        $response2->assertOk();
        $this->assertEquals($office1->id,$response2->json()['data']['data'][0]['id']);
    }
    /**
     * @test
     */
    public function it_shows_single_office(){
        $office = Office::factory()->create();
        $response = $this->get('api/offices/'. $office->id);
        $response->assertOk();
        $this->assertEquals($office->id,$response->json()['data']['id']);
    }
    /**
     * @test
     */
    public function it_shows_active_resetvaions_count_and_images_and_tags(){
        $office = Office::factory()->create();
        Reservation::factory(5)->for($office)->create(['status'=> Reservation::STATUS_ACTIVE]);
        Reservation::factory(5)->for($office)->create(['status'=> Reservation::STATUS_CANCELED]);
        Image::factory(5)->create(['resource_type'=> $office->getMorphClass(),'resource_id'=> $office->id]);
        $tags = Tag::factory(5)->create();
        $office->tags()->attach($tags);
        $response = $this->get('api/offices/'. $office->id);
        $response->assertOk();
        $this->assertEquals($office->id,$response->json()['data']['id']);
        $this->assertEquals(5,$response->json()['data']['reservations_count']);
        $response->assertJsonCount(5,'data.images');
        $response->assertJsonCount(5,'data.tags');
    }
    /**
     * @test
     */
    public function it_creates_office(){
        $user = User::factory()->create();
        $office = Office::factory()->make()->toArray();
        $office['tags'] = Tag::factory(3)->create()->pluck('id')->toArray();
        Storage::fake('public');
        $office['images'] = [
            UploadedFile::fake()->create('image.png',100),
            UploadedFile::fake()->create('image1.png',100),
            UploadedFile::fake()->create('image2.png',100),
        ];
        $response = $this->post('api/offices', $office,[
            'Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken
        ]);
        $response->assertOk();
        $this->assertEquals($office['title'],$response->json()['data']['title']);
        $response->assertJsonCount(3,'data.images');
        $response->assertJsonCount(3,'data.tags');
    }
}
