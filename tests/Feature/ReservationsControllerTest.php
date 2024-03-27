<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReservationsControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @test
     */
    public function it_lists_all_reservations(): void
    {
        $user = User::factory()->create();
        $reservations = Reservation::factory()->count(3)->create();
        $this->actingAs($user);
        $response = $this->get('/api/reservations');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data'=> [
                'data' =>[
                    [
                        'user_id',
                        'office_id',
                        'start_date',
                        'end_date',
                        'status',
                        'price',
                        'office'=>[
                            'user_id',
                            'title',
                            'description',
                            'featured_image_id',
                            'lat',
                            'lng',
                            'address_line1',
                            'address_line2',
                            'approval_status',
                            'hidden',
                            'price_per_day',
                            'monthly_discount'
                        ]
                    ],
                ]
            ]
        ]);
    }
    /**
     * @test
     */
    public function it_filters_reservations(): void
    {
        $user = User::factory()->create();
        $office = Office::factory()->create();
        Reservation::factory()->count(3)->create();
        Reservation::factory()->create(['user_id'=>$user->id]);
        Reservation::factory()->create(['office_id'=>$office->id]);
        Reservation::factory()->create(['user_id'=> $user->id,'office_id'=>$office->id,'status' => Reservation::STATUS_PENDING]);
        Reservation::factory()->create(['user_id'=> $user->id,'office_id'=>$office->id,'status' => Reservation::STATUS_CANCELED]);
        $this->actingAs($user);
        $response = $this->get("/api/reservations?user_id=$user->id&office_id=$office->id&status=".Reservation::STATUS_CANCELED);
        $response->assertOk();
        $response->assertJsonCount(1,"data.data");
        // $response->dd();
        $this->assertEquals($office->id, $response->json()["data"]["data"][0]["office_id"]);
        $this->assertEquals($user->id, $response->json()["data"]["data"][0]["user_id"]);
        $this->assertEquals(Reservation::STATUS_CANCELED, $response->json()["data"]["data"][0]["status"]);
    }
    /**
     * @test
     */
    public function it_filters_reservations_by_date(): void
    {
        $user = User::factory()->create();
        $from = now()->subDays(1);
        $to = now()->addDays(7);
        Reservation::factory()->create(['start_date'=>now()->subDays(7),'end_date'=>now()->subDays(3)]);
        $reservation1 = Reservation::factory()->create(['start_date'=>now()->subDays(2),'end_date'=>$from]);
        $reservation2 = Reservation::factory()->create(['start_date'=>now(),'end_date'=>$to]);
        $reservation3 = Reservation::factory()->create(['start_date'=> now()->subDays(2),'end_date'=>now()->addDays(8)]);
        Reservation::factory()->create(['start_date'=>now()->addDays(8),'end_date'=>now()->addDays(14)]);
        $this->actingAs($user);
        $from = $from->format('Y-m-d');
        $to = $to->format('Y-m-d');
        $response = $this->get("/api/reservations?from_time=$from&to_time=$to");
        $response->assertOk();
        $response->assertJsonCount(3,"data.data");
        $response->assertJson([
            'success' => true,
            'status' => 200,
            "data"=> [
                "data" => [
                    ["id" => $reservation1->id],
                    ["id" => $reservation2->id],
                    ["id" => $reservation3->id],
                ]
            ]
        ]);
    }
    /**
     * @test
     */
    public function it_lists_my_reservations(): void
    {
        $user = User::factory()->create();
        Reservation::factory()->count(2)->create();
        Reservation::factory()->count(3)->create(['user_id'=>$user->id]);
        $this->actingAs($user);
        $response = $this->get('/api/my-reservations');
        $response->assertStatus(200);
        $response->assertJsonCount(3,'data.data');
        foreach($response->json('data.data') as $reservation){
            $this->assertEquals($reservation['user_id'], $user->id);
        }
    }
    /**
     * @test
     */
    public function it_filters_my_reservations(): void
    {
        $user = User::factory()->create();
        $office = Office::factory()->create();
        Reservation::factory()->count(3)->create();
        Reservation::factory()->create(['user_id'=>$user->id]);
        Reservation::factory()->create(['user_id'=>$user->id,'office_id'=>$office->id]);
        Reservation::factory()->create(['user_id'=> $user->id,'office_id'=>$office->id,'status' => Reservation::STATUS_PENDING]);
        Reservation::factory()->create(['user_id'=> $user->id,'office_id'=>$office->id,'status' => Reservation::STATUS_CANCELED]);
        $this->actingAs($user);
        $response = $this->get("/api/my-reservations?office_id=$office->id&status=".Reservation::STATUS_CANCELED);
        $response->assertOk();
        $response->assertJsonCount(1,"data.data");
        $this->assertEquals($office->id, $response->json()["data"]["data"][0]["office_id"]);
        $this->assertEquals($user->id, $response->json()["data"]["data"][0]["user_id"]);
        $this->assertEquals(Reservation::STATUS_CANCELED, $response->json()["data"]["data"][0]["status"]);
    }
    /**
     * @test
     */
    public function it_filters_my_reservations_by_date(): void
    {
        $user = User::factory()->create();
        $from = now()->subDays(1);
        $to = now()->addDays(7);
        Reservation::factory()->create(['user_id'=> $user->id,'start_date'=>now()->subDays(7),'end_date'=>now()->subDays(3)]);
        $reservation1 = Reservation::factory()->create(['user_id'=> $user->id,'start_date'=>now()->subDays(2),'end_date'=>$from]);
        $reservation2 = Reservation::factory()->create(['user_id'=> $user->id,'start_date'=>now(),'end_date'=>$to]);
        $reservation3 = Reservation::factory()->create(['user_id'=> $user->id,'start_date'=>now()->subDays(2),'end_date'=>now()->addDays(8)]);
        Reservation::factory()->create(['user_id'=> $user->id,'start_date'=>now()->addDays(8),'end_date'=>now()->addDays(14)]);
        $this->actingAs($user);
        $from = $from->format('Y-m-d');
        $to = $to->format('Y-m-d');
        $response = $this->get("/api/my-reservations?from_time=$from&to_time=$to");
        $response->assertOk();
        $response->assertJsonCount(3,"data.data");
        $response->assertJson([
            'success' => true,
            'status' => 200,
            "data"=> [
                "data" => [
                    ["id" => $reservation1->id],
                    ["id" => $reservation2->id],
                    ["id" => $reservation3->id],
                ]
            ]
        ]);
    }
    /**
     * @test
     */
    public function it_lists_my_offices_reservations(): void
    {
        $host = User::factory()->create();
        $office = Office::factory()->create(["user_id"=> $host->id]);
        Reservation::factory()->count(2)->create();
        Reservation::factory()->count(3)->create(['office_id'=>$office->id]);
        $this->actingAs($host);
        $response = $this->get('/api/office-reservations');
        $response->assertStatus(200);
        $response->assertJsonCount(3,'data.data');
        foreach($response->json('data.data') as $reservation){
            $this->assertEquals($reservation['office_id'], $office->id);
        }
    }
    /**
     * @test
     */
    public function it_filters_my_offices_reservations(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $office = Office::factory()->create(['user_id'=> $host->id]);
        Reservation::factory()->count(3)->create();
        Reservation::factory()->create(['office_id'=>$office->id]);
        Reservation::factory()->create(['user_id'=>$user->id,'office_id'=>$office->id]);
        Reservation::factory()->create(['user_id'=> $user->id,'office_id'=>$office->id,'status' => Reservation::STATUS_PENDING]);
        Reservation::factory()->create(['user_id'=> $user->id,'office_id'=>$office->id,'status' => Reservation::STATUS_CANCELED]);
        $this->actingAs($host);
        $response = $this->get("/api/office-reservations?user_id=$user->id&status=".Reservation::STATUS_CANCELED);
        $response->assertOk();
        $response->assertJsonCount(1,"data.data");
        $this->assertEquals($office->id, $response->json()["data"]["data"][0]["office_id"]);
        $this->assertEquals($user->id, $response->json()["data"]["data"][0]["user_id"]);
        $this->assertEquals(Reservation::STATUS_CANCELED, $response->json()["data"]["data"][0]["status"]);
    }
    /**
     * @test
     */
    public function it_filters_my_offices_reservations_by_date(): void
    {
        $host = User::factory()->create();
        $office = Office::factory()->create(['user_id'=> $host->id]);
        $from = now()->subDays(1);
        $to = now()->addDays(7);
        Reservation::factory()->create(['office_id'=> $office->id,'start_date'=>now()->subWeek(),'end_date'=>now()->subDays(3)]);
        $reservation1 = Reservation::factory()->create(['office_id'=> $office->id,'start_date'=>now()->subDays(2),'end_date'=>$from]);
        $reservation2 = Reservation::factory()->create(['office_id'=> $office->id,'start_date'=>now(),'end_date'=>$to]);
        $reservation3 = Reservation::factory()->create(['office_id'=> $office->id,'start_date'=>now()->subDays(2),'end_date'=>now()->addDays(8)]);
        Reservation::factory()->create(['office_id'=> $office->id,'start_date'=>now()->addDays(8),'end_date'=>now()->addDays(14)]);
        $this->actingAs($host);
        $from = $from->format('Y-m-d');
        $to = $to->format('Y-m-d');
        $response = $this->get("/api/office-reservations?from_time=$from&to_time=$to");
        $response->assertOk();
        $response->assertJsonCount(3,"data.data");
        $response->assertJson([
            'success' => true,
            'status' => 200,
            "data"=> [
                "data" => [
                    ["id" => $reservation1->id],
                    ["id" => $reservation2->id],
                    ["id" => $reservation3->id],
                ]
            ]
        ]);
    }
    /**
     * @test
     */
    public function it_dosnt_makes_reservations_if_office_not_available(): void
    {
        $user = User::factory()->create();
        $office = Office::factory()->create();
        $from = now()->subDays(1);
        $to = now()->addWeek();
        Reservation::factory()->create(['office_id'=> $office->id,'start_date'=>now(),'end_date'=>$to,'status'=> Reservation::STATUS_ACTIVE]);
        $this->actingAs($user);
        $from = $from->format('Y-m-d');
        $to = $to->format('Y-m-d');
        $response = $this->post("/api/reservations",[
            'office_id' => $office->id,
            'start_date' => $from,
            'end_date' => $to,
        ]);
        $response->assertSessionHasErrors(['office_id']);
    }
    /**
     * @test
     */
    public function it_dosnt_makes_reservations_if_office_belongs_to_current_user(): void
    {
        $user = User::factory()->create();
        $office = Office::factory()->create(['user_id'=>$user->id]);
        $from = now()->subDays(1);
        $to = now()->addWeek();
        Reservation::factory()->create(['office_id'=> $office->id,'start_date'=>now(),'end_date'=>$to,'status'=> Reservation::STATUS_ACTIVE]);
        $this->actingAs($user);
        $from = $from->format('Y-m-d');
        $to = $to->format('Y-m-d');
        $response = $this->post("/api/reservations",[
            'office_id' => $office->id,
            'start_date' => $from,
            'end_date' => $to,
        ]);
        $response->assertSessionHasErrors(['office_id']);
    }
    /**
     * @test
     */
    public function it_makes_reservations(): void
    {
        $user = User::factory()->create();
        $office = Office::factory()->create();
        $from = now()->subDays(1);
        $to = now()->addWeek();
        $this->actingAs($user);
        $from = $from->format('Y-m-d');
        $to = $to->format('Y-m-d');
        $response = $this->post("/api/reservations",[
            'office_id' => $office->id,
            'start_date' => $from,
            'end_date' => $to,
        ]);
        $response->assertOk();
    }
}
