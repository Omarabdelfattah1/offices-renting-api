<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\OfficePendingNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfficesControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @test
     */
    public function it_lists_all_offices_for_current_user(){
        $host = User::factory()->create();
        $this->actingAs($host);
        Office::factory()->count(3)->create(["user_id"=> $host->id,"approval_status"=> Office::APPROVAL_PENDING]);
        Office::factory()->count(3)->create(["user_id"=> $host->id,"hidden" =>true]);
        Office::factory()->create(["user_id"=> $host->id,'approval_status'=>Office::APPROVAL_APPROVED]);
        $response = $this->get('api/offices?user_id='.$host->id);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data'=> [
                'data',
            ]
        ]);
        $response->assertJsonCount(7,'data.data');
    }
    /**
     * @test
     */
    public function it_lists_approved_and_visible_offices_only(){
        $office = Office::factory()->create(['approval_status'=>Office::APPROVAL_APPROVED]);
        Office::factory()->create(['approval_status'=>Office::APPROVAL_PENDING]);
        Office::factory()->create(['hidden'=>true]);
        $response = $this->get('api/offices');
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
    public function it_filter_offices_by_host_id(){
        $host = User::factory()->create();
        $host2 = User::factory()->create();
        $office = Office::factory()->create(["user_id"=> $host->id,'approval_status'=>Office::APPROVAL_APPROVED]);
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
        $response = $this->get('api/offices?visitor_id='. $user->id);
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
        // $this->assertEquals(1,count($response->json()['data']['data']));
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
        Storage::fake('assets');
        $office['images'] = [
            UploadedFile::fake()->create('image.png',100),
            UploadedFile::fake()->create('image1.png',100),
            UploadedFile::fake()->create('image2.png',100),
        ];
        Notification::fake();
        $response = $this->post('api/offices', $office,[
            'Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken
        ]);
        $response->assertCreated();
        $this->assertEquals($office['title'],$response->json()['data']['title']);
        $response->assertJsonCount(3,'data.images');
        $response->assertJsonCount(3,'data.tags');
        Notification::assertSentTo(User::where('role',User::ROLE_SUPPER_ADMIN)->first(),OfficePendingNotification::class);

    }
    /**
     * @test
     */
    public function it_cant_update_others_office(){
        $host = User::factory()->create();
        $host2 = User::factory()->create();
        $office = Office::factory()->create(["user_id"=> $host->id]);
        $tags = Tag::factory(2)->create();
        $office->tags()->attach($tags);
        $office->images()->create(['path'=> 'image.jpg']);

        $officeUpdated = Office::factory()->make()->toArray();
        $response = $this->putJson('api/offices/'.$office->id, $officeUpdated,[
            'Authorization' => 'Bearer '.$host2->createToken('test')->plainTextToken
        ]);

        $response->assertStatus(403);
    }
    /**
     * @test
     */
    public function it_updates_office(){
        $host = User::factory()->create();
        $office = Office::factory()->create(["user_id"=> $host->id]);
        $tags = Tag::factory(2)->create();
        $office->tags()->attach($tags);
        $office->images()->create(['path'=> 'image.jpg']);

        $officeUpdated = Office::factory()->make()->toArray();
        $officeUpdated['tags'] = [Tag::factory()->create()->id,$tags->first()->id];
        Storage::fake('public');
        Notification::fake();
        $officeUpdated['images'] = [
            UploadedFile::fake()->create('image.png',100),
            UploadedFile::fake()->create('image1.png',100),
            UploadedFile::fake()->create('image2.png',100),
        ];
        $response2 = $this->putJson('api/offices/'.$office->id, $officeUpdated,[
            'Authorization' => 'Bearer '.$host->createToken('test')->plainTextToken
        ]);
        // $response2->dd();
        $response2->assertOk();
        $this->assertEquals($officeUpdated['title'],$response2->json()['data']['title']);
        $response2->assertJsonCount(4,'data.images');
        $response2->assertJsonCount(2,'data.tags');
        $this->assertContains($response2->json()['data']['tags'][0]['id'], $officeUpdated['tags']);
        $this->assertContains($response2->json()['data']['tags'][1]['id'], $officeUpdated['tags']);
        Notification::assertSentTo(User::where('role',User::ROLE_SUPPER_ADMIN)->first(),OfficePendingNotification::class);
    }
    /**
     * @test
     */
    public function it_cant_delete_office_with_active_reservations(){
        $host = User::factory()->create();
        $office = Office::factory()->create(["user_id"=> $host->id]);
        Reservation::factory()->create(["office_id"=> $office->id]);
        $response = $this->deleteJson('api/offices/'.$office->id,[],[
            'Authorization' => 'Bearer '.$host->createToken('test')->plainTextToken
        ]);
        $response->assertStatus(422);
    }
    /**
     * @test
     */
    public function it_cant_delete_office_for_other_users(){
        $host = User::factory()->create();
        $host2 = User::factory()->create();
        $office = Office::factory()->create(["user_id"=> $host2->id]);
        $response = $this->deleteJson('api/offices/'.$office->id,[],[
            'Authorization' => 'Bearer '.$host->createToken('test')->plainTextToken
        ]);
        $response->assertStatus(403);
    }
    /**
     * @test
     */
    public function it_delete_offices(){
        $host = User::factory()->create();
        $office = Office::factory()->create(["user_id"=> $host->id]);
        $response = $this->deleteJson('api/offices/'.$office->id,[],[
            'Authorization' => 'Bearer '.$host->createToken('test')->plainTextToken
        ]);
        $response->assertStatus(200);
    }
    /**
     * @test
     */
    public function it_allows_admin_to_delete_offices(){
        $host = User::factory()->create();
        $office = Office::factory()->create(["user_id"=> $host->id]);
        $response = $this->deleteJson('api/offices/'.$office->id,[],[
            'Authorization' => 'Bearer '.User::where('role',User::ROLE_SUPPER_ADMIN)->first()->createToken('test')->plainTextToken
        ]);
        $response->assertStatus(200);
    }
}
