<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImagesControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @test
     */
    public function it_lists_images(){
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $office = Office::factory()->create();
        $office->images()->createMany([
            ['path' =>'image.jpg'],
            ['path' =>'image.jpg'],
            ['path' =>'image2.jpg'],
        ]);
        $response = $this->get("api/offices/$office->id/images");
        $response->assertStatus(200);
        $response->assertJsonCount(3,'data.data');
    }
    /**
     * @test
     */
    public function it_creates_image(){
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $office = Office::factory()->create();
        Storage::fake('assets');
        $image = UploadedFile::fake()->create('image.png',100);
        $response = $this->post("api/offices/$office->id/images",[
            'image' => $image
        ]);
        $response->assertCreated();

        $response2 = $this->get('api/offices/'. $office->id);
        $response2->assertOk();
        $response2->assertJsonCount(1,'data.images');
    }
    /**
     * @test
     */
    public function it_deletes_image(){
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $office = Office::factory()->create();
        Storage::put('image1.png','empty');
        Storage::put('image2.png','empty');
        Storage::put('image3.png','empty');
        $image1Created = $office->images()->create([
            'path' => 'image1.png'
        ]);
        $image2Created = $office->images()->create([
            'path' => 'image2.png'
        ]);
        $image3Created = $office->images()->create([
            'path' => 'image3.png'
        ]);

        $response2 = $this->delete('api/offices/'. $office->id.'/images/'.$image1Created->id);
        $response2->assertOk();
        Storage::assertMissing('image1.png');
    }
    /**
     * @test
     */
    public function it_cant_delete_the_only_image(){
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $office = Office::factory()->create();
        Storage::put('image1.png','empty');
        $image1Created = $office->images()->create([
            'path' => 'image1.png'
        ]);
        $response2 = $this->delete('api/offices/'. $office->id.'/images/'.$image1Created->id);
        $response2->assertUnprocessable();
        Storage::assertExists('image1.png');
    }
    /**
     * @test
     */
    public function it_cant_delete_featured_image(){
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $office = Office::factory()->create();
        Storage::put('image1.png','empty');
        $image = Image::create([
            'path' => 'image1.png',
            'resource_type' => 'offices',
            'resource_id' => $office->id,
        ]);
        $office->update(['featured_image_id' => $image->id]);
        $response2 = $this->delete('api/offices/'. $office->id.'/images/'.$image->id);
        $response2->assertUnprocessable();
        Storage::assertExists('image1.png');
    }
}
