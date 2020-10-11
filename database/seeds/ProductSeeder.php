<?php

use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Product;

class ProductSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    factory(Product::class, 25)->create();
  }
}
